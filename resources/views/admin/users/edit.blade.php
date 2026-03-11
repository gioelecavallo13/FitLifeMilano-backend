@extends('layouts.layout')

@section('title', 'Modifica Utente | ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-dark text-white border-secondary shadow-lg">
                <div class="card-header border-secondary bg-black p-4 text-center">
                    <h3 class="mb-0 fw-bold text-uppercase text-warning">Modifica Utente</h3>
                    <small class="text-secondary">{{ $user->email }}</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Foto profilo (solo admin può modificarla) --}}
                        <div class="mb-4">
                            <label class="form-label small text-secondary fw-bold">FOTO PROFILO</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="Foto attuale" class="rounded-circle object-fit-cover" width="80" height="80" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px;">
                                        <i class="bi bi-person-circle fs-2"></i>
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="custom-file-wrapper @error('profile_photo') is-invalid @enderror">
                                        <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/jpg,image/webp">
                                        <label for="profile_photo" class="custom-file-label">
                                            <i class="bi bi-camera-fill"></i> Scegli immagine
                                        </label>
                                        <span class="custom-file-name text-secondary"></span>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="bi bi-upload me-1"></i> Carica foto
                                    </button>
                                    <small class="text-secondary d-block w-100 mt-1">JPG, PNG, WebP. Max 2MB. Lascia vuoto per mantenere l'attuale.</small>
                                </div>
                            </div>
                            @error('profile_photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Nome --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-secondary fw-bold">NOME</label>
                                <input type="text" name="first_name" class="form-control bg-black text-white border-secondary @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            {{-- Cognome --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-secondary fw-bold">COGNOME</label>
                                <input type="text" name="last_name" class="form-control bg-black text-white border-secondary @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label small text-secondary fw-bold">EMAIL</label>
                            <input type="email" name="email" class="form-control bg-black text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        {{-- Ruolo --}}
                        <div class="mb-4">
                            <label class="form-label small text-secondary fw-bold">RUOLO UTENTE</label>
                            <select name="role" class="form-select bg-black text-white border-secondary @error('role') is-invalid @enderror">
                                <option value="client" {{ old('role', $user->role) == 'client' ? 'selected' : '' }}>Cliente</option>
                                <option value="coach" {{ old('role', $user->role) == 'coach' ? 'selected' : '' }}>Coach</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Amministratore</option>
                            </select>
                            @error('role') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning fw-bold py-2 text-uppercase">Salva Modifiche</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-link text-secondary text-decoration-none small">Annulla e torna alla lista</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-3 p-3 bg-black border border-secondary rounded">
                <p class="small text-secondary mb-0 text-center">
                    <i class="bi bi-info-circle me-2"></i>Stai modificando il profilo di un membro del team o di un cliente.
                </p>
            </div>
        </div>
    </div>
</div>

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
});
</script>
@endpush
@endsection