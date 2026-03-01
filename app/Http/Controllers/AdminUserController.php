<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('letterTypes')->get();
        $letterTypes = \App\Models\LetterType::all();
        return view('admin.users.index', compact('users', 'letterTypes'));
    }

    public function apiIndex()
    {
        $users = User::with('letterTypes')->get();
        $letterTypes = \App\Models\LetterType::all();
        return response()->json(['users' => $users, 'letterTypes' => $letterTypes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,direktur,kaprodi,wadir,staf,dosen',
            'code' => 'required|string|max:50|unique:users,code',
            'password' => 'required|min:8',
            'letter_types' => 'array',
            'letter_types.*' => 'exists:letter_types,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'code' => $request->code,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('letter_types')) {
            $user->letterTypes()->sync($request->letter_types);
        }

        return response()->json(['message' => 'Pengguna berhasil ditambahkan.', 'user' => $user->load('letterTypes')]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,direktur,kaprodi,wadir,staf,dosen',
            'code' => 'required|string|max:50|unique:users,code,' . $id,
            'password' => 'nullable|min:8',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'code' => $request->code,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('letter_types')) {
            $user->letterTypes()->sync($request->letter_types);
        } else {
            $user->letterTypes()->detach();
        }

        return response()->json(['message' => 'Data pengguna berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
