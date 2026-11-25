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

    /**
     * Test authenticated user can create a user with jabatan Panitera Muda.
     */
    public function test_authenticated_user_can_create_panitera_muda_user(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $uniqueNip = '199708202021081'.rand(100, 999);

        $response = $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'Siti Aminah',
            'nip' => $uniqueNip,
            'email' => 'panmud'.rand(100, 999).'@example.com',
            'kontak' => '081276543210',
            'jabatan' => 'Panitera Muda',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nip' => $uniqueNip,
            'name' => 'Siti Aminah',
            'jabatan' => 'Panitera Muda',
        ]);
    }

    /**
     * Test non-admin roles cannot access user management or user export endpoints.
     */
    public function test_non_admin_users_cannot_access_user_management_endpoints(): void
    {
        $targetUser = User::factory()->create(['jabatan' => 'Petugas']);
        $nonAdmins = [
            User::factory()->create(['jabatan' => 'Petugas']),
            User::factory()->create(['jabatan' => 'Kepala Sub Bagian']),
            User::factory()->create(['jabatan' => 'Panitera Muda']),
        ];

        foreach ($nonAdmins as $user) {
            $this->actingAs($user)->get(route('user.index'))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');

            $this->actingAs($user)->get(route('user.create'))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');

            $this->actingAs($user)->post(route('user.store'), [
                'name' => 'Unauthorized',
                'nip' => '199901012025011999',
                'jabatan' => 'Petugas',
                'status' => 'Belum Ditugaskan',
                'password' => 'secret1234',
                'password_confirmation' => 'secret1234',
            ])->assertRedirect(route('dashboard'))->assertSessionHas('error');

            $this->actingAs($user)->get(route('user.edit', $targetUser->id))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');

            $this->actingAs($user)->put(route('user.update', $targetUser->id), [
                'name' => 'Hacked Name',
                'nip' => $targetUser->nip,
                'jabatan' => 'Petugas',
                'status' => 'Belum Ditugaskan',
            ])->assertRedirect(route('dashboard'))->assertSessionHas('error');

            $this->actingAs($user)->delete(route('user.destroy', $targetUser->id))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');

            $this->actingAs($user)->get(route('user.export.excel'))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');

            $this->actingAs($user)->get(route('user.export.pdf'))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');
        }

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => $targetUser->name,
        ]);
    }

    /**
     * Test admin can assign and filter Pimpinan, Kesekretariatan, and Kepaniteraan klasifikasi while existing users default to empty (null).
     */
    public function test_admin_can_manage_and_filter_user_klasifikasi_while_existing_users_default_empty(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);
        $existingUser = User::factory()->create(['name' => 'Pegawai Lama Tanpa Klasifikasi', 'jabatan' => 'Petugas']);

        // 1. Existing user created without klasifikasi defaults to null (empty)
        $this->assertNull($existingUser->fresh()->klasifikasi);

        // 2. Create users with each valid klasifikasi (Pimpinan, Kesekretariatan, Kepaniteraan)
        $pimpinanNip = '199001012020011'.rand(100, 999);
        $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'User Pimpinan Baru',
            'nip' => $pimpinanNip,
            'jabatan' => 'Kepala Sub Bagian',
            'klasifikasi' => 'Pimpinan',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertRedirect(route('user.index'))->assertSessionHas('success');

        $sekreNip = '199102022020021'.rand(100, 999);
        $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'User Kesekretariatan Baru',
            'nip' => $sekreNip,
            'jabatan' => 'Petugas',
            'klasifikasi' => 'Kesekretariatan',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertRedirect(route('user.index'))->assertSessionHas('success');

        $kepaNip = '199203032020031'.rand(100, 999);
        $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'User Kepaniteraan Baru',
            'nip' => $kepaNip,
            'jabatan' => 'Panitera Muda',
            'klasifikasi' => 'Kepaniteraan',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertRedirect(route('user.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['nip' => $pimpinanNip, 'klasifikasi' => 'Pimpinan']);
        $this->assertDatabaseHas('users', ['nip' => $sekreNip, 'klasifikasi' => 'Kesekretariatan']);
        $this->assertDatabaseHas('users', ['nip' => $kepaNip, 'klasifikasi' => 'Kepaniteraan']);

        // 3. Invalid klasifikasi is rejected
        $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'Invalid Klasifikasi User',
            'nip' => '199304042020041'.rand(100, 999),
            'jabatan' => 'Petugas',
            'klasifikasi' => 'Umum',
            'status' => 'Belum Ditugaskan',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertSessionHasErrors('klasifikasi');

        // 4. Filter checks before manual edit
        $this->actingAs($admin)->get(route('user.index', ['klasifikasi' => 'belum_diatur']))
            ->assertStatus(200)
            ->assertSee('Pegawai Lama Tanpa Klasifikasi')
            ->assertDontSee('User Kesekretariatan Baru');

        $this->actingAs($admin)->get(route('user.index', ['klasifikasi' => 'Kesekretariatan']))
            ->assertStatus(200)
            ->assertSee('User Kesekretariatan Baru')
            ->assertDontSee('User Kepaniteraan Baru');

        $this->actingAs($admin)->get(route('user.index', ['klasifikasi' => 'Kepaniteraan']))
            ->assertStatus(200)
            ->assertSee('User Kepaniteraan Baru')
            ->assertDontSee('User Pimpinan Baru');

        // 5. Existing empty user can be manually edited to Pimpinan
        $this->actingAs($admin)->put(route('user.update', $existingUser->id), [
            'name' => $existingUser->name,
            'nip' => $existingUser->nip,
            'email' => $existingUser->email,
            'kontak' => $existingUser->kontak,
            'jabatan' => $existingUser->jabatan,
            'klasifikasi' => 'Pimpinan',
            'status' => $existingUser->status,
        ])->assertRedirect(route('user.index'))->assertSessionHas('success');

        $this->assertSame('Pimpinan', $existingUser->fresh()->klasifikasi);

        $this->actingAs($admin)->get(route('user.index', ['klasifikasi' => 'Pimpinan']))
            ->assertStatus(200)
            ->assertSee('Pegawai Lama Tanpa Klasifikasi')
            ->assertSee('User Pimpinan Baru');
    }
}
