<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // CẢNH BÁO: Câu lệnh SQL này nối trực tiếp biến $email, gây ra lỗ hổng SQL Injection
        $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        
        // Thực thi câu lệnh SQL thô
        $users = DB::select($query);

        // Nếu tìm thấy user từ kết quả SQL Injection
        if (!empty($users)) {
            $user = User::find($users[0]->id);
            
            // Đăng nhập trực tiếp vào hệ thống bỏ qua kiểm tra password (hoặc bạn có thể tự check password nếu muốn)
            Auth::login($user);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
