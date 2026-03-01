<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LetterTypeController extends Controller
{
    public function index()
    {
        $types = \App\Models\LetterType::all();
        return view('admin.types.index', compact('types'));
    }

    public function apiIndex()
    {
        $types = \App\Models\LetterType::all();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:letter_types,code',
            'template_path' => 'nullable|file|mimes:docx',
        ]);

        $path = null;
        $originalFilename = null;
        if ($request->hasFile('template_path')) {
            $file = $request->file('template_path');
            $originalFilename = $file->getClientOriginalName();
            $path = $file->store('templates');
        }

        $type = \App\Models\LetterType::create([
            'name' => $request->name,
            'code' => $request->code,
            'template_path' => $path,
            'original_filename' => $originalFilename,
        ]);

        return response()->json(['message' => 'Jenis surat berhasil ditambahkan.', 'type' => $type]);
    }

    public function update(Request $request, $id)
    {
        $type = \App\Models\LetterType::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:letter_types,code,' . $id,
            'template_path' => 'nullable|file|mimes:docx',
        ]);

        $data = $request->only('name', 'code');

        if ($request->hasFile('template_path')) {
            if ($type->template_path && \Illuminate\Support\Facades\Storage::exists($type->template_path)) {
                \Illuminate\Support\Facades\Storage::delete($type->template_path);
            }

            $file = $request->file('template_path');
            $data['original_filename'] = $file->getClientOriginalName();
            $data['template_path'] = $file->store('templates');
        }

        $type->update($data);

        return response()->json(['message' => 'Jenis surat berhasil diupdate.']);
    }

    public function destroy($id)
    {
        $type = \App\Models\LetterType::findOrFail($id);
        
        if ($type->template_path && \Illuminate\Support\Facades\Storage::exists($type->template_path)) {
            \Illuminate\Support\Facades\Storage::delete($type->template_path);
        }

        $type->delete();
        
        return response()->json(['message' => 'Jenis surat berhasil dihapus.']);
    }

    public function download($id)
    {
        $type = \App\Models\LetterType::findOrFail($id);
        
        if (!$type->template_path) {
            abort(404, 'Template tidak ditemukan');
        }

        $filename = $type->original_filename ?? basename($type->template_path);
        
        return \Illuminate\Support\Facades\Storage::download($type->template_path, $filename);
    }
}
