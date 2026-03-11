@extends('layouts.layout')
@section('title', 'Corsi' . " | " . config("app.name"))
@section('content')
    <x-hero 
        imagePath="images/corsi/" 
        imageName="corsi-img-2"
        title="Iscriviti ai nostri corsi!" 
        subtitle="Tu metti il tuo tempo, noi lo trasformiamo in benessere."
    />

    <section id="section-cards" class="py-5" style="background-color: rgb(87, 86, 86);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-warning">I NOSTRI CORSI</h2>
            </div>
            
            <div class="row g-4 justify-content-center">
                
                {{-- Esempio Card Yoga --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-corso h-100">
                        <h4>Yoga Relax</h4>
                        <p class="orari"><i class="bi bi-clock me-2"></i>Lunedì - 18:00/19:00</p>
                        <p class="descrizione">Lezioni per migliorare flessibilità, respirazione e rilassamento.</p>
                        <p class="costo">€30/mese</p>
                    </div>
                </div>

                {{-- Esempio Card Pilates --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-corso h-100">
                        <h4>Pilates Core</h4>
                        <p class="orari"><i class="bi bi-clock me-2"></i>Martedì - 19:00/20:00</p>
                        <p class="descrizione">Allenamento del core e della postura, ideale per tonificare.</p>
                        <p class="costo">€35/mese</p>
                    </div>
                </div>

                {{-- Esempio Card CrossFit --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-corso h-100">
                        <h4>CrossFit Power</h4>
                        <p class="orari"><i class="bi bi-clock me-2"></i>Mercoledì - 20:00/21:00</p>
                        <p class="descrizione">Allenamenti intensi a circuito per forza e resistenza.</p>
                        <p class="costo">€45/mese</p>
                    </div>
                </div>

                {{-- Esempio Card Zumba --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-corso h-100">
                        <h4>Zumba Energy</h4>
                        <p class="orari"><i class="bi bi-clock me-2"></i>Giovedì - 18:30/19:30</p>
                        <p class="descrizione">Lezioni di ballo fitness a ritmo di musica latina.</p>
                        <p class="costo">€30/mese</p>
                    </div>
                </div>

                {{-- Esempio Card Functional --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-corso h-100">
                        <h4>Functional Training</h4>
                        <p class="orari"><i class="bi bi-clock me-2"></i>Venerdì - 19:30/20:30</p>
                        <p class="descrizione">Esercizi funzionali per migliorare forza, mobilità e resistenza.</p>
                        <p class="costo">€40/mese</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection