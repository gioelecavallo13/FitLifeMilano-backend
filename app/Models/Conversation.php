<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['coach_id', 'client_id', 'admin_id', 'other_user_id'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function otherUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'other_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Numero di messaggi non letti in questa conversazione per l'utente $userId
     * (messaggi inviati dall'altro partecipante con read_at null).
     */
    public function unreadCountFor($userId): int
    {
        return $this->messages()->unreadBy($userId)->count();
    }

    public function isParticipant($user): bool
    {
        $id = (int) $user->id;
        if ($this->coach_id && $id === (int) $this->coach_id) {
            return true;
        }
        if ($this->client_id && $id === (int) $this->client_id) {
            return true;
        }
        if ($this->admin_id && $id === (int) $this->admin_id) {
            return true;
        }
        if ($this->other_user_id && $id === (int) $this->other_user_id) {
            return true;
        }
        return false;
    }

    public function otherParticipant($user): ?User
    {
        $id = (int) $user->id;
        if ($this->coach_id && $id === (int) $this->coach_id) {
            return $this->client;
        }
        if ($this->client_id && $id === (int) $this->client_id) {
            return $this->coach;
        }
        if ($this->admin_id && $id === (int) $this->admin_id) {
            return $this->otherUser;
        }
        if ($this->other_user_id && $id === (int) $this->other_user_id) {
            return $this->admin;
        }
        return null;
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}
