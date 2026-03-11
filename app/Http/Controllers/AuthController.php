<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Gestisce il tentativo di autenticazione.
     */
    public function login(Request $request)
    {
        // 1. Validazione dati
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Tentativo di accesso
        if (Auth::attempt($credentials)) {
            // Rigenera la sessione per prevenire attacchi di fixation
            $request->session()->regenerate();

            // 3. Reindirizzamento basato sul ruolo tramite la rotta ponte
            // intended() riporta l'utente alla pagina che stava cercando di visitare
            return redirect()->intended(route('dashboard.selector'));
        }

        // 4. Se fallisce, torna indietro con l'errore
        return back()->withErrors([
            'email' => 'Le credenziali fornite non sono corrette.',
        ])->onlyInput('email');
    }

    /**
     * Gestisce il logout dell'utente.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalida e rigenera il token CSRF per sicurezza
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}