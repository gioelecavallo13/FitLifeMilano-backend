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

            {{-- Card Anagrafica Corso --}}
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
                            @if($course->coach)
                                <a href="{{ route('admin.users.show', $course->coach->id) }}?from=course&course_id={{ $course->id }}" class="fs-5 text-primary text-decoration-none link-anagrafica">{{ $course->coach->first_name }} {{ $course->coach->last_name }}</a>
                            @else
                                <span class="fs-5">N/D</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-secondary small text-uppercase fw-bold d-block">Descrizione</label>
                        <div class="bg-black p-3 rounded border border-secondary" style="white-space: pre-wrap; line-height: 1.5;">{{ $course->description ?? '—' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Prezzo</label>
                            <span class="fw-bold text-primary">{{ number_format($course->price, 2) }} €</span>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Data prima occorrenza</label>
                            <span>{{ $course->first_occurrence_date?->format('d/m/Y') ?? $course->day_label ?? 'N/D' }}</span>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Ripetibile</label>
                            <span>{{ $course->is_repeatable ? 'Sì' : 'No' }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Orario</label>
                            <span>{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-secondary small text-uppercase fw-bold d-block">Prenotazioni chiudono alle</label>
                        <span>{{ $course->getBookingDeadlineAt()?->format('H:i') ?? '—' }}</span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Ultima lezione (fine ciclo)</label>
                            <span>{{ $course->last_lesson_date?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Ultimo giorno disdette clienti</label>
                            <span>{{ $course->client_cancellations_close_on?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block">Capacità</label>
                        <span>{{ $course->capacity }} posti</span>
                    </div>
                </div>
                <div class="card-footer border-primary bg-black p-4 d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-warning fw-bold">
                        <i class="bi bi-pencil-square"></i> Modifica
                    </a>
                    <form action="{{ route('admin.courses.destroy') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Sei sicuro di voler eliminare questo corso? Questa azione è irreversibile.')">
                        @csrf
                        <input type="hidden" name="id" value="{{ $course->id }}">
                        <button type="submit" class="btn btn-outline-danger fw-bold">
                            <i class="bi bi-trash"></i> Elimina
                        </button>
                    </form>
                </div>
            </div>

            @if($course->is_repeatable && count($repeatableScheduleRows) > 0)
            <div class="card bg-dark border-info text-white shadow-lg mb-4">
                <div class="card-header border-info bg-black p-4">
                    <h3 class="mb-0 text-info h5">Calendario lezioni (prossime date) — override per singola data</h3>
                    <p class="small text-secondary mb-0 mt-2">Gli orari mostrati sono quelli effettivi (default corso o eccezione). Clicca &quot;Modifica&quot; per personalizzare una data.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle small">
                            <thead class="bg-black text-info text-uppercase">
                                <tr>
                                    <th class="ps-4 py-2">Data</th>
                                    <th class="py-2">Inizio – Fine</th>
                                    <th class="py-2">Chiusura prenot.</th>
                                    <th class="py-2">Chiusura annull.</th>
                                    <th class="pe-4 py-2 text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($repeatableScheduleRows as $row)
                                    <tr>
                                        <td class="ps-4 py-2">
                                            {{ $row['day']->locale('it')->isoFormat('dddd D MMM YYYY') }}
                                            @if($row['has_override'])
                                                <span class="badge bg-warning text-dark ms-1">modificato</span>
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            {{ \Carbon\Carbon::parse($row['start'])->format('H:i') }} – {{ \Carbon\Carbon::parse($row['end'])->format('H:i') }}
                                        </td>
                                        <td class="py-2">{{ $row['booking_deadline'] ? \Carbon\Carbon::parse($row['booking_deadline'])->format('H:i') : '—' }}</td>
                                        <td class="py-2">{{ $row['cancel_deadline'] ? \Carbon\Carbon::parse($row['cancel_deadline'])->format('H:i') : '—' }}</td>
                                        <td class="pe-4 py-2 text-end">
                                            <a href="{{ route('admin.courses.occurrence.edit', [$course->id, $row['dateKey']]) }}" class="btn btn-sm btn-outline-info">Modifica</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Card Utenti prenotati (raggruppati per giorno lezione) --}}
            <div class="card bg-dark border-warning text-white shadow-lg">
                <div class="card-header border-warning bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase text-warning">
                        <i class="bi bi-people-fill me-2"></i>Utenti prenotati ({{ $enrollmentsFilteredCount }})
                    </h5>
                    @if($course->is_repeatable && count($lessonDatesForFilter) > 0)
                        <form method="get" action="{{ route('admin.courses.show', $course->id) }}" class="mt-3 mb-0">
                            <label for="filter-occurrence-date" class="form-label small text-secondary mb-1">Filtra per giorno lezione</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <select name="occurrence_date" id="filter-occurrence-date" class="form-select form-select-sm bg-black text-white border-secondary" style="max-width: 280px;" onchange="this.form.submit()">
                                    <option value="" @selected($selectedOccurrenceDate === null)>Tutti i giorni</option>
                                    @foreach($lessonDatesForFilter as $d)
                                        <option value="{{ $d }}" @selected($selectedOccurrenceDate === $d)>
                                            {{ \Carbon\Carbon::parse($d)->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-warning text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">Nome</th>
                                    <th class="py-3">Cognome</th>
                                    <th class="py-3">Email</th>
                                    <th class="py-3">Giorno lezione</th>
                                    <th class="py-3">Data prenotazione</th>
                                    <th class="pe-4 py-3 text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollmentsByDate as $dateKey => $enrollments)
                                    @foreach($enrollments as $enrollment)
                                @php $user = $enrollment->user; @endphp
                                <tr class="table-row-course-user cursor-pointer" data-href="{{ route('admin.users.show', $user->id) }}?from=course&course_id={{ $course->id }}" role="button" tabindex="0">
                                    <td class="ps-4 py-3 fw-bold">{{ $user->first_name }}</td>
                                    <td class="py-3">{{ $user->last_name }}</td>
                                    <td class="py-3">
                                        <a href="mailto:{{ $user->email }}" class="text-warning text-decoration-none" onclick="event.stopPropagation()">{{ $user->email }}</a>
                                    </td>
                                    <td class="py-3 text-info">{{ \Carbon\Carbon::parse($dateKey)->format('d/m/Y') }}</td>
                                    <td class="py-3 text-secondary small">
                                        {{ $enrollment->created_at ? $enrollment->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <form action="{{ route('admin.courses.unenroll', [$course->id, $user->id]) }}" method="POST"
                                              class="m-0 d-flex justify-content-end"
                                              onsubmit="event.stopPropagation(); return confirm('Vuoi annullare la prenotazione di questo utente per il {{ \Carbon\Carbon::parse($dateKey)->format('d/m/Y') }}?')">
                                            @csrf
                                            <input type="hidden" name="occurrence_date" value="{{ $dateKey }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" style="white-space: nowrap" onclick="event.stopPropagation()">
                                                <i class="bi bi-x-circle me-1"></i> Annulla
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                    @endforeach
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
