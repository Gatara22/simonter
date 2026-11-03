<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test admin can view the account settings page.
     */
    public function test_admin_can_view_pengaturan_page(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('pengaturan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun');
        $response->assertSee($admin->name);
        $response->assertSee($admin->nip);
        $response->assertSee('Administrator');
    }

    /**
     * Test officer (petugas) can view the account settings page.
     */
    public function test_petugas_can_view_pengaturan_page(): void
    {
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        $response = $this->actingAs($petugas)->get(route('pengaturan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun');
        $response->assertSee($petugas->name);
        $response->assertSee($petugas->nip);
        $response->assertSee('Petugas');
    }

    /**
     * Test user can update their phone number (kontak).
     */
    public function test_user_can_update_phone_number(): void
    {
        $user = User::factory()->create([
            'kontak' => '08111111111',
        ]);

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => '089999999999',
        ]);

        $response->assertRedirect(route('pengaturan.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('089999999999', $user->kontak);
    }

    /**
     * Test user can upload a new profile photo under 2MB.
     */
    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $fakePhoto = UploadedFile::fake()->image('my_avatar.jpg', 300, 300)->size(800); // 800 KB

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'foto' => $fakePhoto,
        ]);

        $response->assertRedirect(route('pengaturan.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->foto);
        Storage::disk('public')->assertExists($user->foto);
    }

    /**
     * Test user can remove their existing profile photo.
     */
    public function test_user_can_remove_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'foto' => 'avatars/existing_photo.jpg',
        ]);
        Storage::disk('public')->put('avatars/existing_photo.jpg', 'content');

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'hapus_foto' => 1,
        ]);

        $response->assertRedirect(route('pengaturan.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->foto);
        Storage::disk('public')->assertMissing('avatars/existing_photo.jpg');
    }

    /**
     * Test uploading profile photo rejects non-image files.
     */
    public function test_upload_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $document = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'foto' => $document,
        ]);

        $response->assertSessionHasErrors(['foto']);
    }

    /**
     * Test uploading profile photo rejects files over 2MB.
     */
    public function test_upload_rejects_file_over_2mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $largePhoto = UploadedFile::fake()->image('large.jpg')->size(2500); // 2.5 MB > 2048 KB

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'foto' => $largePhoto,
        ]);

        $response->assertSessionHasErrors(['foto']);
    }

    /**
     * Test user can successfully change password with valid current password and min 8 characters.
     */
    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('pengaturan.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Test changing password fails if current password is wrong.
     */
    public function test_change_password_fails_if_current_password_incorrect(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'current_password' => 'wrongcurrentpass',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    /**
     * Test changing password fails if new password is under 8 characters.
     */
    public function test_change_password_fails_if_new_password_under_8_chars(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('pengaturan.update'), [
            'kontak' => $user->kontak,
            'current_password' => 'oldpassword123',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test unauthenticated guest cannot access pengaturan.
     */
    public function test_unauthenticated_user_cannot_access_pengaturan(): void
    {
        $response = $this->get(route('pengaturan.index'));

        $response->assertRedirect(route('login'));
    }
}

