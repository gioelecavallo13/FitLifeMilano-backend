<?php

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('course_user')) {
            return;
        }

        $rows = DB::table('course_user')->get();

        foreach ($rows as $row) {
            $course = Course::find($row->course_id);
            if (! $course) {
                continue;
            }

            $created = Carbon::parse($row->created_at);

            if ($course->first_occurrence_date) {
                if (! $course->is_repeatable) {
                    $occurrenceDate = $course->first_occurrence_date->copy()->startOfDay();
                } else {
                    $next = $course->getNextOccurrence($created);
                    $occurrenceDate = $next
                        ? $next->copy()->startOfDay()
                        : $course->first_occurrence_date->copy()->startOfDay();
                }
            } else {
                $next = $course->getNextOccurrence($created);
                $occurrenceDate = $next
                    ? $next->copy()->startOfDay()
                    : $created->copy()->startOfDay();
            }

            $exists = DB::table('course_enrollments')
                ->where('user_id', $row->user_id)
                ->where('course_id', $row->course_id)
                ->whereDate('occurrence_date', $occurrenceDate->format('Y-m-d'))
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('course_enrollments')->insert([
                'user_id' => $row->user_id,
                'course_id' => $row->course_id,
                'occurrence_date' => $occurrenceDate->format('Y-m-d'),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at ?? $row->created_at,
            ]);
        }

        Schema::drop('course_user');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('course_user', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        $rows = DB::table('course_enrollments')->get();
        foreach ($rows as $row) {
            $exists = DB::table('course_user')
                ->where('user_id', $row->user_id)
                ->where('course_id', $row->course_id)
                ->exists();
            if (! $exists) {
                DB::table('course_user')->insert([
                    'user_id' => $row->user_id,
                    'course_id' => $row->course_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('course_enrollments');
    }
};
