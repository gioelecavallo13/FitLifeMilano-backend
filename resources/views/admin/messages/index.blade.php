@extends('layouts.layout')
@section('title', 'Messaggi ricevuti' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white fw-bold text-uppercase">Messaggi Ricevuti</h1>
    </div>

    {{-- BARRA DEI FILTRI --}}
    <div class="card bg-dark border-secondary mb-4 shadow">
        <div class="card-body">
            <form action="{{ route('admin.messages.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="text-secondary small">Filtra per Email</label>
                    <input type="text" name="email" class="form-control bg-black text-white border-secondary" 
                           placeholder="esempio@mail.com" value="{{ request('email') }}">
                </div>
                <div class="col-md-4">
                    <label class="text-secondary small">Stato Messaggio</label>
                    <select name="status" class="form-select bg-black text-white border-secondary">
                        <option value="">Tutti gli stati</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Nuovi messaggi</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Letti</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Risposti</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-filter"></i> APPLICA FILTRI
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABELLA MESSAGGI --}}
    <div class="card bg-dark border-warning shadow-lg text-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-black text-warning">
                        <tr>
                            <th class="ps-4 py-3">Stato</th>
                            <th class="py-3">Data</th>
                            <th class="py-3">Utente / Email</th>
                            <th class="py-3 pe-4">Oggetto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $item)
                        <tr class="table-row-chat cursor-pointer" data-href="{{ route('admin.messages.show', $item->id) }}" role="button" tabindex="0">
                            <td class="ps-4 py-3">
                                @if($item->status == 'new')
                                    <span class="badge bg-danger rounded-pill px-3">Nuovo</span>
                                @elseif($item->status == 'read')
                                    <span class="badge bg-secondary rounded-pill px-3">Letto</span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3">Risposto</span>
                                @endif
                            </td>
                            <td class="py-3">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3">
                                <div class="fw-bold">{{ $item->first_name }} {{ $item->last_name }}</div>
                                <div class="small text-secondary">{{ $item->email }}</div>
                            </td>
                            <td class="py-3 pe-4">
                                <span class="text-warning small text-uppercase fw-bold">{{ $item->subject }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-secondary italic">
                                Nessun messaggio trovato con i criteri selezionati.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $requests->links() }}
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