<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertOk();
        $response->assertSee('Reset password');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword@123'),
        ]);
        $token = Password::createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', trans(Password::PASSWORD_RESET));

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
    }

    public function test_new_password_must_match_complexity_rules(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.store'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'simplepass',
                'password_confirmation' => 'simplepass',
            ]);

        $response->assertRedirect(route('password.reset', ['token' => $token]));
        $response->assertSessionHasErrors('password');
    }
}
