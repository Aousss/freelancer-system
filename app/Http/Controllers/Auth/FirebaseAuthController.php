<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FirebaseAuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function callback(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        $apiKey = config('services.firebase.api_key');

        // Verify the Firebase ID token via REST API
        $response = Http::post(
            "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
            ['idToken' => $request->id_token]
        );

        if ($response->failed() || empty($response->json('users'))) {
            return response()->json(['error' => 'Invalid Firebase token.'], 401);
        }

        $firebaseUser = $response->json('users.0');

        $user = User::updateOrCreate(
            ['firebase_uid' => $firebaseUser['localId']],
            [
                'name'              => $firebaseUser['displayName'] ?? 'Unknown',
                'email'             => $firebaseUser['email'] ?? '',
                'avatar'            => $firebaseUser['photoUrl'] ?? null,
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user, remember: true);

        return response()->json(['redirect' => route('dashboard')]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
