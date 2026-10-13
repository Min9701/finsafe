<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TotpActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unactivated_user_is_redirected_to_totp_activation(): void
    {
        $user = User::factory()->unverifiedTotp()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('totp.activation.show'));
    }

    public function test_activation_screen_displays_secret_and_generates_secret_when_missing(): void
    {
        $user = User::factory()->unverifiedTotp()->create();

        $response = $this->actingAs($user)->get(route('totp.activation.show'));

        $response->assertOk();
        $this->assertNotNull($user->refresh()->totp_secret);
        $response->assertSee($user->totp_secret, false);
    }

    public function test_user_can_activate_account_with_valid_totp_code(): void
    {
        $google2fa = new Google2FA(request());
        $user = User::factory()->unverifiedTotp()->create([
            'totp_secret' => $google2fa->generateSecretKey(),
        ]);

        $response = $this->actingAs($user)->post(route('totp.activation.store'), [
            'code' => $google2fa->getCurrentOtp($user->totp_secret),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->refresh()->totp_verified_at);
    }

    public function test_user_cannot_activate_account_with_invalid_totp_code(): void
    {
        $user = User::factory()->unverifiedTotp()->create([
            'totp_secret' => (new Google2FA(request()))->generateSecretKey(),
        ]);

        RateLimiter::clear('totp-activation:'.$user->id.'|127.0.0.1');

        $response = $this->actingAs($user)->post(route('totp.activation.store'), [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->refresh()->totp_verified_at);
    }
}
