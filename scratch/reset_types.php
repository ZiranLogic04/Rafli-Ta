<?php

use App\Models\LetterType;
use App\Models\Letter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Matikan foreign key check
Schema::disableForeignKeyConstraints();

// Hapus semua data lama
Letter::truncate();
LetterType::truncate();

$types = [
    ['name' => 'Surat Tugas', 'code' => 'ST', 'code_format' => '{no}/ST/PP/{bln}/{thn}'],
    ['name' => 'Surat Pengantar', 'code' => 'PNG-DIR', 'code_format' => '{no}/Png-Dir/PP/{bln}/{thn}'],
    ['name' => 'Surat Pernyataan', 'code' => 'PR-DIR', 'code_format' => '{no}/Pr-Dir/PP/{bln}/{thn}'],
    ['name' => 'Surat Keluar Umum', 'code' => 'SU', 'code_format' => '{no}/SU/PP/{bln}/{thn}'],
    ['name' => 'SK Dir Umum', 'code' => 'SK-DIR', 'code_format' => '{no}/SK/Dir/PP/{bln}/{thn}'],
    ['name' => 'SK Yysn ICB Umum', 'code' => 'SK-YICB', 'code_format' => '{no}/SK/YICB/PP/{bln}/{thn}'],
    ['name' => 'NO MOU', 'code' => 'MOU', 'code_format' => '{no}/MOU/PP/{bln}/{thn}'],
    ['name' => 'Implementation Agreement (IA)', 'code' => 'IA', 'code_format' => '{no}/IA-{extra}/PP/{bln}/{thn}'],
];

foreach ($types as $type) {
    LetterType::create($type);
}

Schema::enableForeignKeyConstraints();

echo "Semua jenis surat telah diperbarui sesuai gambar!";
