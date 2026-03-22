<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Dashboard del Cliente
     */
    public function index()
    {
        $myCourses = Auth::user()->courses()->with('coach')->orderByPivot('occurrence_date', 'asc')->get();
        $breadcrumb = [['label' => 'Dashboard', 'url' => null]];
        $unreadMessagesCount = Auth::user()->unreadMessagesCount();

        return view('client.dashboard', compact('myCourses', 'breadcrumb', 'unreadMessagesCount'));
    }

    /**
     * Mostra la pagina di prenotazione con la lista dei corsi
     */
    public function booking()
    {
        $courses = Course::with(['coach', 'occurrenceSettings'])->withCount('users')->paginate(12)->withQueryString();
        $user = Auth::user();

        $bookedByCourse = $user->enrollments()
            ->whereIn('course_id', $courses->pluck('id'))
            ->get()
            ->groupBy('course_id')
            ->map(fn ($rows) => $rows->map(fn ($e) => $e->occurrence_date->format('Y-m-d'))->values()->all());

        $from = now()->startOfDay();
        $defaultTo = now()->copy()->addMonths(3)->startOfDay();

        $occurrenceMeta = [];
        foreach ($courses as $course) {
            $to = $defaultTo;
            if ($course->last_lesson_date) {
                $cap = $course->last_lesson_date->copy()->startOfDay();
                if ($cap->lt($to)) {
                    $to = $cap;
                }
            }
            $meta = [];
            foreach ($course->occurrenceDatesBetween($from, $to) as $day) {
                $key = $day->format('Y-m-d');
                $meta[$key] = [
                    'spots_left' => $course->capacity - $course->enrollmentCountForOccurrence($day),
                    'enrollment_open' => $course->isEnrollmentOpenForOccurrenceDate($day),
                    'cancellation_open' => $course->isCancellationOpenForOccurrenceDate($day),
                    'booking_deadline' => $course->getBookingDeadlineAtForOccurrenceDate($day)?->format('H:i'),
                    'cancel_deadline' => $course->getCancellationDeadlineAtForOccurrenceDate($day)?->format('H:i'),
                    'lesson_start' => Carbon::parse($course->getEffectiveStartTimeForDate($day))->format('H:i'),
                    'lesson_end' => Carbon::parse($course->getEffectiveEndTimeForDate($day))->format('H:i'),
                ];
            }
            $occurrenceMeta[$course->id] = $meta;
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('client.dashboard')],
            ['label' => 'Prenota corsi', 'url' => null],
        ];

        return view('client.booking', compact('courses', 'bookedByCourse', 'occurrenceMeta', 'breadcrumb'));
    }

    /**
     * Anagrafica corso per il client (dettaglio corso, senza lista altri iscritti)
     */
    public function courseShow($id)
    {
        $course = Course::with(['coach', 'occurrenceSettings'])->withCount('users')->findOrFail($id);

        $occurrenceDateInput = request('occurrence_date');
        $next = $course->getNextOccurrence();

        if ($occurrenceDateInput) {
            $occurrenceDate = Carbon::parse($occurrenceDateInput)->startOfDay();
        } elseif ($course->first_occurrence_date && ! $course->is_repeatable) {
            $occurrenceDate = $course->first_occurrence_date->copy()->startOfDay();
        } elseif ($next) {
            $occurrenceDate = $next->copy()->startOfDay();
        } else {
            $occurrenceDate = now()->startOfDay();
        }

        if (! $course->getOccurrenceAt($occurrenceDate) && $next) {
            $occurrenceDate = $next->copy()->startOfDay();
        }

        $isEnrolled = Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->whereDate('occurrence_date', $occurrenceDate)
            ->exists();

        $enrolledForOccurrence = $course->enrollmentCountForOccurrence($occurrenceDate);
        $spotsLeft = $course->capacity - $enrolledForOccurrence;

        $courseLabel = strlen($course->name) > 40 ? substr($course->name, 0, 37).'...' : $course->name;
        $from = request('from');

        if ($from === 'dashboard') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Le mie prenotazioni', 'url' => route('client.dashboard')],
                ['label' => $courseLabel, 'url' => null],
            ];
        } else {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('client.dashboard')],
                ['label' => 'Prenota corsi', 'url' => route('client.booking')],
                ['label' => $courseLabel, 'url' => null],
            ];
        }

        return view('client.courses.show', compact(
            'course',
            'isEnrolled',
            'occurrenceDate',
            'spotsLeft',
            'breadcrumb'
        ));
    }

    /**
     * Gestisce l'iscrizione di un utente a un corso (per una occorrenza)
     */
    public function enroll(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = Auth::user();

        $occurrenceDate = $this->resolveOccurrenceDateForEnrollment($request, $course);

        if (! $course->getOccurrenceAt($occurrenceDate)) {
            return redirect()->back()->with('error', 'La data selezionata non corrisponde a un giorno di questo corso.');
        }

        if (! $course->isOccurrenceWithinSeries($occurrenceDate)) {
            return redirect()->back()->with('error', 'Le prenotazioni per questa data non sono più disponibili (fine ciclo corso).');
        }

        if ($user->enrollments()
            ->where('course_id', $course->id)
            ->whereDate('occurrence_date', $occurrenceDate)
            ->exists()) {
            return redirect()->back()->with('error', 'Sei già iscritto a questa data.');
        }

        if ($course->enrollmentCountForOccurrence($occurrenceDate) >= $course->capacity) {
            return redirect()->back()->with('error', 'Spiacenti, non ci sono posti disponibili per questa data.');
        }

        if (! $course->isEnrollmentOpenForOccurrenceDate($occurrenceDate)) {
            return redirect()->back()->with('error', 'Le prenotazioni sono chiuse: il corso sta per iniziare.');
        }

        $user->enrollments()->create([
            'course_id' => $course->id,
            'occurrence_date' => $occurrenceDate->format('Y-m-d'),
        ]);

        return redirect()->route('client.dashboard')->with('success', 'Prenotazione effettuata con successo! Ti aspettiamo in sala.');
    }

    /**
     * Consente all'utente di annullare una prenotazione per una occorrenza
     */
    public function cancelBooking(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = Auth::user();

        $occurrenceDate = $this->resolveOccurrenceDateForCancel($request, $course);

        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->whereDate('occurrence_date', $occurrenceDate)
            ->first();

        if (! $enrollment) {
            return redirect()->back()->with('error', 'Nessuna prenotazione trovata per questa data.');
        }

        if (! $course->isCancellationOpenForOccurrenceDate($occurrenceDate)) {
            return redirect()->back()->with('error', 'Non è più possibile annullare: il corso sta per iniziare.');
        }

        $enrollment->delete();

        return redirect()->back()->with('success', 'Prenotazione annullata.');
    }

    private function resolveOccurrenceDateForEnrollment(Request $request, Course $course): Carbon
    {
        if ($course->first_occurrence_date && ! $course->is_repeatable) {
            return $course->first_occurrence_date->copy()->startOfDay();
        }

        $request->validate([
            'occurrence_date' => 'required|date',
        ]);

        return Carbon::parse($request->occurrence_date)->startOfDay();
    }

    private function resolveOccurrenceDateForCancel(Request $request, Course $course): Carbon
    {
        $raw = $request->query('occurrence_date') ?? $request->input('occurrence_date');
        if ($raw !== null && $raw !== '') {
            return Carbon::parse($raw)->startOfDay();
        }

        if ($course->first_occurrence_date && ! $course->is_repeatable) {
            return $course->first_occurrence_date->copy()->startOfDay();
        }

        $request->validate([
            'occurrence_date' => 'required|date',
        ]);

        return Carbon::parse($request->occurrence_date)->startOfDay();
    }
}
