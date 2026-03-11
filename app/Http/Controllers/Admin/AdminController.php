<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $newMessagesCount = Cache::remember('admin.new_messages_count', now()->addMinutes(2), fn () => ContactRequest::where('status', 'new')->count());
        $unreadChatCount = Cache::remember('admin.unread_chat_count.' . Auth::id(), now()->addMinutes(1), fn () => Auth::user()->unreadMessagesCount());
        $breadcrumb = [['label' => 'Dashboard', 'url' => null]];
        return view('admin.dashboard', compact('newMessagesCount', 'unreadChatCount', 'breadcrumb'));
    }

    /* --- FUNZIONI DI RECUPERO DATI (Interne) --- */
    private function getClientsList() { return User::where('role', 'client')->latest()->paginate(15); }
    private function getCoachesList() { return User::where('role', 'coach')->latest()->paginate(15); }
    private function getCoursesList() { return Course::with('coach')->withCount('users')->latest()->paginate(15); }

    /* --- GESTIONE MESSAGGI --- */
    public function messages(Request $request)
    {
        $query = ContactRequest::query();
        if ($request->filled('email')) $query->where('email', 'like', '%' . $request->email . '%');
        if ($request->filled('status')) $query->where('status', $request->status);
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
        $subject = strlen($message->subject) > 40 ? substr($message->subject, 0, 37) . '...' : $message->subject;
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
            Mail::send('emails.contact-response', $emailData, function($mail) use ($message) {
                $mail->to($message->email)->subject('Risposta FitLife Milano: ' . $message->subject);
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
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'coach',
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
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'client',
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

    public function courseShow($id)
    {
        $course = Course::with(['coach', 'users'])->findOrFail($id);
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Corsi', 'url' => route('admin.courses.create')],
            ['label' => $course->name, 'url' => null],
        ];
        return view('admin.courses.show', compact('course', 'breadcrumb'));
    }

    public function courseStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:0',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|integer|min:1',
        ]);
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
        $course->update($request->all());
        return redirect()->route('admin.courses.create')->with('success', 'Corso aggiornato!');
    }

    public function courseDestroy(Request $request)
    {
        $course = Course::findOrFail($request->id);
        $course->users()->detach();
        $course->delete();
        return redirect()->route('admin.courses.create')->with('success', 'Corso eliminato!');
    }

    public function courseUnenroll($courseId, $userId)
    {
        $course = Course::findOrFail($courseId);
        $course->users()->detach($userId);
        return redirect()->back()->with('success', 'Prenotazione annullata.');
    }

    /* --- ANAGRAFICA UTENTI --- */
    public function usersIndex(Request $request)
    {
        $query = User::query();
        if ($request->filled('role')) $query->where('role', $request->role);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        } elseif ($from === 'coach') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Coach', 'url' => route('admin.coaches.create')],
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        } elseif ($from === 'client') {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Clienti', 'url' => route('admin.clients.create')],
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        } else {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Lista utenti', 'url' => route('admin.users.index')],
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        }

        return view('admin.users.show', compact('user', 'breadcrumb'));
    }

    public function userEdit($id) { return view('admin.users.edit', ['user' => User::findOrFail($id)]); }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
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
}