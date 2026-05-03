<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
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
                'total' => \App\Models\Letter::count(),
                'types' => \App\Models\LetterType::count(),
                'users' => \App\Models\User::count(),
            ];

            return response()->json([
                'user' => $user,
                'letters' => $letters,
                'letterTypes' => $letterTypes,
                'stats' => $stats,
            ]);
        }

        $letters = \App\Models\Letter::where('user_id', $user->id)->with('type.parent')->latest()->take(5)->get();
        $letterTypes = $user->allowedLetterTypes()->get();
        $total = \App\Models\Letter::where('user_id', $user->id)->count();

        return response()->json([
            'user' => $user,
            'letters' => $letters,
            'letterTypes' => $letterTypes,
            'stats' => [
                'total' => $total,
            ],
        ]);
    }
}
