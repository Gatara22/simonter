<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that the login page can be rendered with the "Saya bukan robot" security captcha widget.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('SiMonter | Login');
        $response->assertSee('Saya bukan robot');
        $response->assertSee('Security Guard');
        $this->assertNotEmpty(session('login_captcha_token'));
    }

    /**
     * Test authenticated user accessing login is redirected to dashboard.
     */
    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test users can authenticate with valid NIP, password, and checked "Saya bukan robot" captcha.
     */
    public function test_users_can_authenticate_with_valid_nip_and_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'password123',
            'captcha_check' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test login fails when "Saya bukan robot" captcha checkbox is not checked.
     */
    public function test_login_fails_when_captcha_checkbox_is_not_checked(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('captcha_check');
    }

    /**
     * Test login fails when captcha session token does not match submitted token.
     */
    public function test_login_fails_when_captcha_session_token_is_invalid(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->get(route('login'));

        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'password123',
            'captcha_check' => '1',
            'captcha_token' => 'forged-bot-token',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('captcha_check');
    }

    /**
     * Test login fails when anti-bot honeypot field is filled by automated bot.
     */
    public function test_login_blocks_bot_filling_honeypot_field(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'password123',
            'captcha_check' => '1',
            'website_hp' => 'http://spam-bot.example.com',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('captcha_check');
    }

    /**
     * Test brute-force rate limiter blocks login after 5 consecutive failed attempts.
     */
    public function test_login_rate_limiter_blocks_brute_force_after_five_attempts(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('loginProses'), [
                'nip' => $user->nip,
                'password' => 'wrong-password-attempt',
                'captcha_check' => '1',
            ]);
        }

        // 6th attempt even with valid password should be throttled
        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'password123',
            'captcha_check' => '1',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('nip');

        RateLimiter::clear(strtolower($user->nip).'|127.0.0.1');
    }

    /**
     * Test users cannot authenticate with invalid credentials.
     */
    public function test_users_cannot_authenticate_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this->post(route('loginProses'), [
            'nip' => $user->nip,
            'password' => 'wrong-password',
            'captcha_check' => '1',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('nip');
    }

    /**
     * Test users can logout successfully.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    /**
     * Test unauthenticated users cannot access dashboard.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
