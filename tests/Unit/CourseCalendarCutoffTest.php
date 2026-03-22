<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCalendarCutoffTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coach = User::create([
            'first_name' => 'Test',
            'last_name' => 'Coach',
            'email' => 'coach-cutoff@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    public function test_occurrence_dates_between_excludes_dates_after_last_lesson_date(): void
    {
        $course = Course::create([
            'name' => 'Test',
            'description' => 'd',
            'user_id' => $this->coach->id,
            'price' => 10,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
            'last_lesson_date' => '2026-04-06',
        ]);

        $from = Carbon::parse('2026-03-23')->startOfDay();
        $to = Carbon::parse('2026-12-31')->startOfDay();
        $dates = $course->occurrenceDatesBetween($from, $to);
        $keys = array_map(fn (Carbon $d) => $d->format('Y-m-d'), $dates);

        $this->assertContains('2026-03-23', $keys);
        $this->assertContains('2026-03-30', $keys);
        $this->assertContains('2026-04-06', $keys);
        $this->assertNotContains('2026-04-13', $keys);
    }

    public function test_get_next_occurrence_returns_null_after_last_lesson_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-10 12:00:00'));

        $course = Course::create([
            'name' => 'Test',
            'description' => 'd',
            'user_id' => $this->coach->id,
            'price' => 10,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
            'last_lesson_date' => '2026-04-06',
        ]);

        $this->assertNull($course->getNextOccurrence());
    }

    public function test_is_cancellation_open_for_occurrence_date_false_after_client_cancellations_close_on(): void
    {
        $course = Course::create([
            'name' => 'Test',
            'description' => 'd',
            'user_id' => $this->coach->id,
            'price' => 10,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
            'client_cancellations_close_on' => '2026-03-25',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-25 23:59:00'));
        $this->assertTrue($course->isCancellationOpenForOccurrenceDate(Carbon::parse('2026-03-30')));

        Carbon::setTestNow(Carbon::parse('2026-03-26 00:00:01'));
        $this->assertFalse($course->isCancellationOpenForOccurrenceDate(Carbon::parse('2026-03-30')));

        Carbon::setTestNow();
    }
}
