<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmTotpActivationRequest;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TotpActivationController extends Controller
{
    public function show(Request $request, Google2FA $google2fa): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->totp_verified_at !== null) {
            return redirect()->route('dashboard');
        }

        if ($user->totp_secret === null) {
            $user->forceFill(['totp_secret' => $google2fa->generateSecretKey()])->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $user->totp_secret);
        $qrCode = (new Writer(new ImageRenderer(new RendererStyle(220, 4), new SvgImageBackEnd())))
            ->writeString($qrCodeUrl);

        return view('auth.activate-totp', [
            'qrCode' => $qrCode,
            'secret' => $user->totp_secret,
        ]);
    }

    public function store(ConfirmTotpActivationRequest $request, Google2FA $google2fa): RedirectResponse
    {
        $user = $request->user();
        $key = 'totp-activation:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Vui lòng thử lại sau '.RateLimiter::availableIn($key).' giây.']);
        }

        if ($user->totp_secret === null || ! $google2fa->verifyKey($user->totp_secret, $request->validated('code'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['code' => 'Mã xác thực không hợp lệ.']);
        }

        RateLimiter::clear($key);
        $user->forceFill(['totp_verified_at' => now()])->save();

        return redirect()->route('dashboard')->with('success', 'Google Authenticator đã được kích hoạt.');
    }
}
