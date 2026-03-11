@extends('layouts.layout')
@section('title', 'Dettaglio corso: ' . $course->name . " | " . config("app.name"))
@section('content')
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
                            <label class="text-secondary small text-uppercase fw-bold d-block">Giorno</label>
                            @php
                                $giorni = ['Monday' => 'Lunedì', 'Tuesday' => 'Martedì', 'Wednesday' => 'Mercoledì', 'Thursday' => 'Giovedì', 'Friday' => 'Venerdì', 'Saturday' => 'Sabato', 'Sunday' => 'Domenica'];
                            @endphp
                            <span>{{ $giorni[$course->day_of_week] ?? $course->day_of_week }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Orario</label>
                            <span>{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block">Posti</label>
                        <span>{{ $course->capacity - $course->users_count }} su {{ $course->capacity }}</span>
                    </div>

                    @if($isEnrolled)
                        <hr class="border-secondary my-4">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('client.messages.startWithCoach', $course->user_id) }}" class="btn btn-info fw-bold text-uppercase">
                                <i class="bi bi-chat-dots me-1"></i> Messaggio al coach
                            </a>
                            <form action="{{ route('client.cancel', $course->id) }}" method="POST" onsubmit="return confirm('Vuoi davvero annullare la prenotazione?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger fw-bold text-uppercase">
                                    <i class="bi bi-x-circle me-1"></i> Annulla prenotazione
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
