<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 新規登録画面を表示
    public function showRegister()
    {
        return view('auth.register');
    }

    // 新規登録処理
    public function register(Request $request)
    {
        // 入力チェック
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // ユーザーを保存
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 自動ログイン
        Auth::login($user);
        $request->session()->regenerate();

        // 履修一覧へ移動
        return redirect('/entries');
    }

    // ログイン画面を表示
    public function showLogin()
    {
        return view('auth.login');
    }

    // ログイン処理
    public function login(Request $request)
    {
        // 入力チェック
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // メールアドレスとパスワードを確認
        if (Auth::attempt($validated)) {
            // セッションを再生成
            $request->session()->regenerate();

            // ログイン成功
            return redirect('/entries');
        }

        // ログイン失敗
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        // ログアウト
        Auth::logout();

        // セッションを無効化
        $request->session()->invalidate();

        // CSRFトークンを再生成
        $request->session()->regenerateToken();

        // ログイン画面へ移動
        return redirect('/login');
    }
}
