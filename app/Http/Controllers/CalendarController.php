<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Calendario per coach: solo i propri corsi.
     */
    public function coach(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        $data = app(CalendarService::class)->getMonthData($year, $month, Auth::id());

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('coach.dashboard')],
            ['label' => 'Calendario corsi', 'url' => null],
        ];
        $calendarRoute = 'coach.calendar';
        $courseShowRoute = 'coach.courses.show';

        return view('admin.calendar.index', array_merge($data, compact('breadcrumb', 'year', 'month', 'calendarRoute', 'courseShowRoute')));
    }

    /**
     * Calendario per client: solo i corsi a cui è iscritto.
     */
    public function client(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        $data = app(CalendarService::class)->getMonthData($year, $month, null, Auth::id());

        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('client.dashboard')],
            ['label' => 'Calendario corsi', 'url' => null],
        ];
        $calendarRoute = 'client.calendar';
        $courseShowRoute = 'client.courses.show';

        return view('admin.calendar.index', array_merge($data, compact('breadcrumb', 'year', 'month', 'calendarRoute', 'courseShowRoute')));
    }
}
