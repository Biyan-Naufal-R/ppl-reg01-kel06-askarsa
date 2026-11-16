<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_posts_to_the_login_route(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('login.store'), false);
    }

    public function test_a_registered_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'ayu@example.com',
            'password' => 'password123',
        ]);

        $this->followingRedirects()
            ->post(route('login.store'), [
            'email' => 'ayu@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertSee('Login berhasil. Selamat datang kembali!')
            ->assertSee('id="status-toast"', false);

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_sees_the_account_menu(): void
    {
        $user = User::factory()->create([
            'name' => 'Ayu Pratama',
            'email' => 'ayu@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Ayu Pratama')
            ->assertSee('ayu@example.com')
            ->assertSee('Profil saya')
            ->assertSee('Edit profil / akun')
            ->assertSee(route('logout'), false);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Anda berhasil logout.');

        $this->assertGuest();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ayu@example.com',
            'password' => 'password123',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'ayu@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
