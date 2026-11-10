<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form and initialize security captcha token.
     */
    public function login(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $captchaToken = Str::random(40);
        $request->session()->put('login_captcha_token', $captchaToken);

        return view('auth.login', [
            'captchaToken' => $captchaToken,
        ]);
    }

    /**
     * Handle an authentication attempt with anti-bot captcha & brute-force rate limiting.
     */
    public function loginProses(Request $request): RedirectResponse
    {
        $throttleKey = Str::transliterate(Str::lower((string) $request->input('nip')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors([
                    'nip' => "Terlalu banyak percobaan login yang gagal (proteksi anti-brute force). Silakan coba lagi dalam {$seconds} detik.",
                ])
                ->onlyInput('nip');
        }

        // Honeypot field check: bots that auto-fill hidden fields are rejected
        if ($request->filled('website_hp')) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors([
                    'captcha_check' => 'Aktivitas mencurigakan terdeteksi oleh sistem keamanan.',
                ])
                ->onlyInput('nip');
        }

        $expectedToken = $request->session()->get('login_captcha_token');

        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'captcha_check' => [
                'required',
                'accepted',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $expectedToken): void {
                    if ($expectedToken !== null && $request->input('captcha_token') !== $expectedToken) {
                        $fail('Token keamanan "Saya bukan robot" tidak valid atau telah kedaluwarsa. Silakan ulangi ceklis.');
                    }
                },
            ],
        ], [
            'nip.required' => 'NIP Tidak Boleh Kosong',
            'password.required' => 'Password Tidak Boleh Kosong',
            'password.min' => 'Password Minimal 8 Karakter',
            'captcha_check.required' => 'Silakan centang verifikasi keamanan "Saya bukan robot" terlebih dahulu.',
            'captcha_check.accepted' => 'Silakan centang verifikasi keamanan "Saya bukan robot" terlebih dahulu.',
        ]);

        if (Auth::attempt(['nip' => $credentials['nip'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->forget('login_captcha_token');
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.Auth::user()->name);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withErrors([
                'nip' => 'NIP atau password yang Anda masukkan salah.',
            ])
            ->onlyInput('nip');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
