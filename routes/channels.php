<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conversation = Conversation::find($id);
    if (! $conversation) {
        return false;
    }
    return $user->id === $conversation->coach_id
        || $user->id === $conversation->client_id
        || $user->id === $conversation->admin_id
        || $user->id === $conversation->other_user_id;
});
