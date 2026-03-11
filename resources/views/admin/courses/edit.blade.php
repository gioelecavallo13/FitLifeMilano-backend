@extends('layouts.layout')
@section('title', 'Modifica Corso' . " | " . config("app.name"))
@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-white fw-bold text-uppercase mb-0">Modifica Corso</h1>
            <p class="text-secondary">Stai modificando: <span class="text-primary fw-bold">{{ $course->name }}</span></p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-dark border-warning shadow-lg text-white">
                <div class="card-header bg-warning text-black fw-bold">
                    <i class="bi bi-pencil-square"></i> DETTAGLI DEL CORSO
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Nome Corso --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Nome Corso</label>
                                <input type="text" name="name" class="form-control bg-black text-white border-secondary @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $course->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Coach --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Coach Istruttore</label>
                                <select name="user_id" class="form-select bg-black text-white border-secondary" required>
                                    @foreach($coaches as $coach)
                                        <option value="{{ $coach->id }}" {{ $course->user_id == $coach->id ? 'selected' : '' }}>
                                            {{ $coach->first_name }} {{ $coach->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Prezzo --}}
                            <div class="col-md-4 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Prezzo (€)</label>
                                <input type="number" step="0.01" name="price" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('price', $course->price) }}" required>
                            </div>
                            {{-- Capacità --}}
                            <div class="col-md-4 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Capacità Max</label>
                                <input type="number" name="capacity" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('capacity', $course->capacity) }}" required>
                            </div>
                            {{-- Giorno --}}
                            <div class="col-md-4 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Giorno</label>
                                <select name="day_of_week" class="form-select bg-black text-white border-secondary" required>
                                    @foreach(['Monday' => 'Lunedì', 'Tuesday' => 'Martedì', 'Wednesday' => 'Mercoledì', 'Thursday' => 'Giovedì', 'Friday' => 'Venerdì', 'Saturday' => 'Sabato', 'Sunday' => 'Domenica'] as $value => $label)
                                        <option value="{{ $value }}" {{ $course->day_of_week == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Orario Inizio --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario Inizio</label>
                                <input type="time" name="start_time" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('start_time', \Carbon\Carbon::parse($course->start_time)->format('H:i')) }}" required>
                            </div>
                            {{-- Orario Fine --}}
                            <div class="col-md-6 mb-3">
                                <label class="small text-secondary text-uppercase fw-bold">Orario Fine</label>
                                <input type="time" name="end_time" class="form-control bg-black text-white border-secondary" 
                                       value="{{ old('end_time', \Carbon\Carbon::parse($course->end_time)->format('H:i')) }}" required>
                            </div>
                        </div>

                        {{-- Descrizione --}}
                        <div class="mb-4">
                            <label class="small text-secondary text-uppercase fw-bold">Descrizione</label>
                            <textarea name="description" rows="4" class="form-control bg-black text-white border-secondary" required>{{ old('description', $course->description) }}</textarea>
                        </div>

                        <hr class="border-secondary mb-4">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold text-uppercase py-2 shadow">
                                <i class="bi bi-check-lg"></i> Salva Modifiche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection