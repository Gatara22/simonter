<?php

namespace Database\Seeders;

use App\Models\Tugas;
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
        $admin = User::updateOrCreate(
            ['nip' => '199509062020121009'],
            [
                'name' => 'Reza Fahlevi',
                'email' => 'admin@simonter.go.id',
                'kontak' => '087882410609',
                'jabatan' => 'Admin',
                'status' => 'Belum Ditugaskan',
                'password' => 'password',
            ]
        );

        $kasubbag = User::updateOrCreate(
            ['nip' => '199604152021041005'],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'kasubbag@simonter.go.id',
                'kontak' => '081287654321',
                'jabatan' => 'Kepala Sub Bagian',
                'status' => 'Belum Ditugaskan',
                'password' => 'password',
            ]
        );

        $petugas = User::updateOrCreate(
            ['nip' => '199805122022031002'],
            [
                'name' => 'Ahmad Sanusi',
                'email' => 'petugas@simonter.go.id',
                'kontak' => '081298765432',
                'jabatan' => 'Petugas',
                'status' => 'Sedang Bertugas',
                'password' => 'password',
            ]
        );

        Tugas::updateOrCreate(
            [
                'admin_id' => $admin->id,
                'petugas_id' => $petugas->id,
                'judul' => 'Maintenance Jaringan & Server PTSP',
            ],
            [
                'deskripsi' => 'Lakukan pemeliharaan rutin infrastruktur jaringan lokal dan pengecekan akses server PTSP Pengadilan Agama Jakarta Utara.',
                'tanggal_mulai' => now()->startOfMonth()->format('Y-m-d'),
                'tanggal_selesai' => now()->endOfMonth()->format('Y-m-d'),
                'status' => 'Sedang Dikerjakan',
            ]
        );
    }
}
