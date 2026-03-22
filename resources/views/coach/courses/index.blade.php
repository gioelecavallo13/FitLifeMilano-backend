@extends('layouts.layout')
@section('title', 'I miei corsi' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <h1 class="text-white mb-4 fw-bold text-uppercase">I miei corsi</h1>
    <div class="card bg-dark border-primary shadow-lg text-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-black text-primary text-uppercase small">
                        <tr>
                            <th class="ps-4 py-3">Corso</th>
                            <th class="py-3">Giorno</th>
                            <th class="py-3">Orario</th>
                            <th class="py-3">Capacità</th>
                            <th class="py-3 pe-4 text-center">Iscritti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                        <tr class="table-row-chat cursor-pointer" data-href="{{ route('coach.courses.show', $course->id) }}" role="button" tabindex="0">
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-uppercase">{{ $course->name }}</span>
                                <div class="small text-secondary">Max {{ $course->capacity }} persone</div>
                            </td>
                            <td class="py-3">{{ $course->first_occurrence_date?->format('d/m/Y') ?? $course->day_label }} @if($course->first_occurrence_date)<span class="badge {{ $course->is_repeatable ? 'bg-info' : 'bg-secondary' }}">{{ $course->is_repeatable ? 'Ripetibile' : 'Singolo' }}</span>@endif</td>
                            <td class="py-3">{{ \Carbon\Carbon::parse($course->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($course->end_time)->format('H:i') }}</td>
                            <td class="py-3">{{ $course->capacity }}</td>
                            <td class="py-3 pe-4 text-center">{{ $course->users_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary italic">
                                Nessun corso creato.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
