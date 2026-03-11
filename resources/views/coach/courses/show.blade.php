@extends('layouts.layout')
@section('title', 'Anagrafica corso: ' . $course->name . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white fw-bold text-uppercase mb-0 h4">Anagrafica corso: {{ $course->name }}</h1>
            </div>

            {{-- Card Dettaglio corso --}}
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
                        <label class="text-secondary small text-uppercase fw-bold d-block">Capacità</label>
                        <span>{{ $course->capacity }} posti</span>
                    </div>
                </div>
            </div>

            {{-- Card Utenti prenotati --}}
            <div class="card bg-dark border-warning text-white shadow-lg">
                <div class="card-header border-warning bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase text-warning">
                        <i class="bi bi-people-fill me-2"></i>Utenti prenotati ({{ $course->users->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-warning text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 50px;"></th>
                                    <th class="py-3">Nome</th>
                                    <th class="py-3">Cognome</th>
                                    <th class="py-3">Email</th>
                                    <th class="py-3">Data prenotazione</th>
                                    <th class="pe-4 text-end py-3">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($course->users as $user)
                                <tr class="table-row-course-user cursor-pointer" data-href="{{ route('coach.clients.show', $user->id) }}?from=course&course_id={{ $course->id }}" role="button" tabindex="0">
                                    <td class="ps-4 py-2">
                                        <img src="{{ $user->profile_photo_url_small }}" alt="" class="rounded-circle object-fit-cover" width="36" height="36" style="object-fit: cover;">
                                    </td>
                                    <td class="py-3 fw-bold">{{ $user->first_name }}</td>
                                    <td class="py-3">{{ $user->last_name }}</td>
                                    <td class="py-3">
                                        <a href="mailto:{{ $user->email }}" class="text-warning text-decoration-none" onclick="event.stopPropagation()">{{ $user->email }}</a>
                                    </td>
                                    <td class="py-3 text-secondary small">
                                        {{ $user->pivot->created_at ? $user->pivot->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="pe-4 py-3 text-end" onclick="event.stopPropagation()">
                                        <a href="{{ route('coach.messages.startWithClient', $user->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-chat-dots me-1"></i> Messaggio</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary italic">
                                        Nessun utente prenotato per questo corso.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table-row-course-user { cursor: pointer; }
.table-row-course-user:hover { background-color: rgba(255,255,255,0.05); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table-row-course-user[data-href]').forEach(function(row) {
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
