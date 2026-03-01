<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function index()
    {
        $query = auth()->user()->letters()->with('type')->latest();
        
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('type', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $letters = $query->paginate(10)->withQueryString();
        $types = \App\Models\LetterType::all();

        return view('user.letters.index', compact('letters', 'types'));
    }

    public function apiIndex(Request $request)
    {
        $query = auth()->user()->letters()->with('type')->latest();
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('type', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $letters = $query->paginate(10);
        return response()->json($letters);
    }

    public function apiCreateData(Request $request)
    {
        $typeId = $request->type_id;
        $letterType = \App\Models\LetterType::findOrFail($typeId);
        return response()->json(['letterType' => $letterType]);
    }

    public function create()
    {
        $typeId = request('type_id');
        $letterType = \App\Models\LetterType::findOrFail($typeId);
        return view('user.letters.create', compact('letterType'));
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'letter_type_id' => 'required|exists:letter_types,id',
            'file' => 'required|mimes:pdf,docx,doc|max:2048',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . auth()->id() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('letters/' . date('Y/m'), $filename);

        $letter = \App\Models\Letter::create([
            'user_id' => auth()->id(),
            'letter_type_id' => $request->letter_type_id,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Surat berhasil diajukan.', 'letter' => $letter]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'letter_type_id' => 'required|exists:letter_types,id',
            'file' => 'required|mimes:pdf,docx,doc|max:2048',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . auth()->id() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('letters/' . date('Y/m'), $filename);

        \App\Models\Letter::create([
            'user_id' => auth()->id(),
            'letter_type_id' => $request->letter_type_id,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Surat berhasil diajukan dan menunggu persetujuan.');
    }

    public function download($id)
    {
        $letter = \App\Models\Letter::with('type')->findOrFail($id);
        $fullPath = \Illuminate\Support\Facades\Storage::path($letter->file_path);

        if (!\Illuminate\Support\Facades\Storage::exists($letter->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        \Log::info("Download Letter ID: " . $id . " - Status: " . $letter->status);
        
        // Revert to direct DOCX download (preserving 100% fidelity)
        if ($letter->status === 'approved') {
            \Log::info("Approved DOCX downloaded directly without conversion.");
        } else {
            \Log::info("Condition for PDF not met (or disabled): Status=" . $letter->status);
        }

        return response()->download($fullPath);
    }

    public function downloadTemplate($id)
    {
        $type = \App\Models\LetterType::findOrFail($id);
        if (!$type->template_path || !\Illuminate\Support\Facades\Storage::exists($type->template_path)) {
            return response()->json(['message' => 'Template belum tersedia.'], 404);
        }
        
        $filename = $type->original_filename ?? basename($type->template_path);
        return response()->download(\Illuminate\Support\Facades\Storage::path($type->template_path), $filename);
    }

}
