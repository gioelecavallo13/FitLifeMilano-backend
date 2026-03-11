<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Dashboard del Cliente
     */
    public function index()
    {
        // Recuperiamo i corsi a cui l'utente è già iscritto per mostrarli in dashboard
        $myCourses = Auth::user()->courses()->with('coach')->get();
        $breadcrumb = [['label' => 'Dashboard', 'url' => null]];
        $unreadMessagesCount = Auth::user()->unreadMessagesCount();
        return view('client.dashboard', compact('myCourses', 'breadcrumb', 'unreadMessagesCount'));
    }

    /**
     * Mostra la pagina di prenotazione con la lista dei corsi
     */
    public function booking()
    {
        // Carichiamo i corsi con il coach e il conteggio degli iscritti (paginato)
        $courses = Course::with('coach')->withCount('users')->paginate(12)->withQueryString();
        $enrolledCourseIds = Auth::user()->courses()->pluck('courses.id')->toArray();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('client.dashboard')],
            ['label' => 'Prenota corsi', 'url' => null],
        ];
        return view('client.booking', compact('courses', 'enrolledCourseIds', 'breadcrumb'));
    }

    /**
     * Anagrafica corso per il client (dettaglio corso, senza lista altri iscritti)
     */
    public function courseShow($id)
    {
        $course = Course::with('coach')->withCount('users')->findOrFail($id);
        $isEnrolled = Auth::user()->courses()->where('course_id', $id)->exists();
        $courseLabel = strlen($course->name) > 40 ? substr($course->name, 0, 37) . '...' : $course->name;
        $from = request('from');

        if ($from === 'dashboard') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Le mie prenotazioni', 'url' => route('client.dashboard')],
                ['label' => $courseLabel, 'url' => null],
            ];
        } else {
            // from=booking o default: da Prenota corsi
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Prenota corsi', 'url' => route('client.booking')],
                ['label' => $courseLabel, 'url' => null],
            ];
        }

        return view('client.courses.show', compact('course', 'isEnrolled', 'breadcrumb'));
    }

    /**
     * Gestisce l'iscrizione di un utente a un corso
     */
    public function enroll(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = Auth::user();

        // 1. Controllo se l'utente è già iscritto
        if ($user->courses()->where('course_id', $courseId)->exists()) {
            return redirect()->back()->with('error', 'Sei già iscritto a questo corso!');
        }

        // 2. Controllo disponibilità posti
        if ($course->users()->count() >= $course->capacity) {
            return redirect()->back()->with('error', 'Spiacenti, il corso è al completo!');
        }

        // 3. Iscrizione (inserimento nella tabella pivot)
        $user->courses()->attach($courseId);

        return redirect()->route('client.dashboard')->with('success', 'Prenotazione effettuata con successo! Ti aspettiamo in sala.');
    }

    /**
     * Consente all'utente di annullare una prenotazione
     */
    public function cancelBooking($courseId)
    {
        $user = Auth::user();
        $user->courses()->detach($courseId);

        return redirect()->back()->with('success', 'Prenotazione annullata.');
    }
}