@extends('layouts.layout')
@section('title', 'Gestione Corsi' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white fw-bold text-uppercase">Gestione Corsi Fitness</h1>
    </div>

    <div class="row g-4">
        {{-- SEZIONE A SINISTRA: FORM INSERIMENTO --}}
        <div class="col-lg-4">
            <div class="card bg-dark border-primary shadow-lg text-white">
                <div class="card-header bg-primary text-black fw-bold">
                    <i class="bi bi-plus-circle-fill"></i> AGGIUNGI NUOVO CORSO
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small text-secondary text-uppercase fw-bold">Nome Corso</label>
                            <input type="text" name="name" class="form-control bg-black text-white border-secondary" placeholder="es. Yoga Flow" required>
                        </div>

                        <div class="mb-3">
                            <label class="small text-secondary text-uppercase fw-bold">Coach Istruttore</label>
                            <select name="user_id" class="form-select bg-black text-white border-secondary" required>
                                <option value="">Seleziona un Coach</option>
                                @foreach($coaches as $coach)
                                    <option value="{{ $coach->id }}">{{ $coach->first_name }} {{ $coach->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Prezzo (€)</label>
                                <input type="number" step="0.01" name="price" class="form-control bg-black text-white border-secondary" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Capacità</label>
                                <input type="number" name="capacity" class="form-control bg-black text-white border-secondary" placeholder="es. 15" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small text-secondary text-uppercase fw-bold">Giorno</label>
                            <select name="day_of_week" class="form-select bg-black text-white border-secondary" required>
                                <option value="Monday">Lunedì</option>
                                <option value="Tuesday">Martedì</option>
                                <option value="Wednesday">Mercoledì</option>
                                <option value="Thursday">Giovedì</option>
                                <option value="Friday">Venerdì</option>
                                <option value="Saturday">Sabato</option>
                                <option value="Sunday">Domenica</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Inizio</label>
                                <input type="time" name="start_time" class="form-control bg-black text-white border-secondary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Fine</label>
                                <input type="time" name="end_time" class="form-control bg-black text-white border-secondary" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small text-secondary text-uppercase fw-bold">Descrizione</label>
                            <textarea name="description" rows="3" class="form-control bg-black text-white border-secondary" placeholder="Descrivi il corso..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow">
                            <i class="bi bi-save"></i> SALVA CORSO
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- SEZIONE A DESTRA: TABELLA VISUALIZZAZIONE --}}
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg text-white">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-primary">
                                <tr>
                                    <th class="ps-4 py-3">Corso</th>
                                    <th class="py-3">Coach</th>
                                    <th class="py-3">Orario</th>
                                    <th class="py-3">Prezzo</th>
                                    <th class="py-3 pe-4 text-center">Iscritti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                <tr class="table-row-chat cursor-pointer" data-href="{{ route('admin.courses.show', $course->id) }}" role="button" tabindex="0">
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-uppercase">{{ $course->name }}</div>
                                        <div class="small text-secondary">Max {{ $course->capacity }} persone</div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-outline-info border border-info text-info">
                                            {{ $course->coach->first_name ?? 'N/D' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="small">{{ $course->day_of_week }}</div>
                                        <div class="fw-bold text-primary">{{ $course->start_time }} - {{ $course->end_time }}</div>
                                    </td>
                                    <td class="py-3">{{ number_format($course->price, 2) }}€</td>
                                    <td class="py-3 pe-4 text-center">{{ $course->users_count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-secondary italic">
                                        Nessun corso creato finora.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($courses->hasPages())
                        <div class="d-flex justify-content-center p-3">
                            {{ $courses->links() }}
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