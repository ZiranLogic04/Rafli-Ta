<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LetterTypeController extends Controller
{
    public function apiIndex()
    {
        $types = \App\Models\LetterType::with(['parent', 'letters'])
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                $type->letter_count = $type->letters->count();

                return $type;
            });

        return response()->json($types);
    }

    public function publicIndex()
    {
        $types = \App\Models\LetterType::with('parent')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                $data = $type->toArray();
                $data['has_template'] = $type->template_path && \Illuminate\Support\Facades\Storage::exists($type->template_path);

                return $data;
            });

        return response()->json($types);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:letter_types,code',
            'parent_id' => 'nullable|exists:letter_types,id',
            'code_format' => 'nullable|string|max:255',
            'template_path' => 'nullable|file|mimes:docx',
        ]);

        if ($request->filled('parent_id')) {
            $parent = \App\Models\LetterType::findOrFail($request->parent_id);
            if ($parent->parent_id) {
                return response()->json(['message' => 'Subjenis hanya bisa ditempatkan di bawah kategori utama.'], 422);
            }
        }

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
            'parent_id' => $request->parent_id,
            'code_format' => $request->code_format,
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
            'code' => 'required|unique:letter_types,code,'.$id,
            'parent_id' => 'nullable|exists:letter_types,id',
            'code_format' => 'nullable|string|max:255',
            'template_path' => 'nullable|file|mimes:docx',
        ]);

        if ($request->filled('parent_id')) {
            $parent = \App\Models\LetterType::findOrFail($request->parent_id);
            if ((int) $parent->id === (int) $type->id) {
                return response()->json(['message' => 'Jenis surat tidak bisa menjadi induk bagi dirinya sendiri.'], 422);
            }

            if ($parent->parent_id) {
                return response()->json(['message' => 'Subjenis hanya bisa ditempatkan di bawah kategori utama.'], 422);
            }
        }

        $data = $request->only('name', 'code', 'parent_id', 'code_format');

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

        // Lepaskan relasi anak agar parent type aman dihapus
        \App\Models\LetterType::where('parent_id', $type->id)->update(['parent_id' => null]);

        if ($type->template_path && \Illuminate\Support\Facades\Storage::exists($type->template_path)) {
            \Illuminate\Support\Facades\Storage::delete($type->template_path);
        }

        $type->delete();

        return response()->json(['message' => 'Jenis surat berhasil dihapus.']);
    }

    public function download($id)
    {
        $type = \App\Models\LetterType::findOrFail($id);

        if (! $type->template_path) {
            abort(404, 'Template tidak ditemukan');
        }

        $filename = $type->original_filename ?? basename($type->template_path);

        return \Illuminate\Support\Facades\Storage::download($type->template_path, $filename);
    }

    public function apiRolePermissions($role)
    {
        $permissions = \App\Models\RoleLetterTypePermission::where('role', $role)->get();

        return response()->json($permissions);
    }

    public function apiSaveRolePermissions(Request $request, $role)
    {
        $request->validate([
            'letter_type_ids' => 'required|array',
            'letter_type_ids.*' => 'exists:letter_types,id',
        ]);

        \App\Models\RoleLetterTypePermission::where('role', $role)->delete();

        foreach ($request->letter_type_ids as $typeId) {
            \App\Models\RoleLetterTypePermission::create([
                'role' => $role,
                'letter_type_id' => $typeId,
            ]);
        }

        return response()->json(['message' => 'Izin surat berhasil diperbarui.']);
    }
}
