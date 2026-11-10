<?php

namespace Tests\Feature;

use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test admin can view the dashboard with performance infographics for all officers.
     */
    public function test_admin_can_view_dashboard_with_all_officers_performance_infographics(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas1 = User::factory()->create(['name' => 'Bambang Sudirman', 'jabatan' => 'Petugas']);
        $petugas2 = User::factory()->create(['name' => 'Siti Rahmawati', 'jabatan' => 'Petugas']);

        // Task 1: Finished on-time (2 days duration, deadline in 5 days)
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas1->id,
            'judul' => 'Pemeliharaan Jaringan PA Jakarta Utara',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(2),
        ]);

        // Task 2: Finished late (5 days duration, deadline was 3 days)
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas2->id,
            'judul' => 'Audit Berkas Perkara',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(8)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(3),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Pimpinan &amp; Administrator', false);
        $response->assertSee('Infografis Performa Penyelesaian Tugas');
        $response->assertSee('Rata-rata Waktu Pengerjaan');
        $response->assertSee('Tingkat Ketepatan Waktu');
        $response->assertSee('Matriks & Peringkat Kinerja Seluruh Petugas');
        $response->assertSee('Bambang Sudirman');
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('chartPetugasPerformance');
        $response->assertSee('chartDistribusiStatus');
        $response->assertSee('chartMonthlyTrend');
        $response->assertSee('Keterangan Status Evaluasi Performa');
        $response->assertSeeInOrder(['Sangat Cepat', 'Tepat Waktu', 'Cukup Baik', 'Perlu Peningkatan', 'Belum Ada Selesai']);
    }

    /**
     * Test officer (petugas) sees only their own performance metrics and infographics.
     */
    public function test_petugas_can_view_dashboard_with_personal_performance_infographics(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugasA = User::factory()->create(['name' => 'Petugas Khusus A', 'jabatan' => 'Petugas']);
        $petugasB = User::factory()->create(['name' => 'Petugas Rahasia B', 'jabatan' => 'Petugas']);

        // Task for Petugas A
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasA->id,
            'judul' => 'Tugas Khusus Petugas A',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(1),
        ]);

        // Task for Petugas B
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasB->id,
            'judul' => 'Tugas Rahasia Petugas B',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now(),
        ]);

        $response = $this->actingAs($petugasA)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kinerja Petugas');
        $response->assertSee('Infografis Performa Akun Saya');
        $response->assertSee('Distribusi Kinerja Tugas Saya');
        $response->assertSee('chartPetugasStatus');
        $response->assertSee('chartPetugasTasks');
        // Petugas should not see the all-officers leaderboard table
        $response->assertDontSee('Matriks & Peringkat Kinerja Seluruh Petugas');
        $response->assertDontSee('Keterangan Status Evaluasi Performa');
    }

    /**
     * Test admin can filter dashboard performance metrics by period.
     */
    public function test_admin_can_filter_dashboard_by_period(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        // Period: bulan_ini
        $responseBulan = $this->actingAs($admin)->get(route('dashboard', ['periode' => 'bulan_ini']));
        $responseBulan->assertStatus(200);
        $responseBulan->assertSee('Bulan Ini');

        // Period: 3_bulan
        $response3Bulan = $this->actingAs($admin)->get(route('dashboard', ['periode' => '3_bulan']));
        $response3Bulan->assertStatus(200);
        $response3Bulan->assertSee('3 Bulan Terakhir');

        // Period: tahun_ini
        $responseTahun = $this->actingAs($admin)->get(route('dashboard', ['periode' => 'tahun_ini']));
        $responseTahun->assertStatus(200);
        $responseTahun->assertSee('Tahun Ini');
    }

    /**
     * Test accurate calculation of on-time rate and turnaround duration.
     */
    public function test_accurate_calculation_of_on_time_rate_and_turnaround_duration(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        // 1 Task on time: started 4 days ago at 00:00, finished 2 days ago at 00:00 (exact 2.0 days), deadline was today
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(4)->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_selesai' => Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(2)->startOfDay()->format('Y-m-d H:i:s'),
        ]);

        // 1 Task late: started 6 days ago at 00:00, finished 1 day ago at 00:00 (exact 5.0 days), deadline was 3 days ago
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(6)->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_selesai' => Carbon::now()->subDays(3)->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(1)->startOfDay()->format('Y-m-d H:i:s'),
        ]);

        // 1. Verify on Petugas Dashboard (isolated to this petugas's tasks)
        $responsePetugas = $this->actingAs($petugas)->get(route('dashboard'));
        $responsePetugas->assertStatus(200);
        $this->assertEquals(3.5, $responsePetugas->viewData('avgDurasiHari'));
        $this->assertEquals(50.0, $responsePetugas->viewData('onTimeRate'));
        $responsePetugas->assertSee('50%');
        $responsePetugas->assertSeeText('3.5 Hari');

        // 2. Verify on Admin Dashboard in the officer stats leaderboard
        $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
        $responseAdmin->assertStatus(200);
        $petugasStats = $responseAdmin->viewData('petugasStats');
        $targetStat = collect($petugasStats)->firstWhere('id', $petugas->id);
        $this->assertNotNull($targetStat);
        $this->assertEquals(3.5, $targetStat['avg_durasi']);
        $this->assertEquals(50.0, $targetStat['on_time_rate']);
    }

    /**
     * Test admin dashboard has trend chart period options (1 tahun, 6 bulan, 3 bulan, 1 bulan).
     */
    public function test_admin_dashboard_has_trend_chart_period_options(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('data-period="1_tahun"', false);
        $response->assertSee('data-period="6_bulan"', false);
        $response->assertSee('data-period="3_bulan"', false);
        $response->assertSee('data-period="1_bulan"', false);
        $response->assertSee('1 Tahun');
        $response->assertSee('6 Bulan');
        $response->assertSee('3 Bulan');
        $response->assertSee('1 Bulan');

        $trendDatasets = $response->viewData('trendDatasets');
        $this->assertIsArray($trendDatasets);
        $this->assertArrayHasKey('1_tahun', $trendDatasets);
        $this->assertArrayHasKey('6_bulan', $trendDatasets);
        $this->assertArrayHasKey('3_bulan', $trendDatasets);
        $this->assertArrayHasKey('1_bulan', $trendDatasets);

        // 1 Tahun: 12 data points
        $this->assertCount(12, $trendDatasets['1_tahun']['labels']);
        $this->assertCount(12, $trendDatasets['1_tahun']['selesai']);
        $this->assertCount(12, $trendDatasets['1_tahun']['durasi']);

        // 6 Bulan: 6 data points
        $this->assertCount(6, $trendDatasets['6_bulan']['labels']);
        $this->assertCount(6, $trendDatasets['6_bulan']['selesai']);
        $this->assertCount(6, $trendDatasets['6_bulan']['durasi']);

        // 3 Bulan: 3 data points
        $this->assertCount(3, $trendDatasets['3_bulan']['labels']);
        $this->assertCount(3, $trendDatasets['3_bulan']['selesai']);
        $this->assertCount(3, $trendDatasets['3_bulan']['durasi']);

        // 1 Bulan: 4 data points (4 pekan)
        $this->assertCount(4, $trendDatasets['1_bulan']['labels']);
        $this->assertCount(4, $trendDatasets['1_bulan']['selesai']);
        $this->assertCount(4, $trendDatasets['1_bulan']['durasi']);
    }

    /**
     * Test admin dashboard displays delegator (pemberi tugas) performance matrix with respond time, langsung closed, and perlu revisi metrics.
     */
    public function test_admin_dashboard_displays_pemberi_tugas_performance_leaderboard(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pemberi Utama', 'jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['name' => 'Kasubbag Koordinator', 'jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create(['name' => 'Petugas Pelaksana', 'jabatan' => 'Petugas']);

        // 1. Task delegated by Admin: submitted by Petugas 10 hours ago, verified Selesai (Langsung Closed) 2 hours ago -> Respond Time = 8 hours
        $tugasClosed = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Langsung Closed',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subHours(10),
        ]);
        $logKirim1 = $tugasClosed->recordLog('kirim_balik', 'Menunggu Konfirmasi', 'Evidence dikirim', null, $petugas->id);
        $logKirim1->created_at = Carbon::now()->subHours(10);
        $logKirim1->save();

        $logVerif1 = $tugasClosed->recordLog('selesai', 'Selesai', 'Langsung disetujui closed', null, $admin->id);
        $logVerif1->created_at = Carbon::now()->subHours(2);
        $logVerif1->save();

        // 2. Task delegated by Kasubbag: submitted by Petugas 30 hours ago, returned for Perlu Revisi 6 hours ago -> Respond Time = 24 hours
        $tugasRevisi = Tugas::factory()->create([
            'admin_id' => $kasubbag->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Perlu Revisi',
            'status' => 'Perlu Revisi',
            'tanggal_mulai' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subHours(30),
        ]);
        $logKirim2 = $tugasRevisi->recordLog('kirim_balik', 'Menunggu Konfirmasi', 'Evidence awal dikirim', null, $petugas->id);
        $logKirim2->created_at = Carbon::now()->subHours(30);
        $logKirim2->save();

        $logVerif2 = $tugasRevisi->recordLog('revisi', 'Perlu Revisi', 'Perlu perbaikan lampiran', 'Perbaiki halaman 2', $kasubbag->id);
        $logVerif2->created_at = Carbon::now()->subHours(6);
        $logVerif2->save();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Matriks & Peringkat Kinerja Pemberi Tugas / Pekerjaan');
        $response->assertSee('Admin Pemberi Utama');
        $response->assertSee('Kasubbag Koordinator');
        $response->assertSee('Langsung Closed');
        $response->assertSee('Perlu Revisi');
        $response->assertSee('Sangat Responsif');

        $pemberiStats = collect($response->viewData('pemberiStats'));

        $adminStat = $pemberiStats->firstWhere('id', $admin->id);
        $this->assertNotNull($adminStat);
        $this->assertEquals(1, $adminStat['total_delegasi']);
        $this->assertEquals(1, $adminStat['direspon']);
        $this->assertEquals(1, $adminStat['langsung_closed']);
        $this->assertEquals(0, $adminStat['perlu_revisi']);
        $this->assertEquals(100.0, $adminStat['direct_close_rate']);
        $this->assertEquals(8.0, $adminStat['avg_respond_jam']);

        $kasubbagStat = $pemberiStats->firstWhere('id', $kasubbag->id);
        $this->assertNotNull($kasubbagStat);
        $this->assertEquals(1, $kasubbagStat['total_delegasi']);
        $this->assertEquals(1, $kasubbagStat['direspon']);
        $this->assertEquals(0, $kasubbagStat['langsung_closed']);
        $this->assertEquals(1, $kasubbagStat['perlu_revisi']);
        $this->assertEquals(100.0, $kasubbagStat['revisi_rate']);
        $this->assertEquals(24.0, $kasubbagStat['avg_respond_jam']);
    }
}
