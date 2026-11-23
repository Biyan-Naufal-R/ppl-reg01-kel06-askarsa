<?php

namespace Tests\Feature;

use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_and_edit_pages_are_separate_and_require_authentication(): void
    {
        $this->get(route('dashboard.profile'))
            ->assertRedirect(route('login'));

        $this->get(route('dashboard.edit-profile'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create([
            'name' => 'Ayu Pratama',
            'domisili' => 'Sokaraja',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Ayu Pratama')
            ->assertSee('Sokaraja')
            ->assertSee('Ubah password')
            ->assertSee(route('dashboard.edit-profile').'#password-settings', false)
            ->assertDontSee('name="password"', false);

        $this->get(route('dashboard.edit-profile'))
            ->assertOk()
            ->assertSee('Edit profil / akun')
            ->assertSee('Simpan perubahan')
            ->assertSee('name="current_password"', false)
            ->assertSee('data-password-toggle="current_password"', false)
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false);
    }

    public function test_edit_profile_lists_all_banyumas_districts(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('dashboard.edit-profile'))
            ->assertOk();

        foreach (config('domisili.kecamatan') as $district) {
            $response->assertSee('value="'.$district.'"', false);
        }
    }

    public function test_user_can_update_profile_without_changing_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Ayu Pratama',
            'email' => 'ayu@example.com',
            'domisili' => 'Sokaraja',
        ]);
        $oldPassword = $user->password;

        $this->actingAs($user)
            ->followingRedirects()
            ->put(route('dashboard.profile.update'), [
                'name' => 'Ayu Wibowo',
                'email' => 'ayu.wibowo@example.com',
                'domisili' => 'Baturraden',
                'current_password' => 'browser-autofilled-password',
            ])
            ->assertOk()
            ->assertSee('Profil berhasil diperbarui.')
            ->assertSee('id="status-toast"', false)
            ->assertSee('Baturraden');

        $user->refresh();
        $this->assertSame('Ayu Wibowo', $user->name);
        $this->assertSame('ayu.wibowo@example.com', $user->email);
        $this->assertSame('Baturraden', $user->domisili);
        $this->assertSame($oldPassword, $user->password);
    }

    public function test_user_can_change_password_after_confirming_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Purwokerto Utara',
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
    }

    public function test_profile_update_rejects_duplicate_email_and_invalid_current_password(): void
    {
        $user = User::factory()->create(['email' => 'ayu@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($user)
            ->from(route('dashboard.edit-profile'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => 'other@example.com',
                'domisili' => 'Sokaraja',
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('dashboard.edit-profile'))
            ->assertSessionHasErrors(['email', 'current_password']);
    }

    public function test_profile_update_rejects_a_district_outside_banyumas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.edit-profile'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Cilacap',
            ])
            ->assertRedirect(route('dashboard.edit-profile'))
            ->assertSessionHasErrors('domisili');
    }

    public function test_edit_page_menampilkan_toggle_dan_form_profil_jasa(): void
    {
        $user = User::factory()->create();
        ProfilJasa::factory()->create([
            'user_id' => $user->id,
            'nama_profil' => 'Joanna Siap Bantu',
            'tarif' => 25000,
            'kategori' => ['temani', 'titip-beli'],
            'deskripsi' => 'Bantu urusan harian',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.edit-profile'))
            ->assertOk()
            ->assertSee('Aktifkan jasa minta tolong')
            ->assertSee('id="toggle-jasa"', false)
            ->assertSee('id="jasa-section"', false)
            ->assertSee('value="Joanna Siap Bantu"', false)
            ->assertSee('value="25000"', false)
            ->assertSee('value="temani"', false)
            ->assertSee('Bantu urusan harian');
    }

    public function test_user_can_save_active_jasa_profile_in_separate_table(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Sokaraja',
                'jasa_aktif' => 1,
                'nama_profil' => 'Ayu Siap Bantu',
                'tarif' => 30000,
                'kategori' => ['temani', 'titip-beli'],
                'deskripsi' => 'Bantu belanja',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $profil = $user->fresh()->profilJasa;
        $this->assertNotNull($profil);
        $this->assertTrue($profil->aktif);
        $this->assertSame('Ayu Siap Bantu', $profil->nama_profil);
        $this->assertSame(30000, $profil->tarif);
        $this->assertSame(['temani', 'titip-beli'], $profil->kategori);
        $this->assertSame('Bantu belanja', $profil->deskripsi);
        $this->assertDatabaseCount('profil_jasa', 1);
    }

    public function test_jasa_fields_are_required_when_jasa_is_active(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.edit-profile'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Sokaraja',
                'jasa_aktif' => 1,
            ])
            ->assertSessionHasErrors(['nama_profil', 'tarif', 'kategori']);

        $this->assertDatabaseCount('profil_jasa', 0);
    }

    public function test_kategori_jasa_harus_termasuk_daftar_yang_diperbolehkan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.edit-profile'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Sokaraja',
                'jasa_aktif' => 1,
                'nama_profil' => 'Ayu Siap Bantu',
                'tarif' => 30000,
                'kategori' => ['kategori-tidak-ada'],
            ])
            ->assertSessionHasErrors('kategori.0');
    }

    public function test_form_jasa_mengikuti_toggle_dan_tombol_simpan_adalah_submit_native(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard.edit-profile'))
            ->assertOk()
            ->assertSee('class="card jasa-toggle space-y-5"', false)
            ->assertSee('id="jasa-section" class="rounded-xl border border-slate-200 bg-askarsa-surface/70 p-5"', false)
            ->assertDontSee('data-jasa-save', false)
            ->assertSee('<button class="btn-primary" type="submit">Simpan</button>', false);
    }

    public function test_field_jasa_diabaikan_saat_toggle_nonaktif(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Sokaraja',
                'nama_profil' => 'Harus Diabaikan',
                'tarif' => 99999,
                'kategori' => ['temani'],
                'deskripsi' => 'Juga diabaikan',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $this->assertDatabaseCount('profil_jasa', 0);

        $pemilikJasaNonaktif = User::factory()->create();
        ProfilJasa::factory()->nonaktif()->create([
            'user_id' => $pemilikJasaNonaktif->id,
            'nama_profil' => 'Profil Lama',
            'tarif' => 1000,
            'kategori' => ['temani'],
        ]);

        $this->actingAs($pemilikJasaNonaktif)
            ->put(route('dashboard.profile.update'), [
                'name' => $pemilikJasaNonaktif->name,
                'email' => $pemilikJasaNonaktif->email,
                'domisili' => 'Sokaraja',
                'nama_profil' => 'Profil Baru',
                'tarif' => 99999,
                'kategori' => ['perbaikan'],
            ])
            ->assertRedirect(route('dashboard.profile'));

        $profil = $pemilikJasaNonaktif->fresh()->profilJasa;
        $this->assertFalse($profil->aktif);
        $this->assertSame('Profil Lama', $profil->nama_profil);
        $this->assertSame(1000, $profil->tarif);
    }

    public function test_profil_jasa_aktif_muncul_di_katalog_dan_hilang_saat_dinonaktifkan(): void
    {
        $user = User::factory()->create([
            'name' => 'Joanna Mareta',
            'domisili' => 'Purwokerto Utara',
        ]);

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Purwokerto Utara',
                'jasa_aktif' => 1,
                'nama_profil' => 'Joanna Siap Bantu',
                'tarif' => 25000,
                'kategori' => ['temani', 'titip-beli'],
                'deskripsi' => 'Bisa bantu urusan harian.',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $slug = 'joanna-mareta';

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Joanna Siap Bantu')
            ->assertSee('Temani, Titip Beli')
            ->assertSee('Bisa bantu urusan harian.')
            ->assertSee('Purwokerto Utara')
            ->assertSee('Rp25.000')
            ->assertSee(route('services.show', $slug), false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Joanna Siap Bantu');

        $this->get(route('services.show', $slug))
            ->assertOk()
            ->assertSee('Joanna Siap Bantu')
            ->assertSee('Bisa bantu urusan harian.')
            ->assertDontSee('Ceritakan kebutuhan Anda dengan jelas agar penyedia dapat menyiapkan bantuan yang sesuai.')
            ->assertSee('Pesan Jasa');

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Purwokerto Utara',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $this->get(route('services.index'))
            ->assertOk()
            ->assertDontSee('Joanna Siap Bantu');

        $this->get(route('services.show', $slug))->assertNotFound();
    }

    public function test_nonaktifkan_jasa_menyimpan_flag_false_tanpa_menghapus_data(): void
    {
        $user = User::factory()->create();
        ProfilJasa::factory()->create([
            'user_id' => $user->id,
            'nama_profil' => 'Joanna Siap Bantu',
            'tarif' => 25000,
            'kategori' => ['temani'],
        ]);

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'domisili' => 'Sokaraja',
            ])
            ->assertRedirect(route('dashboard.profile'));

        $profil = $user->fresh()->profilJasa;
        $this->assertFalse($profil->aktif);
        $this->assertSame('Joanna Siap Bantu', $profil->nama_profil);
        $this->assertDatabaseCount('profil_jasa', 1);
    }
}
