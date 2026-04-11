<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleLetterTypePermission;
use App\Models\LetterType;

class RoleLetterTypePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allTypes = LetterType::all();

        foreach ($allTypes as $type) {
            RoleLetterTypePermission::firstOrCreate([
                'role' => 'admin',
                'letter_type_id' => $type->id,
            ]);
        }

        $permissions = [
            'direktur' => ['SK', 'Surat Undangan', 'Surat Keterangan'],
            'wadir' => ['SK', 'Surat Tugas', 'Surat Rekomendasi'],
            'kaprodi' => ['SK', 'Surat Tugas'],
            'staf' => ['Surat Undangan', 'Surat Keterangan'],
            'dosen' => ['Surat Tugas'],
        ];

        foreach ($permissions as $role => $typeNames) {
            foreach ($typeNames as $typeName) {
                $type = LetterType::where('name', 'like', "%{$typeName}%")
                    ->whereNull('parent_id')
                    ->first();

                if ($type) {
                    RoleLetterTypePermission::firstOrCreate([
                        'role' => $role,
                        'letter_type_id' => $type->id,
                    ]);

                    foreach ($type->children as $child) {
                        RoleLetterTypePermission::firstOrCreate([
                            'role' => $role,
                            'letter_type_id' => $child->id,
                        ]);
                    }
                }
            }
        }
    }
}
