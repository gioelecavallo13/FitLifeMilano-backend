<?php

namespace App\Http\Controllers;

use App\Models\User;

class ProfilePhotoController extends Controller
{
    /**
     * Restituisce la foto profilo dell'utente come risposta binaria.
     * Se l'utente non ha foto, redirect all'immagine default.
     */
    public function show(User $user)
    {
        if (!$user->profile_photo) {
            return redirect('/images/foto-profilo-default-media.jpg');
        }

        return response($user->profile_photo)
            ->header('Content-Type', $user->profile_photo_mime ?? 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
