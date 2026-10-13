<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TotpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private Google2FA $google2fa;

    /**
     * Set up test dependencies.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->google2fa = new Google2FA(request());
    }

    /**
     * Generate a valid user with initialized Google Authenticator secret.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createMfaUser(array $attributes = []): User
    {
        $secret = $this->google2fa->generateSecretKey();

        return User::factory()->create(array_merge([
            'role' => 'USER',
            'status' => 'ACTIVE',
            'totp_secret' => $secret,
            'totp_verified_at' => now(),
        ], $attributes));
    }

    /**
     * Generate an admin user with initialized Google Authenticator secret.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createMfaAdmin(array $attributes = []): User
    {
        return $this->createMfaUser(array_merge([
            'role' => 'ADMIN',
        ], $attributes));
    }

    /**
     * Test that logging in with valid credentials redirects to OTP challenge and dashboard remains protected.
     */
    public function test_login_with_valid_credentials_redirects_to_otp_and_dashboard_remains_blocked(): void
    {
        $user = $this->createMfaUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('totp.challenge.show'));

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test that submitting a valid OTP code completes authentication and grants access to dashboard.
     */
    public function test_valid_otp_code_completes_login_and_grants_access(): void
    {
        $user = $this->createMfaUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $otp = $this->google2fa->getCurrentOtp($user->totp_secret);

        $response = $this->post(route('totp.challenge.store'), [
            'code' => $otp,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($user->id, session('auth.mfa_verified_user_id'));

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk();
    }

    /**
     * Test that an invalid OTP code is rejected and keeps dashboard protected.
     */
    public function test_invalid_otp_code_is_rejected(): void
    {
        $user = $this->createMfaUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        RateLimiter::clear('totp-challenge:'.$user->id.'|127.0.0.1');

        $response = $this->post(route('totp.challenge.store'), [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull(session('auth.mfa_verified_user_id'));

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test that missing or malformed OTP codes are rejected by validation.
     */
    public function test_missing_or_malformed_otp_code_is_rejected(): void
    {
        $user = $this->createMfaUser();

        $this->actingAsWithoutMfa($user);

        // Missing code
        $missingResponse = $this->post(route('totp.challenge.store'), []);
        $missingResponse->assertSessionHasErrors('code');

        // Non-digit code
        $alphaResponse = $this->post(route('totp.challenge.store'), [
            'code' => 'abcdef',
        ]);
        $alphaResponse->assertSessionHasErrors('code');

        // Too short
        $shortResponse = $this->post(route('totp.challenge.store'), [
            'code' => '123',
        ]);
        $shortResponse->assertSessionHasErrors('code');

        // Too long
        $longResponse = $this->post(route('totp.challenge.store'), [
            'code' => '1234567',
        ]);
        $longResponse->assertSessionHasErrors('code');
    }

    /**
     * Test direct access to protected routes before MFA is redirected to challenge.
     */
    public function test_direct_access_to_protected_routes_before_mfa_is_redirected(): void
    {
        $user = $this->createMfaUser();

        $this->actingAsWithoutMfa($user);

        $this->get(route('dashboard'))->assertRedirect(route('totp.challenge.show'));
        $this->get(route('transactions.index'))->assertRedirect(route('totp.challenge.show'));
        $this->get(route('budgets.index'))->assertRedirect(route('totp.challenge.show'));
        $this->get(route('profile.edit'))->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test admin must verify OTP before accessing admin dashboard.
     */
    public function test_admin_must_verify_otp_before_accessing_admin_dashboard(): void
    {
        $admin = $this->createMfaAdmin();

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('totp.challenge.show'));

        $this->get(route('admin.dashboard'))->assertRedirect(route('totp.challenge.show'));
        $this->get(route('admin.users.index'))->assertRedirect(route('totp.challenge.show'));

        $otp = $this->google2fa->getCurrentOtp($admin->totp_secret);
        $otpResponse = $this->post(route('totp.challenge.store'), [
            'code' => $otp,
        ]);

        $otpResponse->assertRedirect(route('admin.dashboard'));
        $this->assertEquals($admin->id, session('auth.mfa_verified_user_id'));

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
    }

    /**
     * Test regular user cannot access admin dashboard even after completing MFA.
     */
    public function test_user_cannot_access_admin_dashboard_even_after_completing_mfa(): void
    {
        $user = $this->createMfaUser();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Test logout clears MFA session and re-login requires OTP again.
     */
    public function test_logging_out_and_logging_in_again_requires_otp_again(): void
    {
        $user = $this->createMfaUser();

        // 1. Initial login
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $this->post(route('totp.challenge.store'), [
            'code' => $this->google2fa->getCurrentOtp($user->totp_secret),
        ]);
        $this->get(route('dashboard'))->assertOk();

        // 2. Logout
        $logoutResponse = $this->post(route('logout'));
        $this->assertGuest();
        $logoutResponse->assertRedirect('/');

        // 3. Login again
        $reloginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $reloginResponse->assertRedirect(route('totp.challenge.show'));

        $this->get(route('dashboard'))->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test admin logout clears MFA session and re-login requires OTP again.
     */
    public function test_admin_logging_out_and_logging_in_again_requires_otp_again(): void
    {
        $admin = $this->createMfaAdmin();

        // 1. Initial login
        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);
        $this->post(route('totp.challenge.store'), [
            'code' => $this->google2fa->getCurrentOtp($admin->totp_secret),
        ]);
        $this->get(route('admin.dashboard'))->assertOk();

        // 2. Admin Logout
        $logoutResponse = $this->post(route('admin.logout'));
        $this->assertGuest();
        $logoutResponse->assertRedirect('/admin/login');

        // 3. Admin Login again
        $reloginResponse = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);
        $reloginResponse->assertRedirect(route('totp.challenge.show'));

        $this->get(route('admin.dashboard'))->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test switching accounts or different sessions does not reuse MFA status.
     */
    public function test_mfa_status_cannot_be_reused_by_different_user(): void
    {
        $userA = $this->createMfaUser();
        $userB = $this->createMfaUser();

        $this->post('/login', [
            'email' => $userA->email,
            'password' => 'password',
        ]);
        $this->post(route('totp.challenge.store'), [
            'code' => $this->google2fa->getCurrentOtp($userA->totp_secret),
        ]);

        $this->actingAsWithoutMfa($userB);
        session(['auth.mfa_verified_user_id' => $userA->id]);

        $this->get(route('dashboard'))->assertRedirect(route('totp.challenge.show'));
    }

    /**
     * Test that user without configured MFA is redirected to activation setup upon login.
     */
    public function test_user_without_configured_mfa_is_redirected_to_activation(): void
    {
        $user = User::factory()->unverifiedTotp()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('totp.activation.show'));

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('totp.activation.show'));
    }

    /**
     * Test that admin without configured MFA is redirected to activation setup upon login.
     */
    public function test_admin_without_configured_mfa_is_redirected_to_activation(): void
    {
        $admin = User::factory()->unverifiedTotp()->create([
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('totp.activation.show'));

        $dashboardResponse = $this->get(route('admin.dashboard'));
        $dashboardResponse->assertRedirect(route('totp.activation.show'));
    }

    /**
     * Test OTP challenge is rate limited after 5 failed attempts.
     */
    public function test_otp_challenge_is_rate_limited_after_excessive_attempts(): void
    {
        $user = $this->createMfaUser();

        $this->actingAsWithoutMfa($user);

        $key = 'totp-challenge:'.$user->id.'|127.0.0.1';
        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('totp.challenge.store'), [
                'code' => '000000',
            ]);
        }

        $limitedResponse = $this->post(route('totp.challenge.store'), [
            'code' => '000000',
        ]);

        $limitedResponse->assertSessionHasErrors('code');
        $this->assertStringContainsString('Vui lòng thử lại sau', session('errors')->first('code'));
    }

    /**
     * Test user can log out from the OTP challenge screen.
     */
    public function test_user_can_logout_from_otp_challenge_screen(): void
    {
        $user = $this->createMfaUser();

        $this->actingAsWithoutMfa($user);

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Test admin can log out from the OTP challenge screen.
     */
    public function test_admin_can_logout_from_otp_challenge_screen(): void
    {
        $admin = $this->createMfaAdmin();

        $this->actingAsWithoutMfa($admin);

        $response = $this->post(route('admin.logout'));

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }
}
