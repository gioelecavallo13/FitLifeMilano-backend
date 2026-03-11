@extends('layouts.layout')
@section('title', 'Chat' . ' | ' . config('app.name'))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="text-white fw-bold text-uppercase mb-0">Chat con coach e clienti</h1>
        <button type="button" class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#nuovaChatModal">
            <i class="bi bi-plus-lg me-1"></i> Nuova chat
        </button>
    </div>

    <div class="card bg-dark border-warning shadow-lg text-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-black text-warning text-uppercase small">
                        <tr>
                            <th class="ps-4 py-3">Utente</th>
                            <th class="py-3">Ultimo messaggio</th>
                            <th class="py-3 pe-4">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversations as $conv)
                            @php
                                $other = $conv->otherUser;
                                $lastMsg = $conv->messages->first();
                                $unread = $conv->unread_count ?? 0;
                            @endphp
                            <tr class="table-row-chat cursor-pointer" data-href="{{ route('admin.chat.show', $conv->id) }}" role="button" tabindex="0">
                                <td class="ps-4 py-3">
                                    {{ $other->first_name }} {{ $other->last_name }}
                                    <span class="badge {{ $other->role === 'coach' ? 'bg-info' : 'bg-secondary' }} rounded-pill ms-1">{{ $other->role === 'coach' ? 'Coach' : 'Cliente' }}</span>
                                    @if($unread > 0)
                                        <span class="badge bg-danger rounded-pill ms-2" aria-label="{{ $unread }} messaggi non letti">{{ $unread }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-secondary">
                                    @if($lastMsg)
                                        {{ Str::limit($lastMsg->body, 40) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 pe-4 text-secondary small">
                                    @if($lastMsg)
                                        {{ $lastMsg->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') }}
                                    @else
                                        {{ $conv->updated_at->timezone('Europe/Rome')->format('d/m/Y') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-secondary">
                                    <i class="bi bi-chat-dots display-6 d-block mb-2"></i>
                                    Nessuna conversazione. Usa "Nuova chat" per scrivere a un coach o a un cliente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Nuova chat: ricerca e scelta destinatario --}}
<div class="modal fade" id="nuovaChatModal" tabindex="-1" aria-labelledby="nuovaChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content bg-dark border-warning text-white">
            <div class="modal-header border-warning">
                <h5 class="modal-title text-warning" id="nuovaChatModalLabel">Scegli a chi scrivere</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="chatSearchUser" class="form-control bg-black text-white border-secondary" placeholder="Cerca per nome o email..." autocomplete="off">
                </div>
                <ul class="nav nav-tabs border-secondary mb-3" id="chatUserTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-warning" id="tab-coach" data-bs-toggle="tab" data-bs-target="#panel-coach" type="button" role="tab">Coach</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-warning" id="tab-client" data-bs-toggle="tab" data-bs-target="#panel-client" type="button" role="tab">Clienti</button>
                    </li>
                </ul>
                <div class="tab-content" id="chatUserPanels">
                    <div class="tab-pane fade show active" id="panel-coach" role="tabpanel">
                        <div id="list-coach" class="list-group list-group-flush">
                            @foreach($coaches as $u)
                                <a href="{{ route('admin.chat.startWithUser', $u->id) }}" class="list-group-item list-group-item-action bg-black border-secondary text-white chat-user-item" data-name="{{ strtolower($u->first_name . ' ' . $u->last_name) }}" data-email="{{ strtolower($u->email) }}">
                                    <strong>{{ $u->first_name }} {{ $u->last_name }}</strong>
                                    <span class="text-secondary small d-block">{{ $u->email }}</span>
                                </a>
                            @endforeach
                            @if($coaches->isEmpty())
                                <p class="text-secondary small">Nessun coach.</p>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="panel-client" role="tabpanel">
                        <div id="list-client" class="list-group list-group-flush">
                            @foreach($clients as $u)
                                <a href="{{ route('admin.chat.startWithUser', $u->id) }}" class="list-group-item list-group-item-action bg-black border-secondary text-white chat-user-item" data-name="{{ strtolower($u->first_name . ' ' . $u->last_name) }}" data-email="{{ strtolower($u->email) }}">
                                    <strong>{{ $u->first_name }} {{ $u->last_name }}</strong>
                                    <span class="text-secondary small d-block">{{ $u->email }}</span>
                                </a>
                            @endforeach
                            @if($clients->isEmpty())
                                <p class="text-secondary small">Nessun cliente.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table-row-chat { cursor: pointer; }
.table-row-chat:hover { background-color: rgba(255,255,255,0.05); }
.chat-user-item.hide-by-search { display: none !important; }
</style>
@endpush

@push('chat-scripts')
@include('partials.chat-scripts')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table-row-chat[data-href]').forEach(function(row) {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
        row.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = this.dataset.href;
            }
        });
    });

    var searchInput = document.getElementById('chatSearchUser');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.trim().toLowerCase();
            document.querySelectorAll('#nuovaChatModal .chat-user-item').forEach(function(el) {
                var name = el.getAttribute('data-name') || '';
                var email = el.getAttribute('data-email') || '';
                if (!q || name.indexOf(q) !== -1 || email.indexOf(q) !== -1) {
                    el.classList.remove('hide-by-search');
                } else {
                    el.classList.add('hide-by-search');
                }
            });
        });
    }
});
</script>
@endpush
@endsection
