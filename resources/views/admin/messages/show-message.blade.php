@extends('layouts.layout')
@section('title', 'Messaggio ' . $message->id . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <x-breadcrumb :items="$breadcrumb" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="text-end ms-auto">
                    <span class="text-secondary small d-block">Stato Attuale:</span>
                    @if($message->status == 'new')
                        <span class="badge bg-danger">NUOVO</span>
                    @elseif($message->status == 'read')
                        <span class="badge bg-secondary">LETTO</span>
                    @else
                        <span class="badge bg-success">RISPOSTO</span>
                    @endif
                </div>
            </div>

            <div class="card bg-dark border-warning text-white shadow-lg mb-4">
                <div class="card-header border-warning bg-black p-4">
                    <h3 class="mb-0 text-warning h4">Dettaglio Richiesta #{{ $message->id }}</h3>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Mittente</label>
                            <span class="fs-5">{{ $message->first_name }} {{ $message->last_name }}</span>
                            <br>
                            <a href="mailto:{{ $message->email }}" class="text-warning text-decoration-none small italic">
                                {{ $message->email }}
                            </a>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <label class="text-secondary small text-uppercase fw-bold d-block">Ricevuto il</label>
                            <span>{{ $message->created_at->format('d/m/Y') }} alle ore {{ $message->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-secondary small text-uppercase fw-bold d-block">Oggetto</label>
                        <p class="fs-5 fw-bold border-bottom border-secondary pb-2">{{ $message->subject }}</p>
                    </div>

                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block mb-2">Contenuto del Messaggio</label>
                        <div class="bg-black p-4 rounded border border-secondary" style="white-space: pre-wrap; line-height: 1.6;">
                            {{ $message->message }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-dark border-success text-white shadow-lg">
                <div class="card-body p-4">
                    <h5 class="text-success fw-bold mb-4">
                        <i class="bi bi-reply-fill"></i> Invia una risposta
                    </h5>
                    
                    <form action="{{ route('admin.messages.reply', $message->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="reply_text" rows="6" class="form-control bg-black text-white border-secondary focus-warning" 
                                      placeholder="Scrivi qui il testo della tua risposta..." required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="small text-secondary m-0">
                                <i class="bi bi-info-circle"></i> Inviando la risposta, lo stato passerà a "Risposto".
                            </p>
                            <button type="submit" class="btn btn-success px-5 fw-bold">
                                INVIA RISPOSTA
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .focus-warning:focus {
        background-color: #111 !important;
        border-color: #ffc107 !important;
        color: white !important;
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
    }
</style>
@endsection