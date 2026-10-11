<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Đã đăng nhập rồi thì khỏi xem lại trang login
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = \App\Models\User::where('username', $credentials['username'])->first();

        if ($user && $user->isLocked()) {
            return back()
                ->withErrors(['username' => 'Tài khoản này đang bị khóa.'])
                ->onlyInput('username');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        Auth::user()->forceFill(['last_login_at' => now()])->save();

        // Vai trò nào cũng tạm vào chung /dashboard — sau này tách theo role thì đổi ở đây
        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
