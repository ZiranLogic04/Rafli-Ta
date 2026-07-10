<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\RoleLetterTypePermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== DEPARTMENTS ====================
        $departments = [
            ['name' => 'Teknik Informatika', 'code' => 'TI'],
            ['name' => 'Sistem Informasi', 'code' => 'SI'],
            ['name' => 'Teknik Mesin', 'code' => 'TM'],
            ['name' => 'Teknik Elektro', 'code' => 'TE'],
            ['name' => 'Manajemen Bisnis', 'code' => 'MB'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']], ['code' => $dept['code']]);
        }

        // ==================== USERS ====================
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sisurat.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'code' => 'ADM-001',
            ]
        );

        // Direktur
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@sisurat.com'],
            [
                'name' => 'Dr. H. Ahmad Suryadi, M.Pd.',
                'password' => Hash::make('password'),
                'role' => 'direktur',
                'code' => 'DIR-001',
            ]
        );

        // Wadir
        $wadir1 = User::firstOrCreate(
            ['email' => 'wadir1@sisurat.com'],
            [
                'name' => 'Ir. Budi Santoso, M.T.',
                'password' => Hash::make('password'),
                'role' => 'wadir',
                'code' => 'WDR-001',
                'wadir_level' => 1,
            ]
        );

        $wadir2 = User::firstOrCreate(
            ['email' => 'wadir2@sisurat.com'],
            [
                'name' => 'Dra. Siti Nurhaliza, M.M.',
                'password' => Hash::make('password'),
                'role' => 'wadir',
                'code' => 'WDR-002',
                'wadir_level' => 2,
            ]
        );

        $wadir3 = User::firstOrCreate(
            ['email' => 'wadir3@sisurat.com'],
            [
                'name' => 'Hendra Wijaya, S.E., M.Ak.',
                'password' => Hash::make('password'),
                'role' => 'wadir',
                'code' => 'WDR-003',
                'wadir_level' => 3,
            ]
        );

        // Kaprodi
        $kaprodiTI = User::firstOrCreate(
            ['email' => 'kaprodi-ti@sisurat.com'],
            [
                'name' => 'Rina Marlina, S.Kom., M.Cs.',
                'password' => Hash::make('password'),
                'role' => 'kaprodi',
                'code' => 'KPD-TI',
                'jurusan' => 'Teknik Informatika',
            ]
        );

        $kaprodiSI = User::firstOrCreate(
            ['email' => 'kaprodi-si@sisurat.com'],
            [
                'name' => 'Agus Prasetyo, S.Kom., M.T.I.',
                'password' => Hash::make('password'),
                'role' => 'kaprodi',
                'code' => 'KPD-SI',
                'jurusan' => 'Sistem Informasi',
            ]
        );

        $kaprodiTM = User::firstOrCreate(
            ['email' => 'kaprodi-tm@sisurat.com'],
            [
                'name' => 'Ir. Dedi Kurniawan, M.T.',
                'password' => Hash::make('password'),
                'role' => 'kaprodi',
                'code' => 'KPD-TM',
                'jurusan' => 'Teknik Mesin',
            ]
        );

        // Staf TU
        $staf1 = User::firstOrCreate(
            ['email' => 'staf1@sisurat.com'],
            [
                'name' => 'Dewi Lestari, S.E.',
                'password' => Hash::make('password'),
                'role' => 'staf',
                'code' => 'STF-001',
            ]
        );

        // Dosen
        $dosen1 = User::firstOrCreate(
            ['email' => 'dosen1@sisurat.com'],
            [
                'name' => 'Fajar Nugroho, S.Kom., M.Cs.',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'code' => 'DSN-001',
                'jurusan' => 'Teknik Informatika',
            ]
        );

        $dosen2 = User::firstOrCreate(
            ['email' => 'dosen2@sisurat.com'],
            [
                'name' => 'Maya Sari, S.Kom., M.T.',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'code' => 'DSN-002',
                'jurusan' => 'Teknik Informatika',
            ]
        );

        $dosen3 = User::firstOrCreate(
            ['email' => 'dosen3@sisurat.com'],
            [
                'name' => 'Rizky Pratama, S.Kom., M.M.',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'code' => 'DSN-003',
                'jurusan' => 'Sistem Informasi',
            ]
        );

        $dosen4 = User::firstOrCreate(
            ['email' => 'dosen4@sisurat.com'],
            [
                'name' => 'Lina Handayani, S.T., M.T.',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'code' => 'DSN-004',
                'jurusan' => 'Teknik Mesin',
            ]
        );

        // ==================== LETTER TYPES (Parents) ====================
        $sk = LetterType::firstOrCreate(
            ['name' => 'Surat Keputusan'],
            ['code' => 'SK', 'parent_id' => null]
        );

        $st = LetterType::firstOrCreate(
            ['name' => 'Surat Tugas'],
            ['code' => 'ST', 'parent_id' => null]
        );

        $su = LetterType::firstOrCreate(
            ['name' => 'Surat Undangan'],
            ['code' => 'SU', 'parent_id' => null]
        );

        $sket = LetterType::firstOrCreate(
            ['name' => 'Surat Keterangan'],
            ['code' => 'SKET', 'parent_id' => null]
        );

        $sed = LetterType::firstOrCreate(
            ['name' => 'Surat Edaran'],
            ['code' => 'SED', 'parent_id' => null]
        );

        $sm = LetterType::firstOrCreate(
            ['name' => 'Surat Memo'],
            ['code' => 'SM', 'parent_id' => null]
        );

        // ==================== LETTER TYPES (Children) ====================
        // SK children
        $skMutasi = LetterType::firstOrCreate(
            ['name' => 'SK Mutasi'],
            ['code' => 'SK-MUT', 'parent_id' => $sk->id]
        );

        $skKenaikan = LetterType::firstOrCreate(
            ['name' => 'SK Kenaikan Pangkat'],
            ['code' => 'SK-KP', 'parent_id' => $sk->id]
        );

        $skPemberhentian = LetterType::firstOrCreate(
            ['name' => 'SK Pemberhentian'],
            ['code' => 'SK-PHB', 'parent_id' => $sk->id]
        );

        $skPromosi = LetterType::firstOrCreate(
            ['name' => 'SK Promosi Jabatan'],
            ['code' => 'SK-PROM', 'parent_id' => $sk->id]
        );

        // ST children
        $stLuar = LetterType::firstOrCreate(
            ['name' => 'Tugas Luar Kota'],
            ['code' => 'ST-LK', 'parent_id' => $st->id]
        );

        $stDalam = LetterType::firstOrCreate(
            ['name' => 'Tugas Dalam Kota'],
            ['code' => 'ST-DK', 'parent_id' => $st->id]
        );

        $stSeminar = LetterType::firstOrCreate(
            ['name' => 'Seminar / Workshop'],
            ['code' => 'ST-SW', 'parent_id' => $st->id]
        );

        // SU children
        $suRapat = LetterType::firstOrCreate(
            ['name' => 'Undangan Rapat'],
            ['code' => 'SU-RPT', 'parent_id' => $su->id]
        );

        $suAcara = LetterType::firstOrCreate(
            ['name' => 'Undangan Acara'],
            ['code' => 'SU-ACR', 'parent_id' => $su->id]
        );

        // SKET children
        $sketAktif = LetterType::firstOrCreate(
            ['name' => 'Keterangan Aktif'],
            ['code' => 'SKET-AKT', 'parent_id' => $sket->id]
        );

        $sketMagang = LetterType::firstOrCreate(
            ['name' => 'Keterangan Magang'],
            ['code' => 'SKET-MG', 'parent_id' => $sket->id]
        );

        // SED children
        $sedInternal = LetterType::firstOrCreate(
            ['name' => 'Edaran Internal'],
            ['code' => 'SED-INT', 'parent_id' => $sed->id]
        );

        $sedEksternal = LetterType::firstOrCreate(
            ['name' => 'Edaran Eksternal'],
            ['code' => 'SED-EKS', 'parent_id' => $sed->id]
        );



        // ==================== ROLE PERMISSIONS ====================
        $allTypes = LetterType::all();

        // Admin = semua
        foreach ($allTypes as $type) {
            RoleLetterTypePermission::firstOrCreate([
                'role' => 'admin',
                'letter_type_id' => $type->id,
            ]);
        }

        // Direktur = semua jenis
        foreach ($allTypes as $type) {
            RoleLetterTypePermission::firstOrCreate([
                'role' => 'direktur',
                'letter_type_id' => $type->id,
            ]);
        }

        // Wadir = SK, ST, SU, SM
        foreach ([$sk, $st, $su, $sm] as $type) {
            RoleLetterTypePermission::firstOrCreate(['role' => 'wadir', 'letter_type_id' => $type->id]);
            foreach ($type->children as $child) {
                RoleLetterTypePermission::firstOrCreate(['role' => 'wadir', 'letter_type_id' => $child->id]);
            }
        }

        // Kaprodi = ST, SU, SKET
        foreach ([$st, $su, $sket] as $type) {
            RoleLetterTypePermission::firstOrCreate(['role' => 'kaprodi', 'letter_type_id' => $type->id]);
            foreach ($type->children as $child) {
                RoleLetterTypePermission::firstOrCreate(['role' => 'kaprodi', 'letter_type_id' => $child->id]);
            }
        }

        // Staf = SU, SKET, SM
        foreach ([$su, $sket, $sm] as $type) {
            RoleLetterTypePermission::firstOrCreate(['role' => 'staf', 'letter_type_id' => $type->id]);
            foreach ($type->children as $child) {
                RoleLetterTypePermission::firstOrCreate(['role' => 'staf', 'letter_type_id' => $child->id]);
            }
        }

        // Dosen = ST, SKET
        foreach ([$st, $sket] as $type) {
            RoleLetterTypePermission::firstOrCreate(['role' => 'dosen', 'letter_type_id' => $type->id]);
            foreach ($type->children as $child) {
                RoleLetterTypePermission::firstOrCreate(['role' => 'dosen', 'letter_type_id' => $child->id]);
            }
        }

        // ==================== SAMPLE LETTERS ====================
        $now = now();

        // Approved letters
        Letter::create([
            'user_id' => $dosen1->id,
            'user_name' => $dosen1->name,
            'letter_type_id' => $stLuar->id,
            'target_user_id' => $kaprodiTI->id,
            'target_name' => $kaprodiTI->name,
            'target_role' => 'kaprodi',
            'target_jurusan' => 'Teknik Informatika',
            'file_path' => 'letters/2026/03/sample1.pdf',
            'status' => 'approved',
            'letter_number' => '001/ST-LK/DSN-001/III/2026',
            'approved_at' => $now->copy()->subDays(5),
            'approved_by' => $kaprodiTI->id,
            'current_approver_role' => null,
            'created_at' => $now->copy()->subDays(7),
        ]);

        Letter::create([
            'user_id' => $dosen2->id,
            'user_name' => $dosen2->name,
            'letter_type_id' => $skMutasi->id,
            'target_user_id' => $direktur->id,
            'target_name' => $direktur->name,
            'target_role' => 'direktur',
            'file_path' => 'letters/2026/03/sample2.pdf',
            'status' => 'approved',
            'letter_number' => '002/SK-MUT/DSN-002/III/2026',
            'approved_at' => $now->copy()->subDays(3),
            'approved_by' => $direktur->id,
            'current_approver_role' => null,
            'created_at' => $now->copy()->subDays(10),
        ]);

        Letter::create([
            'user_id' => $staf1->id,
            'user_name' => $staf1->name,
            'letter_type_id' => $suRapat->id,
            'target_user_id' => $wadir1->id,
            'target_name' => $wadir1->name,
            'target_role' => 'wadir',
            'target_wadir_level' => 1,
            'file_path' => 'letters/2026/03/sample3.pdf',
            'status' => 'approved',
            'letter_number' => '003/SU-RPT/STF-001/III/2026',
            'approved_at' => $now->copy()->subDays(2),
            'approved_by' => $wadir1->id,
            'current_approver_role' => null,
            'created_at' => $now->copy()->subDays(4),
        ]);

        // Pending letters
        Letter::create([
            'user_id' => $dosen3->id,
            'user_name' => $dosen3->name,
            'letter_type_id' => $stSeminar->id,
            'target_user_id' => $kaprodiSI->id,
            'target_name' => $kaprodiSI->name,
            'target_role' => 'kaprodi',
            'target_jurusan' => 'Sistem Informasi',
            'file_path' => 'letters/2026/04/sample4.pdf',
            'status' => 'pending',
            'current_approver_role' => 'kaprodi',
            'created_at' => $now->copy()->subDay(),
        ]);

        Letter::create([
            'user_id' => $dosen4->id,
            'user_name' => $dosen4->name,
            'letter_type_id' => $sketAktif->id,
            'target_user_id' => $kaprodiTM->id,
            'target_name' => $kaprodiTM->name,
            'target_role' => 'kaprodi',
            'target_jurusan' => 'Teknik Mesin',
            'file_path' => 'letters/2026/04/sample5.pdf',
            'status' => 'pending',
            'current_approver_role' => 'kaprodi',
            'created_at' => $now->copy()->subHours(12),
        ]);

        Letter::create([
            'user_id' => $dosen1->id,
            'user_name' => $dosen1->name,
            'letter_type_id' => $suAcara->id,
            'target_user_id' => $wadir2->id,
            'target_name' => $wadir2->name,
            'target_role' => 'wadir',
            'target_wadir_level' => 2,
            'file_path' => 'letters/2026/04/sample6.pdf',
            'status' => 'pending',
            'current_approver_role' => 'wadir',
            'created_at' => $now->copy()->subHours(6),
        ]);

        // Rejected letter
        Letter::create([
            'user_id' => $dosen2->id,
            'user_name' => $dosen2->name,
            'letter_type_id' => $stDalam->id,
            'target_user_id' => $kaprodiTI->id,
            'target_name' => $kaprodiTI->name,
            'target_role' => 'kaprodi',
            'target_jurusan' => 'Teknik Informatika',
            'file_path' => 'letters/2026/04/sample7.pdf',
            'status' => 'rejected',
            'rejection_note' => 'Surat tugas tidak diperlukan untuk kegiatan dalam kampus. Cukup menggunakan memo internal.',
            'current_approver_role' => null,
            'created_at' => $now->copy()->subDays(2),
        ]);
    }

    }
}
