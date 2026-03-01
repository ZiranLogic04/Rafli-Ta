<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create(['name' => 'Admin System', 'email' => 'admin@campus.com', 'password' => bcrypt('password'), 'role' => 'admin', 'code' => 'ADM-SYS']);

        // Direktur
        User::create(['name' => 'Direktur Utama', 'email' => 'direktur@campus.com', 'password' => bcrypt('password'), 'role' => 'direktur', 'code' => 'DIR-1']);
        
        // Kaprodi
        User::create(['name' => 'Kaprodi TI', 'email' => 'kaprodi@campus.com', 'password' => bcrypt('password'), 'role' => 'kaprodi', 'code' => 'KPD-TI']);

        // Wadir
        User::create(['name' => 'Wadir Akademik', 'email' => 'wadir@campus.com', 'password' => bcrypt('password'), 'role' => 'wadir', 'code' => 'WDR-A']);

        // Staf
        User::create([
            'name' => 'Staf TU',
            'email' => 'staf@campus.com',
            'password' => bcrypt('password'),
            'role' => 'staf',
            'code' => 'STF-TU',
        ]);

        // Dosen
        User::create([
             'name' => 'Dosen A',
             'email' => 'dosen@campus.com',
             'password' => bcrypt('password'),
             'role' => 'dosen',
             'code' => 'DSN',
        ]);

        // Letter Types
        $st = \App\Models\LetterType::create(['name' => 'Surat Tugas', 'code' => 'ST']);
        $sk = \App\Models\LetterType::create(['name' => 'Surat Keputusan', 'code' => 'SK']);
        $su = \App\Models\LetterType::create(['name' => 'Surat Undangan', 'code' => 'SU']);
        $skk = \App\Models\LetterType::create(['name' => 'Surat Keterangan', 'code' => 'SKET']);
        $sed = \App\Models\LetterType::create(['name' => 'Surat Edaran', 'code' => 'SED']);

        // Assign Permissions
        // Admin gets all
        $admin = User::where('email', 'admin@campus.com')->first();
        $admin->letterTypes()->sync([$st->id, $sk->id, $su->id, $skk->id, $sed->id]);

        // Dosen gets ST, SU
        $dosen = User::where('email', 'dosen@campus.com')->first();
        $dosen->letterTypes()->sync([$st->id, $su->id]);
    }
}
