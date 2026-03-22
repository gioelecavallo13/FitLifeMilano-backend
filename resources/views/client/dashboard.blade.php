@extends('layouts.layout')

@section('title', 'Client Dashboard' . " | " . config("app.name"))

@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <h1 class="text-white mb-5 fw-bold text-uppercase">Dashboard Cliente</h1>

    {{-- Azioni Rapide --}}
    <div class="row g-4 text-center mb-5">
        <div class="col-md-4">
            <div class="card bg-dark border-warning text-white h-100 shadow">
                <div class="card-body py-5">
                    <i class="bi bi-calendar-plus display-4 text-warning mb-3"></i>
                    <h4 class="fw-bold">PRENOTA NUOVO CORSO</h4>
                    <p class="text-secondary small">Scegli tra le attività disponibili e assicurati il tuo posto.</p>
                    <a href="{{ route('client.booking') }}" class="btn btn-warning w-100 mt-3 fw-bold text-uppercase">Vedi Corsi</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-secondary text-white h-100 shadow">
                <div class="card-body py-5">
                    <i class="bi bi-calendar3 display-4 mb-3"></i>
                    <h4 class="fw-bold">CALENDARIO CORSI</h4>
                    <p class="text-secondary small">Visualizza il calendario dei corsi a cui sei iscritto.</p>
                    <a href="{{ route('client.calendar') }}" class="btn btn-outline-light w-100 mt-3 fw-bold text-uppercase">Apri calendario</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-info text-white h-100 shadow">
                <div class="card-body py-5">
                    <i class="bi bi-chat-dots display-4 text-info mb-3"></i>
                    <h4 class="fw-bold">MESSAGGI
                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                            <span class="badge bg-danger rounded-pill ms-2" aria-label="{{ $unreadMessagesCount }} messaggi non letti">{{ $unreadMessagesCount }}</span>
                        @endif
                    </h4>
                    <p class="text-secondary small">Scrivi ai coach dei corsi a cui sei iscritto.</p>
                    <a href="{{ route('client.messages.index') }}" class="btn btn-info w-100 mt-3 fw-bold text-uppercase">Vai ai messaggi</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Sezione Corsi Prenotati --}}
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark border-secondary text-white shadow-lg">
                <div class="card-header border-secondary bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase"><i class="bi bi-list-stars text-info me-2"></i>Le Mie Prenotazioni</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-info text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">Corso</th>
                                    <th class="py-3">Coach</th>
                                    <th class="py-3 pe-4">Giorno e Orario</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $myCourses viene passato dal metodo index() del ClientController --}}
                                @isset($myCourses)
                                    @forelse($myCourses as $course)
                                        <tr class="table-row-chat cursor-pointer" data-href="{{ route('client.courses.show', $course->id) }}?from=dashboard&occurrence_date={{ \Carbon\Carbon::parse($course->pivot->occurrence_date)->format('Y-m-d') }}" role="button" tabindex="0">
                                            <td class="ps-4 py-3">
                                                <div class="fw-bold text-warning">{{ $course->name }}</div>
                                            </td>
                                            <td class="py-3">{{ $course->coach->first_name ?? 'N/D' }} {{ $course->coach->last_name ?? '' }}</td>
                                            <td class="py-3 pe-4">
                                                <span class="badge bg-outline-secondary border border-secondary text-white small">
                                                    {{ \Carbon\Carbon::parse($course->pivot->occurrence_date)->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }}
                                                </span>
                                                @if($course->first_occurrence_date)
                                                    <span class="badge {{ $course->is_repeatable ? 'bg-info' : 'bg-secondary' }} ms-1">{{ $course->is_repeatable ? 'Ripetibile' : 'Singolo' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-secondary italic">
                                                Non hai ancora effettuato alcuna prenotazione.
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-warning">
                                            Caricamento prenotazioni...
                                        </td>
                                    </tr>
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table-row-chat { cursor: pointer; }
.table-row-chat:hover { background-color: rgba(255,255,255,0.05); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table-row-chat[data-href]').forEach(function(row) {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
        row.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = this.dataset.href;
            }
        });
    });
});
</script>
@endpush
@endsection