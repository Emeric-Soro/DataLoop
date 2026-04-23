<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $plainPassword = $validated['mot_de_passe'] ?? $validated['password'];
        $hashedPassword = Hash::make($plainPassword);

        $user = User::create([
            'name' => $validated['nom'] ?? $validated['name'] ?? $validated['telephone'],
            'telephone' => $validated['telephone'],
            'email' => $validated['email'] ?? null,
            'password' => $hashedPassword,
            'role' => 'contributeur',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription reussie.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => UserResource::make($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $plainPassword = $validated['mot_de_passe'] ?? $validated['password'] ?? null;

        if (!$plainPassword) {
            return response()->json([
                'message' => 'Le mot de passe est requis.',
            ], 422);
        }

        $user = User::where('telephone', $validated['telephone'])->first();

        if (!$user || !Hash::check($plainPassword, $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion reussie.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'telephone' => ['required', 'string', 'max:20'],
        ]);

        $code = random_int(100000, 999999);

        Log::info('OTP Mock', [
            'telephone' => $validated['telephone'],
            'code' => (string) $code,
        ]);

        Cache::put('otp:' . $validated['telephone'], (string) $code, now()->addMinutes(5));

        return response()->json([
            'message' => 'OTP genere et logge pour le mode hackathon.',
            'otp_debug' => app()->environment(['local', 'testing']) || config('app.debug')
                ? (string) $code
                : null,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'telephone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'digits:6'],
        ]);

        $savedCode = Cache::get('otp:' . $validated['telephone']);

        if (!$savedCode || $savedCode !== $validated['code']) {
            return response()->json([
                'message' => 'Code OTP invalide ou expire.',
            ], 422);
        }

        Cache::forget('otp:' . $validated['telephone']);

        $user = User::query()->where('telephone', $validated['telephone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'OTP valide. Aucun utilisateur associe a ce numero.',
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verifie avec succes.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => UserResource::make($request->user()),
        ]);
    }
}
