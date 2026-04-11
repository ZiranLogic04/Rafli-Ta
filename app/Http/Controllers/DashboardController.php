<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    private function approvalInboxCount($user): int
    {
        return \App\Models\Letter::where('status', 'pending')
            ->where(function ($q) use ($user) {
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
            })->count();
    }

    public function apiIndex()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $letters = \App\Models\Letter::with(['user', 'type.parent'])->latest()->take(5)->get();
            $letterTypes = \App\Models\LetterType::with('parent')
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('name')
                ->get();
            $stats = [
                'pending' => \App\Models\Letter::where('status', 'pending')->count(),
                'approved' => \App\Models\Letter::where('status', 'approved')->count(),
                'rejected' => \App\Models\Letter::where('status', 'rejected')->count(),
                'users' => \App\Models\User::count(),
            ];

            return response()->json([
                'user' => $user,
                'letters' => $letters,
                'letterTypes' => $letterTypes,
                'stats' => [
                    'total' => $stats['pending'] + $stats['approved'] + $stats['rejected'],
                    'pending' => $stats['pending'],
                    'approved' => $stats['approved'],
                    'rejected' => $stats['rejected'],
                    'users' => $stats['users'],
                    'approvalInboxCount' => $this->approvalInboxCount($user),
                ],
            ]);
        }

        $letters = \App\Models\Letter::where('user_id', $user->id)->with('type.parent')->latest()->take(5)->get();
        $letterTypes = $user->allowedLetterTypes()->get();
        $baseQuery = \App\Models\Letter::where('user_id', $user->id);
        $approvalInboxCount = $this->approvalInboxCount($user);

        return response()->json([
            'user' => $user,
            'letters' => $letters,
            'letterTypes' => $letterTypes,
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
                'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
                'users' => 0,
                'approvalInboxCount' => $approvalInboxCount,
            ],
        ]);
    }
}
