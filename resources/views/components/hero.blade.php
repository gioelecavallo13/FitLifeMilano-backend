{{-- resources/views/components/hero.blade.php --}}
@props([
    'imageName',   //Nome del file senza estensione (es. 'home-img-2')
    'imagePath',   //Percorso della cartella (es. 'images/index/')
    'title'=> null,
    'subtitle'=> null, 
    'buttonText' => null, 
    'buttonUrl' => null,
    'alt' => 'FitLife Training'
])

<section class="hero-container">
    <picture>
        {{-- Generazione automatica del percorso WebP --}}
        <source srcset="{{ asset($imagePath . $imageName . '.webp') }}" type="image/webp">
        {{-- Fallback JPG con dimensioni esplicite per evitare CLS --}}
        <img src="{{ asset($imagePath . $imageName . '.jpg') }}" class="hero-img" alt="{{ $alt }}" width="1920" height="1080" decoding="async">
    </picture>
    
    <div class="hero-overlay"></div>

    <div class="hero-content container px-3">
        <h1 class="display-5 fw-bold text-white mb-3">{{ $title }}</h1>
        <p class="lead text-white mb-4">{{ $subtitle }}</p>
        
        @if($buttonText && $buttonUrl)
            <a href="{{ $buttonUrl }}" class="btn btn-warning btn-lg fw-bold px-5 shadow">
                {{ $buttonText }}
            </a>
        @endif
    </div>
</section>