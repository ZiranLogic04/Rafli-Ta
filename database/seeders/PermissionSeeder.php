<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LetterType;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Letter Types (firstOrCreate to avoid duplicates)
        $st = LetterType::firstOrCreate(['code' => 'ST'], ['name' => 'Surat Tugas']);
        $sk = LetterType::firstOrCreate(['code' => 'SK'], ['name' => 'Surat Keputusan']);
        $su = LetterType::firstOrCreate(['code' => 'SU'], ['name' => 'Surat Undangan']);
        $skk = LetterType::firstOrCreate(['code' => 'SKK'], ['name' => 'Surat Keterangan Kuliah']);
        $sed = LetterType::firstOrCreate(['code' => 'SED'], ['name' => 'Surat Edaran']);

        // Admin gets all
        $admin = User::firstOrCreate(
            ['email' => 'admin@campus.com'],
            ['name' => 'Admin System', 'password' => bcrypt('password'), 'role' => 'admin']
        );
        $admin->letterTypes()->sync([$st->id, $sk->id, $su->id, $skk->id, $sed->id]);

        // Dosen (SK, SKK, SED)
        $dosen = User::firstOrCreate(
            ['email' => 'dosen@campus.com'],
            ['name' => 'Dosen A', 'password' => bcrypt('password'), 'role' => 'dosen', 'code' => 'DSN']
        );
        $dosen->letterTypes()->sync([$sk->id, $skk->id, $sed->id]);

        // Staf (ST, SU)
        $staf = User::firstOrCreate(
            ['email' => 'staf@campus.com'],
            ['name' => 'Staf Administrasi', 'password' => bcrypt('password'), 'role' => 'staf', 'code' => 'ADM']
        );
        $staf->letterTypes()->sync([$st->id, $su->id]);

        // Direktur (All)
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@campus.com'],
            ['name' => 'Pak Direktur', 'password' => bcrypt('password'), 'role' => 'direktur', 'code' => 'DIR']
        );
        $direktur->letterTypes()->sync([$st->id, $sk->id, $su->id, $skk->id, $sed->id]);

        // Wadir (All)
        $wadir = User::firstOrCreate(
            ['email' => 'wadir@campus.com'],
            ['name' => 'Ibu Wadir', 'password' => bcrypt('password'), 'role' => 'wadir', 'code' => 'WDIR']
        );
        $wadir->letterTypes()->sync([$st->id, $sk->id, $su->id, $skk->id, $sed->id]);

        // Kaprodi (All)
        $kaprodi = User::firstOrCreate(
            ['email' => 'kaprodi@campus.com'],
            ['name' => 'Kaprodi TI', 'password' => bcrypt('password'), 'role' => 'kaprodi', 'code' => 'TI']
        );
        $kaprodi->letterTypes()->sync([$st->id, $sk->id, $su->id, $skk->id, $sed->id]);

        $this->command->info('Permissions seeded successfully.');
    }
}
