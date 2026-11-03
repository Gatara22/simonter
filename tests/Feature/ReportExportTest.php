<?php

namespace Tests\Feature;

use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test admin can export user data to Excel (.xls).
     */
    public function test_admin_can_export_user_data_to_excel(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        User::factory()->create(['name' => 'Pegawai Satu', 'nip' => '199001012020011001']);

        $response = $this->actingAs($admin)->get(route('user.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Pegawai Satu', $response->getContent());
        $this->assertStringContainsString('199001012020011001', $response->getContent());
    }

    /**
     * Test admin can export user data to Excel with period filter.
     */
    public function test_admin_can_export_user_data_to_excel_with_period_filter(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $userThisMonth = User::factory()->create([
            'name' => 'User Bulan Ini',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('user.export.excel', [
            'tanggal_awal' => now()->startOfMonth()->format('Y-m-d'),
            'tanggal_akhir' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('User Bulan Ini', $response->getContent());
    }

    /**
     * Test admin can export user data to PDF.
     */
    public function test_admin_can_export_user_data_to_pdf(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        User::factory()->create(['name' => 'User Cetak PDF']);

        $response = $this->actingAs($admin)->get(route('user.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test admin can export task data to Excel (.xls).
     */
    public function test_admin_can_export_task_data_to_excel(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Pemeliharaan Server Utama',
        ]);

        $response = $this->actingAs($admin)->get(route('tugas.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Pemeliharaan Server Utama', $response->getContent());
    }

    /**
     * Test admin can export task data to Excel with period and status filter.
     */
    public function test_admin_can_export_task_data_to_excel_with_period_filter(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        $tugasFiltered = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Masuk Periode',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($admin)->get(route('tugas.export.excel', [
            'tanggal_awal' => now()->subDay()->format('Y-m-d'),
            'tanggal_akhir' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Selesai',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('Tugas Masuk Periode', $response->getContent());
    }

    /**
     * Test admin can export task data to PDF.
     */
    public function test_admin_can_export_task_data_to_pdf(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Dokumen PDF',
        ]);

        $response = $this->actingAs($admin)->get(route('tugas.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test guest cannot export user or task reports.
     */
    public function test_guest_cannot_export_reports(): void
    {
        $this->get(route('user.export.excel'))->assertRedirect(route('login'));
        $this->get(route('user.export.pdf'))->assertRedirect(route('login'));
        $this->get(route('tugas.export.excel'))->assertRedirect(route('login'));
        $this->get(route('tugas.export.pdf'))->assertRedirect(route('login'));
    }
}
