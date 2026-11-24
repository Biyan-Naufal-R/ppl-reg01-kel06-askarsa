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

    private function payloadPesan(User $penyedia, array $override = []): array
    {
        $slug = \Illuminate\Support\Str::slug($penyedia->name);
        return array_merge([
            'penyedia_id' => $penyedia->id,
            'slug' => $slug,
            'nama_pemesan' => 'Pemesan',
            'nomor_telepon' => '081234567890',
            'email_pemesan' => 'pemesan@example.com',
            'domisili_pemesan' => 'Sokaraja',
            'titik_ambil' => 'Jl. Mawar No. 1',
            'titik_antar' => 'Jl. Melati No. 2',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'perkiraan_durasi' => '1-2 jam',
            'catatan_tambahan' => 'tolong bantu',
        ], $override);
    }

    public function test_riwayat_lama_redirect_ke_pesanan_jasa_riwayat(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pesanan.riwayat'))
            ->assertRedirect(route('dashboard.pesanan.riwayat'));
    }

    public function test_pesanan_jasa_riwayat_hanya_menampilkan_pesanan_berakhir_milik_pemesan(): void
    {
        $user = User::factory()->create();

        Pesanan::factory()->selesai()->create(['pelanggan_id' => $user->id, 'judul' => 'Servis AC']);
        Pesanan::factory()->dibatalkan()->create(['pelanggan_id' => $user->id, 'judul' => 'Cat Dinding']);
        Pesanan::factory()->diproses()->create(['pelanggan_id' => $user->id, 'judul' => 'Masih Jalan']);
        Pesanan::factory()->selesai()->create(['penyedia_id' => $user->id, 'judul' => 'Sebagai Penyedia']);
        Pesanan::factory()->selesai()->create(['judul' => 'Milik Orang Lain']);

        $this->actingAs($user)
            ->get(route('dashboard.pesanan.riwayat'))
            ->assertOk()
            ->assertSee('Servis AC')
            ->assertSee('Cat Dinding')
            ->assertDontSee('Masih Jalan')
            ->assertDontSee('Sebagai Penyedia')
            ->assertDontSee('Milik Orang Lain');
    }

    public function test_penyedia_bisa_menyelesaikan_pesanan_diproses(): void
    {
        $pesanan = Pesanan::factory()->diproses()->create();

        $this->actingAs($pesanan->penyedia)
            ->post(route('pesanan.selesaikan', $pesanan), ['catatan_penyelesaian' => 'Sudah beres'])
            ->assertRedirect(route('dashboard.permintaan.riwayat'));

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

        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'tarif' => 50000]);

        $this->actingAs($pemesan)
            ->from(route('services.show', 'bima-saputra'))
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia))
            ->assertRedirect(route('services.show', 'bima-saputra'))
            ->assertSessionHasErrors('jasa');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_user_bisa_memesan_setelah_jasanya_dinonaktifkan(): void
    {
        $pemesan = User::factory()->create();
        ProfilJasa::factory()->nonaktif()->create(['user_id' => $pemesan->id]);

        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create([
            'user_id' => $penyedia->id,
            'nama_profil' => 'Jasa Bima',
            'tarif' => 50000,
            'kategori' => ['antar-jemput', 'titip-beli'],
        ]);

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia, [
                'catatan_tambahan' => 'Tolong antar dokumen',
            ]))
            ->assertRedirect(route('pesanan.show', Pesanan::first()));

        $pesanan = Pesanan::first();
        $this->assertSame($pemesan->id, $pesanan->pelanggan_id);
        $this->assertSame($penyedia->id, $pesanan->penyedia_id);
        $this->assertSame('Pesan jasa: Jasa Bima', $pesanan->judul);
        $this->assertSame(50000, $pesanan->harga);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $pesanan->status);
        $this->assertSame('081234567890', $pesanan->nomor_telepon);
        $this->assertSame(['antar-jemput', 'titip-beli'], $pesanan->jenis_bantuan);
        $this->assertSame('Tolong antar dokumen', $pesanan->catatan_tambahan);
        $this->assertSame('Jl. Mawar No. 1', $pesanan->titik_ambil);
        $this->assertSame('Jl. Melati No. 2', $pesanan->titik_antar);
    }

    public function test_tidak_bisa_memesan_jasa_milik_sendiri(): void
    {
        $user = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('pesanan.store'), $this->payloadPesan($user))
            ->assertSessionHasErrors('jasa');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_tidak_bisa_memesan_penyedia_yang_profil_jasanya_nonaktif(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->nonaktif()->create(['user_id' => $penyedia->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia))
            ->assertNotFound();

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_tidak_bisa_memesan_penyedia_yang_tidak_tersedia(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->tidakTersedia()->create(['user_id' => $penyedia->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia))
            ->assertNotFound();

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_form_pesan_jasa_bisa_dibuka_dan_jenis_bantuan_otomatis_dari_sistem(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'kategori' => ['antar-jemput', 'titip-beli']]);

        $pemesan = User::factory()->create(['name' => 'Pemesan', 'email' => 'pemesan@example.com', 'domisili' => 'Sokaraja']);

        $this->actingAs($pemesan)
            ->get(route('pesanan.create', 'bima-saputra'))
            ->assertOk()
            ->assertSee('Nomor Telepon')
            ->assertSee('Jenis Bantuan')
            ->assertSee('Antar Jemput')
            ->assertSee('Titip Beli')
            ->assertSee('value="Pemesan"', false)
            ->assertSee('value="pemesan@example.com"', false);
    }

    public function test_jenis_bantuan_tidak_bisa_dimanipulasi_dari_request(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'kategori' => ['temani']]);
        $pemesan = User::factory()->create();

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia, [
                'jenis_bantuan' => ['perbaikan'],
            ]))
            ->assertRedirect();

        $this->assertSame(['temani'], Pesanan::first()->jenis_bantuan);
    }

    public function test_nomor_telepon_wajib_dan_format_divalidasi(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id]);
        $pemesan = User::factory()->create();

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia, ['nomor_telepon' => 'abc']))
            ->assertSessionHasErrors('nomor_telepon');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_titik_antar_wajib_untuk_kategori_antar_titip_dan_temani(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'kategori' => ['temani']]);
        $pemesan = User::factory()->create();

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia, ['titik_antar' => '']))
            ->assertSessionHasErrors('titik_antar');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_titik_antar_opsional_untuk_kategori_non_antar(): void
    {
        $penyedia = User::factory()->create(['name' => 'Bima Saputra']);
        ProfilJasa::factory()->create(['user_id' => $penyedia->id, 'kategori' => ['bantuan-rumah']]);
        $pemesan = User::factory()->create();

        $this->actingAs($pemesan)
            ->post(route('pesanan.store'), $this->payloadPesan($penyedia, ['titik_antar' => '']))
            ->assertRedirect();

        $pesanan = Pesanan::first();
        $this->assertNull($pesanan->titik_antar);
        $this->assertSame('Jl. Mawar No. 1', $pesanan->titik_ambil);
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
            ->assertRedirect(route('dashboard.permintaan.riwayat'));

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
            ->assertSee(route('pesanan.create', 'bima-saputra'), false)
            ->assertSee('Pesan Jasa');

        $pemilikJasa = User::factory()->create();
        ProfilJasa::factory()->create(['user_id' => $pemilikJasa->id]);
        $this->actingAs($pemilikJasa)
            ->get(route('services.show', 'bima-saputra'))
            ->assertOk()
            ->assertSee('Untuk memesan jasa orang lain, nonaktifkan terlebih dahulu profil jasa Anda melalui halaman Edit Profil / Akun.')
            ->assertDontSee(route('pesanan.create', 'bima-saputra'), false);

        $this->actingAs($penyedia)
            ->get(route('services.show', 'bima-saputra'))
            ->assertOk()
            ->assertSee('Ini jasa milik Anda sendiri.')
            ->assertDontSee('name="penyedia_id"', false);
    }
}