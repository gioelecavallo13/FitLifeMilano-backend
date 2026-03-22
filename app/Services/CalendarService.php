<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseOccurrenceSnapshot;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Restituisce i dati del calendario per un dato mese.
     *
     * @return array{occurrences_by_date: array<string, array>, prev_month: array, next_month: array, month_label: string, weeks: array}
     */
    public function getMonthData(int $year, int $month, ?int $coachId = null, ?int $clientId = null): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $today = now()->startOfDay();

        $query = Course::with('coach');
        if ($coachId !== null) {
            $query->where('user_id', $coachId);
        }

        $courses = $query->get();

        $occurrencesByDate = [];
        $snapshots = CourseOccurrenceSnapshot::whereBetween('occurrence_date', [$start, $end])
            ->get()
            ->keyBy(fn ($s) => "{$s->course_id}_{$s->occurrence_date->format('Y-m-d')}");

        foreach ($courses as $course) {
            $courseOccurrences = $course->getOccurrencesInMonth($year, $month);

            foreach ($courseOccurrences as $occ) {
                $dateKey = $occ['occurrence_date']->format('Y-m-d');

                if ($clientId !== null) {
                    $isEnrolled = $course->enrollments()
                        ->where('user_id', $clientId)
                        ->whereDate('occurrence_date', $occ['occurrence_date']->format('Y-m-d'))
                        ->exists();
                    if (! $isEnrolled) {
                        continue;
                    }
                }

                $occurrenceDate = $occ['occurrence_date'];
                $isPast = $occurrenceDate->lt($today);

                if ($isPast) {
                    $snapshot = $snapshots->get("{$course->id}_{$dateKey}");
                    $enrolledCount = $snapshot?->enrolled_count ?? null;
                } else {
                    $enrolledCount = $course->enrollmentCountForOccurrence($occurrenceDate);
                }

                $occurrencesByDate[$dateKey][] = [
                    'course' => $course,
                    'date' => $occ['date'],
                    'occurrence_date' => $occurrenceDate,
                    'start_time' => $occ['start_time'],
                    'end_time' => $occ['end_time'],
                    'enrolled_count' => $enrolledCount,
                    'capacity' => $course->capacity,
                    'coach_name' => $course->coach
                        ? "{$course->coach->first_name} {$course->coach->last_name}"
                        : 'N/D',
                    'is_past' => $isPast,
                ];
            }
        }

        foreach ($occurrencesByDate as $dateKey => $items) {
            usort($items, fn ($a, $b) => strcmp($a['start_time'], $b['start_time']));
            $occurrencesByDate[$dateKey] = $items;
        }

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();

        $monthLabels = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
            5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
            9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre',
        ];
        $monthLabel = ($monthLabels[$month] ?? $month).' '.$year;

        $weeks = $this->buildWeeks($year, $month);

        return [
            'occurrences_by_date' => $occurrencesByDate,
            'prev_month' => ['year' => $prevMonth->year, 'month' => $prevMonth->month],
            'next_month' => ['year' => $nextMonth->year, 'month' => $nextMonth->month],
            'month_label' => $monthLabel,
            'weeks' => $weeks,
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Costruisce le settimane del mese per la griglia calendario (Lun-Dom).
     */
    private function buildWeeks(int $year, int $month): array
    {
        $start = Carbon::createFromDate($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $weeks = [];
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);

        $endOfCalendar = $end->copy()->endOfWeek(Carbon::MONDAY);
        if ($endOfCalendar->lt($end)) {
            $endOfCalendar->addWeek();
        }

        while ($current->lte($endOfCalendar)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $current->copy();
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }
}
