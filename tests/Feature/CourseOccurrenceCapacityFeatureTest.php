<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOccurrenceCapacityFeatureTest extends TestCase
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

    private function createClient(string $suffix): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => $suffix,
            'email' => uniqid('client_'.$suffix.'_', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    public function test_capacity_is_per_occurrence_two_different_days_both_can_book(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00'));

        $coach = $this->createCoach();
        $clientA = $this->createClient('A');
        $clientB = $this->createClient('B');

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Friday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 1,
            'booking_deadline_time' => '17:50',
            'cancellation_deadline_time' => '17:50',
        ]);

        $this->actingAs($clientA);
        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-20',
        ])->assertSessionHas('success');

        $this->actingAs($clientB);
        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-27',
        ])->assertSessionHas('success');

        $this->assertTrue(
            CourseEnrollment::where('user_id', $clientA->id)->where('course_id', $course->id)->whereDate('occurrence_date', '2026-03-20')->exists()
        );
        $this->assertTrue(
            CourseEnrollment::where('user_id', $clientB->id)->where('course_id', $course->id)->whereDate('occurrence_date', '2026-03-27')->exists()
        );
    }

    public function test_capacity_blocks_second_booking_on_same_occurrence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-13 10:00:00'));

        $coach = $this->createCoach();
        $clientA = $this->createClient('A');
        $clientB = $this->createClient('B');

        $course = Course::create([
            'name' => 'Pilates',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 50,
            'day_of_week' => 'Friday',
            'first_occurrence_date' => '2026-03-20',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 1,
            'booking_deadline_time' => '17:50',
            'cancellation_deadline_time' => '17:50',
        ]);

        $this->actingAs($clientA);
        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-20',
        ])->assertSessionHas('success');

        $this->actingAs($clientB);
        $this->post(route('client.enroll', $course->id), [
            'occurrence_date' => '2026-03-20',
        ])->assertSessionHas('error', 'Spiacenti, non ci sono posti disponibili per questa data.');
    }
}
