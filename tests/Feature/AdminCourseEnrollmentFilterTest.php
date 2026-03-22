<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseEnrollmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin-filter-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createCoach(): User
    {
        return User::create([
            'first_name' => 'Coach',
            'last_name' => 'Test',
            'email' => 'coach-filter-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    private function createClient(string $emailSuffix): User
    {
        return User::create([
            'first_name' => 'Client',
            'last_name' => $emailSuffix,
            'email' => 'client-'.$emailSuffix.'-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    public function test_repeatable_course_shows_both_enrollments_without_filter(): void
    {
        $admin = $this->createAdmin();
        $coach = $this->createCoach();
        $clientA = $this->createClient('A');
        $clientB = $this->createClient('B');

        $course = Course::create([
            'name' => 'Zumba Filter',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 30,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 30,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
        ]);

        CourseEnrollment::create([
            'user_id' => $clientA->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-23',
        ]);
        CourseEnrollment::create([
            'user_id' => $clientB->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-04-13',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.courses.show', $course->id));

        $response->assertOk();
        $response->assertSee($clientA->email, false);
        $response->assertSee($clientB->email, false);
        $response->assertSee('Utenti prenotati (2)', false);
        $response->assertSee('Filtra per giorno lezione', false);
    }

    public function test_repeatable_course_filter_shows_only_selected_day(): void
    {
        $admin = $this->createAdmin();
        $coach = $this->createCoach();
        $clientA = $this->createClient('A');
        $clientB = $this->createClient('B');

        $course = Course::create([
            'name' => 'Zumba Filter',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 30,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 30,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
        ]);

        CourseEnrollment::create([
            'user_id' => $clientA->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-23',
        ]);
        CourseEnrollment::create([
            'user_id' => $clientB->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-04-13',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.courses.show', $course->id).'?occurrence_date=2026-03-23'
        );

        $response->assertOk();
        $response->assertSee($clientA->email, false);
        $response->assertDontSee($clientB->email, false);
        $response->assertSee('Utenti prenotati (1)', false);
    }

    public function test_non_repeatable_course_ignores_occurrence_date_query(): void
    {
        $admin = $this->createAdmin();
        $coach = $this->createCoach();
        $client = $this->createClient('S');

        $course = Course::create([
            'name' => 'Singolo',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 20,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-05-01',
            'is_repeatable' => false,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'capacity' => 10,
            'booking_deadline_time' => '09:50',
            'cancellation_deadline_time' => '09:50',
        ]);

        CourseEnrollment::create([
            'user_id' => $client->id,
            'course_id' => $course->id,
            'occurrence_date' => '2026-05-01',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.courses.show', $course->id).'?occurrence_date=2026-05-01'
        );

        $response->assertOk();
        $response->assertSee($client->email, false);
        $response->assertSee('Utenti prenotati (1)', false);
        $response->assertDontSee('Filtra per giorno lezione', false);
    }
}
