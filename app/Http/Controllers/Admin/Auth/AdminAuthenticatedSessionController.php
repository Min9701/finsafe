<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (
            ! Auth::attempt(
                array_merge($credentials, [
                    'role' => 'ADMIN',
                    'status' => 'ACTIVE',
                ]),
                $remember
            )
        ) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->forget('auth.mfa_verified_user_id');

        $user = Auth::user();

        if ($user->totp_secret === null || $user->totp_verified_at === null) {
            return redirect()->route('totp.activation.show');
        }

        return redirect()->route('totp.challenge.show');
    }

    /**
     * Destroy an authenticated admin session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
