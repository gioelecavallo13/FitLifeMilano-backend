<footer class="text-white pt-5 pb-3" 
        style="background: linear-gradient(rgba(0,0,0,0.95)">
    <div class="container">
        <div class="row gy-4 text-center text-md-start">
            
            <div class="col-12 col-md-4">
                <div class="d-flex flex-column align-items-center align-items-md-start">
                    <picture>
                        <source srcset="{{ asset('images/logo_white.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo_white.png') }}" 
                             alt="FitLife logo" 
                             class="mb-3" 
                             width="90" height="90"
                             style="width: auto; height: 90px; filter: drop-shadow(0 2px 6px #0008);" loading="lazy" decoding="async">
                    </picture>
                    <p class="text-secondary small lh-lg">
                        Il tuo benessere, la nostra missione.<br>
                        Vivi FitLife ogni giorno!
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <h4 class="h6 fw-bold text-warning text-uppercase mb-3" style="letter-spacing: 0.05em;">Link utili</h4>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ config('app.frontend_url') }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-white-50 hover-warning transition-all">Visita il nostro sito</a>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-4">
                <h4 class="h6 fw-bold text-warning text-uppercase mb-3" style="letter-spacing: 0.05em;">Contatti</h4>
                <div class="text-white-50 small mb-3 lh-lg">
                    Via Benessere 10, 20100 Milano<br>
                    Tel: <a href="tel:+390212345678" class="text-decoration-none text-white hover-warning">02 12345678</a><br>
                    Email: <a href="mailto:info@fitlife.it" class="text-decoration-none text-white hover-warning">info@fitlife.it</a>
                </div>
                <div class="d-flex justify-content-center justify-content-md-start gap-3 mt-3">
                    <a href="#" class="text-warning fs-4 social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-warning fs-4 social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-warning fs-4 social-icon"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>

        <div class="row mt-5">
            <div class="col-12 text-center border-top border-secondary pt-3" style="--bs-border-opacity: .2;">
                <p class="text-secondary small mb-0" style="font-size: 0.85rem; letter-spacing: 0.03em;">
                    &copy; {{ date('Y') }} FitLife. Tutti i diritti riservati.
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Transizioni fluide */
    .transition-all {
        transition: all 0.2s ease-in-out;
    }

    /* Hover Links */
    .hover-warning:hover {
        color: #ffcc00 !important;
        text-decoration: underline !important;
    }

    /* Animazione Icone Social */
    .social-icon {
        transition: transform 0.2s ease;
        display: inline-block;
    }
    .social-icon:hover {
        transform: scale(1.2);
        color: #fff !important;
    }
</style>