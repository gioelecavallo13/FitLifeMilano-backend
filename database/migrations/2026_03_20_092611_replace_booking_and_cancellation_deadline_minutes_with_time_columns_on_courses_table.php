<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->time('booking_deadline_time')->nullable()->after('start_time');
            $table->time('cancellation_deadline_time')->nullable()->after('booking_deadline_time');
        });

        // Backfill: convert minutes -> time-of-day based on start_time.
        $courses = DB::table('courses')->select([
            'id',
            'start_time',
            'booking_deadline_minutes',
            'cancellation_deadline_minutes',
        ])->get();

        foreach ($courses as $course) {
            $start = Carbon::parse($course->start_time);

            $bookingMinutes = $course->booking_deadline_minutes ?? 30;
            $bookingDeadline = $start->copy()->subMinutes((int) $bookingMinutes);

            // Old semantics: cancellation_deadline_minutes = null means "always allowed".
            // Backfill requested: set cancellation_deadline_time = start_time.
            if ($course->cancellation_deadline_minutes === null) {
                $cancellationDeadline = $start;
            } else {
                $cancellationDeadline = $start->copy()->subMinutes((int) $course->cancellation_deadline_minutes);
            }

            DB::table('courses')
                ->where('id', $course->id)
                ->update([
                    'booking_deadline_time' => $bookingDeadline->format('H:i'),
                    'cancellation_deadline_time' => $cancellationDeadline->format('H:i'),
                ]);
        }

        // Drop old columns after successful backfill.
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['booking_deadline_minutes', 'cancellation_deadline_minutes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('booking_deadline_minutes')->nullable()->after('capacity');
            $table->integer('cancellation_deadline_minutes')->nullable()->after('booking_deadline_minutes');
        });

        $courses = DB::table('courses')->select([
            'id',
            'start_time',
            'booking_deadline_time',
            'cancellation_deadline_time',
        ])->get();

        foreach ($courses as $course) {
            $start = Carbon::parse($course->start_time);

            $bookingDeadline = $course->booking_deadline_time
                ? Carbon::parse($course->booking_deadline_time)
                : null;

            $cancellationDeadline = $course->cancellation_deadline_time
                ? Carbon::parse($course->cancellation_deadline_time)
                : null;

            $bookingMinutes = null;
            if ($bookingDeadline) {
                $bookingMinutes = $start->diffInMinutes($bookingDeadline, false);
            }

            $cancellationMinutes = null;
            if ($cancellationDeadline) {
                // If deadline == start_time, we consider old semantics = "always allowed" => null.
                if ($cancellationDeadline->format('H:i') === $start->format('H:i')) {
                    $cancellationMinutes = null;
                } else {
                    $cancellationMinutes = $start->diffInMinutes($cancellationDeadline, false);
                }
            }

            DB::table('courses')
                ->where('id', $course->id)
                ->update([
                    'booking_deadline_minutes' => $bookingMinutes,
                    'cancellation_deadline_minutes' => $cancellationMinutes,
                ]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['booking_deadline_time', 'cancellation_deadline_time']);
        });
    }
};
