<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mappa day_of_week (Monday, Tuesday...) alla data del primo giorno corrispondente da 2026-01-01.
     */
    private static array $dayToFirstDate = [
        'Monday' => '2026-01-05',
        'Tuesday' => '2026-01-06',
        'Wednesday' => '2026-01-07',
        'Thursday' => '2026-01-01',
        'Friday' => '2026-01-02',
        'Saturday' => '2026-01-03',
        'Sunday' => '2026-01-04',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->date('first_occurrence_date')->nullable()->after('day_of_week');
            $table->boolean('is_repeatable')->default(true)->after('first_occurrence_date');
        });

        foreach (self::$dayToFirstDate as $dayOfWeek => $date) {
            DB::table('courses')
                ->where('day_of_week', $dayOfWeek)
                ->update([
                    'first_occurrence_date' => $date,
                    'is_repeatable' => true,
                ]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY day_of_week VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE courses ALTER COLUMN day_of_week DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('day_of_week')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY day_of_week VARCHAR(255) NOT NULL');
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['first_occurrence_date', 'is_repeatable']);
        });
    }
};
