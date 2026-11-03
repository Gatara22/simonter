<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test authenticated user can view the user list.
     */
    public function test_authenticated_user_can_view_user_list(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $otherUser = User::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->actingAs($admin)->get(route('user.index'));

        $response->assertStatus(200);
        $response->assertSee('Data User');
        $response->assertSee('Budi Santoso');
    }

    /**
     * Test authenticated user can view the create user form.
     */
    public function test_authenticated_user_can_view_create_user_form(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('user.create'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Pengguna Baru');
    }

    /**
     * Test authenticated user can create a new user.
     */
    public function test_authenticated_user_can_create_new_user(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $uniqueNip = '199912312024011'.rand(100, 999);

        $response = $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'Doni Siregar',
            'nip' => $uniqueNip,
            'email' => 'doni'.rand(100, 999).'@example.com',
            'kontak' => '081234567890',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nip' => $uniqueNip,
            'name' => 'Doni Siregar',
            'jabatan' => 'Petugas',
        ]);
    }

    /**
     * Test creating a user fails with duplicate NIP.
     */
    public function test_create_user_fails_with_duplicate_nip(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $existingUser = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'Cloned User',
            'nip' => $existingUser->nip,
            'email' => 'different@example.com',
            'kontak' => '081234567890',
            'jabatan' => 'Petugas',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors('nip');
    }

    /**
     * Test authenticated user can view edit user form.
     */
    public function test_authenticated_user_can_view_edit_user_form(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $user = User::factory()->create(['name' => 'Target Edit']);

        $response = $this->actingAs($admin)->get(route('user.edit', $user->id));

        $response->assertStatus(200);
        $response->assertSee('Formulir Edit Data Pengguna');
        $response->assertSee('Target Edit');
    }

    /**
     * Test updating user without changing password.
     */
    public function test_authenticated_user_can_update_user_without_changing_password(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $user = User::factory()->create([
            'password' => 'old_secret_password',
        ]);

        $response = $this->actingAs($admin)->put(route('user.update', $user->id), [
            'name' => 'Updated Name',
            'nip' => $user->nip,
            'email' => $user->email,
            'kontak' => '08999999999',
            'jabatan' => 'Petugas',
            'status' => 'Sedang Bertugas',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'status' => 'Sedang Bertugas',
        ]);

        // Verify old password still works
        $user->refresh();
        $this->assertTrue(Hash::check('old_secret_password', $user->password));
    }

    /**
     * Test updating user with a new password.
     */
    public function test_authenticated_user_can_update_user_with_new_password(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $user = User::factory()->create([
            'password' => 'old_secret_password',
        ]);

        $response = $this->actingAs($admin)->put(route('user.update', $user->id), [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'kontak' => $user->kontak,
            'jabatan' => $user->jabatan,
            'status' => $user->status,
            'password' => 'new_secret_password_123',
            'password_confirmation' => 'new_secret_password_123',
        ]);

        $response->assertRedirect(route('user.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('new_secret_password_123', $user->password));
    }

    /**
     * Test deleting another user.
     */
    public function test_authenticated_user_can_delete_another_user(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('user.destroy', $userToDelete->id));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }

    /**
     * Test authenticated user cannot delete their own account.
     */
    public function test_authenticated_user_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $response = $this->actingAs($admin)->delete(route('user.destroy', $admin->id));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    /**
     * Test authenticated user can create a user with jabatan Kepala Sub Bagian.
     */
    public function test_authenticated_user_can_create_kepala_sub_bagian_user(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $uniqueNip = '199604152021041'.rand(100, 999);

        $response = $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'Hendra Wijaya',
            'nip' => $uniqueNip,
            'email' => 'kasubbag'.rand(100, 999).'@example.com',
            'kontak' => '081234567899',
            'jabatan' => 'Kepala Sub Bagian',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nip' => $uniqueNip,
            'name' => 'Hendra Wijaya',
            'jabatan' => 'Kepala Sub Bagian',
        ]);
    }
}
