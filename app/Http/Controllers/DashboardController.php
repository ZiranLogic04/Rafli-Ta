<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $letterTypes = \App\Models\LetterType::all();

        if ($user->role === 'admin') {
            $letters = \App\Models\Letter::with(['user', 'type'])->latest()->take(5)->get();
            $stats = [
                'pending' => \App\Models\Letter::where('status', 'pending')->count(),
                'approved' => \App\Models\Letter::where('status', 'approved')->count(),
                'rejected' => \App\Models\Letter::where('status', 'rejected')->count(),
                'users' => \App\Models\User::count(),
            ];
            return view('admin.dashboard', compact('letters', 'letterTypes', 'stats'));
        }

        $letters = \App\Models\Letter::where('user_id', $user->id)->with('type')->latest()->take(5)->get();
        $letterTypes = $user->letterTypes;
        return view('user.dashboard', compact('letters', 'letterTypes'));
    }

    public function apiIndex()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $letters = \App\Models\Letter::with(['user', 'type'])->latest()->take(5)->get();
            $letterTypes = \App\Models\LetterType::all();
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
                'stats' => $stats,
            ]);
        }

        $letters = \App\Models\Letter::where('user_id', $user->id)->with('type')->latest()->take(5)->get();
        $letterTypes = $user->letterTypes;

        return response()->json([
            'user' => $user,
            'letters' => $letters,
            'letterTypes' => $letterTypes,
        ]);
    }
}
