@extends('layouts.layout')
@section('title', 'Dettaglio corso: ' . $course->name . " | " . config("app.name"))
@section('content')
@php
    $occStr = $occurrenceDate->format('Y-m-d');
@endphp
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white fw-bold text-uppercase mb-0 h4">Dettaglio corso: {{ $course->name }}</h1>
            </div>

            <div class="card bg-dark border-primary text-white shadow-lg mb-4">
                <div class="card-header border-primary bg-black p-4">
                    <h3 class="mb-0 text-primary h4">Dettaglio corso</h3>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Nome corso</label>
                            <span class="fs-5 fw-bold">{{ $course->name }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Coach</label>
                            <span class="fs-5">{{ $course->coach ? $course->coach->first_name . ' ' . $course->coach->last_name : 'N/D' }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-secondary small text-uppercase fw-bold d-block">Descrizione</label>
                        <div class="bg-black p-3 rounded border border-secondary" style="white-space: pre-wrap; line-height: 1.5;">{{ $course->description ?? '—' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Prezzo</label>
                            <span class="fw-bold text-primary">{{ number_format($course->price, 2) }} €</span>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Giorno lezione</label>
                            <span>{{ $occurrenceDate->locale('it')->isoFormat('dddd D MMMM YYYY') }}</span>
                            @if($course->first_occurrence_date)
                                <span class="badge {{ $course->is_repeatable ? 'bg-info' : 'bg-secondary' }} ms-1">{{ $course->is_repeatable ? 'Ripetibile' : 'Singolo' }}</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Orario</label>
                            <span>{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block">Posti (questa data)</label>
                        <span>{{ $spotsLeft }} su {{ $course->capacity }}</span>
                    </div>

                    <div class="mt-3">
                        <label class="text-secondary small text-uppercase fw-bold d-block">Prenotazioni chiudono alle</label>
                        <span>{{ $course->getBookingDeadlineAtForOccurrenceDate($occurrenceDate)?->format('H:i') ?? '—' }}</span>
                    </div>

                    @if($isEnrolled)
                        <hr class="border-secondary my-4">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('client.messages.startWithCoach', $course->user_id) }}" class="btn btn-info fw-bold text-uppercase">
                                <i class="bi bi-chat-dots me-1"></i> Messaggio al coach
                            </a>
                            @if($course->isCancellationOpenForOccurrenceDate($occurrenceDate))
                                <form action="{{ route('client.cancel', $course->id) }}" method="POST" onsubmit="return confirm('Vuoi davvero annullare la prenotazione per questa data?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="occurrence_date" value="{{ $occStr }}">
                                    <button type="submit" class="btn btn-outline-danger fw-bold text-uppercase">
                                        <i class="bi bi-x-circle me-1"></i> Annulla prenotazione
                                    </button>
                                </form>
                            @else
                                @php
                                    $cancelDeadlineAt = $course->getCancellationDeadlineAtForOccurrenceDate($occurrenceDate);
                                @endphp
                                <button type="button" class="btn btn-secondary fw-bold text-uppercase disabled" title="Annulla disponibile fino alle {{ $cancelDeadlineAt?->format('H:i') ?? '—' }}">
                                    <i class="bi bi-x-circle me-1"></i> Annulla non disponibile
                                </button>
                            @endif
                        </div>
                    @else
                        <hr class="border-secondary my-4">
                        @if($spotsLeft > 0)
                            @if($course->isEnrollmentOpenForOccurrenceDate($occurrenceDate))
                                <form method="POST" action="{{ route('client.enroll', $course->id) }}" class="d-inline">
                                    @csrf
                                    @if($course->first_occurrence_date && ! $course->is_repeatable)
                                    @else
                                        <input type="hidden" name="occurrence_date" value="{{ $occStr }}">
                                    @endif
                                    <button type="submit" class="btn btn-warning w-100 fw-bold text-uppercase">
                                        Prenota ora
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-secondary w-100 fw-bold text-uppercase disabled" title="Le prenotazioni sono chiuse: il corso sta per iniziare">
                                    Prenotazioni chiuse
                                </button>
                            @endif
                        @else
                            <button class="btn btn-secondary w-100 fw-bold text-uppercase disabled">
                                Sold Out
                            </button>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
