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
                <div class="col-md-6 col-lg-4">
                    <div class="card bg-dark border-secondary h-100 text-white shadow-lg course-card"
                         data-href="{{ route('client.courses.show', $course->id) }}?from=booking"
                         tabindex="0">
                        {{-- Badge con il Giorno --}}
                        <div class="card-header border-secondary bg-black d-flex justify-content-between align-items-center">
                            <span class="badge bg-warning text-dark fw-bold text-uppercase">
                                {{ $course->day_of_week }}
                            </span>
                            <span class="small text-secondary">
                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}
                            </span>
                        </div>

                        <div class="card-body">
                            <h4 class="card-title fw-bold text-warning mb-2">{{ $course->name }}</h4>
                            <p class="card-text text-secondary small mb-3">
                                {{ Str::limit($course->description, 100) }}
                            </p>
                            
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2 small">
                                    <i class="bi bi-person-badge text-info me-2"></i> Coach: 
                                    <span class="text-white">{{ $course->coach->first_name ?? 'N/D' }} {{ $course->coach->last_name ?? '' }}</span>
                                </li>
                                <li class="mb-2 small">
                                    <i class="bi bi-people text-warning me-2"></i> Posti: 
                                    <span class="text-white">{{ $course->capacity - $course->users_count }} </span> su {{ $course->capacity }}
                                </li>
                                <li class="small">
                                    <i class="bi bi-tag text-success me-2"></i> Prezzo: 
                                    <span class="text-white">{{ number_format($course->price, 2) }} €</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer border-top-0 bg-transparent pb-4 px-4">
                            @if(in_array($course->id, $enrolledCourseIds ?? []))
                                {{-- Cliente già iscritto: Anagrafica corso + Annulla --}}
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('client.courses.show', $course->id) }}?from=booking" class="btn btn-warning w-100 fw-bold text-uppercase">
                                        Anagrafica corso
                                    </a>
                                    <form action="{{ route('client.cancel', $course->id) }}" method="POST" onsubmit="return confirm('Vuoi davvero annullare la prenotazione?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold text-uppercase">
                                            Annulla
                                        </button>
                                    </form>
                                </div>
                            @elseif(($course->capacity - $course->users_count) > 0)
                                {{-- Form di prenotazione --}}
                                <form action="{{ route('client.enroll', $course->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100 fw-bold text-uppercase">
                                        Prenota Ora
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary w-100 fw-bold text-uppercase disabled">
                                    Sold Out
                                </button>
                            @endif
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
    const cards = document.querySelectorAll('.course-card[data-href]');

    cards.forEach(function (card) {
        const navigate = function () {
            const href = card.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        };

        card.addEventListener('click', function (e) {
            if (e.target.closest('button, a, form')) {
                return;
            }
            navigate();
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.closest('button, a, form')) {
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