<?php

namespace App\Notifications;

use App\Models\Tugas;
use App\Models\User;
use Illuminate\Notifications\Notification;

class TugasNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Tugas $tugas,
        public User $sender,
        public string $type,
        public ?string $catatan = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array{
     *     type: string,
     *     badge_label: string,
     *     badge_color: string,
     *     icon: string,
     *     header_tag: string,
     *     title: string,
     *     message: string,
     *     catatan: string|null,
     *     action_text: string,
     *     tugas_id: int,
     *     tugas_judul: string,
     *     tugas_status: string,
     *     tanggal_mulai: string|null,
     *     tanggal_selesai: string|null,
     *     evidence_info: string|null,
     *     sender_id: int,
     *     sender_name: string,
     *     sender_jabatan: string,
     *     url: string,
     *     waktu_wib: string
     * }
     */
    public function toArray(object $notifiable): array
    {
        $deadlineFormatted = $this->tugas->tanggal_selesai
            ? $this->tugas->tanggal_selesai->format('d/m/Y')
            : '-';

        $mulaiFormatted = $this->tugas->tanggal_mulai
            ? $this->tugas->tanggal_mulai->format('d/m/Y')
            : null;

        $evidenceInfo = null;
        if ($this->type === 'tindak_lanjut') {
            if ($this->tugas->file_evidence && $this->tugas->link_evidence) {
                $evidenceInfo = 'Berkas Lampiran & Tautan Evidence';
            } elseif ($this->tugas->file_evidence) {
                $evidenceInfo = 'Berkas: '.($this->tugas->file_evidence_name ?? 'Lampiran Evidence');
            } elseif ($this->tugas->link_evidence) {
                $evidenceInfo = 'Tautan (Link) Evidence';
            }
        }

        $meta = match ($this->type) {
            'tugas_baru' => [
                'badge_label' => 'Delegasi Tugas Baru',
                'badge_color' => 'primary',
                'icon' => 'fas fa-clipboard-list',
                'header_tag' => 'PUSH NOTIFIKASI • TUGAS BARU',
                'title' => 'Tugas Baru Didelegasikan Kepada Anda',
                'message' => "Anda menerima delegasi tugas baru \"{$this->tugas->judul}\" dari {$this->sender->name} ({$this->sender->jabatan}) dengan batas waktu pengerjaan hingga {$deadlineFormatted}. Silakan segera tinjau instruksi dan mulai tindak lanjut.",
                'action_text' => 'Buka & Kerjakan Tugas',
            ],
            'delegasi_lanjutan' => [
                'badge_label' => 'Delegasi Tugas Lanjutan',
                'badge_color' => 'info',
                'icon' => 'fas fa-share-square',
                'header_tag' => 'PUSH NOTIFIKASI • DELEGASI TUGAS',
                'title' => 'Tugas Baru Diteruskan Kepada Anda',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) mendelegasikan tugas \"{$this->tugas->judul}\" kepada Anda dengan batas penyelesaian {$deadlineFormatted}. Silakan periksa arahan tugas dan laksanakan tindak lanjut.",
                'action_text' => 'Buka & Kerjakan Tugas',
            ],
            'tindak_lanjut' => [
                'badge_label' => 'Tindak Lanjut Selesai',
                'badge_color' => 'success',
                'icon' => 'fas fa-file-signature',
                'header_tag' => 'PUSH NOTIFIKASI • TINDAK LANJUT TUGAS',
                'title' => "Tugas Selesai Ditindaklanjuti oleh {$this->sender->name}",
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) telah menyelesaikan tindak lanjut tugas \"{$this->tugas->judul}\" beserta bukti pengerjaan (evidence). Tugas kini berstatus Menunggu Konfirmasi dan siap untuk Anda verifikasi.",
                'action_text' => 'Periksa & Verifikasi Tugas',
            ],
            'revisi' => [
                'badge_label' => 'Permintaan Revisi Tugas',
                'badge_color' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'header_tag' => 'PUSH NOTIFIKASI • PERLU REVISI',
                'title' => 'Tugas Dikembalikan untuk Revisi',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) meminta perbaikan/revisi pada tugas \"{$this->tugas->judul}\". Silakan periksa catatan evaluasi pendelegasi dan kirimkan ulang bukti perbaikan.",
                'action_text' => 'Lihat Catatan Revisi',
            ],
            'buka_kembali' => [
                'badge_label' => 'Tugas Dibuka Kembali',
                'badge_color' => 'danger',
                'icon' => 'fas fa-undo-alt',
                'header_tag' => 'PUSH NOTIFIKASI • DELEGASI ULANG',
                'title' => 'Tugas Dibuka Kembali & Didelegasikan Ulang',
                'message' => "Tugas \"{$this->tugas->judul}\" dibuka kembali dan didelegasikan ulang kepada Anda oleh {$this->sender->name} ({$this->sender->jabatan}) karena terdapat koreksi/perbaikan pengerjaan.",
                'action_text' => 'Periksa Koreksi Tugas',
            ],
            'selesai' => [
                'badge_label' => 'Verifikasi Tugas Selesai',
                'badge_color' => 'success',
                'icon' => 'fas fa-check-double',
                'header_tag' => 'PUSH NOTIFIKASI • TUGAS CLOSED',
                'title' => 'Tugas Diverifikasi & Dinyatakan Selesai',
                'message' => "Laporan tindak lanjut Anda pada tugas \"{$this->tugas->judul}\" telah diverifikasi dan dinyatakan Selesai (Closed) oleh {$this->sender->name} ({$this->sender->jabatan}).",
                'action_text' => 'Lihat Detail Tugas',
            ],
            'hold' => [
                'badge_label' => 'Status Tugas: HOLD',
                'badge_color' => 'secondary',
                'icon' => 'fas fa-pause-circle',
                'header_tag' => 'PUSH NOTIFIKASI • TUGAS HOLD',
                'title' => 'Pengerjaan Tugas Ditunda Sementara (HOLD)',
                'message' => "Tugas \"{$this->tugas->judul}\" diubah menjadi status HOLD oleh {$this->sender->name} ({$this->sender->jabatan}). Selama berstatus HOLD, durasi penundaan tidak dihitung dalam keterlambatan performa.",
                'action_text' => 'Lihat Detail Tugas',
            ],
            'lanjutkan' => [
                'badge_label' => 'Status HOLD Dicabut',
                'badge_color' => 'primary',
                'icon' => 'fas fa-play-circle',
                'header_tag' => 'PUSH NOTIFIKASI • LANJUTKAN TUGAS',
                'title' => 'Status HOLD Dicabut & Tugas Dilanjutkan',
                'message' => "Status HOLD pada tugas \"{$this->tugas->judul}\" telah dicabut oleh {$this->sender->name} ({$this->sender->jabatan}). Silakan lanjutkan kembali pengerjaan tugas.",
                'action_text' => 'Buka & Lanjutkan Tugas',
            ],
            'permohonan_petugas' => [
                'badge_label' => 'Permohonan Hold / Perpanjangan',
                'badge_color' => 'warning',
                'icon' => 'fas fa-hand-paper',
                'header_tag' => 'PUSH NOTIFIKASI • PERMOHONAN PETUGAS',
                'title' => "Permohonan Petugas pada Tugas: {$this->tugas->judul}",
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) mengajukan permohonan terkait status HOLD dan/atau perpanjangan masa pengerjaan tugas \"{$this->tugas->judul}\". Silakan tinjau detail tugas untuk menindaklanjuti.",
                'action_text' => 'Tinjau Permohonan Petugas',
            ],
            'tanggapi_permohonan' => [
                'badge_label' => 'Tanggapan Permohonan Tugas',
                'badge_color' => 'info',
                'icon' => 'fas fa-calendar-check',
                'header_tag' => 'PUSH NOTIFIKASI • TANGGAPAN PEMBERI TUGAS',
                'title' => "Permohonan Tugas Ditanggapi oleh {$this->sender->name}",
                'message' => "Permohonan Anda pada tugas \"{$this->tugas->judul}\" telah ditanggapi ({$this->tugas->status_permohonan}) oleh {$this->sender->name} ({$this->sender->jabatan}).",
                'action_text' => 'Lihat Detail Tugas',
            ],
            'diskusi_progres' => [
                'badge_label' => 'Chat Progres Tugas',
                'badge_color' => 'info',
                'icon' => 'fas fa-comments',
                'header_tag' => 'PUSH NOTIFIKASI • DISKUSI PROGRES',
                'title' => "Pesan Progres Baru dari {$this->sender->name}",
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) mengirim pembaruan progres pada ruang diskusi tugas \"{$this->tugas->judul}\".",
                'action_text' => 'Buka Ruang Diskusi',
            ],
            'kolaborator_baru' => [
                'badge_label' => 'Tim Kolaborasi Tugas',
                'badge_color' => 'primary',
                'icon' => 'fas fa-users',
                'header_tag' => 'PUSH NOTIFIKASI • MULTI PETUGAS',
                'title' => 'Anda Ditambahkan ke Tim Pelaksana Tugas',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) menambahkan Anda sebagai petugas kolaborator pada tugas \"{$this->tugas->judul}\" dengan batas waktu {$deadlineFormatted}.",
                'action_text' => 'Buka Detail Tugas',
            ],
            'persetujuan_lintas_bidang' => [
                'badge_label' => 'Persetujuan Lintas Bidang',
                'badge_color' => 'warning',
                'icon' => 'fas fa-user-check',
                'header_tag' => 'PUSH NOTIFIKASI • PERSETUJUAN ATASAN',
                'title' => 'Permintaan Persetujuan Delegasi Lintas Bidang',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) mendelegasikan tugas lintas bidang \"{$this->tugas->judul}\" yang memerlukan verifikasi dan persetujuan Anda ({$this->tugas->jabatan_atasan_penyetuju}).",
                'action_text' => 'Tinjau & Verifikasi Delegasi',
            ],
            'persetujuan_lintas_bidang_disetujui' => [
                'badge_label' => 'Delegasi Lintas Bidang Disetujui',
                'badge_color' => 'success',
                'icon' => 'fas fa-check-circle',
                'header_tag' => 'PUSH NOTIFIKASI • DELEGASI DISETUJUI',
                'title' => 'Delegasi Lintas Bidang Disetujui Atasan',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) telah menyetujui delegasi lintas bidang pada tugas \"{$this->tugas->judul}\". Tugas kini resmi diteruskan kepada petugas pelaksana.",
                'action_text' => 'Lihat Detail Tugas',
            ],
            'persetujuan_lintas_bidang_ditolak' => [
                'badge_label' => 'Delegasi Lintas Bidang Ditolak',
                'badge_color' => 'danger',
                'icon' => 'fas fa-ban',
                'header_tag' => 'PUSH NOTIFIKASI • DELEGASI DITOLAK',
                'title' => 'Delegasi Lintas Bidang Ditolak Atasan',
                'message' => "{$this->sender->name} ({$this->sender->jabatan}) menolak permohonan delegasi lintas bidang pada tugas \"{$this->tugas->judul}\".",
                'action_text' => 'Lihat Alasan Penolakan',
            ],
            default => [
                'badge_label' => 'Pembaruan Tugas',
                'badge_color' => 'primary',
                'icon' => 'fas fa-bell',
                'header_tag' => 'PUSH NOTIFIKASI • SIMONTER',
                'title' => "Pembaruan Tugas: {$this->tugas->judul}",
                'message' => "Terdapat pembaruan pada tugas \"{$this->tugas->judul}\" oleh {$this->sender->name} ({$this->sender->jabatan}).",
                'action_text' => 'Lihat Detail Tugas',
            ],
        };

        return [
            'type' => $this->type,
            'badge_label' => $meta['badge_label'],
            'badge_color' => $meta['badge_color'],
            'icon' => $meta['icon'],
            'header_tag' => $meta['header_tag'],
            'title' => $meta['title'],
            'message' => $meta['message'],
            'catatan' => $this->catatan,
            'action_text' => $meta['action_text'],
            'tugas_id' => $this->tugas->id,
            'tugas_judul' => $this->tugas->judul,
            'tugas_status' => $this->tugas->status,
            'tanggal_mulai' => $mulaiFormatted,
            'tanggal_selesai' => $deadlineFormatted,
            'evidence_info' => $evidenceInfo,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'sender_jabatan' => $this->sender->jabatan,
            'url' => route('tugas.show', $this->tugas->id),
            'waktu_wib' => now()->format('d M Y, H:i').' WIB',
        ];
    }
}
