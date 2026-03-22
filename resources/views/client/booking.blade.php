@extends('layouts.layout')

@section('title', 'Prenota Corsi' . " | " . config("app.name"))

@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    {{-- Header della pagina --}}
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <h1 class="text-white fw-bold mb-3 text-uppercase">Prenota la tua sessione</h1>
            <p class="text-secondary mb-0">
                Esplora le nostre attività e prenota il tuo posto. La disponibilità è limitata!
            </p>
        </div>
    </div>

    {{-- Griglia delle Card dei Corsi --}}
    <div class="row g-4">
        @isset($courses)
            @forelse($courses as $course)
                @php
                    $next = $course->getNextOccurrence();
                    $defaultDate = $next?->format('Y-m-d');
                    $fromDay = now()->startOfDay();
                    $toDay = now()->copy()->addMonths(3)->startOfDay();
                    if ($course->last_lesson_date && $course->last_lesson_date->copy()->startOfDay()->lt($toDay)) {
                        $toDay = $course->last_lesson_date->copy()->startOfDay();
                    }
                    $pickableDates = $course->occurrenceDatesBetween($fromDay, $toDay);
                    $meta = $occurrenceMeta[$course->id] ?? [];
                    $bookedDates = collect($bookedByCourse->get($course->id, []));
                    $initialOccurrenceKey = $defaultDate ?: (collect($pickableDates)->first()?->format('Y-m-d'));
                    $initialMetaRow = ($initialOccurrenceKey && isset($meta[$initialOccurrenceKey])) ? $meta[$initialOccurrenceKey] : [];
                    $initialLessonRange = (isset($initialMetaRow['lesson_start'], $initialMetaRow['lesson_end']))
                        ? $initialMetaRow['lesson_start'].' - '.$initialMetaRow['lesson_end']
                        : '—';
                    $initialCancelDeadline = $initialMetaRow['cancel_deadline'] ?? '—';
                    $initialBookingDeadline = $initialMetaRow['booking_deadline'] ?? '—';
                    $initialSpots = $initialMetaRow['spots_left'] ?? null;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card bg-dark border-secondary h-100 text-white shadow-lg course-card"
                         data-href="{{ route('client.courses.show', $course->id) }}?from=booking{{ $defaultDate ? '&occurrence_date='.$defaultDate : '' }}"
                         tabindex="0"
                         data-course-id="{{ $course->id }}"
                         data-occurrence-meta='@json($meta)'
                         data-default-date="{{ $course->first_occurrence_date && ! $course->is_repeatable ? $course->first_occurrence_date->format('Y-m-d') : ($defaultDate ?? '') }}"
                         data-booked-dates='@json($bookedDates->values()->all())'
                         data-repeatable="{{ $course->is_repeatable ? '1' : '0' }}"
                         data-has-first="{{ $course->first_occurrence_date ? '1' : '0' }}">
                        {{-- Badge con data/occorrenza --}}
                        <div class="card-header border-secondary bg-black d-flex justify-content-between align-items-center">
                            <span class="badge bg-warning text-dark fw-bold text-uppercase">
                                @if($course->first_occurrence_date)
                                    @if($course->is_repeatable)
                                        <span class="course-card-occurrence-badge-date">{{ $defaultDate ? \Carbon\Carbon::parse($defaultDate)->locale('it')->isoFormat('D MMM YYYY') : $course->first_occurrence_date->format('d M Y') }}</span>
                                        <span class="small text-secondary">(ogni settimana)</span>
                                    @else
                                        {{ $course->first_occurrence_date->format('d M Y') }}
                                    @endif
                                @else
                                    {{ $course->day_label }}
                                @endif
                            </span>
                            <span class="small text-secondary">
                                <i class="bi bi-clock"></i> <span class="course-card-lesson-time-range">{{ $initialLessonRange }}</span>
                            </span>
                        </div>

                        <div class="card-body">
                            <h4 class="card-title fw-bold text-warning mb-2">{{ $course->name }}</h4>
                            <p class="card-text text-secondary small mb-3">
                                {{ Str::limit($course->description, 100) }}
                            </p>

                            @if($course->is_repeatable || ! $course->first_occurrence_date)
                                <div class="mb-3">
                                    <label class="form-label small text-secondary mb-1">Giorno della lezione</label>
                                    @if($course->last_lesson_date)
                                        <p class="small text-secondary mb-0">Ciclo prenotabile fino al {{ $course->last_lesson_date->locale('it')->isoFormat('D MMMM YYYY') }}.</p>
                                    @endif
                                    <select class="form-select form-select-sm bg-black text-white border-secondary occurrence-date-select" name="occurrence_date">
                                        @forelse($pickableDates as $d)
                                            <option value="{{ $d->format('Y-m-d') }}" data-badge-text="{{ $d->locale('it')->isoFormat('D MMM YYYY') }}" @selected($defaultDate === $d->format('Y-m-d'))>
                                                {{ $d->locale('it')->isoFormat('dddd D MMMM YYYY') }}
                                            </option>
                                        @empty
                                            <option value="">—</option>
                                        @endforelse
                                    </select>
                                </div>
                            @endif

                            <ul class="list-unstyled mb-4 occurrence-info" data-course-card="{{ $course->id }}">
                                <li class="mb-2 small">
                                    <i class="bi bi-person-badge text-info me-2"></i> Coach:
                                    <span class="text-white">{{ $course->coach->first_name ?? 'N/D' }} {{ $course->coach->last_name ?? '' }}</span>
                                </li>
                                <li class="mb-2 small">
                                    <i class="bi bi-people text-warning me-2"></i> Posti:
                                    <span class="text-white fw-bold spots-left-display">{{ $initialSpots !== null ? $initialSpots : '—' }}</span> su {{ $course->capacity }}
                                </li>
                                <li class="small">
                                    <i class="bi bi-stopwatch text-info me-2"></i>
                                    Prenotazioni chiudono alle:
                                    <span class="text-white fw-bold ms-1 booking-deadline-display">{{ $initialBookingDeadline }}</span>
                                </li>
                                <li class="small">
                                    <i class="bi bi-slash-circle text-danger me-2"></i>
                                    Annullamenti chiudono alle:
                                    <span class="text-white fw-bold ms-1 cancel-deadline-display">{{ $initialCancelDeadline }}</span>
                                </li>
                                <li class="small">
                                    <i class="bi bi-tag text-success me-2"></i> Prezzo:
                                    <span class="text-white">{{ number_format($course->price, 2) }} €</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer border-top-0 bg-transparent pb-4 px-4">
                            {{-- Stato bottoni/posti: solo renderActions() in JS (stesso meta di data-occurrence-meta) --}}
                            <div class="booking-card-actions"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-emoji-frown display-1 text-secondary"></i>
                    <h3 class="text-white mt-3">Nessun corso disponibile al momento.</h3>
                    <p class="text-secondary">Torna a trovarci presto!</p>
                </div>
            @endforelse
        @else
            <div class="col-12 text-center">
                <p class="text-warning">Errore nel caricamento dei dati: variabile $courses non definita.</p>
            </div>
        @endisset
    </div>
    @isset($courses)
        @if($courses->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $courses->links() }}
            </div>
        @endif
    @endisset
