<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseDeadlineFeatureTest extends TestCase
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

    public function test_enroll_is_allowed_until_booking_deadline_is_inclusive(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Wednesday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        $this->actingAs($client);

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:19:59'));
        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-20',
        ])
            ->assertSessionHas('success');
        $this->assertTrue(
            CourseEnrollment::where('user_id', $client->id)
                ->where('course_id', $course->id)
                ->whereDate('occurrence_date', '2026-03-20')
                ->exists()
        );

        $this->actingAs($client);
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:00'));
        $this->assertTrue(
            CourseEnrollment::where('user_id', $client->id)->where('course_id', $course->id)->exists()
        );
    }

    public function test_enroll_is_blocked_after_booking_deadline_boundary(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Wednesday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        $this->actingAs($client);
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:01'));

        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-20',
        ])
            ->assertSessionHas('error', 'Le prenotazioni sono chiuse: il corso sta per iniziare.');

        $this->assertDatabaseMissing('course_enrollments', [
            'user_id' => $client->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_cancel_is_allowed_until_cancellation_deadline_is_inclusive(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Wednesday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        CourseEnrollment::create([
            'user_id' => $client->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-20',
        ]);
        $this->actingAs($client);

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:19:59'));
        $this->delete(route('client.cancel', $course->id).'?occurrence_date=2026-03-20')
            ->assertSessionHas('success', 'Prenotazione annullata.');

        $this->assertDatabaseMissing('course_enrollments', [
            'user_id' => $client->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_cancel_is_blocked_after_cancellation_deadline_boundary(): void
    {
        $coach = $this->createCoach();
        $client = $this->createClient();

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Wednesday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '08:30:00',
            'end_time' => '09:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:20',
            'cancellation_deadline_time' => '08:20',
        ]);

        CourseEnrollment::create([
            'user_id' => $client->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-20',
        ]);
        $this->actingAs($client);

        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:01'));

        $this->delete(route('client.cancel', $course->id).'?occurrence_date=2026-03-20')
            ->assertSessionHas('error', 'Non è più possibile annullare: il corso sta per iniziare.');

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $client->id,
            'course_id' => $course->id,
        ]);
    }
}
