<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Poll unread notifications count and latest notifications for real-time push toast and topbar dropdown.
     */
    public function poll(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->formatNotification($notification));

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read and redirect to its target task page.
     */
    public function baca(Request $request, string $id): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()->where('id', $id)->first();

        $targetUrl = route('tugas.index');

        if ($notification) {
            if ($notification->read_at === null) {
                $notification->markAsRead();
            }
            $targetUrl = $notification->data['url'] ?? route('tugas.index');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
                'redirect_url' => $targetUrl,
            ]);
        }

        return redirect()->to($targetUrl);
    }

    /**
     * Mark all unread notifications for the current user as read.
     */
    public function bacaSemua(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Seluruh notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Format a database notification into a structured array for JSON / UI consumption.
     *
     * @return array{
     *     id: string,
     *     is_unread: bool,
     *     time_ago: string,
     *     waktu_wib: string,
     *     type: string,
     *     badge_label: string,
     *     badge_color: string,
     *     icon: string,
     *     header_tag: string,
     *     title: string,
     *     message: string,
     *     catatan: string|null,
     *     action_text: string,
     *     tugas_id: int|null,
     *     tugas_judul: string,
     *     tugas_status: string,
     *     tanggal_selesai: string,
     *     evidence_info: string|null,
     *     sender_name: string,
     *     sender_jabatan: string,
     *     url: string,
     *     read_url: string
     * }
     */
    public function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'is_unread' => $notification->read_at === null,
            'time_ago' => $notification->created_at?->diffForHumans() ?? 'Baru saja',
            'waktu_wib' => $data['waktu_wib'] ?? ($notification->created_at?->format('d M Y, H:i').' WIB'),
            'type' => $data['type'] ?? 'tugas_baru',
            'badge_label' => $data['badge_label'] ?? 'Notifikasi Tugas',
            'badge_color' => $data['badge_color'] ?? 'primary',
            'icon' => $data['icon'] ?? 'fas fa-bell',
            'header_tag' => $data['header_tag'] ?? 'PUSH NOTIFIKASI • SIMONTER',
            'title' => $data['title'] ?? 'Pembaruan Tugas',
            'message' => $data['message'] ?? '',
            'catatan' => $data['catatan'] ?? null,
            'action_text' => $data['action_text'] ?? 'Lihat Detail Tugas',
            'tugas_id' => $data['tugas_id'] ?? null,
            'tugas_judul' => $data['tugas_judul'] ?? '-',
            'tugas_status' => $data['tugas_status'] ?? '-',
            'tanggal_selesai' => $data['tanggal_selesai'] ?? '-',
            'evidence_info' => $data['evidence_info'] ?? null,
            'sender_name' => $data['sender_name'] ?? '-',
            'sender_jabatan' => $data['sender_jabatan'] ?? '-',
            'url' => $data['url'] ?? route('tugas.index'),
            'read_url' => route('notifikasi.baca', $notification->id),
        ];
    }
}
