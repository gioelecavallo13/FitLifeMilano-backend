<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Coach\CoachController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;

/*
|--------------------------------------------------------------------------
| Web Routes - Backend (area riservata)
|--------------------------------------------------------------------------
|
| Questo progetto espone solo l'area riservata e la pagina di login.
| Il sito pubblico è gestito dal progetto frontend (FitLifeMilano-frontend).
|
*/

// --- LOGIN (UNICA PARTE PUBBLICA) ---
Route::middleware(['guest'])->group(function () {
    Route::get('/area-riservata', function () {
        return view('area-riservata');
    })->name('login');

    Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');
});

// Redirect root verso login (opzionale)
Route::get('/', function () {
    return redirect()->route('login');
});

// --- ROTTE PROTETTE (Richiedono Login) ---
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard-selector', function () {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'coach') {
            return redirect()->route('coach.dashboard');
        }
        return redirect()->route('client.dashboard');
    })->name('dashboard.selector');

    // Profilo utente (accessibile a tutti gli autenticati)
    Route::get('/profilo', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profilo/foto', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
    Route::get('/utenti/{user}/foto', [ProfilePhotoController::class, 'show'])->name('profile.photo');

    // --- GRUPPO AMMINISTRATORI ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        Route::get('/courses/create', [AdminController::class, 'courseCreate'])->name('courses.create');
        Route::get('/courses/{id}', [AdminController::class, 'courseShow'])->name('courses.show');
        Route::post('/courses/store', [AdminController::class, 'courseStore'])->name('courses.store');
        Route::get('/courses/{id}/edit', [AdminController::class, 'courseEdit'])->name('courses.edit');
        Route::put('/courses/{id}', [AdminController::class, 'courseUpdate'])->name('courses.update');
        Route::post('/courses/destroy', [AdminController::class, 'courseDestroy'])->name('courses.destroy');
        Route::post('/courses/{courseId}/unenroll/{userId}', [AdminController::class, 'courseUnenroll'])->name('courses.unenroll');

        Route::get('/messaggi', [AdminController::class, 'messages'])->name('messages.index');
        Route::get('/messaggi/{id}', [AdminController::class, 'messageShow'])->name('messages.show');
        Route::post('/messaggi/{id}/reply', [AdminController::class, 'messageReply'])->name('messages.reply');

        Route::get('/chat', [AdminConversationController::class, 'index'])->name('chat.index');
        Route::get('/chat/conversazione/{id}', [AdminConversationController::class, 'show'])->name('chat.show');
        Route::post('/chat/conversazione/{id}', [AdminConversationController::class, 'storeMessage'])->name('chat.send');
        Route::post('/chat/conversazione/{id}/segna-letti', [AdminConversationController::class, 'markAsRead'])->name('chat.markRead');
        Route::get('/chat/conversazione/con-utente/{userId}', [AdminConversationController::class, 'startWithUser'])->name('chat.startWithUser');

        Route::get('/inserisci-coach', [AdminController::class, 'createCoach'])->name('coaches.create');
        Route::post('/store-coach', [AdminController::class, 'storeCoach'])->name('coaches.store');

        Route::get('/inserisci-clienti', [AdminController::class, 'createClient'])->name('clients.create');
        Route::post('/store-clienti', [AdminController::class, 'storeClient'])->name('clients.store');

        Route::get('/utenti', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::get('/utenti/{id}', [AdminController::class, 'userShow'])->name('users.show');
        Route::get('/utenti/{id}/modifica', [AdminController::class, 'userEdit'])->name('users.edit');
        Route::put('/utenti/{id}/aggiorna', [AdminController::class, 'userUpdate'])->name('users.update');
        Route::delete('/utenti/{id}/elimina', [AdminController::class, 'userDestroy'])->name('users.destroy');
    });

    // --- GRUPPO COACH ---
    Route::middleware(['role:coach'])->prefix('coach')->name('coach.')->group(function () {
        Route::get('/dashboard', [CoachController::class, 'index'])->name('dashboard');
        Route::get('/corsi', [CoachController::class, 'coursesIndex'])->name('courses.index');
        Route::get('/corsi/{id}', [CoachController::class, 'courseShow'])->name('courses.show');
        Route::get('/clienti/{id}', [CoachController::class, 'clientShow'])->name('clients.show');
        Route::get('/messaggi', [ConversationController::class, 'index'])->name('messages.index');
        Route::get('/messaggi/conversazione/{id}', [ConversationController::class, 'show'])->name('messages.show');
        Route::post('/messaggi/conversazione/{id}', [ConversationController::class, 'storeMessage'])->name('messages.send');
        Route::post('/messaggi/conversazione/{id}/segna-letti', [ConversationController::class, 'markAsRead'])->name('messages.markRead');
        Route::get('/messaggi/conversazione/con-client/{clientId}', [ConversationController::class, 'startWithClient'])->name('messages.startWithClient');
        Route::get('/messaggi/conversazione/con-coach-collega/{coachId}', [ConversationController::class, 'startWithCoachColleague'])->name('messages.startWithCoachColleague');
    });

    // --- GRUPPO CLIENTI ---
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
        Route::get('/prenota-corsi', [ClientController::class, 'booking'])->name('booking');
        Route::get('/corsi/{id}', [ClientController::class, 'courseShow'])->name('courses.show');
        Route::post('/corsi/{courseId}/prenota', [ClientController::class, 'enroll'])->name('enroll');
        Route::delete('/corsi/{courseId}/annulla', [ClientController::class, 'cancelBooking'])->name('cancel');
        Route::get('/messaggi', [ConversationController::class, 'index'])->name('messages.index');
        Route::get('/messaggi/conversazione/{id}', [ConversationController::class, 'show'])->name('messages.show');
        Route::post('/messaggi/conversazione/{id}', [ConversationController::class, 'storeMessage'])->name('messages.send');
        Route::post('/messaggi/conversazione/{id}/segna-letti', [ConversationController::class, 'markAsRead'])->name('messages.markRead');
        Route::get('/messaggi/conversazione/con-coach/{coachId}', [ConversationController::class, 'startWithCoach'])->name('messages.startWithCoach');
    });

});
