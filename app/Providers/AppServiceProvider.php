<?php

namespace App\Providers;

use App\Http\Controllers\NotifikasiController;
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

            $view->with([
                'topbarUnreadCount' => $unreadCount,
                'topbarNotifications' => $notifications,
            ]);
        });
    }
}
