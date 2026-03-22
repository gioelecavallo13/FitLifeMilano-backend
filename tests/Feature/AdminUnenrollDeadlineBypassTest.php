<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUnenrollDeadlineBypassTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => uniqid('admin_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createCoach(): User
    {
        return User::create([
            'first_name' => 'Coach',
            'last_name' => 'Test',
            'email' => uniqid('coach_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    private function createClient(): User
    {
        return User::create([
            'first_name' => 'Client',
            'last_name' => 'Test',
            'email' => uniqid('client_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    public function test_admin_can_unenroll_even_if_client_cancellation_is_closed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-20 08:20:01'));

        $admin = $this->createAdmin();
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

        $this->assertFalse($course->isCancellationOpenForOccurrenceDate(Carbon::parse('2026-03-20')));

        $this->actingAs($admin);

        $this->post(route('admin.courses.unenroll', [$course->id, $client->id]), [
            'occurrence_date' => '2026-03-20',
        ])
            ->assertSessionHas('success', 'Prenotazione annullata.');

        $this->assertDatabaseMissing('course_enrollments', [
            'user_id' => $client->id,
            'course_id' => $course->id,
        ]);
    }
}
