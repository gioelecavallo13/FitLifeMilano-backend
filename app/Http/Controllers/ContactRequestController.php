<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest; 
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
public function store(Request $request) 
{
    $validated = $request->validate([
        'email'   => 'required|email|max:255',
        'subject' => 'required|in:informazioni,corsi,staff,commerciale', 
        'message' => 'required|min:10|max:500', 
    ]);

    // Ora che i nomi coincidono, puoi passare direttamente $validated!
    ContactRequest::create($validated);

    return back()->with('success', 'Richiesta inviata con successo!');
}
}