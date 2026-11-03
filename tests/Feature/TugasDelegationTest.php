<?php

namespace Tests\Feature;

use App\Models\Tugas;
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
}
