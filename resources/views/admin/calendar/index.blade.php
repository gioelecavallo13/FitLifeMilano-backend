@extends('layouts.layout')
@section('title', 'Calendario corsi - ' . $month_label . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1 class="text-white fw-bold text-uppercase mb-0">Calendario corsi</h1>
        <div class="d-flex gap-2">
            <a href="{{ route($calendarRoute ?? 'admin.calendar', ['year' => $prev_month['year'], 'month' => $prev_month['month']]) }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Mese precedente
            </a>
            <span class="btn btn-warning text-dark fw-bold px-4">{{ $month_label }}</span>
            <a href="{{ route($calendarRoute ?? 'admin.calendar', ['year' => $next_month['year'], 'month' => $next_month['month']]) }}"
               class="btn btn-outline-secondary">
                Mese successivo <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="card bg-dark border-primary text-white shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-bordered mb-0 calendar-grid">
                    <thead class="bg-black">
                        <tr>
                            <th class="text-center py-2 text-primary">Lun</th>
                            <th class="text-center py-2 text-primary">Mar</th>
                            <th class="text-center py-2 text-primary">Mer</th>
                            <th class="text-center py-2 text-primary">Gio</th>
                            <th class="text-center py-2 text-primary">Ven</th>
                            <th class="text-center py-2 text-primary">Sab</th>
                            <th class="text-center py-2 text-primary">Dom</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeks as $week)
                        <tr>
                            @foreach($week as $day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $isCurrentMonth = $day->month == $start->month;
                                    $isToday = $day->isToday();
                                    $occurrences = $occurrences_by_date[$dateKey] ?? [];
                                @endphp
                                <td class="calendar-day p-2 {{ !$isCurrentMonth ? 'text-secondary' : '' }} {{ $isToday ? 'bg-primary bg-opacity-25' : '' }}"
                                    style="min-height: 120px; vertical-align: top;">
                                    <div class="fw-bold mb-1">{{ $day->day }}</div>
                                    @foreach($occurrences as $occ)
                                        <div class="small mb-2 {{ $occ['is_past'] ? 'opacity-75' : '' }}">
                                            <a href="{{ route($courseShowRoute ?? 'admin.courses.show', $occ['course']->id) }}" class="text-warning text-decoration-none fw-bold">
                                                {{ $occ['course']->name }}
                                            </a>
                                            <div class="text-secondary">
                                                {{ \Carbon\Carbon::parse($occ['start_time'])->format('H:i') }}–{{ \Carbon\Carbon::parse($occ['end_time'])->format('H:i') }}
                                                · {{ $occ['coach_name'] }}
                                            </div>
                                            <span class="badge {{ $occ['is_past'] ? 'bg-secondary' : 'bg-info' }}">
                                                @if($occ['enrolled_count'] !== null)
                                                    {{ $occ['enrolled_count'] }}/{{ $occ['capacity'] }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-secondary small mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Per i corsi passati, il conteggio iscritti (—) è disponibile solo per le occorrenze registrate dallo snapshot notturno.
    </p>
</div>
@endsection
