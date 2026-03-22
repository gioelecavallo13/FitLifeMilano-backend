<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOccurrenceSetting extends Model
{
    protected $fillable = [
        'course_id',
        'occurrence_date',
        'start_time',
        'end_time',
        'booking_deadline_time',
        'cancellation_deadline_time',
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
}
