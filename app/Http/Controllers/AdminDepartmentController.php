<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class AdminDepartmentController extends Controller
{
    public function apiIndex()
    {
        $departments = Department::orderBy('name')->get();

        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        $department = Department::create($request->only(['name', 'code']));

        return response()->json(['message' => 'Jurusan berhasil ditambahkan.', 'department' => $department]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,'.$id,
            'code' => 'required|string|max:50|unique:departments,code,'.$id,
        ]);

        $department = Department::findOrFail($id);
        $department->update($request->only(['name', 'code']));

        return response()->json(['message' => 'Jurusan berhasil diperbarui.', 'department' => $department]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json(['message' => 'Jurusan berhasil dihapus.']);
    }
}
