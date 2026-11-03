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
