<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    // Étape 1 : demander un code de réinitialisation
    public function demander(Request $request)
    {
        $request->validate([
            'login' => 'required|string|exists:users,login',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'password_reset_' . $request->login;

        $user = User::where('login', $request->login)->firstOrFail();
        Cache::put($cacheKey, Hash::make($code), now()->addMinutes(30));

        $response = ['message' => 'Code de réinitialisation envoyé. Valable 30 minutes.'];

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($code, $request->login));
        } catch (\Exception) {
            // Mailer non configuré : retourner le code directement (mode développement)
            $response['debug_code'] = $code;
        }

        return response()->json($response, 200);
    }

    // Étape 2 : valider le code et changer le mot de passe
    public function reinitialiser(Request $request)
    {
        $request->validate([
            'login'                 => 'required|string|exists:users,login',
            'code'                  => 'required|string|size:6',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $cacheKey = 'password_reset_' . $request->login;
        $stored   = Cache::get($cacheKey);

        if (!$stored || !Hash::check($request->code, $stored)) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $user = User::where('login', $request->login)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        Cache::forget($cacheKey);

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.'], 200);
    }
}
