<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coach = User::create([
            'first_name' => 'Test',
            'last_name' => 'Coach',
            'email' => 'coach@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    public function test_get_next_occurrence_returns_correct_date_when_course_is_later_this_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-16 10:00:00')); // Monday 10:00

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $next = $course->getNextOccurrence();
        $this->assertNotNull($next);
        $this->assertEquals('2026-03-18 18:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_get_next_occurrence_returns_today_when_same_day_and_before_start(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 10:00:00')); // Wednesday 10:00

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $next = $course->getNextOccurrence();
        $this->assertNotNull($next);
        $this->assertEquals('2026-03-18 18:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_get_next_occurrence_returns_next_week_when_same_day_but_after_start(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 19:00:00')); // Wednesday 19:00, course was 18:00

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $next = $course->getNextOccurrence();
        $this->assertNotNull($next);
        $this->assertEquals('2026-03-25 18:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_non_repeatable_course_returns_single_occurrence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-16 10:00:00'));

        $course = Course::create([
            'name' => 'Workshop',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => false,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $next = $course->getNextOccurrence();
        $this->assertNotNull($next);
        $this->assertEquals('2026-03-20 18:00:00', $next->format('Y-m-d H:i:s'));

        $occurrences = $course->getOccurrencesInMonth(2026, 3);
        $this->assertCount(1, $occurrences);
        $this->assertEquals('2026-03-20', $occurrences[0]['occurrence_date']->format('Y-m-d'));
    }

    public function test_non_repeatable_course_returns_null_after_date_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-21 10:00:00')); // Day after the single occurrence

        $course = Course::create([
            'name' => 'Workshop',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => false,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $next = $course->getNextOccurrence();
        $this->assertNull($next);
    }

    public function test_is_enrollment_open_returns_true_when_far_from_start(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 10:00:00')); // Wednesday 10:00, course at 18:00

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $this->assertTrue($course->isEnrollmentOpen());
    }

    public function test_is_enrollment_open_returns_false_when_within_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 17:45:00')); // Wednesday 17:45, course at 18:00, deadline 30 min

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
        ]);

        $this->assertFalse($course->isEnrollmentOpen());
    }

    public function test_is_cancellation_open_returns_true_when_deadline_is_start_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 17:45:00')); // 15 min before start

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
            'cancellation_deadline_time' => '18:00',
        ]);

        $this->assertTrue($course->isCancellationOpen());
    }

    public function test_is_cancellation_open_returns_false_when_within_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 17:50:00')); // 10 min before start, cancellation deadline 60 min

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '17:30',
            'cancellation_deadline_time' => '17:00',
        ]);

        $this->assertFalse($course->isCancellationOpen());
    }

    public function test_repeatable_enrollment_deadline_boundary_is_inclusive(): void
    {
        // Corso a 08:30, prenotazioni consentite fino a 08:20 (ora <= 08:20 => true)
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:10:00'));

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
        ]);

        $this->assertTrue($course->isEnrollmentOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:19:59'));
        $this->assertTrue($course->isEnrollmentOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:00'));
        $this->assertTrue($course->isEnrollmentOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:30:00'));
        $this->assertFalse($course->isEnrollmentOpen());
    }

    public function test_repeatable_cancellation_deadline_boundary_is_inclusive(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:10:00'));

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        $this->assertTrue($course->isCancellationOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:19:59'));
        $this->assertTrue($course->isCancellationOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:00'));
        $this->assertTrue($course->isCancellationOpen());

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:01'));
        $this->assertFalse($course->isCancellationOpen());
    }

    public function test_get_booking_deadline_at_returns_expected_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:10:00'));

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        $deadlineAt = $course->getBookingDeadlineAt();
        $this->assertNotNull($deadlineAt);
        $this->assertEquals('2026-03-20 08:20:00', $deadlineAt->format('Y-m-d H:i:s'));
    }

    public function test_day_label_attribute_returns_italian_label(): void
    {
        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-18',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
        ]);

        $this->assertEquals('mercoledì', $course->day_label);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
