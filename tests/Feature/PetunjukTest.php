<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PetunjukTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Admin can view the Petunjuk Aplikasi page and see the sidebar menu item.
     */
    public function test_admin_can_access_petunjuk_aplikasi_page(): void
    {
        $admin = User::factory()->create(['jabatan' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('petunjuk.index'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Aplikasi');
        $response->assertSee('Pusat Petunjuk Penggunaan');
        $response->assertSee('Administrator (Admin)');
        $response->assertSee('Kepala Sub Bagian');
        $response->assertSee('Petugas (Pelaksana)');
        $response->assertSee('Standar Operasional Prosedur (SOP) — Administrator');
    }

    /**
     * Test Kepala Sub Bagian can view the Petunjuk Aplikasi page and see the sidebar menu item.
     */
    public function test_kepala_sub_bagian_can_access_petunjuk_aplikasi_page(): void
    {
        $kasubbag = User::factory()->create(['jabatan' => 'Kepala Sub Bagian']);

        $response = $this->actingAs($kasubbag)->get(route('petunjuk.index'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Aplikasi');
        $response->assertSee('Peran Aktif Anda Saat Ini:');
        $response->assertSee('Kepala Sub Bagian');
        $response->assertSee('Standar Operasional Prosedur (SOP) — Kepala Sub Bagian');
        $response->assertSee('Kesekretariatan');
        $response->assertSee('Kepaniteraan');
    }

    /**
     * Test Panitera Muda can view the Petunjuk Aplikasi page and see the updated classification documentation.
     */
    public function test_panitera_muda_can_access_petunjuk_aplikasi_page(): void
    {
        $panmud = User::factory()->create(['jabatan' => 'Panitera Muda']);

        $response = $this->actingAs($panmud)->get(route('petunjuk.index'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Aplikasi');
        $response->assertSee('Panitera Muda');
        $response->assertSee('Kesekretariatan');
        $response->assertSee('Kepaniteraan');
    }

    /**
     * Test Petugas can view the Petunjuk Aplikasi page and see the sidebar menu item.
     */
    public function test_petugas_can_access_petunjuk_aplikasi_page(): void
    {
        $petugas = User::factory()->create(['jabatan' => 'Petugas']);

        $response = $this->actingAs($petugas)->get(route('petunjuk.index'));

        $response->assertStatus(200);
        $response->assertSee('Petunjuk Aplikasi');
        $response->assertSee('Petugas (Pelaksana)');
        $response->assertSee('Standar Operasional Prosedur (SOP) — Petugas Pelaksana');
    }

    /**
     * Test unauthenticated guest is redirected to login when accessing Petunjuk Aplikasi.
     */
    public function test_guest_cannot_access_petunjuk_aplikasi_page(): void
    {
        $response = $this->get(route('petunjuk.index'));

        $response->assertRedirect(route('login'));
    }
}
