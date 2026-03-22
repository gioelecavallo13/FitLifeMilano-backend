<?php

namespace Tests\Unit;

use App\Models\Course;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseOccurrenceDeadlineTest extends TestCase
{
    #[Test]
    public function booking_deadline_for_occurrence_date_matches_day_and_time(): void
    {
        $course = new Course([
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'booking_deadline_time' => '08:20',
        ]);
        $course->syncOriginal();

        $d = Carbon::parse('2026-03-27');
        $deadline = $course->getBookingDeadlineAtForOccurrenceDate($d);

        $this->assertNotNull($deadline);
        $this->assertSame('2026-03-27', $deadline->format('Y-m-d'));
        $this->assertSame('08:20:00', $deadline->format('H:i:s'));
    }

    #[Test]
    public function is_enrollment_open_inclusive_at_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:00'));

        $course = new Course([
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'booking_deadline_time' => '08:20',
        ]);
        $course->syncOriginal();

        $this->assertTrue($course->isEnrollmentOpenForOccurrenceDate(Carbon::parse('2026-03-20')));

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:01'));
        $this->assertFalse($course->isEnrollmentOpenForOccurrenceDate(Carbon::parse('2026-03-20')));
    }
}
