<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Mostra la pagina del profilo dell'utente corrente (propria anagrafica)
     */
    public function show()
    {
        $user = Auth::user()->load(['courses.coach', 'createdCourses']);

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('dashboard.selector')],
            ['label' => 'Profilo', 'url' => null],
        ];

        return view('profile.show', compact('user', 'breadcrumb'));
    }

    /**
     * Aggiorna la foto profilo dell'utente corrente.
     * L'immagine viene ridimensionata a 150x150 e compressa in JPEG 75% prima del salvataggio nel DB.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();
        $data = \App\Models\User::processProfilePhotoFromUpload($request->file('profile_photo'));
        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Foto profilo aggiornata con successo!');
    }
}
