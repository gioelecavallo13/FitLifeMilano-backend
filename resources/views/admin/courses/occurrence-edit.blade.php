@extends('layouts.layout')
@section('title', 'Modifica lezione ' . $day->format('d/m/Y') . ' | ' . config('app.name'))
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-breadcrumb :items="$breadcrumb" />
            <h1 class="text-white fw-bold text-uppercase h4 mb-2">Lezione del {{ $day->locale('it')->isoFormat('dddd D MMMM YYYY') }}</h1>
            <p class="text-secondary small mb-4">Corso: <span class="text-white">{{ $course->name }}</span>. Modifica solo se questa data ha orari o scadenze diverse dal <strong>default del corso</strong>. Valori uguali al default non vengono salvati come eccezione.</p>

            <div class="card bg-dark border-warning text-white shadow-lg">
                <div class="card-header bg-warning text-black fw-bold">
                    <i class="bi bi-calendar-event"></i> Orari e scadenze (questa occorrenza)
                </div>
                <div class="card-body p-4">
                    <p class="small text-secondary mb-3">Default corso: inizio {{ $defaults['start_time'] }}, fine {{ $defaults['end_time'] }}, chiusura prenotazioni {{ $defaults['booking_deadline_time'] }}, chiusura annullamenti {{ $defaults['cancellation_deadline_time'] }}.</p>

                    <form action="{{ route('admin.courses.occurrence.update', [$course->id, $day->format('Y-m-d')]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Inizio lezione</label>
                                <input type="time" name="start_time" class="form-control bg-black text-white border-secondary @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time', $effective['start_time']) }}" required>
                                @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Fine lezione</label>
                                <input type="time" name="end_time" class="form-control bg-black text-white border-secondary @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time', $effective['end_time']) }}" required>
                                @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Chiusura prenotazioni (stesso giorno della lezione)</label>
                                <input type="time" name="booking_deadline_time" class="form-control bg-black text-white border-secondary @error('booking_deadline_time') is-invalid @enderror"
                                       value="{{ old('booking_deadline_time', $effective['booking_deadline_time']) }}" required>
                                @error('booking_deadline_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Chiusura annullamenti (stesso giorno della lezione)</label>
                                <input type="time" name="cancellation_deadline_time" class="form-control bg-black text-white border-secondary @error('cancellation_deadline_time') is-invalid @enderror"
                                       value="{{ old('cancellation_deadline_time', $effective['cancellation_deadline_time']) }}" required>
                                @error('cancellation_deadline_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="submit" class="btn btn-warning fw-bold">
                                <i class="bi bi-check-lg"></i> Salva
                            </button>
                            <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-outline-secondary">Annulla</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
