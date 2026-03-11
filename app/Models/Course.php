<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    /**
     * Campi popolabili per il corso
     */
    protected $fillable = [
        'name',
        'description',
        'user_id',      // ID del Coach
        'price',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
    ];

    /**
     * RELAZIONE: Il Coach che tiene il corso
     * (Un corso appartiene a un solo User/Coach)
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * RELAZIONE: I Clienti iscritti al corso
     * (Un corso ha molti utenti iscritti tramite course_user)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}