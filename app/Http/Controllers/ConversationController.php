<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Lista conversazioni dell'utente (come coach o come client).
     */
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::where('coach_id', $user->id)
            ->orWhere('client_id', $user->id)
            ->orWhere('other_user_id', $user->id)
            ->with(['coach', 'client', 'admin', 'otherUser', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        $unreadMap = Message::unreadCountsByConversation($conversations->pluck('id')->toArray(), (int) $user->id);
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $conv->unread_count = (int) ($unreadMap[$conv->id] ?? 0);
            $totalUnread += $conv->unread_count;
        }

        $isCoach = $user->role === 'coach';
        $breadcrumb = $isCoach
            ? [
                ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
                ['label' => 'Messaggi', 'url' => null],
            ]
            : [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Messaggi', 'url' => null],
            ];

        $view = $isCoach ? 'coach.messages.index' : 'client.messages.index';
        if ($isCoach) {
            $clients = User::where('role', 'client')->orderBy('first_name')->orderBy('last_name')->get();
            $coaches = User::where('role', 'coach')->where('id', '!=', $user->id)->orderBy('first_name')->orderBy('last_name')->get();
            return view($view, compact('conversations', 'breadcrumb', 'clients', 'coaches', 'totalUnread'));
        }
        $coaches = User::where('role', 'coach')->orderBy('first_name')->orderBy('last_name')->get();
        return view($view, compact('conversations', 'breadcrumb', 'coaches', 'totalUnread'));
    }

    /**
     * Mostra la chat di una conversazione.
     */
    public function show($id)
    {
        $conversation = Conversation::with(['messages' => fn ($q) => $q->orderBy('created_at')->orderBy('id'), 'messages.user', 'coach', 'client'])
            ->findOrFail($id);

        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }

        $otherUser = $conversation->otherParticipant(Auth::user());

        // Marca come letti i messaggi ricevuti dall'altro in questa chat
        $conversation->messages()
            ->where('user_id', $otherUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $user = Auth::user();
        $isCoach = $user->role === 'coach';

        $breadcrumb = $isCoach
            ? [
                ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
                ['label' => 'Messaggi', 'url' => route('coach.messages.index')],
                ['label' => 'Chat con ' . $otherUser->first_name . ' ' . $otherUser->last_name, 'url' => null],
            ]
            : [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Messaggi', 'url' => route('client.messages.index')],
                ['label' => 'Chat con ' . $otherUser->first_name . ' ' . $otherUser->last_name, 'url' => null],
            ];

        $sendMessageRoute = $isCoach ? 'coach.messages.send' : 'client.messages.send';
        $routeMarkRead = $isCoach ? 'coach.messages.markRead' : 'client.messages.markRead';

        return view('messages.chat', compact('conversation', 'otherUser', 'breadcrumb', 'sendMessageRoute', 'routeMarkRead'));
    }

    /**
     * Segna come letti i messaggi ricevuti dall'altro in questa conversazione (chiamata da AJAX quando si riceve un messaggio via Echo).
     */
    public function markAsRead($id)
    {
        $conversation = Conversation::with(['coach', 'client'])->findOrFail($id);
        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }
        $otherUser = $conversation->otherParticipant(Auth::user());
        $conversation->messages()
            ->where('user_id', $otherUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true], 200);
    }

    /**
     * Invia un messaggio nella conversazione (chiamata da form/AJAX).
     */
    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $conversation = Conversation::findOrFail($id);
        if (! $conversation->isParticipant(Auth::user())) {
            abort(403);
        }

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('body'),
        ]);
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
     * Coach: apri o crea conversazione con un cliente e redirect alla chat.
     */
    public function startWithClient($clientId)
    {
        if (Auth::user()->role !== 'coach') {
            abort(403);
        }
        $conversation = Conversation::firstOrCreate(
            [
                'coach_id' => Auth::id(),
                'client_id' => $clientId,
            ]
        );
        return redirect()->route('coach.messages.show', $conversation->id);
    }

    /**
     * Client: apri o crea conversazione con un coach e redirect alla chat.
     */
    public function startWithCoach($coachId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403);
        }
        $conversation = Conversation::firstOrCreate(
            [
                'coach_id' => $coachId,
                'client_id' => Auth::id(),
            ]
        );
        return redirect()->route('client.messages.show', $conversation->id);
    }

    /**
     * Coach: apri o crea conversazione con un altro coach (collega) e redirect alla chat.
     */
    public function startWithCoachColleague($coachId)
    {
        if (Auth::user()->role !== 'coach') {
            abort(403);
        }
        $conversation = Conversation::firstOrCreate(
            [
                'coach_id' => Auth::id(),
                'client_id' => $coachId,
            ]
        );
        return redirect()->route('coach.messages.show', $conversation->id);
    }
}
