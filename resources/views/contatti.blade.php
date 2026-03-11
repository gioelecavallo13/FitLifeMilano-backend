@extends('layouts.layout')
@section('title', 'Contatti' . " | " . config("app.name"))
@section('content')
    <x-hero 
        imagePath="images/contatti/" 
        imageName="contatti-img-2"
        title="Contattaci" 
        subtitle="Siamo qui per rispondere alle tue domande!"
    />

    <section class="py-5" style="background-color: rgb(87, 86, 86);">
        <div class="container px-4 px-md-0">
            <div class="row g-4 justify-content-center align-items-stretch">
                
                <div class="col-12 col-lg-5">
                    <form class="login-form h-100" action="{{ route('contact.store') }}" method="post">
                        @csrf

                        {{-- Messaggio di Successo --}}
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
                                ✅ {{ session('success') }}
                            </div>
                        @endif

                        {{-- Messaggio di Errore Globale (Opzionale, se vuoi un avviso generale) --}}
                        @if($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                opps! Ci sono degli errori nei dati inseriti.
                            </div>
                        @endif

                        <h2 class="mb-4">Inviaci un messaggio</h2>
                        
                        {{-- OGGETTO --}}
                        <div class="mb-3">
                            <label for="oggetto" class="form-label">Oggetto</label>
                            <select class="form-select custom-input @error('subject') is-invalid @enderror" 
                                    id="oggetto" name="subject" required>
                                <option value="" selected disabled>Scegli l'oggetto del messaggio</option>
                                <option value="informazioni" {{ old('subject') == 'informazioni' ? 'selected' : '' }}>Informazioni Generali</option>
                                <option value="corsi" {{ old('subject') == 'corsi' ? 'selected' : '' }}>Iscrizione Corsi</option>
                                <option value="staff" {{ old('subject') == 'staff' ? 'selected' : '' }}>Informazioni abbonamenti</option>
                                <option value="commerciale" {{ old('subject') == 'commerciale' ? 'selected' : '' }}>Collaborazioni</option>
                            </select>
                            @error('subject')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- EMAIL --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                class="form-control custom-input @error('email') is-invalid @enderror" 
                                id="email" name="email" 
                                value="{{ old('email') }}" 
                                placeholder="nome@esempio.com" 
                                required>
                            @error('email')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- MESSAGGIO --}}
                        <div class="mb-3">
                            <label for="messaggio" class="form-label">Messaggio</label>
                            <textarea class="form-control custom-input @error('message') is-invalid @enderror" 
                                    id="messaggio" name="message" 
                                    rows="4" 
                                    placeholder="Come possiamo aiutarti? (min. 10 caratteri)" 
                                    minlength="10" 
                                    required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-3 mt-2 shadow-sm">
                            Invia Messaggio
                        </button>
                    </form>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="contatti-mappa-container h-100">
                        <iframe class="contatti-mappa w-100 h-100"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2798.2185150917274!2d9.1859243!3d45.4642035!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDXCsDI3JzUxLjEiTiA5wrAxMScwOS4zIkU!5e0!3m2!1sit!2sit!4v1625000000000"
                            allowfullscreen="" loading="lazy" style="min-height: 400px; border: 0;"></iframe>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection