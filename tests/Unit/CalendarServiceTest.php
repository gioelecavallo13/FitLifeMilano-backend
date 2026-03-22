<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $coach;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coach = User::create([
            'first_name' => 'Test',
            'last_name' => 'Coach',
            'email' => 'coach-cal@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);
    }

    public function test_get_month_data_returns_occurrences_for_course_in_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00'));

        Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-04',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
        ]);

        $service = new CalendarService;
        $data = $service->getMonthData(2026, 3);

        $this->assertArrayHasKey('occurrences_by_date', $data);
        $this->assertArrayHasKey('month_label', $data);
        $this->assertArrayHasKey('weeks', $data);
        $this->assertStringContainsString('Marzo', $data['month_label']);
        $this->assertStringContainsString('2026', $data['month_label']);

        $wedDates = ['2026-03-04', '2026-03-11', '2026-03-18', '2026-03-25'];
        foreach ($wedDates as $dateKey) {
            $this->assertArrayHasKey($dateKey, $data['occurrences_by_date']);
            $this->assertCount(1, $data['occurrences_by_date'][$dateKey]);
            $this->assertEquals('Yoga', $data['occurrences_by_date'][$dateKey][0]['course']->name);
        }
    }

    public function test_get_month_data_with_coach_filter_shows_only_coach_courses(): void
    {
        $coach2 = User::create([
            'first_name' => 'Other',
            'last_name' => 'Coach',
            'email' => 'coach2-cal@test.com',
            'password' => bcrypt('password'),
            'role' => 'coach',
        ]);

        Course::create([
            'name' => 'Yoga',
            'description' => 'Test',
            'user_id' => $this->coach->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-04',
            'is_repeatable' => true,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'capacity' => 10,
        ]);
        Course::create([
            'name' => 'Pilates',
            'description' => 'Test',
            'user_id' => $coach2->id,
            'price' => 50,
            'first_occurrence_date' => '2026-03-04',
            'is_repeatable' => true,
            'start_time' => '19:00:00',
            'end_time' => '20:00:00',
            'capacity' => 10,
        ]);

        $service = new CalendarService;
        $data = $service->getMonthData(2026, 3, $this->coach->id);

        $occ = $data['occurrences_by_date']['2026-03-18'] ?? [];
        $courseNames = array_map(fn ($o) => $o['course']->name, $occ);
        $this->assertContains('Yoga', $courseNames);
        $this->assertNotContains('Pilates', $courseNames);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
