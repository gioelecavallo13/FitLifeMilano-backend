<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\CourseOccurrenceSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOccurrenceOverrideTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coach = User::create([
            'first_name' => 'Coach',
            'last_name' => 'T',
            'email' => 'c-ov-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    public function test_effective_booking_deadline_uses_override_when_set(): void
    {
        $course = Course::create([
            'name' => 'T',
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
        ]);

        $day = Carbon::parse('2026-03-23')->startOfDay();
        CourseOccurrenceSetting::create([
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-23',
            'booking_deadline_time' => '07:00:00',
        ]);

        $course->load('occurrenceSettings');

        $deadline = $course->getBookingDeadlineAtForOccurrenceDate($day);
        $this->assertNotNull($deadline);
        $this->assertSame('07:00:00', $deadline->format('H:i:s'));

        $otherMonday = Carbon::parse('2026-03-30')->startOfDay();
        $deadlineOther = $course->getBookingDeadlineAtForOccurrenceDate($otherMonday);
        $this->assertSame('08:50:00', $deadlineOther->format('H:i:s'));
    }
}
