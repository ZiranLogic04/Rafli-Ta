<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminLetterController extends Controller
{
    public function index()
    {
        $query = \App\Models\Letter::with(['user', 'type'])->latest();
        
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('type', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $letters = $query->paginate(10)->withQueryString();
        
        $total = \App\Models\Letter::count();
        $pending = \App\Models\Letter::where('status', 'pending')->count();
        $approved = \App\Models\Letter::where('status', 'approved')->count();
        $rejected = \App\Models\Letter::where('status', 'rejected')->count();

        return view('admin.letters.index', compact('letters', 'total', 'pending', 'approved', 'rejected'));
    }

    public function apiIndex(Request $request)
    {
        $query = \App\Models\Letter::with(['user', 'type'])->latest();
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('type', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $letters = $query->paginate(10);
        
        return response()->json([
            'letters' => $letters,
            'total' => \App\Models\Letter::count(),
            'pending' => \App\Models\Letter::where('status', 'pending')->count(),
            'approved' => \App\Models\Letter::where('status', 'approved')->count(),
            'rejected' => \App\Models\Letter::where('status', 'rejected')->count(),
        ]);
    }

    public function apiShow($id)
    {
        $letter = \App\Models\Letter::with(['user', 'type'])->findOrFail($id);
        return response()->json($letter);
    }

    public function show($id)
    {
        $letter = \App\Models\Letter::with(['user', 'type'])->findOrFail($id);
        return response()->download(\Illuminate\Support\Facades\Storage::path($letter->file_path));
    }

    public function approve($id)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $letter = \App\Models\Letter::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($letter->status !== 'pending') {
                    throw new \Exception("Surat sudah diproses sebelumnya.");
                }

                $year = now()->year;
                $lastNumber = \App\Models\Letter::whereYear('approved_at', $year)
                    ->lockForUpdate()
                    ->max(\Illuminate\Support\Facades\DB::raw('CAST(SUBSTRING_INDEX(letter_number, "/", 1) AS UNSIGNED)'));

                $newSequence = $lastNumber ? $lastNumber + 1 : 1;
                $paddingNo = str_pad($newSequence, 3, '0', STR_PAD_LEFT);

                $romanMonth = $this->getRomanMonth(now()->month);
                $letterCode = $letter->type->code;
                $userCode = $letter->user->code ?? 'UMUM';

                $fullNumber = "{$paddingNo}/{$letterCode}/{$userCode}/{$romanMonth}/{$year}";

                // Automation: Inject Number into .docx if possible
                if (class_exists('\PhpOffice\PhpWord\TemplateProcessor')) {
                    $fullPath = \Illuminate\Support\Facades\Storage::path($letter->file_path);
                    $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

                    if (strtolower($extension) === 'docx') {
                        try {
                            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);
                            $templateProcessor->setValue('letter_number', $fullNumber);
                            $templateProcessor->saveAs($fullPath);
                        } catch (\Exception $e) {
                            \Log::error("TemplateProcessor Error: " . $e->getMessage());
                        }
                    }
                }

                $letter->update([
                    'letter_number' => $fullNumber,
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            });

            return response()->json(['message' => 'Surat berhasil disetujui.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_note' => 'required|string']);

        $letter = \App\Models\Letter::findOrFail($id);
        
        if ($letter->status !== 'pending') {
            return response()->json(['message' => 'Surat tidak dalam status menunggu.'], 422);
        }

        $letter->update([
            'status' => 'rejected',
            'rejection_note' => $request->rejection_note,
        ]);

        return response()->json(['message' => 'Surat telah ditolak.']);
    }

    private function getRomanMonth($month)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month];
    }
}
