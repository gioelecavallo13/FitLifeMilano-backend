<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasBookingDeadlineTime = Schema::hasColumn('courses', 'booking_deadline_time');
        $hasCancellationDeadlineTime = Schema::hasColumn('courses', 'cancellation_deadline_time');

        $hasBookingDeadlineMinutes = Schema::hasColumn('courses', 'booking_deadline_minutes');
        $hasCancellationDeadlineMinutes = Schema::hasColumn('courses', 'cancellation_deadline_minutes');

        if (! $hasBookingDeadlineTime) {
            Schema::table('courses', function (Blueprint $table) {
                $table->time('booking_deadline_time')->nullable()->after('start_time');
            });
        }

        if (! $hasCancellationDeadlineTime) {
            Schema::table('courses', function (Blueprint $table) {
                $table->time('cancellation_deadline_time')->nullable()->after('booking_deadline_time');
            });
        }

        // Backfill only the columns we just ensured (or already existed).
        $courses = DB::table('courses')->select(array_values(array_filter([
            'id',
            'start_time',
            $hasBookingDeadlineMinutes ? 'booking_deadline_minutes' : null,
            $hasCancellationDeadlineMinutes ? 'cancellation_deadline_minutes' : null,
        ])))->get();

        foreach ($courses as $course) {
            $start = Carbon::parse($course->start_time);

            // booking_deadline_time backfill:
            if ($hasBookingDeadlineMinutes) {
                $bookingMinutes = $course->booking_deadline_minutes ?? 30;
            } else {
                $bookingMinutes = 30;
            }
            $bookingDeadline = $start->copy()->subMinutes((int) $bookingMinutes);

            // cancellation_deadline_time backfill:
            if ($hasCancellationDeadlineMinutes) {
                $cancellationMinutes = $course->cancellation_deadline_minutes;
                if ($cancellationMinutes === null) {
                    $cancellationDeadline = $start;
                } else {
                    $cancellationDeadline = $start->copy()->subMinutes((int) $cancellationMinutes);
                }
            } else {
                // Fallback if minutes column doesn't exist.
                $cancellationDeadline = $start;
            }

            DB::table('courses')
                ->where('id', $course->id)
                ->update([
                    'booking_deadline_time' => $bookingDeadline->format('H:i'),
                    'cancellation_deadline_time' => $cancellationDeadline->format('H:i'),
                ]);
        }

        // Optional alignment: drop legacy minutes columns if they still exist.
        if ($hasBookingDeadlineMinutes || $hasCancellationDeadlineMinutes) {
            Schema::table('courses', function (Blueprint $table) use ($hasBookingDeadlineMinutes, $hasCancellationDeadlineMinutes) {
                if ($hasBookingDeadlineMinutes) {
                    $table->dropColumn('booking_deadline_minutes');
                }
                if ($hasCancellationDeadlineMinutes) {
                    $table->dropColumn('cancellation_deadline_minutes');
                }
            });
        }
    }

    public function down(): void
    {
        // No-op: this migration is meant as a forward-only recovery step.
    }
};
