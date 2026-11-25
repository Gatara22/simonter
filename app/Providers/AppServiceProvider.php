<?php

namespace App\Providers;

use App\Http\Controllers\NotifikasiController;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as IlluminateView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function (IlluminateView $view): void {
            if (! Auth::check()) {
                $view->with([
                    'topbarUnreadCount' => 0,
                    'topbarNotifications' => collect(),
                    'sidebarPendingCount' => 0,
                ]);

                return;
            }

            /** @var User $user */
            $user = Auth::user();
            $formatter = app(NotifikasiController::class);

            $unreadCount = $user->unreadNotifications()->count();
            $notifications = $user->notifications()
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (DatabaseNotification $n): array => $formatter->formatNotification($n));

            if ($user->jabatan === 'Admin') {
                $sidebarPendingCount = Tugas::where('status', '!=', 'Selesai')
                    ->where(function ($q): void {
                        $q->where('status', 'Menunggu Konfirmasi')
                            ->orWhere('status_permohonan', 'Menunggu Persetujuan')
                            ->orWhere(function ($cross): void {
                                $cross->where('perlu_persetujuan_atasan', true)
                                    ->where('status_persetujuan_atasan', 'Menunggu Persetujuan');
                            });
                    })
                    ->count();
            } elseif (in_array($user->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true)) {
                $sidebarPendingCount = Tugas::where('status', '!=', 'Selesai')
                    ->where(function ($q) use ($user): void {
                        $q->where(function ($sub) use ($user): void {
                            $sub->where('admin_id', $user->id)
                                ->where(function ($verif): void {
                                    $verif->where('status', 'Menunggu Konfirmasi')
                                        ->orWhere('status_permohonan', 'Menunggu Persetujuan');
                                });
                        })->orWhere(function ($sub) use ($user): void {
                            $sub->where('petugas_id', $user->id)
                                ->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']);
                        })->orWhere(function ($sub) use ($user): void {
                            $sub->where('perlu_persetujuan_atasan', true)
                                ->where('status_persetujuan_atasan', 'Menunggu Persetujuan')
                                ->where('jabatan_atasan_penyetuju', $user->jabatan)
                                ->where('admin_id', '!=', $user->id);
                        });
                    })
                    ->count();
            } else {
                $sidebarPendingCount = Tugas::where('petugas_id', $user->id)
                    ->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi'])
                    ->count();
            }

            $view->with([
                'topbarUnreadCount' => $unreadCount,
                'topbarNotifications' => $notifications,
                'sidebarPendingCount' => $sidebarPendingCount,
            ]);
        });
    }
}
