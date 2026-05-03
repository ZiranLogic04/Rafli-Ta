<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
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
            'wadir_level' => 'nullable|integer|in:1,2,3',
            'jurusan' => 'nullable|string|max:255',
            'password' => 'required|min:8',
            'letter_types' => 'array',
            'letter_types.*' => 'exists:letter_types,id',
        ]);

        if ($request->role === 'wadir' && ! $request->filled('wadir_level')) {
            return response()->json(['message' => 'Wadir wajib memilih level 1-3.'], 422);
        }

        if (in_array($request->role, ['kaprodi', 'dosen']) && ! $request->filled('jurusan')) {
            return response()->json(['message' => 'Role ini wajib mengisi jurusan.'], 422);
        }

        if ($request->role === 'direktur' && User::where('role', 'direktur')->exists()) {
            return response()->json(['message' => 'Akun direktur hanya boleh satu.'], 422);
        }

        if ($request->role === 'wadir' && User::where('role', 'wadir')->where('wadir_level', $request->wadir_level)->exists()) {
            return response()->json(['message' => 'Wadir pada level ini sudah ada.'], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'wadir_level' => $request->role === 'wadir' ? $request->wadir_level : null,
            'jurusan' => in_array($request->role, ['kaprodi', 'dosen']) ? $request->jurusan : null,
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
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:admin,direktur,kaprodi,wadir,staf,dosen',
            'wadir_level' => 'nullable|integer|in:1,2,3',
            'jurusan' => 'nullable|string|max:255',
            'password' => 'nullable|min:8',
        ]);

        if ($request->role === 'wadir' && ! $request->filled('wadir_level')) {
            return response()->json(['message' => 'Wadir wajib memilih level 1-3.'], 422);
        }

        if (in_array($request->role, ['kaprodi', 'dosen']) && ! $request->filled('jurusan')) {
            return response()->json(['message' => 'Role ini wajib mengisi jurusan.'], 422);
        }

        if ($request->role === 'direktur' && User::where('role', 'direktur')->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Akun direktur hanya boleh satu.'], 422);
        }

        if ($request->role === 'wadir' && User::where('role', 'wadir')->where('wadir_level', $request->wadir_level)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Wadir pada level ini sudah ada.'], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'wadir_level' => $request->role === 'wadir' ? $request->wadir_level : null,
            'jurusan' => in_array($request->role, ['kaprodi', 'dosen']) ? $request->jurusan : null,
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
