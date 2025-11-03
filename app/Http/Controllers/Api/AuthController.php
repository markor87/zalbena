<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember', false);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Neispravni kredencijali.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Uspešno ste se odjavili.'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
