@extends('layouts.layout')
@section('title', 'Area Riservata' . " | " . config("app.name"))
@section('content')
    <section class="login-section position-relative d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        
        {{-- SFONDO --}}
        <picture class="login-bg position-absolute top-0 start-0 w-100 h-100">
            <source srcset="{{ asset('images/area-riservata/area_riservata-img-2.webp') }}" type="image/webp">
            <img src="{{ asset('images/area-riservata/area_riservata-img-2.jpg') }}" 
                 class="w-100 h-100 object-fit-cover" 
                 alt="Sfondo Area Riservata"
                 width="1920" height="1080"
                 decoding="async"
                 style="z-index: 1;">
        </picture>

        {{-- OVERLAY --}}
        <div class="overlay position-absolute top-0 start-0 w-100 h-100 bg-black opacity-75" style="z-index: 2;"></div>

        {{-- CONTENUTO --}}
        <div class="container position-relative" style="z-index: 3;">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5 col-xl-4">
                    <form class="login-form p-4 rounded-4 shadow-lg" style="background: rgba(30,30,30,0.9);" action="{{ url('/login-process') }}" method="post">
                        @csrf
                        <h2 class="text-warning fw-bold text-center mb-4">ACCEDI ALL'AREA RISERVATA</h2>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label text-white fw-semibold">Email</label>
                            <input type="email" name="email" 
                                    class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-white fw-semibold">Password</label>
                            <input type="password" id="password" name="password" 
                                   class="form-control bg-dark text-white border-secondary py-2 custom-input" 
                                   placeholder="Inserisci la password" required>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small mb-3">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-2 text-uppercase">Accedi</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection