<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLetterController extends Controller
{
    public function apiIndex(Request $request)
    {
        $query = \App\Models\Letter::with(['user', 'type.parent', 'targetUser'])->latest();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('type', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('target_role', 'like', "%{$search}%")
                    ->orWhere('target_jurusan', 'like', "%{$search}%")
                    ->orWhereHas('targetUser', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type_id')) {
            $query->where('letter_type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $letters = $query->paginate(10);
        $letterTypes = \App\Models\LetterType::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'letters' => $letters,
            'total' => \App\Models\Letter::count(),
            'pending' => \App\Models\Letter::where('status', 'pending')->count(),
            'approved' => \App\Models\Letter::where('status', 'approved')->count(),
            'rejected' => \App\Models\Letter::where('status', 'rejected')->count(),
            'letterTypes' => $letterTypes,
        ]);
    }

    public function apiInbox(Request $request)
    {
        $user = $request->user();

        $baseQuery = \App\Models\Letter::with(['user', 'type.parent', 'targetUser']);

        if ($user->role !== 'admin') {
            $baseQuery->where('status', 'pending');
            $this->applyInboxScope($baseQuery, $user);
        }

        $query = (clone $baseQuery)->latest();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('type', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type_id')) {
            $query->where('letter_type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $total = (clone $baseQuery)->count();
        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $approved = (clone $baseQuery)->where('status', 'approved')->count();
        $rejected = (clone $baseQuery)->where('status', 'rejected')->count();
        $letterTypes = \App\Models\LetterType::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'letters' => $query->paginate(10),
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'letterTypes' => $letterTypes,
        ]);
    }

    public function apiShow($id)
    {
        $letter = \App\Models\Letter::with(['user', 'type.parent', 'targetUser'])->findOrFail($id);

        return response()->json($letter);
    }

    public function show($id)
    {
        $letter = \App\Models\Letter::with(['user', 'type'])->findOrFail($id);

        return response()->download(\Illuminate\Support\Facades\Storage::path($letter->file_path));
    }

    public function updateLetterNumber(Request $request, $id)
    {
        $request->validate([
            'letter_number' => 'required|string|max:255',
        ]);

        $letter = \App\Models\Letter::findOrFail($id);
        $letter->update(['letter_number' => $request->letter_number]);

        return response()->json(['message' => 'Nomor surat berhasil diperbarui.', 'letter' => $letter]);
    }

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $letter = \App\Models\Letter::with(['user', 'type', 'targetUser'])->where('id', $id)->lockForUpdate()->firstOrFail();
                $actor = auth()->user();

                if (! $this->canApprove($letter, $actor)) {
                    throw new \Exception('Anda tidak memiliki akses menyetujui surat ini.');
                }

                if ($letter->status !== 'pending') {
                    throw new \Exception('Surat sudah diproses sebelumnya.');
                }

                $fullNumber = $letter->letter_number ?: $this->generateLetterNumber($letter);

                $letter->update([
                    'letter_number' => $fullNumber,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $actor->id,
                    'current_approver_role' => null,
                ]);
            });

            return response()->json(['message' => 'Surat berhasil disetujui.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: '.$e->getMessage()], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_note' => 'required|string']);

        $letter = \App\Models\Letter::with('targetUser')->findOrFail($id);
        $actor = auth()->user();

        if (! $this->canApprove($letter, $actor)) {
            return response()->json(['message' => 'Anda tidak memiliki akses menolak surat ini.'], 403);
        }

        if ($letter->status !== 'pending') {
            return response()->json(['message' => 'Surat tidak dalam status menunggu.'], 422);
        }

        $letter->update([
            'status' => 'rejected',
            'rejection_note' => $request->rejection_note,
            'current_approver_role' => null,
        ]);

        return response()->json(['message' => 'Surat telah ditolak.']);
    }

    private function canApprove($letter, $actor): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }

        if (! $letter->current_approver_role || $letter->current_approver_role !== $actor->role) {
            return false;
        }

        if ($letter->target_user_id) {
            return (int) $letter->target_user_id === (int) $actor->id;
        }

        $requiredRole = $letter->target_role ?: $letter->current_approver_role;
        if ($requiredRole && $actor->role !== $requiredRole) {
            return false;
        }

        if ($letter->target_role === 'wadir' && ! is_null($letter->target_wadir_level)) {
            if ((int) $actor->wadir_level !== (int) $letter->target_wadir_level) {
                return false;
            }
        }

        if (in_array($letter->target_role, ['kaprodi', 'dosen']) && ! empty($letter->target_jurusan)) {
            if (strtolower((string) $actor->jurusan) !== strtolower((string) $letter->target_jurusan)) {
                return false;
            }
        }

        return true;
    }

    private function applyInboxScope($query, $user): void
    {
        $query->where(function ($q) use ($user) {
            $q->where('target_user_id', $user->id)
                ->orWhere(function ($sub) use ($user) {
                    $sub->whereNull('target_user_id')
                        ->where('current_approver_role', $user->role);

                    if ($user->role === 'wadir' && ! is_null($user->wadir_level)) {
                        $sub->where(function ($w) use ($user) {
                            $w->whereNull('target_wadir_level')
                                ->orWhere('target_wadir_level', $user->wadir_level);
                        });
                    }

                    if (in_array($user->role, ['kaprodi', 'dosen']) && ! empty($user->jurusan)) {
                        $sub->where(function ($j) use ($user) {
                            $j->whereNull('target_jurusan')
                                ->orWhereRaw('LOWER(target_jurusan) = ?', [strtolower((string) $user->jurusan)]);
                        });
                    }
                });
        });
    }

    private function generateLetterNumber($letter): string
    {
        $year = now()->year;
        $lastNumber = \App\Models\Letter::whereYear('approved_at', $year)
            ->lockForUpdate()
            ->max(DB::raw('CAST(SUBSTRING_INDEX(letter_number, "/", 1) AS UNSIGNED)'));

        $newSequence = $lastNumber ? $lastNumber + 1 : 1;
        $paddingNo = str_pad($newSequence, 3, '0', STR_PAD_LEFT);

        $romanMonth = $this->getRomanMonth(now()->month);
        $letterCode = $letter->type->code;
        $userCode = $letter->user->code ?? 'UMUM';

        return "{$paddingNo}/{$letterCode}/{$userCode}/{$romanMonth}/{$year}";
    }

    private function getRomanMonth($month)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month];
    }
}
