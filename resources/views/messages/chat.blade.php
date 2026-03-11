@extends('layouts.layout')
@section('title', 'Chat con ' . $otherUser->first_name . ' ' . $otherUser->last_name . ' | ' . config('app.name'))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white fw-bold text-uppercase mb-0 h4">Chat con {{ $otherUser->first_name }} {{ $otherUser->last_name }}</h1>
    </div>

    <div class="card bg-dark border-warning shadow-lg text-white">
        <div class="card-body p-0 d-flex flex-column" style="min-height: 400px;">
            <div id="chat-messages" class="p-4 overflow-auto flex-grow-1" style="max-height: 400px;">
                @foreach($conversation->messages->sortBy('created_at') as $msg)
                    <div class="mb-3 message-row" data-message-id="{{ $msg->id }}">
                        <div class="d-flex {{ $msg->user_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="rounded p-2 {{ $msg->user_id === auth()->id() ? 'bg-warning text-dark' : 'bg-secondary' }}" style="max-width: 75%;">
                                <span class="small fw-bold d-block">{{ $msg->user->first_name }} {{ $msg->user->last_name }}</span>
                                <span class="small text-muted">{{ $msg->created_at->timezone('Europe/Rome')->format('d/m H:i') }}</span>
                                <p class="mb-0 mt-1">{{ $msg->body }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-top border-secondary">
                <form id="chat-form" action="{{ route($sendMessageRoute, $conversation->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <textarea name="body" id="chat-body" class="form-control bg-black text-white border-secondary" rows="2" placeholder="Scrivi un messaggio..." maxlength="5000" required></textarea>
                        <button type="submit" id="chat-submit" class="btn btn-warning px-4">
                            <i class="bi bi-send-fill"></i> Invia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('chat-scripts')
@include('partials.chat-scripts')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var conversationId = {{ $conversation->id }};
    var sendRoute = "{{ route($sendMessageRoute, $conversation->id) }}";
    var markReadRoute = "{{ route($routeMarkRead ?? 'coach.messages.markRead', $conversation->id) }}";
    var currentUserId = {{ auth()->id() }};
    var currentUserName = "{{ addslashes(auth()->user()->first_name . ' ' . auth()->user()->last_name) }}";

    var form = document.getElementById('chat-form');
    var bodyInput = document.getElementById('chat-body');
    var submitBtn = document.getElementById('chat-submit');
    var messagesContainer = document.getElementById('chat-messages');

    requestAnimationFrame(function() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var body = bodyInput.value.trim();
        if (!body) return;
        submitBtn.disabled = true;
        fetch(sendRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
            },
            body: JSON.stringify({ body: body })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            appendMessage(data.id, data.body, data.user_id, data.sender_name, data.created_at);
            bodyInput.value = '';
        })
        .catch(function() {
            form.submit();
        })
        .finally(function() {
            submitBtn.disabled = false;
        });
    });

    function appendMessage(id, body, userId, senderName, createdAt) {
        var isMe = userId === currentUserId;
        var dateStr = createdAt ? new Date(createdAt).toLocaleString('it-IT', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) : '';
        var esc = function(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; };
        var html = '<div class="mb-3 message-row" data-message-id="' + id + '"><div class="d-flex ' + (isMe ? 'justify-content-end' : 'justify-content-start') + '"><div class="rounded p-2 ' + (isMe ? 'bg-warning text-dark' : 'bg-secondary') + '" style="max-width: 75%;"><span class="small fw-bold d-block">' + esc(senderName) + '</span><span class="small text-muted">' + dateStr + '</span><p class="mb-0 mt-1">' + esc(body) + '</p></div></div></div>';
        messagesContainer.insertAdjacentHTML('beforeend', html);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    if (typeof Echo !== 'undefined') {
        Echo.private('conversation.' + conversationId).listen('.MessageSent', function(e) {
            if (e.user_id !== currentUserId) {
                appendMessage(e.id, e.body, e.user_id, e.sender_name, e.created_at);
                var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                fetch(markReadRoute, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            }
        });
    }
});
</script>
@endpush
@endsection
