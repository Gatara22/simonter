<?php

namespace Tests\Feature;

use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Test HOLD duration is excluded from working duration and lateness calculation when task is completed/closed.
     */
    public function test_hold_duration_is_excluded_from_working_duration_and_lateness_performance(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['name' => 'Petugas Hold Test', 'jabatan' => 'Petugas']);

        // Task started 7 days ago with a 3-day deadline (deadline was 4 days ago).
        // Task was placed on HOLD for 5 days (5 * 86400 = 432000 seconds) and completed today (0 days ago).
        // Raw elapsed time = 7 days (which would be 4 days LATE without HOLD).
        // Effective working duration excluding HOLD = 7 - 5 = 2.0 days (<= 3 days deadline -> ON TIME / Tepat Waktu!).
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Dengan Masa Hold 5 Hari',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_selesai' => Carbon::now()->subDays(4)->startOfDay()->format('Y-m-d H:i:s'),
            'tanggal_tindak_lanjut' => Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
            'total_hold_seconds' => 5 * 86400,
        ]);

        $this->assertEquals(2.0, $tugas->getEffectiveWorkingDurationDays());
        $this->assertTrue($tugas->isCompletedOnTime());

        // Verify on Petugas Dashboard
        $responsePetugas = $this->actingAs($petugas)->get(route('dashboard'));
        $responsePetugas->assertStatus(200);
        $this->assertEquals(2.0, $responsePetugas->viewData('avgDurasiHari'));
        $this->assertEquals(100.0, $responsePetugas->viewData('onTimeRate'));
        $this->assertEquals(1, $responsePetugas->viewData('tugasTepatWaktu'));
        $this->assertEquals(0, $responsePetugas->viewData('tugasTerlambat'));

        // Verify on Admin Dashboard officer leaderboard
        $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
        $responseAdmin->assertStatus(200);
        $petugasStats = collect($responseAdmin->viewData('petugasStats'));
        $stat = $petugasStats->firstWhere('id', $petugas->id);

        $this->assertNotNull($stat);
        $this->assertEquals(2.0, $stat['avg_durasi']);
        $this->assertEquals(100.0, $stat['on_time_rate']);
        $this->assertEquals(1, $stat['tepat_waktu']);
        $this->assertEquals(0, $stat['terlambat']);
    }

    /**
     * Test admin dashboard supports filtering by klasifikasi and includes HOLD in doughnut chart data.
     */
    public function test_dashboard_filters_by_klasifikasi_and_includes_hold_status_in_chart(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        $initialHold = Tugas::where('status', 'HOLD')->count();
        $initialSekTotal = Tugas::where('klasifikasi', 'Kesekretariatan')->count();
        $initialSekHold = Tugas::where('klasifikasi', 'Kesekretariatan')->where('status', 'HOLD')->count();
        $initialKepTotal = Tugas::where('klasifikasi', 'Kepaniteraan')->count();
        $initialKepHold = Tugas::where('klasifikasi', 'Kepaniteraan')->where('status', 'HOLD')->count();

        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Sekretariat Aktif',
            'klasifikasi' => 'Kesekretariatan',
            'status' => 'Sedang Dikerjakan',
        ]);

        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Kepaniteraan Hold',
            'klasifikasi' => 'Kepaniteraan',
            'status' => 'HOLD',
        ]);

        // Unfiltered dashboard includes HOLD in doughnut labels and counts
        $allResp = $this->actingAs($admin)->get(route('dashboard'));
        $allResp->assertStatus(200);
        $labels = $allResp->viewData('chartDoughnutLabels');
        $data = $allResp->viewData('chartDoughnutData');
        $this->assertContains('HOLD', $labels);
        $holdIdx = array_search('HOLD', $labels, true);
        $this->assertSame($initialHold + 1, $data[$holdIdx]);

        // Filtered by Kesekretariatan: excludes Kepaniteraan Hold task
        $sekResp = $this->actingAs($admin)->get(route('dashboard', ['klasifikasi' => 'Kesekretariatan']));
        $sekResp->assertStatus(200);
        $this->assertEquals('Kesekretariatan', $sekResp->viewData('klasifikasi'));
        $this->assertEquals($initialSekTotal + 1, $sekResp->viewData('totalTugas'));
        $this->assertEquals($initialSekHold, $sekResp->viewData('tugasHold'));

        // Filtered by Kepaniteraan: includes only Kepaniteraan Hold task
        $kepResp = $this->actingAs($admin)->get(route('dashboard', ['klasifikasi' => 'Kepaniteraan']));
        $kepResp->assertStatus(200);
        $this->assertEquals('Kepaniteraan', $kepResp->viewData('klasifikasi'));
        $this->assertEquals($initialKepTotal + 1, $kepResp->viewData('totalTugas'));
        $this->assertEquals($initialKepHold + 1, $kepResp->viewData('tugasHold'));
    }

    /**
     * Test collaborative multi-petugas tasks credit delegated officers who post at least 1 comment or attachment.
     */
    public function test_collaborative_multi_petugas_task_credits_delegated_officers_with_at_least_one_comment_or_attachment(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Kolaborasi', 'jabatan' => 'Admin']);
        $petugasPic = User::factory()->create(['name' => 'Petugas PIC Komentar', 'jabatan' => 'Petugas']);
        $petugasAttachment = User::factory()->create(['name' => 'Petugas Lampiran Aktif', 'jabatan' => 'Petugas']);
        $petugasPasif = User::factory()->create(['name' => 'Petugas Pasif Tanpa Aksi', 'jabatan' => 'Petugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasPic->id,
            'judul' => 'Audit Kolaborasi Multi-Petugas',
            'status' => 'Selesai',
            'tanggal_mulai' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'tanggal_tindak_lanjut' => Carbon::now()->subDays(1),
        ]);

        $tugas->kolaborator()->attach([$petugasAttachment->id, $petugasPasif->id]);

        // PIC posts 1 text comment (no file)
        $tugas->diskusi()->create([
            'user_id' => $petugasPic->id,
            'pesan' => 'Progres bagian utama telah selesai disusun.',
        ]);

        // Collaborator 1 posts 1 file attachment (no text message)
        $tugas->diskusi()->create([
            'user_id' => $petugasAttachment->id,
            'pesan' => null,
            'file_path' => 'diskusi/lampiran_kolaborator.pdf',
            'file_name' => 'lampiran_kolaborator.pdf',
        ]);

        // Author (Admin) also posts a comment; should NOT credit Admin in officer stats
        $tugas->diskusi()->create([
            'user_id' => $admin->id,
            'pesan' => 'Terima kasih atas laporannya.',
        ]);

        // Collaborator 2 (petugasPasif) posts NO comment and NO attachment

        // 1. Verify Admin Dashboard performance matrix & leaderboard ranking credits PIC & Attachment collaborator, excludes passive collaborator
        $adminResp = $this->actingAs($admin)->get(route('dashboard'));
        $adminResp->assertStatus(200);
        $petugasStats = collect($adminResp->viewData('petugasStats'));

        $picStat = $petugasStats->firstWhere('id', $petugasPic->id);
        $this->assertNotNull($picStat);
        $this->assertSame(1, $picStat['total_tugas']);
        $this->assertSame(1, $picStat['kolaborasi_count']);
        $this->assertSame(1, $picStat['selesai']);
        $this->assertSame(1, $picStat['tepat_waktu']);
        $this->assertEquals(100.0, $picStat['on_time_rate']);
        $this->assertEquals(70.0, $picStat['score']);

        $attachStat = $petugasStats->firstWhere('id', $petugasAttachment->id);
        $this->assertNotNull($attachStat);
        $this->assertSame(1, $attachStat['total_tugas']);
        $this->assertSame(1, $attachStat['kolaborasi_count']);
        $this->assertSame(1, $attachStat['selesai']);
        $this->assertSame(1, $attachStat['tepat_waktu']);
        $this->assertEquals(100.0, $attachStat['on_time_rate']);
        $this->assertEquals(70.0, $attachStat['score']);

        $pasifStat = $petugasStats->firstWhere('id', $petugasPasif->id);
        $this->assertNotNull($pasifStat);
        $this->assertSame(0, $pasifStat['total_tugas']);
        $this->assertSame(0, $pasifStat['kolaborasi_count']);
        $this->assertSame(0, $pasifStat['selesai']);
        $this->assertEquals(0.0, $pasifStat['score']);

        // 2. Verify Personal Dashboards reflect the same collaborative contribution rule
        $attachDashboard = $this->actingAs($petugasAttachment)->get(route('dashboard'));
        $attachDashboard->assertStatus(200);
        $this->assertSame(1, $attachDashboard->viewData('totalTugas'));
        $this->assertSame(1, $attachDashboard->viewData('tugasSelesai'));
        $this->assertEquals(100.0, $attachDashboard->viewData('onTimeRate'));

        $pasifDashboard = $this->actingAs($petugasPasif)->get(route('dashboard'));
        $pasifDashboard->assertStatus(200);
        $this->assertSame(0, $pasifDashboard->viewData('totalTugas'));
        $this->assertSame(0, $pasifDashboard->viewData('tugasSelesai'));
    }

    /**
     * Test unified single-row horizontal navbar (logo, date, menu, notif, user aligned) without auto-hide and with Superman-shield "S" logo for all roles.
     */
    public function test_unified_horizontal_navbar_without_autohide_and_shield_logo_present_for_all_roles(): void
    {
        $roles = ['Admin', 'Kepala Sub Bagian', 'Panitera Muda', 'Petugas'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['jabatan' => $role]);

            $response = $this->actingAs($user)->get(route('dashboard'));
            $response->assertStatus(200);
            $response->assertDontSee('sidebar-scroll-hidden', false);
            $response->assertDontSee('id="sidebarPeekBtn"', false);
            $response->assertSee('simonter-unified-navbar', false);
            $response->assertSee('position: sticky;', false);
            $response->assertSee('id="btnMinimizeNavbar"', false);
            $response->assertSee('is-minimized', false);
            $response->assertSee('horizontal-menubar', false);
            $response->assertSee('horizontal-nav-list', false);
            $response->assertSee('unified-date-pill', false);
            $response->assertSee('sidebar-icon-box', false);
            $response->assertSee('simonter-shield-s', false);
            $response->assertSee('fa-chart-line', false);
            $response->assertSee('fa-clipboard-check', false);
            $response->assertSee('fa-compass', false);
        }

        Auth::logout();

        $loginResp = $this->get(route('login'));
        $loginResp->assertStatus(200);
        $loginResp->assertSee('simonter-shield-s', false);

        $welcomeResp = $this->get(route('welcome'));
        $welcomeResp->assertStatus(200);
        $welcomeResp->assertSee('simonter-shield-s', false);
        $welcomeResp->assertSee('testimonial-card-modern', false);
        $welcomeResp->assertSee('Dr. Khoiriyah Roihan, S.Ag., M.H.');
        $welcomeResp->assertDontSee('testimonial-img', false);
        $welcomeResp->assertDontSee('enno/assets/img/testimonials/', false);
    }

    /**
     * Test approaching-deadline (0-2 days) box and modal table on dashboard work for all roles and respect filters.
     */
    public function test_dashboard_approaching_deadline_box_and_table_work_for_all_user_roles(): void
    {
        $baseAll = Tugas::approachingDeadline(2)->count();
        $baseSek = Tugas::approachingDeadline(2)->where('klasifikasi', 'Kesekretariatan')->count();
        $baseKep = Tugas::approachingDeadline(2)->where('klasifikasi', 'Kepaniteraan')->count();

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['jabatan' => 'Kepala Sub Bagian', 'klasifikasi' => 'Kesekretariatan']);
        $panmud = User::factory()->create(['jabatan' => 'Panitera Muda', 'klasifikasi' => 'Kepaniteraan']);
        $petugasUtama = User::factory()->create(['name' => 'Petugas Deadline Utama', 'jabatan' => 'Petugas', 'klasifikasi' => 'Kesekretariatan']);
        $petugasKolab = User::factory()->create(['name' => 'Petugas Kolaborator Pasif Deadline', 'jabatan' => 'Petugas', 'klasifikasi' => 'Kepaniteraan']);
        $petugasLain = User::factory()->create(['name' => 'Petugas Lain Deadline', 'jabatan' => 'Petugas', 'klasifikasi' => 'Kesekretariatan']);

        // Included (+0 days, started 2 months ago -> tests periode=bulan_ini still shows it)
        $tugasHariIni = Tugas::factory()->create([
            'admin_id' => $kasubbag->id,
            'petugas_id' => $petugasUtama->id,
            'judul' => 'DL_0_HARI_KESEK',
            'klasifikasi' => 'Kesekretariatan',
            'status' => 'Sedang Dikerjakan',
            'tanggal_mulai' => Carbon::today()->subMonths(2)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::today()->format('Y-m-d'),
        ]);
        // Attach passive collaborator (no diskusi comment yet -> still must see urgent deadline)
        $tugasHariIni->kolaborator()->attach($petugasKolab->id);

        // Included (+1 day)
        $tugasBesok = Tugas::factory()->create([
            'admin_id' => $panmud->id,
            'petugas_id' => $petugasUtama->id,
            'judul' => 'DL_1_HARI_KEPAN',
            'klasifikasi' => 'Kepaniteraan',
            'status' => 'Didelegasikan',
            'tanggal_mulai' => Carbon::today()->subDays(2)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::today()->addDay()->format('Y-m-d'),
        ]);

        // Included (+2 days, assigned to petugasLain)
        $tugasLusa = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasLain->id,
            'judul' => 'DL_2_HARI_KESEK',
            'klasifikasi' => 'Kesekretariatan',
            'status' => 'Menunggu Konfirmasi',
            'tanggal_mulai' => Carbon::today()->subDays(1)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        // Excluded: past (-1 day) and too far (+3 days)
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasUtama->id,
            'judul' => 'EXCL_PAST_MINUS_1',
            'status' => 'Sedang Dikerjakan',
            'tanggal_selesai' => Carbon::today()->subDay()->format('Y-m-d'),
        ]);
        Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasUtama->id,
            'judul' => 'EXCL_PLUS_3_HARI',
            'status' => 'Sedang Dikerjakan',
            'tanggal_selesai' => Carbon::today()->addDays(3)->format('Y-m-d'),
        ]);

        // Excluded statuses even when tanggal_selesai is 0-2 days
        foreach (['Selesai', 'HOLD', 'Menunggu Persetujuan Atasan', 'Ditolak Atasan'] as $excludedStatus) {
            Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $petugasUtama->id,
                'judul' => 'EXCL_STATUS_'.str_replace(' ', '_', $excludedStatus),
                'status' => $excludedStatus,
                'tanggal_selesai' => Carbon::today()->addDay()->format('Y-m-d'),
            ]);
        }

        // 1. Admin dashboard sees all approaching-deadline tasks sorted ascending, ignores periode filter, respects klasifikasi
        $adminResp = $this->actingAs($admin)->get(route('dashboard', ['periode' => 'bulan_ini']));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('id="cardMendekatiDeadline"', false);
        $adminResp->assertSee('id="modalTugasMendekatiDeadline"', false);
        $adminResp->assertSee('id="tableTugasMendekatiDeadline"', false);
        $adminResp->assertSee('Mendekati Deadline (0-2 Hari)');
        $this->assertSame($baseAll + 3, $adminResp->viewData('tugasMendekatiDeadlineCount'));

        $adminItems = $adminResp->viewData('tugasMendekatiDeadline');
        $createdOrderIds = $adminItems->whereIn('id', [$tugasHariIni->id, $tugasBesok->id, $tugasLusa->id])->pluck('id')->values()->all();
        $this->assertSame([$tugasHariIni->id, $tugasBesok->id, $tugasLusa->id], $createdOrderIds);

        $adminModalTail = strstr($adminResp->getContent(), 'id="modalTugasMendekatiDeadline"') ?: '';
        $adminModalHtml = strstr($adminModalTail, 'id="modalAuditGrafik"', true) ?: $adminModalTail;
        $this->assertStringContainsString('DL_0_HARI_KESEK', $adminModalHtml);
        $this->assertStringContainsString('DL_1_HARI_KEPAN', $adminModalHtml);
        $this->assertStringContainsString('DL_2_HARI_KESEK', $adminModalHtml);
        $this->assertStringContainsString('Deadline Hari Ini', $adminModalHtml);
        $this->assertStringContainsString('Sisa 1 Hari', $adminModalHtml);
        $this->assertStringContainsString('Sisa 2 Hari', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_PAST_MINUS_1', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_PLUS_3_HARI', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_STATUS_Selesai', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_STATUS_HOLD', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_STATUS_Menunggu_Persetujuan_Atasan', $adminModalHtml);
        $this->assertStringNotContainsString('EXCL_STATUS_Ditolak_Atasan', $adminModalHtml);

        $adminTitles = $adminItems->pluck('judul')->all();
        $this->assertNotContains('EXCL_PAST_MINUS_1', $adminTitles);
        $this->assertNotContains('EXCL_PLUS_3_HARI', $adminTitles);
        $this->assertNotContains('EXCL_STATUS_Selesai', $adminTitles);
        $this->assertNotContains('EXCL_STATUS_HOLD', $adminTitles);
        $this->assertNotContains('EXCL_STATUS_Menunggu_Persetujuan_Atasan', $adminTitles);
        $this->assertNotContains('EXCL_STATUS_Ditolak_Atasan', $adminTitles);

        // Klasifikasi filter on Admin dashboard
        $adminKepResp = $this->actingAs($admin)->get(route('dashboard', ['klasifikasi' => 'Kepaniteraan']));
        $adminKepResp->assertStatus(200);
        $this->assertSame($baseKep + 1, $adminKepResp->viewData('tugasMendekatiDeadlineCount'));
        $adminKepResp->assertSee('DL_1_HARI_KEPAN');
        $adminKepResp->assertDontSee('DL_0_HARI_KESEK');
        $adminKepResp->assertDontSee('DL_2_HARI_KESEK');

        $adminSekResp = $this->actingAs($admin)->get(route('dashboard', ['klasifikasi' => 'Kesekretariatan']));
        $adminSekResp->assertStatus(200);
        $this->assertSame($baseSek + 2, $adminSekResp->viewData('tugasMendekatiDeadlineCount'));
        $adminSekResp->assertSee('DL_0_HARI_KESEK');
        $adminSekResp->assertSee('DL_2_HARI_KESEK');
        $adminSekResp->assertDontSee('DL_1_HARI_KEPAN');

        // 2. Kepala Sub Bagian sees tasks where they are delegator/PIC/collaborator
        $kasubbagResp = $this->actingAs($kasubbag)->get(route('dashboard'));
        $kasubbagResp->assertStatus(200);
        $kasubbagResp->assertSee('id="cardMendekatiDeadline"', false);
        $this->assertSame(1, $kasubbagResp->viewData('tugasMendekatiDeadlineCount'));
        $kasubbagResp->assertSee('DL_0_HARI_KESEK');
        $kasubbagResp->assertDontSee('DL_1_HARI_KEPAN');

        // 3. Panitera Muda sees tasks where they are delegator/PIC/collaborator
        $panmudResp = $this->actingAs($panmud)->get(route('dashboard'));
        $panmudResp->assertStatus(200);
        $panmudResp->assertSee('id="cardMendekatiDeadline"', false);
        $this->assertSame(1, $panmudResp->viewData('tugasMendekatiDeadlineCount'));
        $panmudResp->assertSee('DL_1_HARI_KEPAN');
        $panmudResp->assertDontSee('DL_0_HARI_KESEK');

        // 4. Petugas Utama sees their 2 approaching-deadline tasks (including 2-month-old start date under periode=bulan_ini)
        $petugasUtamaResp = $this->actingAs($petugasUtama)->get(route('dashboard', ['periode' => 'bulan_ini']));
        $petugasUtamaResp->assertStatus(200);
        $petugasUtamaResp->assertSee('id="cardMendekatiDeadline"', false);
        $this->assertSame(2, $petugasUtamaResp->viewData('tugasMendekatiDeadlineCount'));
        $petugasUtamaResp->assertSee('DL_0_HARI_KESEK');
        $petugasUtamaResp->assertSee('DL_1_HARI_KEPAN');
        $petugasUtamaResp->assertDontSee('DL_2_HARI_KESEK');

        // 5. Passive Collaborator still sees the urgent collaborative task deadline
        $petugasKolabResp = $this->actingAs($petugasKolab)->get(route('dashboard'));
        $petugasKolabResp->assertStatus(200);
        $this->assertSame(1, $petugasKolabResp->viewData('tugasMendekatiDeadlineCount'));
        $petugasKolabResp->assertSee('DL_0_HARI_KESEK');

        // 6. Verify tugas.index?deadline=mendekati works accurately for Admin and Petugas
        $indexAdminResp = $this->actingAs($admin)->get(route('tugas.index', ['deadline' => 'mendekati']));
        $indexAdminResp->assertStatus(200);
        $indexAdminResp->assertSee('Mendekati Deadline (0-2 Hari)');
        $indexAdminResp->assertSee('DL_0_HARI_KESEK');
        $indexAdminResp->assertSee('DL_1_HARI_KEPAN');
        $indexAdminResp->assertSee('DL_2_HARI_KESEK');
        $indexAdminResp->assertDontSee('EXCL_PLUS_3_HARI');

        $indexPetugasResp = $this->actingAs($petugasUtama)->get(route('tugas.index', ['deadline' => 'mendekati']));
        $indexPetugasResp->assertStatus(200);
        $indexPetugasResp->assertSee('DL_0_HARI_KESEK');
        $indexPetugasResp->assertSee('DL_1_HARI_KEPAN');
        $indexPetugasResp->assertDontSee('DL_2_HARI_KESEK');
        $indexPetugasResp->assertDontSee('EXCL_PAST_MINUS_1');
    }

    /**
     * Test trend chart groups completed tasks by actual completion timestamp and uses null for empty periods.
     */
    public function test_trend_chart_uses_actual_completion_date_and_null_for_empty_periods(): void
    {
        Carbon::setTestNow(Carbon::create(2030, 6, 15, 12, 0, 0));

        try {
            $admin = User::factory()->create(['jabatan' => 'Admin']);
            $petugas = User::factory()->create(['jabatan' => 'Petugas']);

            // Task 1: Deadline was 2 months ago (April 2030), but finished & verified today (June 2030) -> Late
            $lateTask = Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $petugas->id,
                'judul' => 'Tren_Deadline_April_Selesai_Juni',
                'status' => 'Selesai',
                'tanggal_mulai' => '2030-04-01',
                'tanggal_selesai' => '2030-04-10',
                'tanggal_tindak_lanjut' => '2030-06-15 10:00:00',
            ]);
            $log1 = $lateTask->recordLog('selesai', 'Selesai', 'Selesai di bulan Juni', null, $admin->id);
            $log1->created_at = Carbon::create(2030, 6, 15, 11, 0, 0);
            $log1->save();

            // Task 2: Deadline is next month (July 2030), completed ahead of time today (June 2030) -> On-Time
            $earlyTask = Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $petugas->id,
                'judul' => 'Tren_Deadline_Juli_Selesai_Juni',
                'status' => 'Selesai',
                'tanggal_mulai' => '2030-06-10',
                'tanggal_selesai' => '2030-07-10',
                'tanggal_tindak_lanjut' => '2030-06-15 12:00:00',
            ]);
            $log2 = $earlyTask->recordLog('selesai', 'Selesai', 'Diselesaikan lebih cepat', null, $admin->id);
            $log2->created_at = Carbon::create(2030, 6, 15, 12, 0, 0);
            $log2->save();

            $response = $this->actingAs($admin)->get(route('dashboard'));
            $response->assertStatus(200);

            $trendDatasets = $response->viewData('trendDatasets');
            $sixBulan = $trendDatasets['6_bulan'];

            // Index 3 = Apr 2030 (Jan, Feb, Mar, Apr, May, Jun 2030) -> must have 0 completed and NULL durations
            $this->assertSame(0, $sixBulan['selesai'][3]);
            $this->assertNull($sixBulan['durasi'][3]);
            $this->assertNull($sixBulan['durasi_kerja'][3]);
            $this->assertNull($sixBulan['median_durasi'][3]);
            $this->assertNull($sixBulan['target_durasi'][3]);
            $this->assertSame([], $sixBulan['tasks'][3]);

            // Index 5 = Jun 2030 -> must contain both tasks (1 on-time, 1 late)
            $this->assertSame(2, $sixBulan['selesai'][5]);
            $this->assertSame(1, $sixBulan['tepat_waktu'][5]);
            $this->assertSame(1, $sixBulan['terlambat'][5]);
            $this->assertEquals(50.0, $sixBulan['on_time_rate'][5]);
            $this->assertNotNull($sixBulan['durasi'][5]);
            $this->assertNotNull($sixBulan['durasi_kerja'][5]);
            $this->assertNotNull($sixBulan['median_durasi'][5]);
            $this->assertNotNull($sixBulan['target_durasi'][5]);

            $juneTaskIds = array_column($sixBulan['tasks'][5], 'id');
            $this->assertContains($lateTask->id, $juneTaskIds);
            $this->assertContains($earlyTask->id, $juneTaskIds);
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Test officer comparison audit metrics: sub-day precision, Mon-Fri business days, SLA efficiency, balanced scoring, and UI audit controls.
     */
    public function test_officer_comparison_audit_metrics_sla_business_days_subday_and_balanced_scoring(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 26, 15, 0, 0));

        try {
            $admin = User::factory()->create(['jabatan' => 'Admin']);
            $officerA = User::factory()->create(['name' => 'Audit Officer A Disiplin', 'jabatan' => 'Petugas']);
            $officerB = User::factory()->create(['name' => 'Audit Officer B Terlambat', 'jabatan' => 'Petugas']);
            $officerC = User::factory()->create(['name' => 'Audit Officer C Nol Tugas', 'jabatan' => 'Petugas']);

            // 1. Sub-day task for Officer A: 2.4 hours -> 0.1 day (not 1.0)
            $subDayTask = Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $officerA->id,
                'judul' => 'Tugas Cepat Sub Hari',
                'status' => 'Selesai',
                'tanggal_mulai' => '2026-11-26',
                'tanggal_selesai' => '2026-11-27',
                'created_at' => Carbon::create(2026, 11, 26, 10, 0, 0),
                'tanggal_tindak_lanjut' => Carbon::create(2026, 11, 26, 12, 24, 0),
            ]);
            $this->assertEquals(0.1, $subDayTask->getEffectiveWorkingDurationDays());

            // 2. Weekend-spanning task for Officer A: Fri 2026-11-20 00:00 -> Mon 2026-11-23 12:00 = 3.5 calendar days, 1.5 business days
            $weekendTask = Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $officerA->id,
                'judul' => 'Tugas Lintas Akhir Pekan',
                'status' => 'Selesai',
                'tanggal_mulai' => '2026-11-20',
                'tanggal_selesai' => '2026-11-25',
                'created_at' => Carbon::create(2026, 11, 19, 16, 0, 0),
                'tanggal_tindak_lanjut' => Carbon::create(2026, 11, 23, 12, 0, 0),
            ]);
            $this->assertEquals(3.5, $weekendTask->getEffectiveWorkingDurationDays());
            $this->assertEquals(1.5, $weekendTask->getEffectiveBusinessWorkingDurationDays());

            // 3. Third on-time first-pass task for Officer A (total 3 on-time, 0 revisions -> score = 3*10 + 100*0.4 + 100*0.2 = 90.0)
            Tugas::factory()->create([
                'admin_id' => $admin->id,
                'petugas_id' => $officerA->id,
                'judul' => 'Tugas Tepat Waktu Ketiga',
                'status' => 'Selesai',
                'tanggal_mulai' => '2026-11-24',
                'tanggal_selesai' => '2026-11-26',
                'created_at' => Carbon::create(2026, 11, 24, 0, 0, 0),
                'tanggal_tindak_lanjut' => Carbon::create(2026, 11, 25, 0, 0, 0),
            ]);

            // 4. Officer B: 5 late tasks with revisions (score = 0*10 + 5*4 + 0 + 0 = 20.0 < Officer A's 90.0)
            for ($i = 1; $i <= 5; $i++) {
                $late = Tugas::factory()->create([
                    'admin_id' => $admin->id,
                    'petugas_id' => $officerB->id,
                    'judul' => 'Tugas Terlambat B #'.$i,
                    'status' => 'Selesai',
                    'tanggal_mulai' => '2026-11-01',
                    'tanggal_selesai' => '2026-11-03',
                    'created_at' => Carbon::create(2026, 11, 1, 0, 0, 0),
                    'tanggal_tindak_lanjut' => Carbon::create(2026, 11, 10, 0, 0, 0),
                ]);
                $late->recordLog('revisi', 'Perlu Revisi', 'Perlu perbaikan', null, $admin->id);
            }

            $response = $this->actingAs($admin)->get(route('dashboard'));
            $response->assertStatus(200);

            // Verify Audit UI elements (dual Y-axis, toggles, methodology collapses, drilldown modal)
            $response->assertSee('y-count', false);
            $response->assertSee('y-days', false);
            $response->assertSee('id="petugasScopeBtnGroup"', false);
            $response->assertSee('id="petugasDayModeBtnGroup"', false);
            $response->assertSee('id="trendDayModeBtnGroup"', false);
            $response->assertSee('id="collapseMetodologiPetugas"', false);
            $response->assertSee('id="collapseMetodologiTren"', false);
            $response->assertSee('id="collapseKeteranganPetugas"', false);
            $response->assertSee('id="collapseKeteranganPemberi"', false);
            $response->assertSee('id="modalAuditGrafik"', false);

            $petugasStats = collect($response->viewData('petugasStats'))->values();
            $statA = $petugasStats->firstWhere('id', $officerA->id);
            $statB = $petugasStats->firstWhere('id', $officerB->id);
            $statC = $petugasStats->firstWhere('id', $officerC->id);

            $this->assertNotNull($statA);
            $this->assertNotNull($statB);
            $this->assertNotNull($statC);

            $this->assertEquals(90.0, $statA['score']);
            $this->assertEquals(100.0, $statA['on_time_rate']);
            $this->assertEquals(100.0, $statA['first_pass_rate']);
            $this->assertNotNull($statA['median_durasi']);
            $this->assertNotNull($statA['avg_sla_pct_kalender']);

            $this->assertEquals(20.0, $statB['score']);
            $this->assertEquals(0.0, $statB['on_time_rate']);
            $this->assertEquals(0.0, $statB['first_pass_rate']);
            $this->assertSame(5, $statB['revisi_total']);

            $this->assertEquals(0.0, $statC['score']);
            $this->assertNull($statC['chart_avg_durasi']);

            // Officer A (3 on-time, quality) ranks strictly ahead of Officer B (5 late, revised)
            $rankA = $petugasStats->search(fn (array $item): bool => $item['id'] === $officerA->id);
            $rankB = $petugasStats->search(fn (array $item): bool => $item['id'] === $officerB->id);
            $this->assertLessThan($rankB, $rankA);

            // Top Aktif excludes 0-completed Officer C, while Semua includes C with null chart duration
            $petugasChartDatasets = $response->viewData('petugasChartDatasets');
            $this->assertNotContains($officerC->id, $petugasChartDatasets['top_aktif']['ids']);
            $this->assertContains($officerC->id, $petugasChartDatasets['semua']['ids']);
            $idxC = array_search($officerC->id, $petugasChartDatasets['semua']['ids'], true);
            $this->assertNull($petugasChartDatasets['semua']['durasi_kalender'][$idxC]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
