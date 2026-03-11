@extends('layouts.layout')

@section('title', 'Home' . " | " . config("app.name"))

@push('styles')
<link rel="preload" href="{{ asset('images/index/home-img-2.webp') }}" as="image">
<style>
    /* --- HERO SECTION --- */
    .hero-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background-color: grey;
    margin: 0;
    padding: 0;
}

    .hero-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1;
    }

    .hero-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        width: 100%;
        text-align: center;
    }

    /* --- TESTIMONIALS --- */
    .testimonial-card {
        transition: transform 0.3s ease;
    }

    .testimonial-card:hover {
        transform: translateY(-8px);
    }

    .testimonial-img {
        width: 80px;
        height: 80px;
        border: 3px solid #ffcc00;
        object-fit: cover;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 900px) {
        .hero-container {
            height: 50vh; /* Più bassa su Mobile */
        }
        .display-3 {
            font-size: 2.5rem;
        }
    }

    /* --- STATS SLIDER (TICKER) --- */
.stats-slider-container {
    overflow: hidden;
    white-space: nowrap;
    width: 100%;
}

.stats-track {
    display: flex;
    width: max-content;
    animation: scroll-left 20s linear infinite;
}

/* Duplichiamo il contenuto per l'effetto infinito */
.stats-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 0 60px; /* Spazio tra una statistica e l'altra */
}

@keyframes scroll-left {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); } /* Scorriamo fino a metà (dove inizia la copia) */
}

/* Pausa al passaggio del mouse */
.stats-slider-container:hover .stats-track {
    animation-play-state: paused;
}

#scrollBanner {
    display: flex;
    will-change: left; /* Ottimizza le prestazioni del browser per l'animazione */
    transition: none;  /* Evita conflitti tra JS e transizioni CSS */
}

.stat {
    flex-shrink: 0;    /* Impedisce alle stats di rimpicciolirsi */
    min-width: 250px;  /* Assicura una larghezza minima per il calcolo del centro */
}

/* --- STATS BANNER: desktop --- */
.stats-banner {
    height: 100px;
}

/* --- STATS BANNER: ridimensionamento su mobile (animazione sempre attiva) --- */
@media (max-width: 768px) {
    .stats-banner {
        height: 72px;
        min-height: 72px;
    }
    .stats-banner .stat {
        min-width: 140px;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    .stats-banner .stat .counter {
        font-size: 1.25rem !important;
    }
    .stats-banner .stat .small {
        font-size: 0.65rem !important;
    }
}
</style>
@endpush

@section('content')

<section class="stats stats-banner bg-warning py-3 shadow-sm overflow-hidden" style="position: relative;">
    {{-- Il wrapper deve avere position absolute per essere mosso dal JS via 'left' --}}
    <div id="scrollBanner" class="d-flex align-items-center position-absolute text-dark fw-bold" style="white-space: nowrap; left: 100%;">
        
        <div class="stat px-5 text-center">
            <h2 class="counter fs-2 fw-bold mb-0" data-target="100">0</h2>
            <p class="mb-0 text-uppercase small">Iscritti felici</p>
        </div>

        <div class="stat px-5 text-center">
            <h2 class="counter fs-2 fw-bold mb-0" data-target="2000">0</h2>
            <p class="mb-0 text-uppercase small">Kg sollevati</p>
        </div>

        <div class="stat px-5 text-center">
            <h2 class="counter fs-2 fw-bold mb-0" data-target="5">0</h2>
            <p class="mb-0 text-uppercase small">Corsi disponibili</p>
        </div>

    </div>
</section>

<section class="hero-container">
    <picture>
        <source srcset="{{ asset('images/index/home-img-2.webp') }}" fetchpriority="high" type="image/webp">
        <img src="{{ asset('images/index/home-img-2.jpg') }}" class="hero-img" alt="FitLife Training" width="1920" height="1080" fetchpriority="high" loading="eager">
    </picture>
    
    <div class="hero-overlay"></div>

    <div class="hero-content container px-3">
        <h1 class="display-5 fw-bold text-white mb-3">Benvenuto in FitLife</h1>
        <p class="lead text-white mb-4">Trasforma il tuo corpo. Trasforma la tua vita.</p>
        <a href="{{ url('/corsi') }}" class="btn btn-warning btn-lg fw-bold px-5 shadow">
            Scopri i nostri corsi
        </a>
    </div>
</section>

<section class="testimonials pb-5 pt-0" style="background-color: #333;">
    <div class="container pt-4">
        <h2 class="text-warning text-center display-5 fw-bold mb-5">Cosa dicono di noi</h2>

        <div class="row g-4 justify-content-center">
            <div class="col-8 col-md-6 col-lg-3">
                <div class="testimonial-card card h-100 bg-dark text-white border-0 shadow p-4 text-center">
                    <img src="{{ asset('images/index/home-img-3.jpg') }}" 
                         class="testimonial-img rounded-circle mx-auto mb-3" alt="Luca" width="80" height="80" loading="lazy" decoding="async">
                    <div class="card-body p-0">
                        <p class="card-text fst-italic mb-3">"FitLife ha cambiato il mio modo di vedere il fitness!"</p>
                        <span class="text-warning fw-bold">- Luca</span>
                    </div>
                </div>
            </div>

            <div class="col-8 col-md-6 col-lg-3">
                <div class="testimonial-card card h-100 bg-dark text-white border-0 shadow p-4 text-center">
                    <img src="{{ asset('images/index/home-img-4.jpg') }}" 
                         class="testimonial-img rounded-circle mx-auto mb-3" alt="Martina" width="80" height="80" loading="lazy" decoding="async">
                    <div class="card-body p-0">
                        <p class="card-text fst-italic mb-3">"Le lezioni sono divertenti e motivanti."</p>
                        <span class="text-warning fw-bold">- Martina</span>
                    </div>
                </div>
            </div>

            <div class="col-8 col-md-6 col-lg-3">
                <div class="testimonial-card card h-100 bg-dark text-white border-0 shadow p-4 text-center">
                    <img src="{{ asset('images/index/home-img-5.jpg') }}" 
                         class="testimonial-img rounded-circle mx-auto mb-3" alt="Giorgio" width="80" height="80" loading="lazy" decoding="async">
                    <div class="card-body p-0">
                        <p class="card-text fst-italic mb-3">"Allenarsi qui è diventato un piacere."</p>
                        <span class="text-warning fw-bold">- Giorgio</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script src="{{ asset('js/index.js') }}"></script>
@endpush

@endsection