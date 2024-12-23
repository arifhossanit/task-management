<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the registration form (web).
     */
    public function showRegisterForm()
    {
        return view('auth.register'); // Blade template for registration
    }

    /**
     * Handle user registration (web and API).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        }

        Auth::login($user); // Log in the user (web)
        return redirect()->route('tasks.index')->with('success', 'Registration successful');
    }

    /**
     * Show the login form (web).
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Blade template for login
    }

    /**
     * Handle user login (web and API).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($request->expectsJson()) {
            if (!Auth::attempt($validated)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();
            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        if (!Auth::attempt($validated)) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->withInput();
        }

        return redirect()->route('tasks.index')->with('success', 'Login successful');
    }

    /**
     * Handle user logout (web and API).
     */
    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        Auth::logout(); // Log out the user (web)

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('/')->with('success', 'Logged out successfully');
    }
}
