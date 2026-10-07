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

        return redirect()->intended('/admin/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
