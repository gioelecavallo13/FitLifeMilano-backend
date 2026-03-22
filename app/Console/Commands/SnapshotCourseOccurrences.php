<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\CourseOccurrenceSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SnapshotCourseOccurrences extends Command
{
    protected $signature = 'snapshot:course-occurrences {--date= : Data (Y-m-d) per cui creare gli snapshot, default: ieri}';

    protected $description = 'Registra gli snapshot delle occorrenze corsi del giorno precedente (conteggio iscritti)';

    public function handle(): int
    {
        $dateStr = $this->option('date');
        $targetDate = $dateStr
            ? Carbon::parse($dateStr)->startOfDay()
            : Carbon::yesterday()->startOfDay();

        $year = $targetDate->year;
        $month = $targetDate->month;

        $courses = Course::all();
        $count = 0;

        foreach ($courses as $course) {
            $occurrences = $course->getOccurrencesInMonth($year, $month);

            foreach ($occurrences as $occ) {
                $occurrenceDate = $occ['occurrence_date'];
                if (! $occurrenceDate->isSameDay($targetDate)) {
                    continue;
                }

                $exists = CourseOccurrenceSnapshot::where('course_id', $course->id)
                    ->where('occurrence_date', $occurrenceDate)
                    ->exists();

                if (! $exists) {
                    CourseOccurrenceSnapshot::create([
                        'course_id' => $course->id,
                        'occurrence_date' => $occurrenceDate,
                        'enrolled_count' => $course->enrollmentCountForOccurrence($occurrenceDate),
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Creati {$count} snapshot per il {$targetDate->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
