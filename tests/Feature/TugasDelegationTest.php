<?php

namespace Tests\Feature;

use App\Models\Tugas;
use App\Models\TugasDiskusi;
use App\Models\TugasLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasDelegationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test admin can view all delegated tasks.
     */
    public function test_admin_can_view_all_delegated_tasks(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Perbaikan Server PA Jakarta Utara',
        ]);

        $response = $this->actingAs($admin)->get(route('tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Tugas (Delegasi Admin)');
        $response->assertSee('Perbaikan Server PA Jakarta Utara');
    }

    /**
     * Test admin can delegate task to petugas and officer status updates to Sedang Bertugas.
     */
    public function test_admin_can_delegate_task_to_petugas(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create([
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        $response = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Audit Keamanan Database',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Lakukan pengecekan berkala port dan backup database.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tugas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Audit Keamanan Database',
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Didelegasikan',
        ]);

        $petugas->refresh();
        $this->assertEquals('Sedang Bertugas', $petugas->status);
    }

    /**
     * Test admin can attach file under 10MB during delegation.
     */
    public function test_admin_can_attach_file_under_10mb_during_delegation(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $file = UploadedFile::fake()->create('petunjuk_teknis.pdf', 3000); // 3 MB

        $response = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Konfigurasi Jaringan Baru',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Rincian terlampir dalam file panduan.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d'),
            'file_tugas' => $file,
        ]);

        $response->assertRedirect(route('tugas.index'));
        $tugas = Tugas::where('judul', 'Konfigurasi Jaringan Baru')->first();

        $this->assertNotNull($tugas->file_tugas);
        Storage::disk('public')->assertExists($tugas->file_tugas);
    }

    /**
     * Test delegating task rejects file attachment over 10MB.
     */
    public function test_delegating_task_rejects_file_attachment_over_10mb(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $largeFile = UploadedFile::fake()->create('file_terlalu_besar.zip', 11000); // 11 MB > 10MB

        $response = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Migrasi Data',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Uji coba file besar.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'file_tugas' => $largeFile,
        ]);

        $response->assertSessionHasErrors('file_tugas');
    }

    /**
     * Test petugas can view only tasks delegated to them.
     */
    public function test_petugas_can_view_only_their_assigned_tasks(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugasA = User::factory()->create(['jabatan' => 'Petugas']);
        $petugasB = User::factory()->create(['jabatan' => 'Petugas']);

        $tugasA = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasA->id,
            'judul' => 'Tugas Spesifik Petugas A',
        ]);

        $tugasB = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugasB->id,
            'judul' => 'Tugas Rahasia Petugas B',
        ]);

        $response = $this->actingAs($petugasA)->get(route('tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Tugas Spesifik Petugas A');
        $response->assertDontSee('Tugas Rahasia Petugas B');
    }

    /**
     * Test petugas can submit tindak lanjut with file evidence under 10MB.
     */
    public function test_petugas_can_submit_tindak_lanjut_with_file_evidence(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Sedang Dikerjakan',
        ]);

        $evidenceFile = UploadedFile::fake()->create('laporan_hasil_audit.pdf', 5000); // 5 MB < 10MB

        $response = $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Audit jaringan telah selesai dilaksanakan tanpa kendala.',
            'file_evidence' => $evidenceFile,
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));
        $response->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('Menunggu Konfirmasi', $tugas->status);
        $this->assertNotNull($tugas->file_evidence);
        $this->assertEquals('laporan_hasil_audit.pdf', $tugas->file_evidence_name);
        Storage::disk('public')->assertExists($tugas->file_evidence);
    }

    /**
     * Test petugas can submit tindak lanjut with link evidence.
     */
    public function test_petugas_can_submit_tindak_lanjut_with_link_evidence(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Sedang Dikerjakan',
        ]);

        $response = $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Dokumentasi foto dan video telah diunggah ke Google Drive.',
            'link_evidence' => 'https://drive.google.com/drive/folders/test-evidence-folder',
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));

        $tugas->refresh();
        $this->assertEquals('Menunggu Konfirmasi', $tugas->status);
        $this->assertEquals('https://drive.google.com/drive/folders/test-evidence-folder', $tugas->link_evidence);
    }

    /**
     * Test submitting tindak lanjut fails if neither file nor link evidence is provided.
     */
    public function test_tindak_lanjut_fails_when_neither_file_nor_link_provided(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
        ]);

        $response = $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Hanya mengirimkan teks tanpa bukti berkas maupun link.',
        ]);

        $response->assertSessionHasErrors(['file_evidence', 'link_evidence']);
    }

    /**
     * Test submitting tindak lanjut rejects file evidence over 10MB.
     */
    public function test_tindak_lanjut_rejects_file_evidence_over_10mb(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
        ]);

        $largeEvidence = UploadedFile::fake()->create('laporan_raksasa.pdf', 12000); // 12 MB > 10MB

        $response = $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Laporan dengan file melebihi kapasitas batas.',
            'file_evidence' => $largeEvidence,
        ]);

        $response->assertSessionHasErrors('file_evidence');
    }

    /**
     * Test admin can verify and mark task as Selesai, updating officer status if all tasks done.
     */
    public function test_admin_can_verify_and_complete_task(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create([
            'jabatan' => 'Petugas',
            'status' => 'Sedang Bertugas',
        ]);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Menunggu Konfirmasi',
        ]);

        $response = $this->actingAs($admin)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Selesai',
            'catatan_admin' => 'Hasil pekerjaan diverifikasi dan sesuai instruksi.',
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));
        $response->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('Selesai', $tugas->status);
        $this->assertEquals('Hasil pekerjaan diverifikasi dan sesuai instruksi.', $tugas->catatan_admin);

        $petugas->refresh();
        $this->assertEquals('Belum Ditugaskan', $petugas->status);
    }

    /**
     * Test admin can request revision on submitted task.
     */
    public function test_admin_can_request_revision(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Menunggu Konfirmasi',
        ]);

        $response = $this->actingAs($admin)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Perlu Revisi',
            'catatan_admin' => 'Lampiran file evidence belum memuat tanda tangan pimpinan.',
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));

        $tugas->refresh();
        $this->assertEquals('Perlu Revisi', $tugas->status);
        $this->assertEquals('Lampiran file evidence belum memuat tanda tangan pimpinan.', $tugas->catatan_admin);
    }

    /**
     * Test admin can update task with a new file attachment.
     */
    public function test_admin_can_update_task_with_new_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);
        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Tugas Awal',
            'file_tugas' => 'tugas/file_lama.pdf',
            'file_tugas_name' => 'file_lama.pdf',
        ]);
        Storage::disk('public')->put('tugas/file_lama.pdf', 'konten lama');

        $newFile = UploadedFile::fake()->create('panduan_revisi.pdf', 2000);

        $response = $this->actingAs($admin)->put(route('tugas.update', $tugas->id), [
            'judul' => 'Tugas Telah Diperbarui',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Deskripsi baru setelah revisi.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'file_tugas' => $newFile,
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));
        $response->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('Tugas Telah Diperbarui', $tugas->judul);
        $this->assertEquals('panduan_revisi.pdf', $tugas->file_tugas_name);
        $this->assertNotEquals('tugas/file_lama.pdf', $tugas->file_tugas);
        Storage::disk('public')->assertMissing('tugas/file_lama.pdf');
        Storage::disk('public')->assertExists($tugas->file_tugas);
    }

    /**
     * Test admin can delegate task to Kepala Sub Bagian and Kepala Sub Bagian can delegate it to Petugas.
     */
    public function test_admin_can_delegate_to_kepala_sub_bagian_and_kasubbag_can_delegate_to_petugas(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $kasubbag = User::factory()->create([
            'jabatan' => 'Kepala Sub Bagian',
            'status' => 'Belum Ditugaskan',
        ]);
        $petugas = User::factory()->create([
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        // 1. Admin delegates task to Kepala Sub Bagian
        $response = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Penyusunan Laporan Evaluasi Bulanan',
            'petugas_id' => $kasubbag->id,
            'deskripsi' => 'Susun laporan evaluasi bulanan sub bagian.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tugas.index'));
        $tugas = Tugas::where('judul', 'Penyusunan Laporan Evaluasi Bulanan')->firstOrFail();

        $this->assertEquals($admin->id, $tugas->admin_id);
        $this->assertEquals($kasubbag->id, $tugas->petugas_id);

        $kasubbag->refresh();
        $this->assertEquals('Sedang Bertugas', $kasubbag->status);

        // 2. Kepala Sub Bagian delegates that received task to Petugas
        $delegateResponse = $this->actingAs($kasubbag)->post(route('tugas.delegasikan', $tugas->id), [
            'petugas_id' => $petugas->id,
            'catatan_delegasi' => 'Tolong siapkan rekapitulasi data awal dan lampirkan buktinya.',
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $delegateResponse->assertRedirect(route('tugas.show', $tugas->id));
        $delegateResponse->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals($kasubbag->id, $tugas->admin_id);
        $this->assertEquals($petugas->id, $tugas->petugas_id);
        $this->assertEquals('Didelegasikan', $tugas->status);
        $this->assertStringContainsString('Tolong siapkan rekapitulasi data awal', $tugas->deskripsi);

        $petugas->refresh();
        $kasubbag->refresh();
        $this->assertEquals('Sedang Bertugas', $petugas->status);
        $this->assertEquals('Belum Ditugaskan', $kasubbag->status);
    }

    /**
     * Test Kepala Sub Bagian can create and delegate a new task to Petugas and verify it.
     */
    public function test_kepala_sub_bagian_can_create_and_verify_task_for_petugas(): void
    {
        $kasubbag = User::factory()->create(['jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create([
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        // Kepala Sub Bagian creates a new task for Petugas
        $response = $this->actingAs($kasubbag)->post(route('tugas.store'), [
            'judul' => 'Inventarisasi Perangkat Komputer Ruang Sidang',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Lakukan pengecekan kondisi PC dan printer di seluruh ruang sidang.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tugas.index'));
        $response->assertSessionHas('success');

        $tugas = Tugas::where('judul', 'Inventarisasi Perangkat Komputer Ruang Sidang')->firstOrFail();
        $this->assertEquals($kasubbag->id, $tugas->admin_id);
        $this->assertEquals($petugas->id, $tugas->petugas_id);

        // Petugas submits tindak lanjut
        $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Inventarisasi selesai dilakukan.',
            'link_evidence' => 'https://drive.google.com/drive/folders/inventaris-pc',
        ]);

        // Kepala Sub Bagian verifies the task as Selesai
        $verifyResponse = $this->actingAs($kasubbag)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Selesai',
            'catatan_admin' => 'Terima kasih, laporan inventarisasi lengkap.',
        ]);

        $verifyResponse->assertRedirect(route('tugas.show', $tugas->id));
        $tugas->refresh();
        $this->assertEquals('Selesai', $tugas->status);
    }

    /**
     * Test Kepala Sub Bagian cannot delegate a task to an Admin.
     */
    public function test_kepala_sub_bagian_cannot_delegate_task_to_admin(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['jabatan' => 'Kepala Sub Bagian']);

        $response = $this->actingAs($kasubbag)->post(route('tugas.store'), [
            'judul' => 'Tugas Tidak Valid ke Admin',
            'petugas_id' => $admin->id,
            'deskripsi' => 'Mencoba menugaskan ke Admin.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('petugas_id');
    }

    /**
     * Test task detail page displays detail log with dates, times, and delegator notes across full lifecycle.
     */
    public function test_task_detail_displays_detail_log_with_timestamps_and_delegator_notes(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pengadilan', 'jabatan' => 'Admin']);
        $petugas = User::factory()->create(['name' => 'Budi Santoso', 'jabatan' => 'Petugas']);

        // 1. Creation / delegation
        $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Pemeliharaan Server PTSP',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Instruksi pemeliharaan server.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $tugas = Tugas::where('judul', 'Pemeliharaan Server PTSP')->firstOrFail();

        // 2. Petugas opens the task
        $this->actingAs($petugas)->get(route('tugas.show', $tugas->id));

        // 3. Petugas sends back evidence
        $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Laporan pekerjaan awal selesai dilampirkan.',
            'link_evidence' => 'https://drive.google.com/test-evidence',
        ]);

        // 4. Admin requests revision with notes
        $this->actingAs($admin)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Perlu Revisi',
            'catatan_admin' => 'Perbaiki checklist nomor 3 dan lakukan restart service.',
        ]);

        // 5. Petugas submits revised evidence
        $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugas->id), [
            'catatan_petugas' => 'Checklist nomor 3 telah diperbaiki dan service telah direstart.',
            'link_evidence' => 'https://drive.google.com/test-evidence-revisi',
        ]);

        // 6. Admin verifies and closes the task
        $this->actingAs($admin)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Selesai',
            'catatan_admin' => 'Pekerjaan telah sesuai standar operasional. Tugas resmi kami closed.',
        ]);

        // 7. Verify task logs exist in database
        $this->assertDatabaseHas('tugas_logs', [
            'tugas_id' => $tugas->id,
            'status' => 'Didelegasikan',
            'action' => 'delegasi',
        ]);

        $this->assertDatabaseHas('tugas_logs', [
            'tugas_id' => $tugas->id,
            'status' => 'Perlu Revisi',
            'catatan' => 'Perbaiki checklist nomor 3 dan lakukan restart service.',
        ]);

        $this->assertDatabaseHas('tugas_logs', [
            'tugas_id' => $tugas->id,
            'status' => 'Selesai',
            'catatan' => 'Pekerjaan telah sesuai standar operasional. Tugas resmi kami closed.',
        ]);

        // 8. View detail page and verify log UI elements
        $response = $this->actingAs($admin)->get(route('tugas.show', $tugas->id));
        $response->assertStatus(200);
        $response->assertSee('Detail Log & Riwayat Aktivitas Tugas', false);
        $response->assertSee('Perbaiki checklist nomor 3 dan lakukan restart service.');
        $response->assertSee('Pekerjaan telah sesuai standar operasional. Tugas resmi kami closed.');
        $response->assertSee('WIB');
        $response->assertSee('Catatan / Arahan Revisi dari Pendelegasi:');
        $response->assertSee('Catatan Verifikasi / Penutupan (Closed):');
    }

    /**
     * Test admin can reopen and redelegate a closed task with correction notes.
     */
    public function test_admin_can_reopen_and_redelegate_closed_task(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Utama', 'jabatan' => 'Admin']);
        $petugas = User::factory()->create([
            'name' => 'Joko Susilo',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Selesai',
            'judul' => 'Pemasangan Access Point Ruang Sidang Utama',
            'catatan_admin' => 'Tugas awal selesai.',
        ]);

        $response = $this->actingAs($admin)->post(route('tugas.buka-kembali', $tugas->id), [
            'catatan' => 'Ditemukan kabel LAN masih belum dirapikan dan SSID belum tersembunyi. Tolong rapikan segera.',
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tugas.show', $tugas->id));
        $response->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('Didelegasikan', $tugas->status);
        $this->assertEquals('Ditemukan kabel LAN masih belum dirapikan dan SSID belum tersembunyi. Tolong rapikan segera.', $tugas->catatan_admin);
        $this->assertNull($tugas->tanggal_tindak_lanjut);

        // Petugas status is updated back to Sedang Bertugas
        $petugas->refresh();
        $this->assertEquals('Sedang Bertugas', $petugas->status);

        // Verify task log recorded the reopen event
        $this->assertDatabaseHas('tugas_logs', [
            'tugas_id' => $tugas->id,
            'action' => 'buka_kembali',
            'status' => 'Didelegasikan',
            'catatan' => 'Ditemukan kabel LAN masih belum dirapikan dan SSID belum tersembunyi. Tolong rapikan segera.',
        ]);

        // Detail page shows the reopened task info and reopen button
        $showResponse = $this->actingAs($admin)->get(route('tugas.show', $tugas->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Ditemukan kabel LAN masih belum dirapikan dan SSID belum tersembunyi.');
    }

    /**
     * Test non-admin cannot reopen closed task.
     */
    public function test_petugas_cannot_reopen_closed_task(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($petugas)->post(route('tugas.buka-kembali', $tugas->id), [
            'catatan' => 'Petugas mencoba membuka kembali.',
        ]);

        $response->assertRedirect(route('tugas.index'));
        $response->assertSessionHas('error');

        $tugas->refresh();
        $this->assertEquals('Selesai', $tugas->status);
    }

    /**
     * Test petugas receives push notification when a new task is delegated by admin or forwarded by kasubbag.
     */
    public function test_petugas_receives_push_notification_when_new_task_delegated(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Delegator', 'jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['name' => 'Kasubbag Umum', 'jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create(['name' => 'Andi Pratama', 'jabatan' => 'Petugas']);

        // 1. Admin delegates new task to Petugas
        $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Penataan Arsip Perkara Perdata',
            'petugas_id' => $petugas->id,
            'deskripsi' => 'Susun dan digitalisasi berkas perkara bulan berjalan.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $this->assertEquals(1, $petugas->unreadNotifications()->count());
        $firstNotif = $petugas->unreadNotifications()->first();
        $this->assertEquals('tugas_baru', $firstNotif->data['type']);
        $this->assertEquals('Tugas Baru Didelegasikan Kepada Anda', $firstNotif->data['title']);
        $this->assertEquals('Penataan Arsip Perkara Perdata', $firstNotif->data['tugas_judul']);
        $this->assertEquals('Admin Delegator', $firstNotif->data['sender_name']);

        // Verify poll endpoint returns the push notification payload for Petugas
        $pollResponse = $this->actingAs($petugas)->getJson(route('notifikasi.poll'));
        $pollResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.tugas_judul', 'Penataan Arsip Perkara Perdata')
            ->assertJsonPath('notifications.0.type', 'tugas_baru');

        // Verify topbar on dashboard displays the notification
        $dashboardResponse = $this->actingAs($petugas)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Pusat Notifikasi');
        $dashboardResponse->assertSee('Penataan Arsip Perkara Perdata');

        // 2. Admin delegates to Kasubbag, then Kasubbag delegates to Petugas
        $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Rekapitulasi Inventaris Gedung',
            'petugas_id' => $kasubbag->id,
            'deskripsi' => 'Lakukan rekapitulasi inventaris.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(6)->format('Y-m-d'),
        ]);

        $tugasKasubbag = Tugas::where('judul', 'Rekapitulasi Inventaris Gedung')->firstOrFail();
        $this->assertEquals(1, $kasubbag->unreadNotifications()->count());

        $this->actingAs($kasubbag)->post(route('tugas.delegasikan', $tugasKasubbag->id), [
            'petugas_id' => $petugas->id,
            'catatan_delegasi' => 'Fokuskan pada lantai 1 dan lantai 2 terlebih dahulu.',
            'tanggal_selesai' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $this->assertEquals(2, $petugas->unreadNotifications()->count());
        $forwardedNotif = $petugas->unreadNotifications()
            ->get()
            ->firstWhere(fn ($n): bool => ($n->data['tugas_id'] ?? null) === $tugasKasubbag->id);

        $this->assertNotNull($forwardedNotif);
        $this->assertEquals('delegasi_lanjutan', $forwardedNotif->data['type']);
        $this->assertEquals('Fokuskan pada lantai 1 dan lantai 2 terlebih dahulu.', $forwardedNotif->data['catatan']);
    }

    /**
     * Test admin receives notification when task follow-up is completed by petugas or kepala sub bagian.
     */
    public function test_admin_receives_notification_when_task_completed_by_petugas_or_kasubbag(): void
    {
        $admin = User::factory()->create(['name' => 'Administrator Utama', 'jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['name' => 'Rina Kasubbag', 'jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create(['name' => 'Dimas Petugas', 'jabatan' => 'Petugas']);

        // 1. Petugas completes follow-up on task delegated by Admin
        $tugasPetugas = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Perbaikan Jaringan Ruang PTSP',
            'status' => 'Sedang Dikerjakan',
        ]);

        $this->actingAs($petugas)->post(route('tugas.kirim-balik', $tugasPetugas->id), [
            'catatan_petugas' => 'Switch jaringan telah diganti dan koneksi normal kembali.',
            'link_evidence' => 'https://drive.google.com/drive/folders/bukti-jaringan-ptsp',
        ]);

        $this->assertEquals(1, $admin->unreadNotifications()->count());
        $adminNotif = $admin->unreadNotifications()->first();
        $this->assertEquals('tindak_lanjut', $adminNotif->data['type']);
        $this->assertEquals('Tugas Selesai Ditindaklanjuti oleh Dimas Petugas', $adminNotif->data['title']);
        $this->assertEquals('Perbaikan Jaringan Ruang PTSP', $adminNotif->data['tugas_judul']);
        $this->assertEquals('Switch jaringan telah diganti dan koneksi normal kembali.', $adminNotif->data['catatan']);

        // 2. Kepala Sub Bagian completes follow-up on task delegated by Admin
        $tugasKasubbag = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $kasubbag->id,
            'judul' => 'Evaluasi SOP Pelayanan Publik',
            'status' => 'Sedang Dikerjakan',
        ]);

        $this->actingAs($kasubbag)->post(route('tugas.kirim-balik', $tugasKasubbag->id), [
            'catatan_petugas' => 'Dokumen evaluasi SOP telah selesai direview dan disahkan.',
            'link_evidence' => 'https://drive.google.com/drive/folders/evaluasi-sop',
        ]);

        $this->assertEquals(2, $admin->unreadNotifications()->count());

        // 3. Clicking notification marks it read and redirects to task detail
        $readResponse = $this->actingAs($admin)->get(route('notifikasi.baca', $adminNotif->id));
        $readResponse->assertRedirect(route('tugas.show', $tugasPetugas->id));
        $this->assertEquals(1, $admin->unreadNotifications()->count());

        // 4. Mark all notifications as read
        $markAllResponse = $this->actingAs($admin)->postJson(route('notifikasi.baca-semua'));
        $markAllResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 0);
        $this->assertEquals(0, $admin->unreadNotifications()->count());
    }

    /**
     * Test Admin can delegate a new task to multiple recipients (Kasubbag and Petugas) simultaneously.
     */
    public function test_admin_can_delegate_new_task_to_multiple_recipients(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['name' => 'Admin Multi', 'jabatan' => 'Admin']);
        $kasubbag = User::factory()->create([
            'name' => 'Kasubbag Perencanaan',
            'jabatan' => 'Kepala Sub Bagian',
            'status' => 'Belum Ditugaskan',
        ]);
        $petugas1 = User::factory()->create([
            'name' => 'Petugas Satu',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);
        $petugas2 = User::factory()->create([
            'name' => 'Petugas Dua',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        $attachment = UploadedFile::fake()->create('instruksi_bersama.pdf', 1200);

        $response = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Persiapan Zona Integritas WBK/WBBM',
            'petugas_id' => [$kasubbag->id, $petugas1->id, $petugas2->id],
            'deskripsi' => 'Lengkapi dokumen eviden pembangunan Zona Integritas sesuai area masing-masing.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'file_tugas' => $attachment,
        ]);

        $response->assertRedirect(route('tugas.index'));
        $response->assertSessionHas('success');

        $createdTasks = Tugas::where('judul', 'Persiapan Zona Integritas WBK/WBBM')->get();
        $this->assertCount(3, $createdTasks);

        foreach ([$kasubbag, $petugas1, $petugas2] as $recipient) {
            $this->assertDatabaseHas('tugas', [
                'judul' => 'Persiapan Zona Integritas WBK/WBBM',
                'admin_id' => $admin->id,
                'petugas_id' => $recipient->id,
                'status' => 'Didelegasikan',
                'file_tugas_name' => 'instruksi_bersama.pdf',
            ]);

            $recipient->refresh();
            $this->assertEquals('Sedang Bertugas', $recipient->status);
            $this->assertEquals(1, $recipient->unreadNotifications()->count());
        }
    }

    /**
     * Test Kepala Sub Bagian can delegate a new task or forward an existing task to multiple Petugas simultaneously.
     */
    public function test_kepala_sub_bagian_can_delegate_to_multiple_petugas(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $kasubbag = User::factory()->create([
            'name' => 'Kasubbag Kepegawaian',
            'jabatan' => 'Kepala Sub Bagian',
            'status' => 'Sedang Bertugas',
        ]);
        $petugasA = User::factory()->create([
            'name' => 'Petugas A',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);
        $petugasB = User::factory()->create([
            'name' => 'Petugas B',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
        ]);

        // 1. Kasubbag creates new task for 2 Petugas at once
        $createResponse = $this->actingAs($kasubbag)->post(route('tugas.store'), [
            'judul' => 'Pemutakhiran Data SIKEP Mahkamah Agung',
            'petugas_id' => [$petugasA->id, $petugasB->id],
            'deskripsi' => 'Perbarui dokumen KP4 dan riwayat jabatan di SIKEP.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $createResponse->assertRedirect(route('tugas.index'));
        $this->assertEquals(2, Tugas::where('judul', 'Pemutakhiran Data SIKEP Mahkamah Agung')->count());

        $petugasA->refresh();
        $petugasB->refresh();
        $this->assertEquals('Sedang Bertugas', $petugasA->status);
        $this->assertEquals('Sedang Bertugas', $petugasB->status);

        // 2. Kasubbag forwards a task from Admin to multiple Petugas at once
        $tugasDariAdmin = Tugas::factory()->create([
            'admin_id' => $admin->id,
            'petugas_id' => $kasubbag->id,
            'judul' => 'Penyusunan Laporan Tahunan Pengadilan',
            'status' => 'Didelegasikan',
        ]);

        $forwardResponse = $this->actingAs($kasubbag)->post(route('tugas.delegasikan', $tugasDariAdmin->id), [
            'petugas_id' => [$petugasA->id, $petugasB->id],
            'catatan_delegasi' => 'Bagi pengerjaan Bab II dan Bab III.',
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $forwardResponse->assertRedirect(route('tugas.show', $tugasDariAdmin->id));
        $forwardedTasks = Tugas::where('judul', 'Penyusunan Laporan Tahunan Pengadilan')->get();
        $this->assertCount(2, $forwardedTasks);

        $kasubbag->refresh();
        $this->assertEquals('Belum Ditugaskan', $kasubbag->status);
    }

    /**
     * Test task classification separates Kesekretariatan (Kasubbag) and Kepaniteraan (Panitera Muda) and is visible to all users.
     */
    public function test_tugas_klasifikasi_kesekretariatan_and_kepaniteraan_by_role_and_visible_to_all_users(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pengadilan', 'jabatan' => 'Admin']);
        $kasubbag = User::factory()->create(['name' => 'Hendra Kasubbag', 'jabatan' => 'Kepala Sub Bagian']);
        $panmud = User::factory()->create(['name' => 'Siti Panmud', 'jabatan' => 'Panitera Muda']);
        $petugas = User::factory()->create(['name' => 'Budi Pelaksana', 'jabatan' => 'Petugas']);

        // 1. Kepala Sub Bagian delegates task -> automatically enters 'Kesekretariatan' (even if 'Kepaniteraan' sent)
        $this->actingAs($kasubbag)->post(route('tugas.store'), [
            'judul' => 'Penyusunan RKAKL Kesekretariatan',
            'klasifikasi' => 'Kepaniteraan',
            'petugas_id' => [$petugas->id],
            'deskripsi' => 'Susun dokumen RKAKL tahun anggaran berjalan.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
        ])->assertRedirect(route('tugas.index'));

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Penyusunan RKAKL Kesekretariatan',
            'admin_id' => $kasubbag->id,
            'petugas_id' => $petugas->id,
            'klasifikasi' => 'Kesekretariatan',
        ]);

        // 2. Panitera Muda delegates task -> automatically enters 'Kepaniteraan' (even if 'Kesekretariatan' sent)
        $this->actingAs($panmud)->post(route('tugas.store'), [
            'judul' => 'Minutasi Berkas Perkara Gugatan',
            'klasifikasi' => 'Kesekretariatan',
            'petugas_id' => [$petugas->id],
            'deskripsi' => 'Selesaikan minutasi dan upload putusan ke SIPP.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(4)->format('Y-m-d'),
        ])->assertRedirect(route('tugas.index'));

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Minutasi Berkas Perkara Gugatan',
            'admin_id' => $panmud->id,
            'petugas_id' => $petugas->id,
            'klasifikasi' => 'Kepaniteraan',
        ]);

        // Clear notifications so topbar notification dropdown doesn't interfere with table filter assertions
        $petugas->notifications()->delete();

        // 3. All user types (Admin, Kasubbag, Panitera Muda, Petugas) can view both classifications on Data Tugas
        foreach ([$admin, $kasubbag, $panmud, $petugas] as $viewer) {
            $allResponse = $this->actingAs($viewer)->get(route('tugas.index'));
            $allResponse->assertStatus(200);
            $allResponse->assertSee('Kesekretariatan');
            $allResponse->assertSee('Kepaniteraan');
            $allResponse->assertSee('Penyusunan RKAKL Kesekretariatan');
            $allResponse->assertSee('Minutasi Berkas Perkara Gugatan');

            // Filter only Kesekretariatan
            $sekretariatResponse = $this->actingAs($viewer)->get(route('tugas.index', ['klasifikasi' => 'Kesekretariatan']));
            $sekretariatResponse->assertStatus(200);
            $sekretariatResponse->assertSee('Penyusunan RKAKL Kesekretariatan');
            $sekretariatResponse->assertDontSee('Minutasi Berkas Perkara Gugatan');

            // Filter only Kepaniteraan
            $kepaniteraanResponse = $this->actingAs($viewer)->get(route('tugas.index', ['klasifikasi' => 'Kepaniteraan']));
            $kepaniteraanResponse->assertStatus(200);
            $kepaniteraanResponse->assertSee('Minutasi Berkas Perkara Gugatan');
            $kepaniteraanResponse->assertDontSee('Penyusunan RKAKL Kesekretariatan');
        }
    }

    /**
     * Test only Admin and Pemberi Tugas can set or release HOLD status on a task.
     */
    public function test_only_admin_and_pemberi_tugas_can_set_and_release_hold_status(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Utama', 'jabatan' => 'Admin']);
        $pemberiKasubbag = User::factory()->create(['name' => 'Kasubbag Pemberi', 'jabatan' => 'Kepala Sub Bagian']);
        $otherKasubbag = User::factory()->create(['name' => 'Kasubbag Lain', 'jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create(['name' => 'Petugas Pelaksana', 'jabatan' => 'Petugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $pemberiKasubbag->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Sinkronisasi Server Backup',
            'status' => 'Sedang Dikerjakan',
        ]);

        // 1. Petugas cannot set task to HOLD
        $petugasHoldResp = $this->actingAs($petugas)->post(route('tugas.hold', $tugas->id), [
            'action' => 'hold',
            'catatan_hold' => 'Petugas mencoba hold sendiri.',
        ]);
        $petugasHoldResp->assertRedirect(route('tugas.index'));
        $petugasHoldResp->assertSessionHas('error');
        $tugas->refresh();
        $this->assertEquals('Sedang Dikerjakan', $tugas->status);

        // 2. Another Kasubbag who is NOT the delegator (pemberi tugas) cannot set task to HOLD
        $otherHoldResp = $this->actingAs($otherKasubbag)->post(route('tugas.hold', $tugas->id), [
            'action' => 'hold',
            'catatan_hold' => 'Kasubbag lain mencoba hold.',
        ]);
        $otherHoldResp->assertRedirect(route('tugas.index'));
        $otherHoldResp->assertSessionHas('error');
        $tugas->refresh();
        $this->assertEquals('Sedang Dikerjakan', $tugas->status);

        // 3. Pemberi Tugas (Kasubbag Pemberi) CAN set task to HOLD
        $pemberiHoldResp = $this->actingAs($pemberiKasubbag)->post(route('tugas.hold', $tugas->id), [
            'action' => 'hold',
            'catatan_hold' => 'Menunggu suku cadang harddisk dari vendor.',
        ]);
        $pemberiHoldResp->assertRedirect(route('tugas.show', $tugas->id));
        $pemberiHoldResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('HOLD', $tugas->status);
        $this->assertNotNull($tugas->hold_started_at);
        $this->assertEquals('Menunggu suku cadang harddisk dari vendor.', $tugas->catatan_admin);

        // Verify detail page shows HOLD badge and explanation
        $showResp = $this->actingAs($pemberiKasubbag)->get(route('tugas.show', $tugas->id));
        $showResp->assertStatus(200);
        $showResp->assertSee('Tugas Sedang Berstatus HOLD');
        $showResp->assertSee('Menunggu suku cadang harddisk dari vendor.');

        // 4. Admin CAN release HOLD status and resume the task
        $adminResumeResp = $this->actingAs($admin)->post(route('tugas.hold', $tugas->id), [
            'action' => 'lanjutkan',
            'catatan_hold' => 'Suku cadang sudah tiba, silakan lanjutkan.',
        ]);
        $adminResumeResp->assertRedirect(route('tugas.show', $tugas->id));
        $adminResumeResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('Sedang Dikerjakan', $tugas->status);
        $this->assertNull($tugas->hold_started_at);
    }

    /**
     * Test Petugas can submit checklist request to HOLD task or extend deadline, and Pemberi Tugas can approve it.
     */
    public function test_petugas_can_submit_checklist_request_for_hold_and_deadline_extension_and_delegator_can_approve(): void
    {
        $kasubbag = User::factory()->create(['name' => 'Kasubbag Pemberi', 'jabatan' => 'Kepala Sub Bagian']);
        $petugas = User::factory()->create(['name' => 'Petugas Pemohon', 'jabatan' => 'Petugas']);

        $initialDeadline = now()->addDays(3)->format('Y-m-d');
        $proposedDeadline = now()->addDays(7)->format('Y-m-d');

        $tugas = Tugas::factory()->create([
            'admin_id' => $kasubbag->id,
            'petugas_id' => $petugas->id,
            'judul' => 'Pembaruan Jaringan LAN Ruang Arsip',
            'status' => 'Sedang Dikerjakan',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => $initialDeadline,
        ]);

        // 1. Petugas views detail page and sees the Panel Ceklist Permohonan Petugas
        $showResp = $this->actingAs($petugas)->get(route('tugas.show', $tugas->id));
        $showResp->assertStatus(200);
        $showResp->assertSee('Panel Ceklist Permohonan Petugas kepada Pemberi Tugas');
        $showResp->assertSee('Meminta HOLD Tugas');
        $showResp->assertSee('Meminta Edit Tugas untuk Memperpanjang Masa Pengerjaan');

        // 2. Petugas submits checklist requesting BOTH Hold and Deadline Extension
        $requestResp = $this->actingAs($petugas)->post(route('tugas.permohonan', $tugas->id), [
            'jenis_permohonan' => ['hold', 'perpanjangan'],
            'usulan_tanggal_selesai' => $proposedDeadline,
            'catatan_permohonan' => 'Ruang arsip sedang direnovasi sehingga instalasi kabel harus ditunda dan diperpanjang 4 hari.',
        ]);

        $requestResp->assertRedirect(route('tugas.show', $tugas->id));
        $requestResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertTrue($tugas->permohonan_hold);
        $this->assertTrue($tugas->permohonan_perpanjangan);
        $this->assertEquals($proposedDeadline, $tugas->usulan_tanggal_selesai->format('Y-m-d'));
        $this->assertEquals('Menunggu Persetujuan', $tugas->status_permohonan);
        $this->assertEquals(1, $kasubbag->unreadNotifications()->count());

        // 3. Pemberi Tugas (Kasubbag) approves the checklist request -> Task becomes HOLD and deadline is extended
        $approveResp = $this->actingAs($kasubbag)->post(route('tugas.permohonan.tanggapi', $tugas->id), [
            'keputusan' => 'setujui',
            'tanggal_selesai_baru' => $proposedDeadline,
            'catatan_admin' => 'Permohonan disetujui, tugas di-HOLD selama renovasi dan deadline diperpanjang.',
        ]);

        $approveResp->assertRedirect(route('tugas.show', $tugas->id));
        $approveResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertEquals('HOLD', $tugas->status);
        $this->assertNotNull($tugas->hold_started_at);
        $this->assertEquals($proposedDeadline, $tugas->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals('Disetujui', $tugas->status_permohonan);
    }

    /**
     * Test Admin can create a collaborative multi-petugas task and manage collaborators on detail page.
     */
    public function test_admin_can_create_collaborative_multi_petugas_task_and_manage_kolaborator(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Kolaborasi', 'jabatan' => 'Admin']);
        $petugasA = User::factory()->create(['name' => 'Petugas PIC Utama', 'jabatan' => 'Petugas', 'status' => 'Belum Ditugaskan']);
        $petugasB = User::factory()->create(['name' => 'Petugas Kolaborator B', 'jabatan' => 'Petugas', 'status' => 'Belum Ditugaskan']);
        $petugasC = User::factory()->create(['name' => 'Petugas Kolaborator C', 'jabatan' => 'Petugas', 'status' => 'Belum Ditugaskan']);

        $initialTaskCount = Tugas::count();

        // 1. Admin creates 1 collaborative task for 2 officers using mode_delegasi = kolaborasi
        $storeResp = $this->actingAs($admin)->post(route('tugas.store'), [
            'judul' => 'Migrasi Database Kolaborasi Tim',
            'klasifikasi' => 'Kesekretariatan',
            'mode_delegasi' => 'kolaborasi',
            'petugas_id' => [$petugasA->id, $petugasB->id],
            'deskripsi' => 'Kerjakan migrasi bersama dalam 1 ruang diskusi.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $storeResp->assertRedirect(route('tugas.index'));
        $storeResp->assertSessionHas('success');

        $this->assertSame($initialTaskCount + 1, Tugas::count());

        $tugas = Tugas::where('judul', 'Migrasi Database Kolaborasi Tim')->latest('id')->firstOrFail();
        $this->assertSame($petugasA->id, $tugas->petugas_id);
        $this->assertTrue($tugas->isAssignedTo($petugasA));
        $this->assertTrue($tugas->isAssignedTo($petugasB));
        $this->assertFalse($tugas->isAssignedTo($petugasC));
        $this->assertSame('Sedang Bertugas', $petugasA->fresh()->status);
        $this->assertSame('Sedang Bertugas', $petugasB->fresh()->status);

        // 2. Collaborating officer B sees the task in index & can view detail page
        $indexRespB = $this->actingAs($petugasB)->get(route('tugas.index'));
        $indexRespB->assertStatus(200);
        $indexRespB->assertSee('Migrasi Database Kolaborasi Tim');

        $showRespB = $this->actingAs($petugasB)->get(route('tugas.show', $tugas->id));
        $showRespB->assertStatus(200);
        $showRespB->assertSee('Ruang Diskusi');
        $showRespB->assertSee('Petugas Kolaborator B');
        $this->assertSame('Sedang Dikerjakan', $tugas->fresh()->status);

        // 3. Unassigned officer C cannot view detail page
        $showRespC = $this->actingAs($petugasC)->get(route('tugas.show', $tugas->id));
        $showRespC->assertRedirect(route('tugas.index'));

        // 4. Admin updates collaborators to swap B out and bring C in
        $syncResp = $this->actingAs($admin)->post(route('tugas.kolaborator.sync', $tugas->id), [
            'kolaborator_ids' => [$petugasC->id],
        ]);
        $syncResp->assertRedirect(route('tugas.show', $tugas->id));
        $syncResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertFalse($tugas->isAssignedTo($petugasB));
        $this->assertTrue($tugas->isAssignedTo($petugasC));
        $this->assertSame('Belum Ditugaskan', $petugasB->fresh()->status);
        $this->assertSame('Sedang Bertugas', $petugasC->fresh()->status);
        $this->assertDatabaseHas('tugas_logs', [
            'tugas_id' => $tugas->id,
            'action' => 'kolaborator',
        ]);
    }

    /**
     * Test author and multiple officers can chat work progress without uploading evidence or triggering review.
     */
    public function test_author_and_multi_petugas_can_chat_progress_without_uploading_evidence(): void
    {
        $author = User::factory()->create(['name' => 'Kasubbag Author', 'jabatan' => 'Kepala Sub Bagian']);
        $pic = User::factory()->create(['name' => 'Petugas Satu', 'jabatan' => 'Petugas']);
        $kolaborator = User::factory()->create(['name' => 'Petugas Dua', 'jabatan' => 'Petugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $author->id,
            'petugas_id' => $pic->id,
            'judul' => 'Koordinasi Laporan Bulanan',
            'status' => 'Sedang Dikerjakan',
        ]);
        $tugas->kolaborator()->attach($kolaborator->id);

        // Collaborator posts text-only progress update (no file, no formal evidence)
        $chatResp1 = $this->actingAs($kolaborator)->post(route('tugas.diskusi.store', $tugas->id), [
            'pesan' => 'Tahap rekap data transaksi sudah mencapai 70 persen.',
        ]);
        $chatResp1->assertRedirect(route('tugas.show', $tugas->id).'#ruang-diskusi');
        $chatResp1->assertSessionHas('success');

        // Author replies in chat
        $chatResp2 = $this->actingAs($author)->post(route('tugas.diskusi.store', $tugas->id), [
            'pesan' => 'Bagus, lanjutkan validasi bagian lampiran sebelum finalisasi.',
        ]);
        $chatResp2->assertRedirect(route('tugas.show', $tugas->id).'#ruang-diskusi');

        $tugas->refresh();
        $this->assertSame('Sedang Dikerjakan', $tugas->status);
        $this->assertSame(2, TugasDiskusi::where('tugas_id', $tugas->id)->count());
        $this->assertSame(2, TugasLog::where('tugas_id', $tugas->id)->where('action', 'diskusi')->count());

        $showResp = $this->actingAs($pic)->get(route('tugas.show', $tugas->id));
        $showResp->assertStatus(200);
        $showResp->assertSee('Tahap rekap data transaksi sudah mencapai 70 persen.');
        $showResp->assertSee('Bagus, lanjutkan validasi bagian lampiran sebelum finalisasi.');
    }

    /**
     * Test uploading a file in progress chat logs clickable URL in task logs without auto-transitioning status to review.
     */
    public function test_uploading_file_in_chat_logs_clickable_url_without_auto_review(): void
    {
        Storage::fake('public');

        $author = User::factory()->create(['name' => 'Panitera Author', 'jabatan' => 'Panitera Muda']);
        $pic = User::factory()->create(['name' => 'Petugas File Chat', 'jabatan' => 'Petugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $author->id,
            'petugas_id' => $pic->id,
            'judul' => 'Audit Berkas Perkara',
            'status' => 'Sedang Dikerjakan',
        ]);

        $file = UploadedFile::fake()->create('progres_tahap_1.pdf', 150, 'application/pdf');

        $resp = $this->actingAs($pic)->post(route('tugas.diskusi.store', $tugas->id), [
            'pesan' => 'Berikut draf awal progres tahap 1 untuk dicek.',
            'file_lampiran' => $file,
        ]);

        $resp->assertRedirect(route('tugas.show', $tugas->id).'#ruang-diskusi');
        $resp->assertSessionHas('success');

        $tugas->refresh();
        // Status must NOT automatically transition to Menunggu Konfirmasi or Selesai
        $this->assertSame('Sedang Dikerjakan', $tugas->status);
        $this->assertNull($tugas->file_evidence);

        $diskusi = TugasDiskusi::where('tugas_id', $tugas->id)->firstOrFail();
        $this->assertNotNull($diskusi->file_path);
        $this->assertSame('progres_tahap_1.pdf', $diskusi->file_name);
        Storage::disk('public')->assertExists($diskusi->file_path);

        $log = TugasLog::where('tugas_id', $tugas->id)->where('action', 'diskusi')->firstOrFail();
        $this->assertSame('progres_tahap_1.pdf', $log->file_name);
        $this->assertNotNull($log->file_url);
        $this->assertStringContainsString('/storage/diskusi/', $log->file_url);

        $showResp = $this->actingAs($author)->get(route('tugas.show', $tugas->id));
        $showResp->assertStatus(200);
        $showResp->assertSee($log->file_url);
        $showResp->assertSee('progres_tahap_1.pdf');
    }

    /**
     * Test only Author or Admin can close a task (Selesai), even when progress was only shared via chat.
     */
    public function test_only_author_or_admin_can_close_task_even_without_formal_evidence(): void
    {
        $author = User::factory()->create(['name' => 'Kasubbag Pemilik', 'jabatan' => 'Kepala Sub Bagian']);
        $pic = User::factory()->create(['name' => 'Petugas Utama', 'jabatan' => 'Petugas', 'status' => 'Sedang Bertugas']);
        $kolaborator = User::factory()->create(['name' => 'Petugas Pendukung', 'jabatan' => 'Petugas', 'status' => 'Sedang Bertugas']);

        $tugas = Tugas::factory()->create([
            'admin_id' => $author->id,
            'petugas_id' => $pic->id,
            'judul' => 'Finalisasi Dokumen Internal',
            'status' => 'Sedang Dikerjakan',
            'tanggal_tindak_lanjut' => null,
        ]);
        $tugas->kolaborator()->attach($kolaborator->id);

        // 1. Primary PIC & Collaborator cannot close the task
        foreach ([$pic, $kolaborator] as $officer) {
            $attempt = $this->actingAs($officer)->post(route('tugas.verifikasi', $tugas->id), [
                'status' => 'Selesai',
                'catatan_admin' => 'Mencoba menutup tugas sendiri.',
            ]);
            $attempt->assertRedirect(route('tugas.index'));
            $attempt->assertSessionHas('error');
            $this->assertSame('Sedang Dikerjakan', $tugas->fresh()->status);
        }

        // 2. Author CAN declare the task Selesai (Closed) without formal evidence submission
        $closeResp = $this->actingAs($author)->post(route('tugas.verifikasi', $tugas->id), [
            'status' => 'Selesai',
            'catatan_admin' => 'Hasil pekerjaan melalui diskusi sudah lengkap dan diterima.',
        ]);

        $closeResp->assertRedirect(route('tugas.show', $tugas->id));
        $closeResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertSame('Selesai', $tugas->status);
        $this->assertNotNull($tugas->tanggal_tindak_lanjut);
        $this->assertSame('Belum Ditugaskan', $pic->fresh()->status);
        $this->assertSame('Belum Ditugaskan', $kolaborator->fresh()->status);
    }

    /**
     * Test Kesekretariatan delegator assigning a task to Kepaniteraan officer requires Panitera Muda verification & approval.
     */
    public function test_kesekretariatan_delegating_to_kepaniteraan_petugas_requires_panitera_muda_approval(): void
    {
        $kasubbagDelegator = User::factory()->create([
            'name' => 'Kasubbag Kesekretariatan',
            'jabatan' => 'Kepala Sub Bagian',
            'klasifikasi' => 'Kesekretariatan',
        ]);
        $otherKasubbag = User::factory()->create([
            'name' => 'Kasubbag Lain',
            'jabatan' => 'Kepala Sub Bagian',
            'klasifikasi' => 'Kesekretariatan',
        ]);
        $paniteraMuda = User::factory()->create([
            'name' => 'Panitera Muda Penyetuju',
            'jabatan' => 'Panitera Muda',
            'klasifikasi' => 'Kepaniteraan',
        ]);
        $petugasKepaniteraan = User::factory()->create([
            'name' => 'Petugas Unit Kepaniteraan',
            'jabatan' => 'Petugas',
            'klasifikasi' => 'Kepaniteraan',
            'status' => 'Belum Ditugaskan',
        ]);

        // 1. Kasubbag delegates task to Kepaniteraan officer
        $storeResp = $this->actingAs($kasubbagDelegator)->post(route('tugas.store'), [
            'judul' => 'Verifikasi Berkas Lintas Bidang Kepaniteraan',
            'petugas_id' => [$petugasKepaniteraan->id],
            'deskripsi' => 'Mohon dukungan data register perkara bulanan.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $storeResp->assertRedirect(route('tugas.index'));
        $storeResp->assertSessionHas('success');

        $tugas = Tugas::where('judul', 'Verifikasi Berkas Lintas Bidang Kepaniteraan')->latest('id')->firstOrFail();
        $this->assertSame('Menunggu Persetujuan Atasan', $tugas->status);
        $this->assertTrue($tugas->perlu_persetujuan_atasan);
        $this->assertSame('Panitera Muda', $tugas->jabatan_atasan_penyetuju);
        $this->assertSame('Menunggu Persetujuan', $tugas->status_persetujuan_atasan);
        $this->assertSame('Belum Ditugaskan', $petugasKepaniteraan->fresh()->status);
        $this->assertEquals(1, $paniteraMuda->unreadNotifications()->count());
        $this->assertEquals(0, $petugasKepaniteraan->unreadNotifications()->count());

        // 2. Officer cannot submit tindak lanjut before supervisor approves
        $blockedTindakLanjut = $this->actingAs($petugasKepaniteraan)->get(route('tugas.tindak-lanjut', $tugas->id));
        $blockedTindakLanjut->assertRedirect(route('tugas.show', $tugas->id));
        $blockedTindakLanjut->assertSessionHas('error');

        // 3. Wrong supervisor role (another Kasubbag) cannot approve
        $wrongApproverResp = $this->actingAs($otherKasubbag)->post(route('tugas.persetujuan-atasan', $tugas->id), [
            'keputusan' => 'setujui',
        ]);
        $wrongApproverResp->assertRedirect(route('tugas.show', $tugas->id));
        $wrongApproverResp->assertSessionHas('error');
        $this->assertSame('Menunggu Persetujuan Atasan', $tugas->fresh()->status);

        // 4. Panitera Muda sees approval panel and approves the cross-unit delegation
        $showPanmud = $this->actingAs($paniteraMuda)->get(route('tugas.show', $tugas->id));
        $showPanmud->assertStatus(200);
        $showPanmud->assertSee('Persetujuan Delegasi Lintas Bidang');
        $showPanmud->assertSee('Setujui &amp; Teruskan ke Petugas', false);

        $approveResp = $this->actingAs($paniteraMuda)->post(route('tugas.persetujuan-atasan', $tugas->id), [
            'keputusan' => 'setujui',
            'catatan_persetujuan_atasan' => 'Disetujui, silakan Petugas Kepaniteraan menindaklanjuti.',
        ]);
        $approveResp->assertRedirect(route('tugas.show', $tugas->id));
        $approveResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertSame('Didelegasikan', $tugas->status);
        $this->assertSame('Disetujui', $tugas->status_persetujuan_atasan);
        $this->assertSame($paniteraMuda->id, $tugas->disetujui_atasan_oleh);
        $this->assertNotNull($tugas->tanggal_persetujuan_atasan);
        $this->assertSame('Sedang Bertugas', $petugasKepaniteraan->fresh()->status);
        $this->assertEquals(1, $petugasKepaniteraan->unreadNotifications()->count());
        $this->assertEquals(1, $kasubbagDelegator->unreadNotifications()->count());
    }

    /**
     * Test Kepaniteraan delegator assigning a task to Kesekretariatan officer requires Kepala Sub Bagian approval and can be rejected.
     */
    public function test_kepaniteraan_delegating_to_kesekretariatan_petugas_requires_kasubbag_approval_and_can_be_rejected(): void
    {
        $panmudDelegator = User::factory()->create([
            'name' => 'Panitera Muda Pendelegasi',
            'jabatan' => 'Panitera Muda',
            'klasifikasi' => 'Kepaniteraan',
        ]);
        $otherPanmud = User::factory()->create([
            'name' => 'Panitera Muda Lain',
            'jabatan' => 'Panitera Muda',
            'klasifikasi' => 'Kepaniteraan',
        ]);
        $kasubbagPenyetuju = User::factory()->create([
            'name' => 'Kasubbag Kesekretariatan Penyetuju',
            'jabatan' => 'Kepala Sub Bagian',
            'klasifikasi' => 'Kesekretariatan',
        ]);
        $petugasSekretariat = User::factory()->create([
            'name' => 'Petugas Unit Kesekretariatan',
            'jabatan' => 'Petugas',
            'klasifikasi' => 'Kesekretariatan',
            'status' => 'Belum Ditugaskan',
        ]);

        // 1. Panitera Muda delegates task to Kesekretariatan officer
        $this->actingAs($panmudDelegator)->post(route('tugas.store'), [
            'judul' => 'Permintaan Pengadaan ATK Persidangan',
            'petugas_id' => [$petugasSekretariat->id],
            'deskripsi' => 'Butuh pengadaan ATK segera untuk ruang sidang.',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
        ])->assertRedirect(route('tugas.index'));

        $tugas = Tugas::where('judul', 'Permintaan Pengadaan ATK Persidangan')->latest('id')->firstOrFail();
        $this->assertSame('Menunggu Persetujuan Atasan', $tugas->status);
        $this->assertSame('Kepala Sub Bagian', $tugas->jabatan_atasan_penyetuju);
        $this->assertSame('Menunggu Persetujuan', $tugas->status_persetujuan_atasan);
        $this->assertEquals(1, $kasubbagPenyetuju->unreadNotifications()->count());

        // 2. Another Panitera Muda cannot approve a Kesekretariatan-bound task
        $this->actingAs($otherPanmud)->post(route('tugas.persetujuan-atasan', $tugas->id), [
            'keputusan' => 'setujui',
        ])->assertRedirect(route('tugas.show', $tugas->id))->assertSessionHas('error');

        // 3. Kepala Sub Bagian rejects the cross-unit delegation
        $rejectResp = $this->actingAs($kasubbagPenyetuju)->post(route('tugas.persetujuan-atasan', $tugas->id), [
            'keputusan' => 'tolak',
            'catatan_persetujuan_atasan' => 'Anggaran triwulan ini sedang dibekukan, ajukan kembali bulan depan.',
        ]);
        $rejectResp->assertRedirect(route('tugas.show', $tugas->id));
        $rejectResp->assertSessionHas('success');

        $tugas->refresh();
        $this->assertSame('Ditolak Atasan', $tugas->status);
        $this->assertSame('Ditolak', $tugas->status_persetujuan_atasan);
        $this->assertSame($kasubbagPenyetuju->id, $tugas->disetujui_atasan_oleh);
        $this->assertSame('Belum Ditugaskan', $petugasSekretariat->fresh()->status);
        $this->assertEquals(1, $panmudDelegator->unreadNotifications()->count());
        $this->assertEquals(0, $petugasSekretariat->unreadNotifications()->count());
    }
}
