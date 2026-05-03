<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Prodi::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:prodis,name',
            'code' => 'required|unique:prodis,code',
        ]);

        $prodi = \App\Models\Prodi::create($request->all());
        return response()->json($prodi);
    }

    public function update(Request $request, $id)
    {
        $prodi = \App\Models\Prodi::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:prodis,name,' . $prodi->id,
            'code' => 'required|unique:prodis,code,' . $prodi->id,
        ]);

        $prodi->update($request->all());
        return response()->json($prodi);
    }

    public function destroy($id)
    {
        \App\Models\Prodi::findOrFail($id)->delete();
        return response()->json(['message' => 'Prodi berhasil dihapus']);
    }
}
