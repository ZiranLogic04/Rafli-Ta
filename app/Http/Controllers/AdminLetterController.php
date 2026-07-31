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
            $baseQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('target_user_id', $user->id);
            });
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

        $total = (clone $baseQuery)->count();
        $letterTypes = \App\Models\LetterType::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'letters' => $query->get(),
            'total' => $total,
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

        $uniqueNumber = \App\Models\Letter::makeUniqueLetterNumber($request->letter_number, $id);

        $letter = \App\Models\Letter::findOrFail($id);
        $letter->update(['letter_number' => $uniqueNumber]);

        return response()->json(['message' => 'Nomor surat berhasil diperbarui.', 'letter' => $letter]);
    }
}
