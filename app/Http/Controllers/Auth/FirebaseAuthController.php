<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FirebaseAuthController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function callback(Request $request): JsonResponse
    {
        $request->validate(['id_token' => 'required|string']);

        $apiKey = config('services.firebase.api_key');

        if (empty($apiKey)) {
            Log::error('Firebase API key is not configured.');

            return response()->json(['error' => 'Firebase API key is not configured.'], 500);
        }

        try {
            // Verify the Firebase ID token via REST API
            $response = Http::post(
                "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
                ['idToken' => $request->input('id_token')]
            );
        } catch (ConnectionException $e) {
            // In local/testing environments, fallback without SSL verification if cURL SSL error 60 occurs
            if ((app()->isLocal() || app()->environment('testing')) && str_contains($e->getMessage(), 'cURL error 60')) {
                Log::warning('Firebase authentication SSL verification skipped: '.$e->getMessage());
                $response = Http::withoutVerifying()->post(
                    "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
                    ['idToken' => $request->input('id_token')]
                );
            } else {
                Log::error('Firebase authentication connection failed: '.$e->getMessage());

                return response()->json(['error' => 'Unable to connect to Google authentication service: '.$e->getMessage()], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Firebase authentication lookup failed: '.$e->getMessage());

            return response()->json(['error' => 'Authentication error: '.$e->getMessage()], 500);
        }

        if ($response->failed() || empty($response->json('users'))) {
            $errorMessage = $response->json('error.message') ?? 'Invalid Firebase token.';
            Log::warning('Firebase authentication failed', ['response' => $response->json()]);

            return response()->json(['error' => $errorMessage], 401);
        }

        $firebaseUser = $response->json('users.0');
        $firebaseUid = $firebaseUser['localId'];
        $email = $firebaseUser['email'] ?? null;

        // Find existing user by firebase_uid or matching email
        $user = User::where('firebase_uid', $firebaseUid)
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->first();

        if ($user) {
            $user->update([
                'firebase_uid' => $firebaseUid,
                'name' => $firebaseUser['displayName'] ?? $user->name,
                'email' => $email ?? $user->email,
                'avatar' => $firebaseUser['photoUrl'] ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'firebase_uid' => $firebaseUid,
                'name' => $firebaseUser['displayName'] ?? 'Unknown',
                'email' => $email,
                'avatar' => $firebaseUser['photoUrl'] ?? null,
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return response()->json(['redirect' => route('dashboard')]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
