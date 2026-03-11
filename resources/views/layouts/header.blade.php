<nav class="navbar navbar-expand-lg navbar-dark" 
     style="background: linear-gradient(rgba(0,0,0,0.95), rgba(0,0,0,0.95));">
  <div class="container-fluid">
    
    <a class="navbar-brand" href="{{ route('home') }}">
      <picture>
        <source srcset="{{ asset('images/logo_white.webp') }}" type="image/webp">
        <img id="header_logo" src="{{ asset('images/logo_white.png') }}" alt="Logo" width="90" height="90" decoding="async" style="width: auto; height: 90px;">
      </picture>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Apri menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-white" href="{{ route('corsi') }}">Corsi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="{{ route('chi-siamo') }}">Chi Siamo?</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="{{ route('contatti') }}">Contatti</a>
        </li>
      </ul>

      {{-- Logica Dinamica per Utenti Loggati o Ospiti --}}
      @guest
        {{-- Se l'utente NON è loggato --}}
        <a href="{{ route('login') }}" class="btn btn-outline-light ms-lg-3">
          Area Riservata
        </a>
      @endguest

      @auth
        {{-- Contenitore con posizionamento relativo per ancorare il menu --}}
        <div class="nav-item dropdown ms-lg-3 position-relative">
            <a class="btn btn-outline-warning dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
            Ciao, {{ Auth::user()->first_name }}
            </a>
            
            {{-- 'dropdown-menu-end' forza l'allineamento a destra rispetto al bottone --}}
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" aria-labelledby="userMenu" style="min-width: 150px;">
            <li>
                <a class="dropdown-item py-2" href="{{ route('dashboard.selector') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li>
                <a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                    <i class="bi bi-person-circle me-2"></i>Profilo
                </a>
            </li>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger py-2">
                    <i class="bi bi-box-arrow-right me-2"></i>Esci
                </button>
                </form>
            </li>
            </ul>
        </div>
        @endauth
    </div>
  </div>
</nav>