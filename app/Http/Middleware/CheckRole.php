<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Controlla se l'utente è loggato
        // 2. Controlla se il ruolo dell'utente coincide con quello richiesto dalla rotta
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, "Accesso negato. Questa area è riservata ai " . $role);
        }

        return $next($request);
    }
}