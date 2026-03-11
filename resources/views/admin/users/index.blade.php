@extends('layouts.layout')
@section('title', 'Gestione utenti' . " | " . config("app.name"))

@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold text-uppercase">Gestione Utenti</h2>
    </div>

    {{-- Sezione Filtri --}}
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control bg-black text-white border-secondary" placeholder="Cerca nome, cognome o email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select bg-black text-white border-secondary">
                        <option value="">Tutti i ruoli</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="coach" {{ request('role') == 'coach' ? 'selected' : '' }}>Coach</option>
                        <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Cliente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-light w-100">Filtra</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabella Utenti --}}
    <div class="table-responsive shadow">
        <table class="table table-dark table-hover border-secondary align-middle">
            <thead class="table-black text-secondary">
                <tr>
                    <th class="ps-4 py-3" style="width: 50px;"></th>
                    <th class="py-3">NOME</th>
                    <th class="py-3">COGNOME</th>
                    <th class="py-3">EMAIL</th>
                    <th class="py-3">RUOLO</th>
                    <th class="py-3 pe-4">DATA REGISTRAZIONE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="table-row-chat cursor-pointer" data-href="{{ route('admin.users.show', $user->id) }}" role="button" tabindex="0">
                    <td class="ps-4 py-2">
                        <img src="{{ $user->profile_photo_url_small }}" alt="" class="rounded-circle object-fit-cover" width="36" height="36" style="object-fit: cover;">
                    </td>
                    <td class="py-3 fw-bold">{{ $user->first_name }}</td>
                    <td class="py-3">{{ $user->last_name }}</td>
                    <td class="py-3 text-info">{{ $user->email }}</td>
                    <td class="py-3">
                        <span class="badge {{ $user->role == 'admin' ? 'bg-danger' : ($user->role == 'coach' ? 'bg-info text-dark' : 'bg-secondary') }}">
                            {{ strtoupper($user->role) }}
                        </span>
                    </td>
                    <td class="py-3 pe-4 small">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-secondary">Nessun utente trovato con questi criteri.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

@push('styles')
<style>
.table-row-chat { cursor: pointer; }
.table-row-chat:hover { background-color: rgba(255,255,255,0.05); }
</style>
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
});
</script>
@endpush
@endsection