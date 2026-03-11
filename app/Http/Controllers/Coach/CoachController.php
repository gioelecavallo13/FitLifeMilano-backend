<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    /**
     * Dashboard Coach
     */
    public function index()
    {
        $courses = Auth::user()->createdCourses()->withCount('users')->get();
        $breadcrumb = [['label' => 'Dashboard', 'url' => null]];
        $unreadMessagesCount = Auth::user()->unreadMessagesCount();
        return view('coach.dashboard', compact('courses', 'breadcrumb', 'unreadMessagesCount'));
    }

    /**
     * Lista corsi del coach
     */
    public function coursesIndex()
    {
        $courses = Auth::user()->createdCourses()->withCount('users')->get();
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
            ['label' => 'I miei corsi', 'url' => null],
        ];
        return view('coach.courses.index', compact('courses', 'breadcrumb'));
    }

    /**
     * Dettaglio corso + utenti prenotati
     */
    public function courseShow($id)
    {
        $course = Course::with(['coach', 'users'])->where('user_id', Auth::id())->findOrFail($id);
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
            ['label' => 'I miei corsi', 'url' => route('coach.courses.index')],
            ['label' => $course->name, 'url' => null],
        ];
        return view('coach.courses.show', compact('course', 'breadcrumb'));
    }

    /**
     * Anagrafica cliente (sola lettura); solo se iscritto ad almeno un corso del coach
     */
    public function clientShow($id)
    {
        $user = User::where('role', 'client')->findOrFail($id);
        $coachCourseIds = Auth::user()->createdCourses()->pluck('id');
        if (!$user->courses()->whereIn('courses.id', $coachCourseIds)->exists()) {
            abort(403);
        }

        $from = request('from');
        $courseId = request('course_id');
        if ($from === 'course' && $courseId) {
            $course = Course::where('user_id', Auth::id())->find($courseId);
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
                ['label' => 'I miei corsi', 'url' => route('coach.courses.index')],
                ['label' => $course ? $course->name : 'Corso', 'url' => $course ? route('coach.courses.show', $course->id) : null],
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        } else {
            $breadcrumb = [
                ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
                ['label' => 'I miei corsi', 'url' => route('coach.courses.index')],
                ['label' => $user->first_name . ' ' . $user->last_name, 'url' => null],
            ];
        }

        return view('coach.clients.show', compact('user', 'breadcrumb'));
    }

    /**
     * Messaggistica (solo UI placeholder)
     */
    public function messages()
    {
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
            ['label' => 'Messaggi', 'url' => null],
        ];
        return view('coach.messages.index', compact('breadcrumb'));
    }
}
