@extends('layouts.layout')
@section('title', 'Modifica Corso' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-white fw-bold text-uppercase mb-0">Modifica Corso</h1>
            <p class="text-secondary">Stai modificando: <span class="text-primary fw-bold">{{ $course->name }}</span></p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-dark border-warning shadow-lg text-white">
                <div class="card-header bg-warning text-black fw-bold">
                    <i class="bi bi-pencil-square"></i> DETTAGLI DEL CORSO
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Nome Corso --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Nome Corso</label>
                                <input type="text" name="name" class="form-control bg-black text-white border-secondary @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $course->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Coach --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Coach Istruttore</label>
                                <select name="user_id" class="form-select bg-black text-white border-secondary" required>
                                    @foreach($coaches as $coach)
                                        <option value="{{ $coach->id }}" {{ $course->user_id == $coach->id ? 'selected' : '' }}>
                                            {{ $coach->first_name }} {{ $coach->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Prezzo --}}
                            <div class="col-md-3 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Prezzo (€)</label>
                                <input type="number" step="0.01" name="price" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('price', $course->price) }}" required>
                            </div>
                            {{-- Capacità --}}
                            <div class="col-md-3 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Capacità Max</label>
                                <input type="number" name="capacity" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('capacity', $course->capacity) }}" required>
                            </div>
                            {{-- Data prima occorrenza --}}
                            <div class="col-md-3 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Data prima occorrenza</label>
                                <input type="date" name="first_occurrence_date" class="form-control bg-black text-white border-secondary @error('first_occurrence_date') is-invalid @enderror" 
                                       value="{{ old('first_occurrence_date', $course->first_occurrence_date?->format('Y-m-d')) }}" required>
                                @error('first_occurrence_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- Ripetibile --}}
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="hidden" name="is_repeatable" value="0">
                                    <input type="checkbox" name="is_repeatable" id="is_repeatable" class="form-check-input" value="1" 
                                           {{ old('is_repeatable', $course->is_repeatable ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-secondary" for="is_repeatable">
                                        Ripetibile (ogni 7 giorni)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="admin-course-repeatable-only" class="rounded border border-secondary p-3 mb-3">
                            <p class="small text-info mb-2 mb-md-3"><i class="bi bi-info-circle"></i> Solo corsi <strong>ripetibili</strong>. Per orari diversi su una singola data usa l’anagrafica corso dopo il salvataggio.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-secondary text-uppercase fw-bold">Ultima lezione (fine ciclo)</label>
                                    <input type="date" name="last_lesson_date" class="form-control bg-black text-white border-secondary @error('last_lesson_date') is-invalid @enderror"
                                           value="{{ old('last_lesson_date', $course->last_lesson_date?->format('Y-m-d')) }}">
                                    <small class="text-secondary d-block mt-1">Opzionale. Dopo questa data non si possono prenotare nuove occorrenze.</small>
                                    @error('last_lesson_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-secondary text-uppercase fw-bold">Ultimo giorno disdette (clienti)</label>
                                    <input type="date" name="client_cancellations_close_on" class="form-control bg-black text-white border-secondary @error('client_cancellations_close_on') is-invalid @enderror"
                                           value="{{ old('client_cancellations_close_on', $course->client_cancellations_close_on?->format('Y-m-d')) }}">
                                    <small class="text-secondary d-block mt-1">Opzionale. Dopo la fine di questo giorno i clienti non possono più annullare da app.</small>
                                    @error('client_cancellations_close_on') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Orario Inizio --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario Inizio</label>
                                <input type="time" name="start_time" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('start_time', \Carbon\Carbon::parse($course->start_time)->format('H:i')) }}" required>
                            </div>
                            {{-- Orario Fine --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario Fine</label>
                                <input type="time" name="end_time" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('end_time', \Carbon\Carbon::parse($course->end_time)->format('H:i')) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Scadenza prenotazione --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario chiusura prenotazioni</label>
                                <input type="time" name="booking_deadline_time" class="form-control bg-black text-white border-secondary @error('booking_deadline_time') is-invalid @enderror"
                                       value="{{ old('booking_deadline_time', isset($course->booking_deadline_time) ? \Carbon\Carbon::parse($course->booking_deadline_time)->format('H:i') : (isset($course->start_time) ? \Carbon\Carbon::parse($course->start_time)->format('H:i') : '08:30')) }}"
                                       required>
                                @error('booking_deadline_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- Scadenza annullamento --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario chiusura annullamenti</label>
                                <input type="time" name="cancellation_deadline_time" class="form-control bg-black text-white border-secondary @error('cancellation_deadline_time') is-invalid @enderror"
                                       value="{{ old('cancellation_deadline_time', isset($course->cancellation_deadline_time) ? \Carbon\Carbon::parse($course->cancellation_deadline_time)->format('H:i') : (isset($course->start_time) ? \Carbon\Carbon::parse($course->start_time)->format('H:i') : '08:30')) }}"
                                       required>
                                @error('cancellation_deadline_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Descrizione --}}
                        <div class="mb-4">
                            <label class="small text-secondary text-uppercase fw-bold">Descrizione</label>
                            <textarea name="description" rows="4" class="form-control bg-black text-white border-secondary" required>{{ old('description', $course->description) }}</textarea>
                        </div>

                        <hr class="border-secondary mb-4">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold text-uppercase py-2 shadow">
                                <i class="bi bi-check-lg"></i> Salva Modifiche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function toggleRepeatableOnlyFields() {
        var cb = document.getElementById('is_repeatable');
        var box = document.getElementById('admin-course-repeatable-only');
        if (!cb || !box) return;
        var show = cb.checked;
        box.style.display = show ? '' : 'none';
        box.querySelectorAll('input').forEach(function(el) {
            if (show) { el.removeAttribute('disabled'); } else { el.setAttribute('disabled', 'disabled'); }
        });
    }
    var isRep = document.getElementById('is_repeatable');
    if (isRep) {
        isRep.addEventListener('change', toggleRepeatableOnlyFields);
        toggleRepeatableOnlyFields();
    }
});
</script>
@endpush
@endsection