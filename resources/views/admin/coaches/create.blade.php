@extends('layouts.layout')

@section('title', 'Gestione Coach | ' . config('app.name'))

@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white fw-bold text-uppercase mb-0">Gestione Staff Coach</h1>
    </div>

    <div class="row g-4">
        {{-- COLONNA SINISTRA: FORM DI REGISTRAZIONE --}}
        <div class="col-lg-4">
            <div class="card bg-dark text-white border-light shadow-lg">
                <div class="card-header border-light bg-black p-3 text-center">
                    <h5 class="mb-0 fw-bold text-uppercase text-info">Registra Nuovo Coach</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.coaches.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Nome</label>
                                <input type="text" name="first_name" class="form-control bg-black text-white border-secondary @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Cognome</label>
                                <input type="text" name="last_name" class="form-control bg-black text-white border-secondary @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-secondary fw-bold text-uppercase">Email Professionale</label>
                            <input type="email" name="email" class="form-control bg-black text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-secondary fw-bold text-uppercase">Password Temp.</label>
                            <input type="password" name="password" class="form-control bg-black text-white border-secondary @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-secondary fw-bold text-uppercase">Conferma Password</label>
                            <input type="password" name="password_confirmation" class="form-control bg-black text-white border-secondary" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-info fw-bold py-2 text-uppercase">
                                <i class="bi bi-person-plus-fill"></i> Registra Coach
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- COLONNA DESTRA: LISTA COACH REGISTRATI --}}
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg text-white">
                <div class="card-header border-secondary bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase text-center">Anagrafica Staff</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-info text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 50px;"></th>
                                    <th class="py-3">Coach</th>
                                    <th class="py-3">Email</th>
                                    <th class="py-3 pe-4">Data Reg.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($coaches)
                                    @forelse($coaches as $coach)
                                    <tr class="table-row-chat cursor-pointer" data-href="{{ route('admin.users.show', $coach->id) }}?from=coach" role="button" tabindex="0">
                                        <td class="ps-4 py-2">
                                            <img src="{{ $coach->profile_photo_url_small }}" alt="" class="rounded-circle object-fit-cover" width="36" height="36" style="object-fit: cover;">
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold">{{ $coach->first_name }} {{ $coach->last_name }}</div>
                                            <span class="badge bg-outline-info border border-info text-info" style="font-size: 0.65rem;">COACH</span>
                                        </td>
                                        <td class="py-3">{{ $coach->email }}</td>
                                        <td class="py-3 pe-4 small text-secondary">
                                            {{ $coach->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-secondary italic">
                                            Nessun coach presente nel database.
                                        </td>
                                    </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-warning">
                                            Caricamento lista... (Verifica variabile $coaches nel controller)
                                        </td>
                                    </tr>
                                @endisset
                            </tbody>
                        </table>
                    </div>
                    @if($coaches->hasPages())
                        <div class="d-flex justify-content-center p-3">
                            {{ $coaches->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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