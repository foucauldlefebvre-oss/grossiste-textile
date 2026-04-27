<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if ($request->filled('website_url')) {
            return redirect()->route('login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $oldSessionId = session()->getId();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Transférer le devis de la session anonyme vers le compte
            \App\Models\Quote::where('session_id', $oldSessionId)
                ->whereNull('user_id')
                ->whereIn('status', ['draft', 'sent'])
                ->update(['user_id' => auth()->id()]);

            $request->session()->regenerate();

            // TODO 2b: redirection vers /mon-devis supprimée (route dégagée)
            return redirect()->intended(route('account.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if ($request->filled('website_url')) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:17'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'rgpd' => ['accepted'],
        ]);

        unset($validated['rgpd']);
        $oldSessionId = session()->getId();
        $user = User::create($validated);

        // Transférer le devis AVANT le login (qui régénère la session)
        \App\Models\Quote::where('session_id', $oldSessionId)
            ->whereNull('user_id')
            ->whereIn('status', ['draft', 'sent'])
            ->update(['user_id' => $user->id]);

        Auth::login($user);

        // Send welcome email
        app(\App\Services\NotificationService::class)->sendWelcome($user);

        // Rediriger vers le devis si existant
        $hasQuote = \App\Models\Quote::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'sent'])
            ->whereHas('items')
            ->exists();

        return redirect()->route($hasQuote ? 'mon-devis' : 'account.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = PasswordBroker::sendResetLink($request->only('email'));

        return $status === PasswordBroker::RESET_LINK_SENT
            ? back()->with('status', 'Un lien de reinitialisation a ete envoye a votre adresse email.')
            : back()->withErrors(['email' => 'Impossible de trouver un utilisateur avec cette adresse email.']);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Votre mot de passe a ete reinitialise.')
            : back()->withErrors(['email' => 'Ce lien de reinitialisation est invalide ou a expire.']);
    }

    private function migrateSessionQuote(User $user): void
    {
        $sessionQuote = \App\Models\Quote::whereNull('user_id')
            ->where('session_id', session()->getId())
            ->where('status', 'draft')
            ->first();

        if ($sessionQuote) {
            $sessionQuote->update(['user_id' => $user->id]);
        }
    }
}
