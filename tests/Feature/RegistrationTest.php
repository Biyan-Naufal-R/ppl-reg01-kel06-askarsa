<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_posts_to_the_registration_route(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(route('register.store'), false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="domisili"', false);
    }

    public function test_registration_form_lists_all_banyumas_districts(): void
    {
        $districts = config('domisili.kecamatan');

        $this->assertCount(27, $districts);

        $response = $this->get(route('register'))->assertOk();
        foreach ($districts as $district) {
            $response->assertSee('value="'.$district.'"', false);
        }
    }

    public function test_a_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ayu Pratama',
            'email' => 'ayu@example.com',
            'domisili' => 'Sumpiuh',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'ayu@example.com')->firstOrFail();

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Sumpiuh', $user->domisili);
        $this->assertNotSame('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function test_registration_rejects_a_district_outside_banyumas(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Ayu Pratama',
                'email' => 'ayu@example.com',
                'domisili' => 'Cilacap',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('domisili');
    }

    public function test_registration_rejects_duplicate_email_and_mismatched_password(): void
    {
        User::factory()->create(['email' => 'ayu@example.com']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Ayu Pratama',
                'email' => 'ayu@example.com',
                'domisili' => 'Purwokerto Utara',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);
    }
}
