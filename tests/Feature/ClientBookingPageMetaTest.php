<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOccurrenceSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBookingPageMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_includes_parseable_occurrence_meta_with_spots_left(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-22 10:00:00', 'Europe/Rome'));

        $coach = User::create([
            'first_name' => 'Coach',
            'last_name' => 'Test',
            'email' => 'coach-booking-meta@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);

        $client = User::create([
            'first_name' => 'Client',
            'last_name' => 'Test',
            'email' => 'client-booking-meta@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);

        Course::create([
            'name' => 'Zumba Meta',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 10,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 30,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
        ]);

        $response = $this->actingAs($client)->get(route('client.booking'));

        $response->assertOk();
        $response->assertSee('data-occurrence-meta=', false);
        $response->assertSee('"spots_left"', false);
        $response->assertSee('"booking_deadline"', false);

        $html = $response->getContent();
        if (preg_match('/data-occurrence-meta=\'([^\']+)\'/', $html, $m)) {
            $json = json_decode($m[1], true);
            $this->assertIsArray($json);
            $this->assertArrayHasKey('2026-03-23', $json);
            $this->assertArrayHasKey('spots_left', $json['2026-03-23']);
            $this->assertSame(30, $json['2026-03-23']['spots_left']);
            $this->assertArrayHasKey('enrollment_open', $json['2026-03-23']);
            $this->assertSame('09:00', $json['2026-03-23']['lesson_start']);
            $this->assertSame('10:30', $json['2026-03-23']['lesson_end']);
        } else {
            $this->fail('data-occurrence-meta attribute with JSON not found');
        }

        Carbon::setTestNow();
    }

    public function test_booking_page_meta_lesson_times_reflect_course_occurrence_settings_override(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-22 10:00:00', 'Europe/Rome'));

        $coach = User::create([
            'first_name' => 'Coach',
            'last_name' => 'Test',
            'email' => 'coach-booking-lesson-ov@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);

        $client = User::create([
            'first_name' => 'Client',
            'last_name' => 'Test',
            'email' => 'client-booking-lesson-ov@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);

        $course = Course::create([
            'name' => 'Zumba Override',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 10,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 30,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
        ]);

        CourseOccurrenceSetting::create([
            'course_id' => $course->id,
            'occurrence_date' => '2026-03-23',
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
        ]);

        $response = $this->actingAs($client)->get(route('client.booking'));

        $response->assertOk();
        $html = $response->getContent();
        if (preg_match('/data-occurrence-meta=\'([^\']+)\'/', $html, $m)) {
            $json = json_decode($m[1], true);
            $this->assertIsArray($json);
            $this->assertSame('14:00', $json['2026-03-23']['lesson_start']);
            $this->assertSame('15:30', $json['2026-03-23']['lesson_end']);
            $this->assertSame('09:00', $json['2026-03-30']['lesson_start']);
            $this->assertSame('10:30', $json['2026-03-30']['lesson_end']);
        } else {
            $this->fail('data-occurrence-meta attribute with JSON not found');
        }

        Carbon::setTestNow();
    }
}
