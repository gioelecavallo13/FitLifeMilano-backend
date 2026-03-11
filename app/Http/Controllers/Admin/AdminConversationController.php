<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminConversationController extends Controller
{
    /**
     * Lista conversazioni chat dell'admin (con coach e clienti).
     */
    public function index()
    {
        $admin = Auth::user();
        $conversations = Conversation::forAdmin($admin->id)
            ->with(['otherUser', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        $unreadMap = Message::unreadCountsByConversation($conversations->pluck('id')->toArray(), (int) $admin->id);
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $conv->unread_count = (int) ($unreadMap[$conv->id] ?? 0);
            $totalUnread += $conv->unread_count;
        }

        $coaches = User::where('role', 'coach')->orderBy('first_name')->orderBy('last_name')->get();
        $clients = User::where('role', 'client')->orderBy('first_name')->orderBy('last_name')->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Chat', 'url' => null],
        ];

        return view('admin.chat.index', compact('conversations', 'breadcrumb', 'coaches', 'clients', 'totalUnread'));
    }

    /**
     * Mostra la chat con un utente (coach o client).
     */
    public function show($id)
    {
        $conversation = Conversation::with([
            'messages' => fn ($q) => $q->orderBy('created_at')->orderBy('id'),
            'messages.user',
            'admin',
            'otherUser',
        ])->findOrFail($id);

        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }

        $otherUser = $conversation->otherParticipant(Auth::user());

        $conversation->messages()
            ->where('user_id', $otherUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Cache::forget('admin.unread_chat_count.' . Auth::id());

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Chat', 'url' => route('admin.chat.index')],
            ['label' => 'Chat con ' . $otherUser->first_name . ' ' . $otherUser->last_name, 'url' => null],
        ];

        return view('messages.chat', [
            'conversation' => $conversation,
            'otherUser' => $otherUser,
            'breadcrumb' => $breadcrumb,
            'sendMessageRoute' => 'admin.chat.send',
            'routeMarkRead' => 'admin.chat.markRead',
        ]);
    }

    /**
     * Segna come letti i messaggi ricevuti dall'altro in questa conversazione (chiamata da AJAX quando si riceve un messaggio via Echo).
     */
    public function markAsRead($id)
    {
        $conversation = Conversation::with('otherUser')->findOrFail($id);
        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }
        $otherUser = $conversation->otherParticipant(Auth::user());
        $conversation->messages()
            ->where('user_id', $otherUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Cache::forget('admin.unread_chat_count.' . Auth::id());

        return response()->json(['ok' => true], 200);
    }

    /**
     * Invia un messaggio (AJAX o form).
     */
    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $conversation = Conversation::with('otherUser')->findOrFail($id);
        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('body'),
        ]);
        $message->setRelation('user', Auth::user());
        $message->setRelation('conversation', $conversation);

        $recipient = $conversation->otherParticipant(Auth::user());
        if ($recipient && $recipient->role === 'admin') {
            Cache::forget('admin.unread_chat_count.' . $recipient->id);
        }

        $senderName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        broadcast(new MessageSent($message, $senderName))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $message->id,
                'body' => $message->body,
                'user_id' => $message->user_id,
                'sender_name' => $senderName,
                'created_at' => $message->created_at->toIso8601String(),
            ]);
        }

        return back()->with('success', 'Messaggio inviato.');
    }

    /**
     * Apri o crea conversazione con un utente (coach o client) e redirect alla chat.
     */
    public function startWithUser($userId)
    {
        $user = User::whereIn('role', ['coach', 'client'])->findOrFail($userId);
        $conversation = Conversation::firstOrCreate(
            [
                'admin_id' => Auth::id(),
                'other_user_id' => $user->id,
            ],
            [
                'coach_id' => null,
                'client_id' => null,
            ]
        );
        return redirect()->route('admin.chat.show', $conversation->id);
    }
}
