<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    // Forza il nome esatto della tabella che vedi nel tuo database
    protected $table = 'contact_requests'; 

    protected $fillable = ['email', 'subject', 'message', 'status'];
}