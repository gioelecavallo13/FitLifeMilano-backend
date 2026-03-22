<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseOccurrenceSetting;
use App\Models\User;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index()
    {
        $newMessagesCount = Cache::remember('admin.new_messages_count', now()->addMinutes(2), fn () => ContactRequest::where('status', 'new')->count());
        $unreadChatCount = Cache::remember('admin.unread_chat_count.'.Auth::id(), now()->addMinutes(1), fn () => Auth::user()->unreadMessagesCount());
        $breadcrumb = [['label' => 'Dashboard', 'url' => null]];

        return view('admin.dashboard', compact('newMessagesCount', 'unreadChatCount', 'breadcrumb'));
    }

    public function calendar(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        $data = app(CalendarService::class)->getMonthData($year, $month);

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Calendario corsi', 'url' => null],
        ];
        $calendarRoute = 'admin.calendar';
        $courseShowRoute = 'admin.courses.show';

        return view('admin.calendar.index', array_merge($data, compact('breadcrumb', 'year', 'month', 'calendarRoute', 'courseShowRoute')));
    }

    /* --- FUNZIONI DI RECUPERO DATI (Interne) --- */
    private function getClientsList()
    {
        return User::where('role', 'client')->latest()->paginate(15);
    }

    private function getCoachesList()
    {
        return User::where('role', 'coach')->latest()->paginate(15);
    }

    private function getCoursesList()
    {
        return Course::with('coach')->withCount('users')->latest()->paginate(15);
    }

    /* --- GESTIONE MESSAGGI --- */
    public function messages(Request $request)
    {
        $query = ContactRequest::query();
        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $requests = $query->latest()->paginate(15)->withQueryString();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Messaggi', 'url' => null],
        ];

        return view('admin.messages.index', compact('requests', 'breadcrumb'));
    }

    public function messageShow($id)
    {
        $message = ContactRequest::findOrFail($id);
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
            Cache::forget('admin.new_messages_count');
        }
        $subject = strlen($message->subject) > 40 ? substr($message->subject, 0, 37).'...' : $message->subject;
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Messaggi', 'url' => route('admin.messages.index')],
            ['label' => $subject, 'url' => null],
        ];

        return view('admin.messages.show-message', compact('message', 'breadcrumb'));
    }

    public function messageReply(Request $request, $id)
    {
        $request->validate(['reply_text' => 'required|min:5']);
        $message = ContactRequest::findOrFail($id);
        $message->update(['status' => 'replied']);
        Cache::forget('admin.new_messages_count');
        try {
            $emailData = ['subject' => $message->subject, 'replyText' => $request->reply_text, 'first_name' => $message->first_name];
            Mail::send('emails.contact-response', $emailData, function ($mail) use ($message) {
                $mail->to($message->email)->subject('Risposta FitLife Milano: '.$message->subject);
            });

            return redirect()->route('admin.messages.index')->with('success', 'Risposta inviata!');
        } catch (\Exception $e) {
            return redirect()->route('admin.messages.index')->with('error', 'Invio email fallito.');
        }
    }

    /* --- GESTIONE COACH --- */
    public function createCoach()
    {
        $coaches = $this->getCoachesList(); // Carica i coach per la tabella a destra
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Coach', 'url' => null],
        ];

        return view('admin.coaches.create', compact('coaches', 'breadcrumb'));
    }

    public function storeCoach(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'coach',
        ]);

        return redirect()->route('admin.coaches.create')->with('success', 'Coach inserito correttamente!');
    }

    /* --- GESTIONE CLIENTI --- */
    public function createClient()
    {
        $clients = $this->getClientsList();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Clienti', 'url' => null],
        ];

        return view('admin.clients.create', compact('clients', 'breadcrumb'));
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name.' '.$request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
        ]);

        return redirect()->route('admin.clients.create')->with('success', 'Cliente registrato correttamente!');
    }

    /* --- GESTIONE CORSI --- */
    public function courseCreate()
    {
        $coaches = User::where('role', 'coach')->orderBy('first_name')->orderBy('last_name')->get();
        $courses = $this->getCoursesList();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Corsi', 'url' => null],
        ];

        return view('admin.courses.create', compact('coaches', 'courses', 'breadcrumb'));
    }

    public function courseShow(Request $request, $id)
    {
        $course = Course::with(['coach', 'enrollments.user', 'occurrenceSettings'])->findOrFail($id);

        $allEnrollments = $course->enrollments;
        $lessonDatesForFilter = $allEnrollments
            ->map(fn ($e) => $e->occurrence_date->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $enrollments = $allEnrollments;
        $selectedOccurrenceDate = null;

        if ($course->is_repeatable && $request->filled('occurrence_date')) {
            $validated = $request->validate([
                'occurrence_date' => 'required|date',
            ]);
            $filterKey = Carbon::parse($validated['occurrence_date'])->format('Y-m-d');
            if (in_array($filterKey, $lessonDatesForFilter, true)) {
                $enrollments = $allEnrollments->filter(
                    fn ($e) => $e->occurrence_date->format('Y-m-d') === $filterKey
                );
                $selectedOccurrenceDate = $filterKey;
            }
        }

        $enrollmentsByDate = $enrollments
            ->sortBy('occurrence_date')
            ->groupBy(fn ($e) => $e->occurrence_date->format('Y-m-d'));

        $enrollmentsFilteredCount = $enrollments->count();

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Corsi', 'url' => route('admin.courses.create')],
            ['label' => $course->name, 'url' => null],
        ];

        $repeatableScheduleRows = [];
        if ($course->is_repeatable) {
            $from = now()->startOfDay();
            $to = now()->copy()->addMonths(3)->startOfDay();
            if ($course->last_lesson_date) {
                $cap = $course->last_lesson_date->copy()->startOfDay();
                if ($cap->lt($to)) {
                    $to = $cap;
                }
            }
            foreach ($course->occurrenceDatesBetween($from, $to) as $day) {
                $repeatableScheduleRows[] = [
                    'day' => $day,
                    'dateKey' => $day->format('Y-m-d'),
                    'has_override' => $course->hasOccurrenceOverrideForDate($day),
                    'start' => $course->getEffectiveStartTimeForDate($day),
                    'end' => $course->getEffectiveEndTimeForDate($day),
                    'booking_deadline' => $course->getEffectiveBookingDeadlineTimeForDate($day),
                    'cancel_deadline' => $course->getEffectiveCancellationDeadlineTimeForDate($day),
                ];
            }
        }

        return view('admin.courses.show', compact(
            'course',
            'enrollmentsByDate',
            'breadcrumb',
            'lessonDatesForFilter',
            'selectedOccurrenceDate',
            'enrollmentsFilteredCount',
            'repeatableScheduleRows'
        ));
    }

    public function courseOccurrenceEdit(Course $course, string $occurrenceDate)
    {
        if (! $course->is_repeatable) {
            abort(404);
        }

        $day = Carbon::parse($occurrenceDate)->startOfDay();
        if (! $course->getOccurrenceAt($day)) {
            abort(404);
        }

        $course->load('occurrenceSettings');

        $defaults = [
            'start_time' => Carbon::parse($course->start_time)->format('H:i'),
            'end_time' => Carbon::parse($course->end_time)->format('H:i'),
            'booking_deadline_time' => Carbon::parse($course->booking_deadline_time)->format('H:i'),
            'cancellation_deadline_time' => Carbon::parse($course->cancellation_deadline_time)->format('H:i'),
        ];

        $effective = [
            'start_time' => Carbon::parse($course->getEffectiveStartTimeForDate($day))->format('H:i'),
            'end_time' => Carbon::parse($course->getEffectiveEndTimeForDate($day))->format('H:i'),
            'booking_deadline_time' => $course->getEffectiveBookingDeadlineTimeForDate($day)
                ? Carbon::parse($course->getEffectiveBookingDeadlineTimeForDate($day))->format('H:i')
                : $defaults['booking_deadline_time'],
            'cancellation_deadline_time' => $course->getEffectiveCancellationDeadlineTimeForDate($day)
                ? Carbon::parse($course->getEffectiveCancellationDeadlineTimeForDate($day))->format('H:i')
                : $defaults['cancellation_deadline_time'],
        ];

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Corsi', 'url' => route('admin.courses.create')],
            ['label' => $course->name, 'url' => route('admin.courses.show', $course->id)],
            ['label' => 'Modifica lezione '.$day->format('d/m/Y'), 'url' => null],
        ];

        return view('admin.courses.occurrence-edit', compact('course', 'day', 'defaults', 'effective', 'breadcrumb'));
    }

    public function courseOccurrenceUpdate(Request $request, Course $course, string $occurrenceDate)
    {
        if (! $course->is_repeatable) {
            abort(404);
        }

        $day = Carbon::parse($occurrenceDate)->startOfDay();
        if (! $course->getOccurrenceAt($day)) {
            abort(404);
        }

        $defaults = [
            'start_time' => Carbon::parse($course->start_time)->format('H:i'),
            'end_time' => Carbon::parse($course->end_time)->format('H:i'),
            'booking_deadline_time' => Carbon::parse($course->booking_deadline_time)->format('H:i'),
            'cancellation_deadline_time' => Carbon::parse($course->cancellation_deadline_time)->format('H:i'),
        ];

        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'booking_deadline_time' => 'required|date_format:H:i',
            'cancellation_deadline_time' => 'required|date_format:H:i',
        ]);

        $effStart = $validated['start_time'];
        if (Carbon::parse($validated['booking_deadline_time'])->gt(Carbon::parse($effStart))) {
            return back()->withErrors(['booking_deadline_time' => 'La scadenza prenotazione deve essere <= orario di inizio lezione.'])->withInput();
        }
        if (Carbon::parse($validated['cancellation_deadline_time'])->gt(Carbon::parse($effStart))) {
            return back()->withErrors(['cancellation_deadline_time' => 'La scadenza annullamento deve essere <= orario di inizio lezione.'])->withInput();
        }

        $norm = static fn (string $t): string => Carbon::parse($t)->format('H:i');

        $row = [];
        foreach (['start_time', 'end_time', 'booking_deadline_time', 'cancellation_deadline_time'] as $field) {
            $row[$field] = $norm($validated[$field]) === $norm($defaults[$field]) ? null : $validated[$field];
        }

        if (collect($row)->every(fn ($v) => $v === null)) {
            $course->occurrenceSettings()->whereDate('occurrence_date', $day->format('Y-m-d'))->delete();
        } else {
            CourseOccurrenceSetting::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'occurrence_date' => $day->format('Y-m-d'),
                ],
                $row
            );
        }

        return redirect()->route('admin.courses.show', $course->id)->with('success', 'Impostazioni della lezione aggiornate.');
    }

    public function courseStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:0',
            'first_occurrence_date' => 'required|date',
            'is_repeatable' => 'boolean',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|integer|min:1',
            'booking_deadline_time' => 'required|date_format:H:i',
            'cancellation_deadline_time' => 'required|date_format:H:i',
            'last_lesson_date' => 'nullable|date',
            'client_cancellations_close_on' => 'nullable|date',
        ]);
        $validated['is_repeatable'] = (bool) ($request->input('is_repeatable', false));
        $validated['last_lesson_date'] = $request->filled('last_lesson_date') ? $validated['last_lesson_date'] : null;
        $validated['client_cancellations_close_on'] = $request->filled('client_cancellations_close_on') ? $validated['client_cancellations_close_on'] : null;

        if (! $validated['is_repeatable']) {
            $validated['last_lesson_date'] = null;
            $validated['client_cancellations_close_on'] = null;
        }

        if ($validated['last_lesson_date'] && Carbon::parse($validated['last_lesson_date'])->lt(Carbon::parse($validated['first_occurrence_date'])->startOfDay())) {
            return back()->withErrors(['last_lesson_date' => 'L’ultima lezione non può essere precedente alla prima occorrenza.'])->withInput();
        }
        if ($validated['last_lesson_date'] && $validated['client_cancellations_close_on']
            && Carbon::parse($validated['client_cancellations_close_on'])->startOfDay()->gt(Carbon::parse($validated['last_lesson_date'])->startOfDay())) {
            return back()->withErrors(['client_cancellations_close_on' => 'L’ultimo giorno per le disdette non può essere successivo all’ultima lezione.'])->withInput();
        }

        // Vincolo di coerenza: le chiusure devono essere <= orario di inizio.
        if (Carbon::parse($validated['booking_deadline_time'])->gt(Carbon::parse($validated['start_time']))) {
            return back()->withErrors(['booking_deadline_time' => 'La scadenza prenotazione deve essere <= orario di inizio.'])->withInput();
        }
        if (Carbon::parse($validated['cancellation_deadline_time'])->gt(Carbon::parse($validated['start_time']))) {
            return back()->withErrors(['cancellation_deadline_time' => 'La scadenza annullamento deve essere <= orario di inizio.'])->withInput();
        }

        Course::create($validated);

        return redirect()->route('admin.courses.create')->with('success', 'Corso aggiunto!');
    }

    public function courseEdit($id)
    {
        $course = Course::findOrFail($id);
        $coaches = User::where('role', 'coach')->get();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Corsi', 'url' => route('admin.courses.create')],
            ['label' => $course->name, 'url' => route('admin.courses.show', $course->id)],
            ['label' => 'Modifica', 'url' => null],
        ];

        return view('admin.courses.edit', compact('course', 'coaches', 'breadcrumb'));
    }

    public function courseUpdate(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:0',
            'first_occurrence_date' => 'required|date',
            'is_repeatable' => 'boolean',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|integer|min:1',
            'booking_deadline_time' => 'required|date_format:H:i',
            'cancellation_deadline_time' => 'required|date_format:H:i',
            'last_lesson_date' => 'nullable|date',
            'client_cancellations_close_on' => 'nullable|date',
        ]);
        $validated['is_repeatable'] = (bool) ($request->input('is_repeatable', false));
        $validated['last_lesson_date'] = $request->filled('last_lesson_date') ? $validated['last_lesson_date'] : null;
        $validated['client_cancellations_close_on'] = $request->filled('client_cancellations_close_on') ? $validated['client_cancellations_close_on'] : null;

        if (! $validated['is_repeatable']) {
            $validated['last_lesson_date'] = null;
            $validated['client_cancellations_close_on'] = null;
        }

        if ($validated['last_lesson_date'] && Carbon::parse($validated['last_lesson_date'])->lt(Carbon::parse($validated['first_occurrence_date'])->startOfDay())) {
            return back()->withErrors(['last_lesson_date' => 'L’ultima lezione non può essere precedente alla prima occorrenza.'])->withInput();
        }
        if ($validated['last_lesson_date'] && $validated['client_cancellations_close_on']
            && Carbon::parse($validated['client_cancellations_close_on'])->startOfDay()->gt(Carbon::parse($validated['last_lesson_date'])->startOfDay())) {
            return back()->withErrors(['client_cancellations_close_on' => 'L’ultimo giorno per le disdette non può essere successivo all’ultima lezione.'])->withInput();
        }

        if (Carbon::parse($validated['booking_deadline_time'])->gt(Carbon::parse($validated['start_time']))) {
            return back()->withErrors(['booking_deadline_time' => 'La scadenza prenotazione deve essere <= orario di inizio.'])->withInput();
        }
        if (Carbon::parse($validated['cancellation_deadline_time'])->gt(Carbon::parse($validated['start_time']))) {
            return back()->withErrors(['cancellation_deadline_time' => 'La scadenza annullamento deve essere <= orario di inizio.'])->withInput();
        }

        $wasRepeatable = $course->is_repeatable;
        $course->update($validated);

        if ($wasRepeatable && ! $validated['is_repeatable']) {
            $course->occurrenceSettings()->delete();
        }

        return redirect()->route('admin.courses.create')->with('success', 'Corso aggiornato!');
    }

    public function courseDestroy(Request $request)
    {
        $course = Course::findOrFail($request->id);
        $course->enrollments()->delete();
        $course->delete();

        return redirect()->route('admin.courses.create')->with('success', 'Corso eliminato!');
    }

    public function courseUnenroll(Request $request, $courseId, $userId)
    {
        $request->validate([
            'occurrence_date' => 'required|date',
        ]);

        Course::findOrFail($courseId);

        CourseEnrollment::query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->whereDate('occurrence_date', $request->occurrence_date)
            ->delete();

        return redirect()->back()->with('success', 'Prenotazione annullata.');
    }

    /* --- ANAGRAFICA UTENTI --- */
    public function usersIndex(Request $request)
    {
        $query = User::query();
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->latest()->paginate(15)->withQueryString();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Lista utenti', 'url' => null],
        ];

        return view('admin.users.index', compact('users', 'breadcrumb'));
    }

    public function userShow($id)
    {
        $user = User::with(['courses.coach', 'createdCourses'])->findOrFail($id);
        $from = request('from');
        $courseId = request('course_id');

        if ($from === 'course' && $courseId) {
            $course = Course::find($courseId);
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Corsi', 'url' => route('admin.courses.create')],
                ['label' => $course ? $course->name : 'Corso', 'url' => $course ? route('admin.courses.show', $course->id) : null],
                ['label' => $user->first_name.' '.$user->last_name, 'url' => null],
            ];
        } elseif ($from === 'coach') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Coach', 'url' => route('admin.coaches.create')],
                ['label' => $user->first_name.' '.$user->last_name, 'url' => null],
            ];
        } elseif ($from === 'client') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Clienti', 'url' => route('admin.clients.create')],
                ['label' => $user->first_name.' '.$user->last_name, 'url' => null],
            ];
        } else {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Lista utenti', 'url' => route('admin.users.index')],
                ['label' => $user->first_name.' '.$user->last_name, 'url' => null],
            ];
        }

        return view('admin.users.show', compact('user', 'breadcrumb'));
    }

    public function userEdit($id)
    {
        return view('admin.users.edit', ['user' => User::findOrFail($id)]);
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,coach,client',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($request->hasFile('profile_photo')) {
            $data = User::processProfilePhotoFromUpload($request->file('profile_photo'));
            $user->profile_photo = $data['profile_photo'];
            $user->profile_photo_mime = $data['profile_photo_mime'];
        }

        $user->save();

        return redirect()->route('admin.users.show', $id)->with('success', 'Utente aggiornato!');
    }

    public function userDestroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // LOGICA DI REINDIRIZZAMENTO DINAMICO
        if (str_contains(url()->previous(), 'inserisci-clienti')) {
            return redirect()->route('admin.clients.create')->with('success', 'Cliente rimosso correttamente!');
        }
        if (str_contains(url()->previous(), 'inserisci-coach')) {
            return redirect()->route('admin.coaches.create')->with('success', 'Coach rimosso correttamente!');
        }

        return redirect()->route('admin.users.index')->with('success', 'Utente rimosso!');
    }

    public function usersBulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $ids = collect($validated['ids'])
            ->unique()
            ->reject(fn ($id) => Auth::id() !== null && (int) $id === (int) Auth::id())
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Nessun utente valido selezionato per l\'eliminazione.');
        }

        $users = User::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($users as $user) {
            $user->delete();
            $deletedCount++;
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Eliminati {$deletedCount} utenti selezionati.");
    }
}
