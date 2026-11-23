<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\ProfilJasa;
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

    public function test_user_dengan_jasa_aktif_tidak_bisa_memesan_jasa_orang_lain(): void
    {
        $pemesan = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $pemesan->id]);

        $penyedia = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'tarif' => 50000]);

        $this->actingAs($pemesan)
            ->from(route('services.show', 'bima-saputra'))
            ->post(route('pesanan.store'), ['penyedia_id' => $penyedia->id])
            ->assertRedirect(route('services.show', 'bima-saputra'))
            ->assertSessionHasErrors('jasa');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_user_bisa_memesan_setelah_jasanya_dinonaktifkan(): void
    {
        $pemesan = User::factory()->create();
        ProfilJasa::factory()->nonaktif()->create(['user_id' => $pemesan->id]);

        $penyedia = User::factory()->create();
        ProfilJasa::factory()->create([
            'user_id' => $penyedia->id,
            'nama_profil' => 'Jasa Bima',
            'tarif' => 50000,
        ]);

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), [
                'penyedia_id' => $penyedia->id,
                'deskripsi' => 'Tolong antar dokumen',
            ])
            ->assertRedirect(route('pesanan.show', Pesanan::first()));

        $pesanan = Pesanan::first();
        $this->assertSame($pemesan->id, $pesanan->pelanggan_id);
        $this->assertSame($penyedia->id, $pesanan->penyedia_id);
        $this->assertSame('Pesan jasa: Jasa Bima', $pesanan->judul);
        $this->assertSame(50000, $pesanan->harga);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $pesanan->status);
    }

    public function test_tidak_bisa_memesan_jasa_milik_sendiri(): void
    {
        $user = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('pesanan.store'), ['penyedia_id' => $user->id])
            ->assertSessionHasErrors('jasa');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_tidak_bisa_memesan_penyedia_yang_profil_jasanya_nonaktif(): void
    {
        $penyedia = User::factory()->create();
        ProfilJasa::factory()->nonaktif()->create(['user_id' => $penyedia->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('pesanan.store'), ['penyedia_id' => $penyedia->id])
            ->assertNotFound();

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_tidak_bisa_memesan_penyedia_yang_tidak_tersedia(): void
    {
        $penyedia = User::factory()->create();
        ProfilJasa::factory()->tidakTersedia()->create(['user_id' => $penyedia->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('pesanan.store'), ['penyedia_id' => $penyedia->id])
            ->assertNotFound();

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_terima_pesanan_membuat_diproses_dan_profil_tidak_tersedia(): void
    {
        $pesanan = Pesanan::factory()->create(['status' => Pesanan::STATUS_MENUNGGU]);
        ProfilJasa::factory()->create(['user_id' => $pesanan->penyedia_id, 'tersedia' => true]);

        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.terima', $pesanan))
            ->assertRedirect();

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->fresh()->status);
        $this->assertFalse($pesanan->penyedia->fresh()->profilJasa->tersedia);
    }

    public function test_selesai_mengembalikan_ketersediaan_otomatis(): void
    {
        $pesanan = Pesanan::factory()->diproses()->create();
        ProfilJasa::factory()->create(['user_id' => $pesanan->penyedia_id, 'tersedia' => false]);

        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.selesaikan', $pesanan), ['catatan_penyelesaian' => 'done'])
            ->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame(Pesanan::STATUS_SELESAI, $pesanan->fresh()->status);
        $this->assertTrue($pesanan->penyedia->fresh()->profilJasa->tersedia);
    }

    public function test_pelanggan_bisa_batalkan_hanya_saat_menunggu(): void
    {
        $pesanan = Pesanan::factory()->create(['status' => Pesanan::STATUS_MENUNGGU]);
        $this->actingAs($pesanan->pelanggan)
            ->post(route('pesanan.batalkan', $pesanan))
            ->assertRedirect();
        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->fresh()->status);

        $diproses = Pesanan::factory()->diproses()->create();
        $this->actingAs($diproses->pelanggan)
            ->post(route('pesanan.batalkan', $diproses))
            ->assertSessionHasErrors('status');
        $this->assertSame(Pesanan::STATUS_DIPROSES, $diproses->fresh()->status);
    }

    public function test_penyedia_bisa_tolak_dan_tidak_bisa_batalkan(): void
    {
        $pesanan = Pesanan::factory()->create(['status' => Pesanan::STATUS_MENUNGGU]);
        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.tolak', $pesanan))
            ->assertRedirect();
        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->fresh()->status);

        $lain = Pesanan::factory()->create(['status' => Pesanan::STATUS_MENUNGGU]);
        $this->actingAs($lain->penyedia)
            ->post(route('pesanan.batalkan', $lain))
            ->assertForbidden();
    }

    public function test_detail_jasa_menampilkan_form_pesan_hanya_untuk_pelanggan_yang_boleh(): void
    {
        $penyedia = User::factory()->create([
            'name' => 'Bima Saputra',
            'email' => 'bima.saputra@askarsa.test',
        ]);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id]);

        $pelanggan = User::factory()->create();
        $this->actingAs($pelanggan)
            ->get(route('services.show', 'bima-saputra'))
            ->assertOk()
            ->assertSee('name="penyedia_id"', false)
            ->assertSee('Pesan Jasa');

        $pemilikJasa = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $pemilikJasa->id]);
        $this->actingAs($pemilikJasa)
            ->get(route('services.show', 'bima-saputra'))
            ->assertOk()
            ->assertSee('Nonaktifkan profil jasa Anda terlebih dahulu')
            ->assertDontSee('name="penyedia_id"', false);

        $this->actingAs($penyedia)
            ->get(route('services.show', 'bima-saputra'))
            ->assertOk()
            ->assertSee('Ini jasa milik Anda sendiri.')
            ->assertDontSee('name="penyedia_id"', false);
    }
}