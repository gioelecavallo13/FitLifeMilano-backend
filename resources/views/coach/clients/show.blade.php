@extends('layouts.layout')
@section('title', 'Anagrafica: ' . $user->first_name . ' ' . $user->last_name . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white fw-bold text-uppercase mb-0 h4">Anagrafica: {{ $user->first_name }} {{ $user->last_name }}</h1>
            </div>

            {{-- Card Dati utente (sola lettura) --}}
            <div class="card bg-dark border-primary text-white shadow-lg mb-4">
                <div class="card-header border-primary bg-black p-4">
                    <h3 class="mb-0 text-primary h4">Dati utente</h3>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-auto">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="Foto profilo" class="rounded-circle object-fit-cover" width="120" height="120" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px;">
                                    <i class="bi bi-person-circle display-4"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <p class="text-secondary small mb-0">Foto profilo</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Nome</label>
                            <span class="fs-5 fw-bold">{{ $user->first_name }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Cognome</label>
                            <span class="fs-5 fw-bold">{{ $user->last_name }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Email</label>
                            <a href="mailto:{{ $user->email }}" class="text-primary text-decoration-none fs-5">{{ $user->email }}</a>
                        </div>
                        <div class="col-md-6">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Ruolo</label>
                            <span class="badge bg-secondary">{{ strtoupper($user->role) }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block">Data registrazione</label>
                        <span>{{ $user->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') }}</span>
                    </div>
                    <hr class="border-secondary my-3">
                    <a href="{{ route('coach.messages.startWithClient', $user->id) }}" class="btn btn-warning">
                        <i class="bi bi-chat-dots me-1"></i> Invia messaggio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
