<?php

namespace Tests\Feature;

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
            ->assertSee(route('dashboard.edit-profile').'#password', false)
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

    public function test_user_can_update_profile_without_changing_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Ayu Pratama',
            'email' => 'ayu@example.com',
            'domisili' => 'Sokaraja',
        ]);
        $oldPassword = $user->password;

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => 'Ayu Wibowo',
                'email' => 'ayu.wibowo@example.com',
                'domisili' => 'Baturaden',
            ])
            ->assertRedirect(route('dashboard.edit-profile'))
            ->assertSessionHas('status', 'Profil dan akun berhasil diperbarui.');

        $user->refresh();
        $this->assertSame('Ayu Wibowo', $user->name);
        $this->assertSame('ayu.wibowo@example.com', $user->email);
        $this->assertSame('Baturaden', $user->domisili);
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
            ->assertRedirect(route('dashboard.edit-profile'));

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
}
