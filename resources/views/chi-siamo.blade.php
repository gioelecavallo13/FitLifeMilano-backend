@extends('layouts.layout')
@section('title', 'Chi siamo' . " | " . config("app.name"))
@section('content')
    <x-hero 
        imagePath="images/chi-siamo/" 
        imageName="chi_siamo-img-7"
        title="Conosciamoci meglio!" 
        subtitle="I nostri valori e il nostro staff."
    />
    
    <section class="py-5" id="valori">
        <div class="container">
            <h1 class="display-5 fw-bold text-center mb-5 text-warning">I nostri valori</h1>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <ul class="list-group list-group-flush custom-list">
                        <li class="list-group-item bg-transparent text-white border-0 ps-4 mb-3">
                            <strong>Benessere prima di tutto:</strong> non puntiamo solo all'estetica, ma a farti stare bene dentro e fuori.
                        </li>
                        <li class="list-group-item bg-transparent text-white border-0 ps-4 mb-3">
                            <strong>Professionalità e passione:</strong> ogni istruttore è qualificato e motivato.
                        </li>
                        <li class="list-group-item bg-transparent text-white border-0 ps-4 mb-3">
                            <strong>Comunità e supporto:</strong> qui sei parte di una famiglia.
                        </li>
                        <li class="list-group-item bg-transparent text-white border-0 ps-4 mb-3">
                            <strong>Innovazione costante:</strong> attrezzi moderni e corsi sempre aggiornati.
                        </li>
                        <li class="list-group-item bg-transparent text-white border-0 ps-4 mb-3">
                            <strong>Equilibrio:</strong> allenamento, alimentazione e riposo vanno di pari passo.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="staff">
        <div class="container">
            <h1 class="display-5 fw-bold text-center mb-5 text-warning">Il nostro staff</h1>
            <div class="row g-4">
                {{-- Esempio di Card Staff --}}
                @php
                    $staff = [
                        ['nome' => 'Marco Rossi', 'ruolo' => 'Personal Trainer', 'img' => 'chi_siamo-img-1'],
                        ['nome' => 'Giulia Bianchi', 'ruolo' => 'Istruttrice Yoga', 'img' => 'chi_siamo-img-2'],
                        ['nome' => 'Davide Conti', 'ruolo' => 'CrossFit & Zumba', 'img' => 'chi_siamo-img-3'],
                        ['nome' => 'Sara Verdi', 'ruolo' => 'Nutrizionista', 'img' => 'chi_siamo-img-4'],
                        ['nome' => 'Luca Moretti', 'ruolo' => 'Personal Trainer', 'img' => 'chi_siamo-img-5'],
                        ['nome' => 'Elena Neri', 'ruolo' => 'Reception', 'img' => 'chi_siamo-img-6'],
                    ];
                @endphp

                @foreach($staff as $membro)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="staff-card card h-100 border-0 shadow-sm p-4 text-center">
                        <picture>
                            <source srcset="{{ asset('images/chi-siamo/' . $membro['img'] . '.webp') }}" type="image/webp">
                            <img src="{{ asset('images/chi-siamo/' . $membro['img'] . '.jpg') }}" 
                                 alt="{{ $membro['nome'] }}" class="staff-img mx-auto mb-3" width="140" height="140" loading="lazy" decoding="async">
                        </picture>                
                        <h2 class="h5 fw-bold text-warning">{{ $membro['nome'] }}</h2>
                        <p class="text-white mb-0">{{ $membro['ruolo'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection