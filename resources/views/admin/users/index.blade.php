@extends('layouts.layout')
@section('title', 'Gestione utenti' . " | " . config("app.name"))

@section('content')
<div class="container py-5">
    <x-breadcrumb :items="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold text-uppercase">Gestione Utenti</h2>
    </div>

    {{-- Sezione Filtri --}}
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control bg-black text-white border-secondary" placeholder="Cerca nome, cognome o email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select bg-black text-white border-secondary">
                        <option value="">Tutti i ruoli</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="coach" {{ request('role') == 'coach' ? 'selected' : '' }}>Coach</option>
                        <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Cliente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-light w-100">Filtra</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabella Utenti --}}
    <form action="{{ route('admin.users.bulkDestroy') }}" method="POST" id="bulk-delete-form">
        @csrf
        @method('DELETE')
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-white small">
                Seleziona uno o più utenti per eliminarli in blocco.
            </div>
            <button type="submit" class="btn btn-danger btn-sm d-none" id="bulk-delete-button">
                Elimina selezionati
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover border-secondary align-middle mb-0 shadow">
                <thead class="table-black text-secondary">
                    <tr>
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th class="ps-4 py-3" style="width: 50px;"></th>
                        <th class="py-3">NOME</th>
                        <th class="py-3">COGNOME</th>
                        <th class="py-3">EMAIL</th>
                        <th class="py-3">RUOLO</th>
                        <th class="py-3 pe-4">DATA REGISTRAZIONE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="table-row-user" data-href="{{ route('admin.users.show', $user->id) }}" tabindex="0">
                        <td class="ps-4 py-2">
                            <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="user-checkbox">
                        </td>
                        <td class="ps-4 py-2">
                            <img src="{{ $user->profile_photo_url_small }}" alt="" class="rounded-circle object-fit-cover" width="36" height="36" style="object-fit: cover;">
                        </td>
                        <td class="py-3 fw-bold">{{ $user->first_name }}</td>
                        <td class="py-3">{{ $user->last_name }}</td>
                        <td class="py-3 text-info">{{ $user->email }}</td>
                        <td class="py-3">
                            <span class="badge role-badge role-badge-{{ $user->role }}">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td class="py-3 small pe-4">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Nessun utente trovato con questi criteri.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    @if($users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

@push('styles')
<style>
.table-row-user {
    cursor: pointer;
    transition: background-color 0.15s ease, transform 0.05s ease;
}

.table-row-user:hover {
    background-color: rgba(255, 255, 255, 0.03);
}

.role-badge {
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    font-size: 0.7rem;
    letter-spacing: 0.05em;
    font-weight: 600;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.role-badge-admin {
    background: linear-gradient(135deg, #ff4b4b, #b31217);
    border-color: rgba(255, 255, 255, 0.18);
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.03);
}

.role-badge-coach {
    background: linear-gradient(135deg, #3dd8ff, #00a4cc);
    color: #00131a;
    border-color: rgba(0, 0, 0, 0.3);
}

.role-badge-client {
    background: linear-gradient(135deg, #505050, #2b2b2b);
    border-color: rgba(255, 255, 255, 0.12);
}

/* Checkbox stile FitLife */
.user-checkbox,
#select-all {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #f44336;
}

@supports (-webkit-appearance: none) or (appearance: none) {
    .user-checkbox,
    #select-all {
        -webkit-appearance: none;
        appearance: none;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.35);
        background-color: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.05s ease;
    }

    .user-checkbox:hover,
    #select-all:hover {
        border-color: rgba(255, 255, 255, 0.7);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.08);
    }

    .user-checkbox:checked,
    #select-all:checked {
        background: linear-gradient(135deg, #ff5252, #ff9800);
        border-color: transparent;
        box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.35);
    }

    .user-checkbox:checked::after,
    #select-all:checked::after {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 2px;
        background-color: #1b1b1b;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');
    const bulkDeleteButton = document.getElementById('bulk-delete-button');

    function updateBulkDeleteState() {
        const selected = Array.from(userCheckboxes).filter(cb => cb.checked).length;
        if (!bulkDeleteButton) return;

        if (selected > 0) {
            bulkDeleteButton.classList.remove('d-none');
            bulkDeleteButton.textContent = selected === 1
                ? 'Elimina selezionato'
                : `Elimina selezionati (${selected})`;
        } else {
            bulkDeleteButton.classList.add('d-none');
            bulkDeleteButton.textContent = 'Elimina selezionati';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            userCheckboxes.forEach(function (cb) {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkDeleteState();
        });
    }

    userCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            updateBulkDeleteState();
        });
    });

    if (bulkDeleteForm && bulkDeleteButton) {
        bulkDeleteForm.addEventListener('submit', function (e) {
            const anySelected = Array.from(userCheckboxes).some(cb => cb.checked);
            if (!anySelected) {
                e.preventDefault();
                alert('Seleziona almeno un utente da eliminare.');
                return;
            }

            const confirmed = confirm('Sei sicuro di voler eliminare gli utenti selezionati?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }

    updateBulkDeleteState();

    const userRows = document.querySelectorAll('.table-row-user[data-href]');
    userRows.forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('input[type="checkbox"]')) {
                return;
            }
            const href = row.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });

        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.closest('input[type="checkbox"]')) {
                    return;
                }
                e.preventDefault();
                const href = row.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            }
        });
    });
});
</script>
@endpush
@endsection