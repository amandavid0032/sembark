<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Basic session-based auth controller.
// Using plain Laravel auth here (no Breeze / Jetstream) because the spec says
// barebones HTML is fine and I didn't want to drag in extra scaffolding.
class AuthController extends Controller
{
    public function showLogin()
    {
        // var_dump('showLogin called');
        // die();

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // var_dump($credentials);
        // die();

        if (Auth::attempt($credentials)) {
            //  session  regenerate after successful login.
            $request->session()->regenerate();

            Log::info('user logged in', ['email' => $credentials['email']]);

            return redirect()->intended('/dashboard');
        }

       
        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // var_dump('logout done');
        // die();

        return redirect('/');
    }
}