</div>
@endsection

@push('styles')
<style>
.course-card {
    cursor: pointer;
    transition: transform 0.08s ease, box-shadow 0.12s ease, background-color 0.12s ease;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.2rem rgba(0, 0, 0, 0.8);
    background-color: #181818;
}

.course-card:focus-visible {
    outline: 2px solid #f5c542;
    outline-offset: 2px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function parseJsonSafe(s, label) {
        try {
            return JSON.parse(s || '{}');
        } catch (e) {
            if (typeof console !== 'undefined' && console.warn) {
                console.warn('Prenota corsi: JSON non valido' + (label ? ' (' + label + ')' : ''), e);
            }
            return {};
        }
    }

    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    document.querySelectorAll('.course-card[data-course-id]').forEach(function (card) {
        var meta = parseJsonSafe(card.getAttribute('data-occurrence-meta'), 'occurrence-meta');
        var bookedDates = parseJsonSafe(card.getAttribute('data-booked-dates'), 'booked-dates');
        if (!Array.isArray(bookedDates)) bookedDates = [];
        var repeatable = card.getAttribute('data-repeatable') === '1';
        var hasFirst = card.getAttribute('data-has-first') === '1';
        var courseId = card.getAttribute('data-course-id');

        function selectedKey() {
            var sel = card.querySelector('.occurrence-date-select');
            if (sel) return sel.value;
            return card.getAttribute('data-default-date') || '';
        }

        function renderActions() {
            var key = selectedKey();
            var m = meta[key] || {};
            var isBooked = bookedDates.indexOf(key) !== -1;
            var actions = card.querySelector('.booking-card-actions');
            if (!actions) return;

            var spotsEl = card.querySelector('.spots-left-display');
            var bdEl = card.querySelector('.booking-deadline-display');
            var cdEl = card.querySelector('.cancel-deadline-display');
            var timeEl = card.querySelector('.course-card-lesson-time-range');
            if (spotsEl) spotsEl.textContent = (m.spots_left !== undefined) ? m.spots_left : '—';
            if (bdEl) bdEl.textContent = m.booking_deadline || '—';
            if (cdEl) cdEl.textContent = m.cancel_deadline || '—';
            if (timeEl) {
                var ls = m.lesson_start;
                var le = m.lesson_end;
                timeEl.textContent = (ls && le) ? (ls + ' - ' + le) : '—';
            }

            var selForBadge = card.querySelector('.occurrence-date-select');
            if (repeatable && selForBadge) {
                var badgeDateEl = card.querySelector('.course-card-occurrence-badge-date');
                var opt = selForBadge.options[selForBadge.selectedIndex];
                var badgeText = opt && opt.getAttribute('data-badge-text');
                if (badgeDateEl && badgeText) {
                    badgeDateEl.textContent = badgeText;
                }
            }

            var showUrl = '{{ url('/client/corsi') }}/' + courseId + '?from=booking&occurrence_date=' + encodeURIComponent(key);
            var needOccInput = (repeatable || !hasFirst);

            if (isBooked) {
                var cancelHtml = '';
                if (m.cancellation_open) {
                    cancelHtml = '<form action="{{ url('/client/corsi') }}/' + courseId + '/annulla" method="POST" onsubmit="return confirm(\'Vuoi davvero annullare la prenotazione per questa data?\')">' +
                        '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '<input type="hidden" name="occurrence_date" value="' + key + '">' +
                        '<button type="submit" class="btn btn-outline-danger w-100 fw-bold text-uppercase">Annulla</button></form>';
                } else {
                    cancelHtml = '<button type="button" class="btn btn-secondary w-100 fw-bold text-uppercase disabled">Annulla non disponibile</button>';
                }
                actions.innerHTML = '<div class="d-flex flex-column gap-2">' +
                    '<a href="' + showUrl + '" class="btn btn-warning w-100 fw-bold text-uppercase">Anagrafica corso</a>' + cancelHtml + '</div>';
            } else if ((m.spots_left !== undefined ? m.spots_left : 0) > 0) {
                if (m.enrollment_open) {
                    var hiddenOcc = '';
                    if (needOccInput) {
                        hiddenOcc = '<input type="hidden" name="occurrence_date" value="' + key + '">';
                    }
                    actions.innerHTML = '<form action="{{ url('/client/corsi') }}/' + courseId + '/prenota" method="POST">' +
                        '<input type="hidden" name="_token" value="' + csrfToken + '">' + hiddenOcc +
                        '<button type="submit" class="btn btn-warning w-100 fw-bold text-uppercase">Prenota Ora</button></form>';
                } else {
                    actions.innerHTML = '<button type="button" class="btn btn-secondary w-100 fw-bold text-uppercase disabled">Prenotazioni chiuse</button>';
                }
            } else {
                actions.innerHTML = '<button class="btn btn-secondary w-100 fw-bold text-uppercase disabled">Sold Out</button>';
            }
        }

        renderActions();

        var sel = card.querySelector('.occurrence-date-select');
        if (sel) {
            sel.addEventListener('change', function () {
                card.setAttribute('data-default-date', sel.value);
                card.setAttribute('data-href', '{{ url('/client/corsi') }}/' + courseId + '?from=booking&occurrence_date=' + encodeURIComponent(sel.value));
                renderActions();
            });
        }
    });

    const cards = document.querySelectorAll('.course-card[data-href]');

    cards.forEach(function (card) {
        const navigate = function () {
            const href = card.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        };

        card.addEventListener('click', function (e) {
            if (e.target.closest('button, a, form, input, select, label, option')) {
                return;
            }
            navigate();
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.closest('button, a, form, input, select, label, option')) {
                    return;
                }
                e.preventDefault();
                navigate();
            }
        });
    });
});
</script>
@endpush
