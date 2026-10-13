<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyTotpChallengeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TotpChallengeController extends Controller
{
    /**
     * Display the Google Authenticator OTP challenge view.
     */
    public function show(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->totp_secret === null || $user->totp_verified_at === null) {
            return redirect()->route('totp.activation.show');
        }

        if ($request->session()->get('auth.mfa_verified_user_id') === $user->id) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.verify-totp');
    }

    /**
     * Verify the submitted OTP code and mark MFA as verified for the current session.
     *
     *
     * @throws ValidationException
     */
    public function store(VerifyTotpChallengeRequest $request, Google2FA $google2fa): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->totp_secret === null || $user->totp_verified_at === null) {
            return redirect()->route('totp.activation.show');
        }

        $key = 'totp-challenge:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        $code = (string) $request->validated('code');

        if (! $google2fa->verifyKey($user->totp_secret, $code)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'code' => 'Mã xác thực không hợp lệ.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->put('auth.mfa_verified_user_id', $user->id);

        $default = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return redirect()->intended($default);
    }
}
