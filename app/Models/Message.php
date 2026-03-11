<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /**
     * Scope: messaggi non letti dal punto di vista di $userId (inviati da altri).
     */
    public function scopeUnreadBy(Builder $query, $userId): void
    {
        $query->where('user_id', '!=', $userId)->whereNull('read_at');
    }

    /**
     * Restituisce una mappa conversation_id => unread_count per le conversazioni date.
     * Evita N+1 query quando si devono mostrare i conteggi unread per molte conversazioni.
     */
    public static function unreadCountsByConversation(array $conversationIds, int $userId): \Illuminate\Support\Collection
    {
        if (empty($conversationIds)) {
            return collect();
        }

        return static::whereIn('conversation_id', $conversationIds)
            ->unreadBy($userId)
            ->selectRaw('conversation_id, count(*) as unread_count')
            ->groupBy('conversation_id')
            ->pluck('unread_count', 'conversation_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
