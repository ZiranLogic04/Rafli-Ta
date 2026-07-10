<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function apiIndex(Request $request)
    {
        $query = auth()->user()->letters()->with(['type.parent', 'targetUser'])->latest();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhereHas('type', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query->paginate(10)->through(function ($letter) {
            return [
                'id' => $letter->id,
                'letter_number' => $letter->letter_number,
                'status' => $letter->status,
                'rejection_note' => $letter->rejection_note,
                'created_at' => $letter->created_at,
                'type' => $letter->type ? [
                    'id' => $letter->type->id,
                    'name' => $letter->type->name,
                    'parent' => $letter->type->parent ? [
                        'id' => $letter->type->parent->id,
                        'name' => $letter->type->parent->name,
                    ] : null,
                ] : null,
                'target' => $letter->target_info,
                'target_info' => $letter->target_info,
                'signatory_name' => $letter->signatory_name,
                'notes' => $letter->notes,
                'file_path' => $letter->file_path,
            ];
        });

        return response()->json($letters);
    }

    public function apiCreateData(Request $request)
    {
        $typeId = $request->type_id;
        $letterType = \App\Models\LetterType::with([
            'parent',
            'children' => fn ($query) => $query->orderBy('name'),
        ])->findOrFail($typeId);

        $targets = \App\Models\User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'jurusan', 'wadir_level']);

        $prodis = \App\Models\Prodi::orderBy('name')->get();

        return response()->json([
            'letterType' => $letterType,
            'childTypes' => $letterType->children,
            'targets' => $targets,
            'prodis' => $prodis,
        ]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'letter_type_id' => 'required|exists:letter_types,id',
            'signatory_name' => 'required|string|max:255',
            'target_user_id' => 'nullable|exists:users,id',
            'target_name' => 'nullable|string|max:255',
            'target_jurusan' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (!$request->target_user_id && !$request->target_name) {
            return response()->json(['message' => 'Tujuan surat harus diisi.'], 422);
        }

        $targetUser = null;
        if ($request->filled('target_user_id')) {
            $targetUser = \App\Models\User::find($request->target_user_id);
        }

        $letterType = \App\Models\LetterType::findOrFail($request->letter_type_id);

        // Ambil jurusan langsung dari apa yang dikirim formulir
        $jurusan = $request->target_jurusan;
        
        $letterNumber = $this->generateLetterNumber($letterType, $jurusan);
        
        // Check if letter number already exists
        if (\App\Models\Letter::where('letter_number', $letterNumber)->exists()) {
            return response()->json(['message' => 'Nomor surat ' . $letterNumber . ' sudah ada di sistem. Mohon cek kembali.'], 422);
        }

        $letter = \App\Models\Letter::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'letter_type_id' => $request->letter_type_id,
            'target_user_id' => $targetUser?->id,
            'target_name' => $targetUser ? $targetUser->name : $request->target_name,
            'target_role' => $targetUser ? $targetUser->role : null,
            'target_wadir_level' => $targetUser ? $targetUser->wadir_level : null,
            'target_jurusan' => $jurusan,
            'signatory_name' => $request->signatory_name,
            'notes' => $request->notes,
            'status' => 'approved',
            'letter_number' => $letterNumber,
            'approved_at' => now(),
            'approved_by' => auth()->id(), // Auto-approved by creator/system
        ]);

        return response()->json(['message' => 'Surat berhasil dicatat.', 'letter' => $letter]);
    }

    public function updateLetterNumber(Request $request, \App\Models\Letter $letter)
    {
        $request->validate([
            'letter_number' => 'required|string|unique:letters,letter_number,' . $letter->id,
        ]);

        $letter->update([
            'letter_number' => $request->letter_number
        ]);

        return response()->json(['message' => 'Nomor surat berhasil diperbarui.']);
    }

    private function generateLetterNumber($letterType, $targetJurusan = null)
    {
        $year = now()->year;
        
        $count = \App\Models\Letter::where('letter_type_id', $letterType->id)->count();
            
        $newSequence = $count + 1;
        $paddingNo = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
        
        $romanMonth = $this->getRomanMonth(now()->month);
        
        $format = $letterType->code_format;
        
        $letterCode = $letterType->code;

        if (!$format) {
            return "{$paddingNo}/{$letterCode}/PP/{$romanMonth}/{$year}";
        }
        
        $prodiCode = $targetJurusan ? $this->getProdiCode($targetJurusan) : 'UMUM';
        
        $result = str_replace(
            ['{no}', '{kode}', '{bln}', '{thn}', '{prodi}', '{Prodi}', '{extra}', '{Extra}'],
            [$paddingNo, $letterCode, $romanMonth, $year, $prodiCode, $prodiCode, $prodiCode, $prodiCode],
            $format
        );
        
        return $result;
    }
    
    private function getProdiCode($jurusan)
    {
        $prodi = \App\Models\Prodi::where('name', $jurusan)->first();
        if ($prodi) {
            return $prodi->code;
        }
        
        // Fallback jika tidak ditemukan di DB (untuk data lama atau input manual baru)
        $map = [
            'teknik informatika' => 'TI',
            'sistem informasi' => 'SI',
            'teknik mesin' => 'TM',
            'teknik elektro' => 'TE',
            'manajemen bisnis' => 'MB',
            'administrasi perkantoran' => 'AP',
            'akuntansi' => 'AK',
            'manajemen pemasaran' => 'MP',
        ];
        
        $key = strtolower(trim($jurusan));
        return $map[$key] ?? strtoupper(substr($jurusan, 0, 2));
    }
    
    private function getRomanMonth($month)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        return $map[$month];
    }

    public function download($id)
    {
        $user = auth()->user();
        $letter = \App\Models\Letter::with('type')->findOrFail($id);

        if ($user->role !== 'admin' && $letter->user_id !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengunduh surat ini.');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::path($letter->file_path);

        if (! \Illuminate\Support\Facades\Storage::exists($letter->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        \Log::info('Download Letter ID: '.$id.' - Status: '.$letter->status);

        if ($letter->status === 'approved') {
            \Log::info('Approved DOCX downloaded directly without conversion.');
        } else {
            \Log::info('Condition for PDF not met (or disabled): Status='.$letter->status);
        }

        return response()->download($fullPath);
    }

    public function apiDestroy($id)
    {
        $letter = \App\Models\Letter::findOrFail($id);

        if ($letter->user_id !== auth()->id()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus surat ini.'], 403);
        }

        if ($letter->file_path && \Illuminate\Support\Facades\Storage::exists($letter->file_path)) {
            \Illuminate\Support\Facades\Storage::delete($letter->file_path);
        }

        $letter->delete();

        return response()->json(['message' => 'Surat berhasil dihapus.']);
    }



    private function formatTargetInfo($letter): string
    {
        if ($letter->target_name) {
            $info = $letter->target_name;
            if ($letter->target_role === 'wadir' && $letter->target_wadir_level) {
                $info .= ' (Wadir '.$letter->target_wadir_level.')';
            }
            if (in_array($letter->target_role, ['kaprodi', 'dosen']) && $letter->target_jurusan) {
                $info .= ' - '.$letter->target_jurusan;
            }

            return $info;
        }

        $roleLabels = [
            'direktur' => 'Direktur',
            'wadir' => 'Wadir',
            'kaprodi' => 'Kaprodi',
            'staf' => 'Staf TU',
            'dosen' => 'Dosen',
        ];

        $info = $roleLabels[$letter->target_role] ?? $letter->target_role;

        if ($letter->target_role === 'wadir' && $letter->target_wadir_level) {
            $info .= ' Level '.$letter->target_wadir_level;
        }

        if (in_array($letter->target_role, ['kaprodi', 'dosen']) && $letter->target_jurusan) {
            $info .= ' - '.$letter->target_jurusan;
        }

        return $info;
    }
}
