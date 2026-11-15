<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    public function test_riwayat_hanya_menampilkan_pesanan_berakhir_milik_user(): void
    {
        $user = User::factory()->create();

        Pesanan::factory()->selesai()->create(['pelanggan_id' => $user->id, 'judul' => 'Servis AC']);
        Pesanan::factory()->dibatalkan()->create(['penyedia_id' => $user->id, 'judul' => 'Cat Dinding']);
        Pesanan::factory()->diproses()->create(['pelanggan_id' => $user->id, 'judul' => 'Masih Jalan']);
        Pesanan::factory()->selesai()->create(['judul' => 'Milik Orang Lain']);

        $this->actingAs($user)
            ->get(route('pesanan.riwayat'))
            ->assertOk()
            ->assertSee('Servis AC')
            ->assertSee('Cat Dinding')
            ->assertDontSee('Masih Jalan')
            ->assertDontSee('Milik Orang Lain');
    }

    public function test_riwayat_bisa_difilter_berdasarkan_status(): void
    {
        $user = User::factory()->create();

        Pesanan::factory()->selesai()->create(['pelanggan_id' => $user->id, 'judul' => 'Servis AC']);
        Pesanan::factory()->dibatalkan()->create(['pelanggan_id' => $user->id, 'judul' => 'Cat Dinding']);

        $this->actingAs($user)
            ->get(route('pesanan.riwayat', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('Servis AC')
            ->assertDontSee('Cat Dinding');
    }

    public function test_penyedia_bisa_menyelesaikan_pesanan_diproses(): void
    {
        $pesanan = Pesanan::factory()->diproses()->create();

        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.selesaikan', $pesanan), ['catatan_penyelesaian' => 'Sudah beres'])
            ->assertRedirect(route('pesanan.show', $pesanan));

        $pesanan->refresh();
        $this->assertSame(Pesanan::STATUS_SELESAI, $pesanan->status);
        $this->assertSame('Sudah beres', $pesanan->catatan_penyelesaian);
        $this->assertNotNull($pesanan->selesai_pada);
    }

    public function test_pelanggan_tidak_bisa_menyelesaikan_pesanan(): void
    {
        $pesanan = Pesanan::factory()->diproses()->create();

        $this->actingAs($pesanan->pelanggan)
            ->post(route('pesanan.selesaikan', $pesanan))
            ->assertForbidden();

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->fresh()->status);
    }

    public function test_pesanan_yang_belum_diproses_tidak_bisa_diselesaikan(): void
    {
        $pesanan = Pesanan::factory()->create(); // status menunggu

        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.selesaikan', $pesanan))
            ->assertSessionHasErrors('status');

        $this->assertSame(Pesanan::STATUS_MENUNGGU, $pesanan->fresh()->status);
    }

    public function test_user_lain_tidak_bisa_melihat_detail_pesanan(): void
    {
        $pesanan = Pesanan::factory()->selesai()->create();
        $orangLain = User::factory()->create();

        $this->actingAs($orangLain)
            ->get(route('pesanan.show', $pesanan))
            ->assertForbidden();
    }

    public function test_pelanggan_bisa_melihat_detail_pesanannya(): void
    {
        $pesanan = Pesanan::factory()->selesai()->create(['judul' => 'Servis AC']);

        $this->actingAs($pesanan->pelanggan)
            ->get(route('pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee('Servis AC');
    }
}