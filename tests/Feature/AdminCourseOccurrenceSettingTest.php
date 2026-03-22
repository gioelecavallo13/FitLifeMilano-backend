<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOccurrenceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseOccurrenceSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_occurrence_settings_for_repeatable_course(): void
    {
        $admin = User::create([
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'adm-occ-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $coach = User::create([
            'first_name' => 'C',
            'last_name' => 'D',
            'email' => 'ch-occ-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);

        $course = Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $coach->id,
            'price' => 20,
            'day_of_week' => 'Monday',
            'first_occurrence_date' => '2026-03-23',
            'is_repeatable' => true,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'capacity' => 10,
            'booking_deadline_time' => '08:50',
            'cancellation_deadline_time' => '08:50',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.courses.occurrence.update', [$course->id, '2026-03-23']), [
                'start_time' => '10:00',
                'end_time' => '11:30',
                'booking_deadline_time' => '09:30',
                'cancellation_deadline_time' => '09:45',
            ])
            ->assertRedirect(route('admin.courses.show', $course->id));

        $row = CourseOccurrenceSetting::where('course_id', $course->id)->whereDate('occurrence_date', '2026-03-23')->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->start_time);
    }

    public function test_non_repeatable_course_occurrence_edit_returns_404(): void
    {
        $admin = User::create([
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'adm-occ2-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $coach = User::create([
            'first_name' => 'C',
            'last_name' => 'D',
            'email' => 'ch-occ2-'.uniqid('', true).'@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);

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

        $this->actingAs($admin)
            ->get(route('admin.courses.occurrence.edit', [$course->id, '2026-05-01']))
            ->assertNotFound();
    }
}
