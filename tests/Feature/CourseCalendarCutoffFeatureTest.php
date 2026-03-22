<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCalendarCutoffFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createCoach(): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'Coach',
            'email' => uniqid('coach_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    private function createClient(): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => uniqid('client_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    public function test_enroll_rejected_for_occurrence_after_last_lesson_date(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
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

        $this->actingAs($client);
        Carbon::setTestNow(Carbon::parse('2026-03-23 08:00:00'));

        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-04-13',
        ])
            ->assertSessionHas('error');

        $this->assertFalse(
            CourseEnrollment::where('user_id', $client->id)->where('course_id', $course->id)->exists()
        );

        Carbon::setTestNow();
    }

    public function test_client_cancel_rejected_after_client_cancellations_close_on(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
            'client_cancellations_close_on' => '2026-03-24',
        ]);

        CourseEnrollment::create([
            'user_id' => $client->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-30',
        ]);

        $this->actingAs($client);
        Carbon::setTestNow(Carbon::parse('2026-03-25 10:00:00'));

        $this->delete(route('client.cancel', $course->id).'?occurrence_date=2026-03-30')
            ->assertSessionHas('error');

        $this->assertTrue(
            CourseEnrollment::where('user_id', $client->id)->where('course_id', $course->id)->exists()
        );

        Carbon::setTestNow();
    }
}
