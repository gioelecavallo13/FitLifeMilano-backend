@extends('layouts.layout')
@section('title', 'Coach Dashboard' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <h1 class="text-white mb-5 fw-bold text-uppercase">Dashboard Coach</h1>
    <div class="row g-4 text-center justify-content-center">
        <div class="col-12 col-sm-6 col-lg">
            <div class="card bg-dark border-primary text-white h-100 shadow">
                <div class="card-body py-5">
                    <i class="bi bi-calendar-check display-4 text-primary mb-3"></i>
                    <h4 class="fw-bold">I MIEI CORSI</h4>
                    <p class="text-secondary small">Visualizza i tuoi corsi e chi è prenotato.</p>
                    <a href="{{ route('coach.courses.index') }}" class="btn btn-primary w-100 mt-3">VEDI CORSI</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card bg-dark border-warning text-white h-100 shadow">
                <div class="card-body py-5">
                    <i class="bi bi-envelope-paper display-4 text-warning mb-3"></i>
                    <h4 class="fw-bold">MESSAGGI
                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                            <span class="badge bg-danger rounded-pill ms-2" aria-label="{{ $unreadMessagesCount }} messaggi non letti">{{ $unreadMessagesCount }}</span>
                        @endif
                    </h4>
                    <p class="text-secondary small">Messaggistica con i clienti.</p>
                    <a href="{{ route('coach.messages.index') }}" class="btn btn-warning w-100 mt-3">APRI</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
