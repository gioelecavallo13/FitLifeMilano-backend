@extends('layouts.layout')
@section('title', 'Il mio profilo' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white fw-bold text-uppercase mb-0 h4">Il mio profilo</h1>
            </div>

            {{-- Card Dati utente --}}
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
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" width="120" height="120" style="width: 120px; height: 120px;">
                                    <i class="bi bi-person-circle display-4"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <p class="text-secondary small mb-2">Foto profilo</p>
                            <form action="{{ route('profile.updatePhoto') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2">
                                @csrf
                                <div class="custom-file-wrapper @error('profile_photo') is-invalid @enderror">
                                    <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                    <label for="profile_photo" class="custom-file-label">
                                        <i class="bi bi-camera-fill"></i> Scegli immagine
                                    </label>
                                    <span class="custom-file-name text-secondary"></span>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="bi bi-upload me-1"></i> Carica foto
                                </button>
                                @error('profile_photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </form>
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
                            <span class="badge {{ $user->role == 'admin' ? 'bg-danger' : ($user->role == 'coach' ? 'bg-info text-dark' : 'bg-secondary') }}">
                                {{ strtoupper($user->role) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-secondary small text-uppercase fw-bold d-block">Data registrazione</label>
                        <span>{{ $user->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @php
                $giorni = ['Monday' => 'Lunedì', 'Tuesday' => 'Martedì', 'Wednesday' => 'Mercoledì', 'Thursday' => 'Giovedì', 'Friday' => 'Venerdì', 'Saturday' => 'Sabato', 'Sunday' => 'Domenica'];
            @endphp

            @if($user->role === 'coach')
            {{-- Card Corsi di cui è personal trainer --}}
            <div class="card bg-dark border-warning text-white shadow-lg">
                <div class="card-header border-warning bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase text-warning">
                        <i class="bi bi-person-badge me-2"></i>I miei corsi ({{ $user->createdCourses->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-warning text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">Corso</th>
                                    <th class="py-3">Giorno</th>
                                    <th class="py-3 pe-4">Orario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->createdCourses as $course)
                                <tr class="table-row-chat cursor-pointer" data-href="{{ route('coach.courses.show', $course->id) }}" role="button" tabindex="0">
                                    <td class="ps-4 py-3 fw-bold text-warning">{{ $course->name }}</td>
                                    <td class="py-3">{{ $giorni[$course->day_of_week] ?? $course->day_of_week }}</td>
                                    <td class="py-3 pe-4">{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-secondary italic">
                                        Nessun corso assegnato.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @elseif($user->role === 'client')
            {{-- Card Corsi a cui è prenotato --}}
            <div class="card bg-dark border-warning text-white shadow-lg">
                <div class="card-header border-warning bg-black p-3">
                    <h5 class="mb-0 fw-bold text-uppercase text-warning">
                        <i class="bi bi-calendar-check me-2"></i>I corsi a cui sono prenotato ({{ $user->courses->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-black text-warning text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">Corso</th>
                                    <th class="py-3">Coach</th>
                                    <th class="py-3">Giorno</th>
                                    <th class="py-3">Orario</th>
                                    <th class="py-3 pe-4">Data prenotazione</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->courses as $course)
                                <tr class="table-row-chat cursor-pointer" data-href="{{ route('client.courses.show', $course->id) }}" role="button" tabindex="0">
                                    <td class="ps-4 py-3 fw-bold text-warning">{{ $course->name }}</td>
                                    <td class="py-3">{{ $course->coach ? $course->coach->first_name . ' ' . $course->coach->last_name : 'N/D' }}</td>
                                    <td class="py-3">{{ $giorni[$course->day_of_week] ?? $course->day_of_week }}</td>
                                    <td class="py-3">{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</td>
                                    <td class="py-3 pe-4 text-secondary small">
                                        {{ $course->pivot->created_at ? $course->pivot->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') : '—' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-secondary italic">
                                        Nessuna prenotazione.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.table-row-chat { cursor: pointer; }
.table-row-chat:hover { background-color: rgba(255,255,255,0.05); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.custom-file-wrapper input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function() {
            var name = this.files.length ? this.files[0].name : '';
            var span = this.closest('.custom-file-wrapper').querySelector('.custom-file-name');
            if (span) span.textContent = name;
        });
    });
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
