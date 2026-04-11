<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function apiIndex(Request $request)
    {
        $query = auth()->user()->letters()->with(['type.parent', 'targetUser'])->latest();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhereHas('type', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query->paginate(10)->through(function ($letter) {
            return [
                'id' => $letter->id,
                'letter_number' => $letter->letter_number,
                'status' => $letter->status,
                'rejection_note' => $letter->rejection_note,
                'created_at' => $letter->created_at,
                'type' => $letter->type ? [
                    'id' => $letter->type->id,
                    'name' => $letter->type->name,
                    'parent' => $letter->type->parent ? [
                        'id' => $letter->type->parent->id,
                        'name' => $letter->type->parent->name,
                    ] : null,
                ] : null,
                'target' => $this->formatTargetInfo($letter),
            ];
        });

        return response()->json($letters);
    }

    public function apiCreateData(Request $request)
    {
        $typeId = $request->type_id;
        $letterType = \App\Models\LetterType::with([
            'parent',
            'children' => fn ($query) => $query->orderBy('name'),
        ])->findOrFail($typeId);

        $targets = \App\Models\User::query()
            ->where('id', '!=', auth()->id())
            ->whereIn('role', ['direktur', 'wadir', 'kaprodi', 'staf', 'dosen'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'code', 'wadir_level', 'jurusan']);

        $jurusanOptions = $targets
            ->pluck('jurusan')
            ->filter(fn ($j) => ! empty($j))
            ->unique()
            ->values();

        return response()->json([
            'letterType' => $letterType,
            'childTypes' => $letterType->children,
            'targetRoles' => [
                ['value' => 'direktur', 'label' => 'Direktur'],
                ['value' => 'wadir', 'label' => 'Wadir'],
                ['value' => 'kaprodi', 'label' => 'Kaprodi'],
                ['value' => 'staf', 'label' => 'Staf TU'],
                ['value' => 'dosen', 'label' => 'Dosen'],
            ],
            'wadirLevels' => [1, 2, 3],
            'jurusanOptions' => $jurusanOptions,
            'targets' => $targets,
        ]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'letter_type_id' => 'required|exists:letter_types,id',
            'target_role' => 'required|in:direktur,wadir,kaprodi,staf,dosen',
            'target_user_id' => 'nullable|exists:users,id',
            'target_wadir_level' => 'nullable|integer|in:1,2,3',
            'target_jurusan' => 'nullable|string|max:255',
            'file' => 'required|mimes:pdf,docx,doc|max:5120',
        ]);

        if ($request->target_role === 'wadir' && ! $request->filled('target_wadir_level')) {
            return response()->json(['message' => 'Target Wadir wajib memilih level 1-3.'], 422);
        }

        if (in_array($request->target_role, ['kaprodi', 'dosen']) && ! $request->filled('target_jurusan')) {
            return response()->json(['message' => 'Target ini wajib memilih jurusan.'], 422);
        }

        $targetUser = null;
        if ($request->filled('target_user_id')) {
            $targetUser = \App\Models\User::find($request->target_user_id);
        } else {
            $candidateQuery = \App\Models\User::query()
                ->where('id', '!=', auth()->id())
                ->where('role', $request->target_role);

            if ($request->target_role === 'wadir' && $request->filled('target_wadir_level')) {
                $candidateQuery->where('wadir_level', $request->target_wadir_level);
            }

            if (in_array($request->target_role, ['kaprodi', 'dosen']) && $request->filled('target_jurusan')) {
                $candidateQuery->whereRaw('LOWER(jurusan) = ?', [strtolower((string) $request->target_jurusan)]);
            }

            $candidates = $candidateQuery->get();

            if ($candidates->count() === 1) {
                $targetUser = $candidates->first();
            } elseif ($candidates->count() === 0) {
                return response()->json(['message' => 'Tidak ada akun tujuan yang cocok untuk pilihan ini.'], 422);
            } elseif ($candidates->count() > 1) {
                return response()->json(['message' => 'Terdapat lebih dari satu tujuan yang cocok. Pilih nama tujuan spesifik.'], 422);
            }
        }

        if ($targetUser) {
            if (! $targetUser || $targetUser->role !== $request->target_role) {
                return response()->json(['message' => 'Nama tujuan tidak sesuai role yang dipilih.'], 422);
            }

            if ($request->target_role === 'wadir' && (int) $targetUser->wadir_level !== (int) $request->target_wadir_level) {
                return response()->json(['message' => 'Nama Wadir tidak sesuai pilihan level.'], 422);
            }

            if (in_array($request->target_role, ['kaprodi', 'dosen'])) {
                $userJurusan = trim((string) $targetUser->jurusan);
                $inputJurusan = trim((string) $request->target_jurusan);
                if (mb_strtolower($userJurusan) !== mb_strtolower($inputJurusan)) {
                    return response()->json(['message' => 'Nama tujuan tidak sesuai jurusan yang dipilih.'], 422);
                }
            }
        }

        $file = $request->file('file');
        $filename = time().'_'.auth()->id().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('letters/'.date('Y/m'), $filename);

        $letter = \App\Models\Letter::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'letter_type_id' => $request->letter_type_id,
            'target_user_id' => $targetUser?->id,
            'target_name' => $targetUser?->name,
            'target_role' => $request->target_role,
            'target_wadir_level' => $request->target_role === 'wadir' ? $request->target_wadir_level : null,
            'target_jurusan' => in_array($request->target_role, ['kaprodi', 'dosen']) ? $request->target_jurusan : null,
            'file_path' => $path,
            'status' => 'pending',
            'current_approver_role' => $request->target_role,
        ]);

        return response()->json(['message' => 'Surat berhasil diajukan.', 'letter' => $letter]);
    }

    public function download($id)
    {
        $user = auth()->user();
        $letter = \App\Models\Letter::with('type')->findOrFail($id);

        if ($user->role !== 'admin' && $letter->user_id !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengunduh surat ini.');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::path($letter->file_path);

        if (! \Illuminate\Support\Facades\Storage::exists($letter->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        \Log::info('Download Letter ID: '.$id.' - Status: '.$letter->status);

        if ($letter->status === 'approved') {
            \Log::info('Approved DOCX downloaded directly without conversion.');
        } else {
            \Log::info('Condition for PDF not met (or disabled): Status='.$letter->status);
        }

        return response()->download($fullPath);
    }

    public function downloadTemplate($id)
    {
        $type = \App\Models\LetterType::findOrFail($id);
        if (! $type->template_path || ! \Illuminate\Support\Facades\Storage::exists($type->template_path)) {
            return response()->json(['message' => 'Template belum tersedia.'], 404);
        }

        $filename = $type->original_filename ?? basename($type->template_path);

        return response()->download(\Illuminate\Support\Facades\Storage::path($type->template_path), $filename);
    }

    private function formatTargetInfo($letter): string
    {
        if ($letter->target_name) {
            $info = $letter->target_name;
            if ($letter->target_role === 'wadir' && $letter->target_wadir_level) {
                $info .= ' (Wadir '.$letter->target_wadir_level.')';
            }
            if (in_array($letter->target_role, ['kaprodi', 'dosen']) && $letter->target_jurusan) {
                $info .= ' - '.$letter->target_jurusan;
            }

            return $info;
        }

        $roleLabels = [
            'direktur' => 'Direktur',
            'wadir' => 'Wadir',
            'kaprodi' => 'Kaprodi',
            'staf' => 'Staf TU',
            'dosen' => 'Dosen',
        ];

        $info = $roleLabels[$letter->target_role] ?? $letter->target_role;

        if ($letter->target_role === 'wadir' && $letter->target_wadir_level) {
            $info .= ' Level '.$letter->target_wadir_level;
        }

        if (in_array($letter->target_role, ['kaprodi', 'dosen']) && $letter->target_jurusan) {
            $info .= ' - '.$letter->target_jurusan;
        }

        return $info;
    }
}
