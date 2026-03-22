<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOccurrenceSnapshot extends Model
{
    protected $fillable = [
        'course_id',
        'occurrence_date',
        'enrolled_count',
    ];

    protected function casts(): array
    {
        return [
            'occurrence_date' => 'date',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope per filtrare per mese (year, month).
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        return $query->whereBetween('occurrence_date', [$start, $end]);
    }
}
