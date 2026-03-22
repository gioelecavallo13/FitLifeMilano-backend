<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    /**
     * Campi popolabili per il corso
     */
    protected $fillable = [
        'name',
        'description',
        'user_id',      // ID del Coach
        'price',
        'day_of_week',
        'first_occurrence_date',
        'is_repeatable',
        'start_time',
        'end_time',
        'capacity',
        'booking_deadline_time',
        'cancellation_deadline_time',
        'last_lesson_date',
        'client_cancellations_close_on',
    ];

    protected $casts = [
        'first_occurrence_date' => 'date',
        'is_repeatable' => 'boolean',
        'last_lesson_date' => 'date',
        'client_cancellations_close_on' => 'date',
    ];

    /**
     * Mappa day_of_week (Monday, Tuesday...) al numero Carbon (1=Monday, 7=Sunday)
     */
    protected static array $dayOfWeekMap = [
        'Monday' => Carbon::MONDAY,
        'Tuesday' => Carbon::TUESDAY,
        'Wednesday' => Carbon::WEDNESDAY,
        'Thursday' => Carbon::THURSDAY,
        'Friday' => Carbon::FRIDAY,
        'Saturday' => Carbon::SATURDAY,
        'Sunday' => Carbon::SUNDAY,
    ];

    public function hasLastLessonDate(): bool
    {
        return $this->last_lesson_date !== null;
    }

    /**
     * L'occorrenza (giorno di calendario) rientra nella serie fino a last_lesson_date inclusa.
     */
    public function isOccurrenceWithinSeries(Carbon $occurrenceDate): bool
    {
        if (! $this->last_lesson_date) {
            return true;
        }

        return $occurrenceDate->copy()->startOfDay()->lte($this->last_lesson_date->copy()->startOfDay());
    }

    /**
     * Dopo la fine del giorno client_cancellations_close_on il cliente non può più cancellare (self-service).
     */
    public function areClientCancellationsGloballyClosed(): bool
    {
        if (! $this->client_cancellations_close_on) {
            return false;
        }

        return now()->gt($this->client_cancellations_close_on->copy()->endOfDay());
    }

    /**
     * Impostazioni per singola data di lezione (override opzionali).
     */
    public function occurrenceSettings(): HasMany
    {
        return $this->hasMany(CourseOccurrenceSetting::class);
    }

    public function getOccurrenceSettingForDate(Carbon $date): ?CourseOccurrenceSetting
    {
        if (! $this->exists) {
            return null;
        }

        $key = $date->format('Y-m-d');
        if ($this->relationLoaded('occurrenceSettings')) {
            return $this->occurrenceSettings->first(
                fn (CourseOccurrenceSetting $s) => $s->occurrence_date->format('Y-m-d') === $key
            );
        }

        return $this->occurrenceSettings()->whereDate('occurrence_date', $key)->first();
    }

    public function getEffectiveStartTimeForDate(Carbon $date): string
    {
        $s = $this->getOccurrenceSettingForDate($date);
        if ($s && $s->start_time !== null) {
            return Carbon::parse($s->start_time)->format('H:i:s');
        }

        return Carbon::parse($this->start_time)->format('H:i:s');
    }

    public function getEffectiveEndTimeForDate(Carbon $date): string
    {
        $s = $this->getOccurrenceSettingForDate($date);
        if ($s && $s->end_time !== null) {
            return Carbon::parse($s->end_time)->format('H:i:s');
        }

        return Carbon::parse($this->end_time)->format('H:i:s');
    }

    public function getEffectiveBookingDeadlineTimeForDate(Carbon $date): ?string
    {
        $s = $this->getOccurrenceSettingForDate($date);
        if ($s && $s->booking_deadline_time !== null) {
            return Carbon::parse($s->booking_deadline_time)->format('H:i:s');
        }
        if (! $this->booking_deadline_time) {
            return null;
        }

        return Carbon::parse($this->booking_deadline_time)->format('H:i:s');
    }

    public function getEffectiveCancellationDeadlineTimeForDate(Carbon $date): ?string
    {
        $s = $this->getOccurrenceSettingForDate($date);
        if ($s && $s->cancellation_deadline_time !== null) {
            return Carbon::parse($s->cancellation_deadline_time)->format('H:i:s');
        }
        if (! $this->cancellation_deadline_time) {
            return null;
        }

        return Carbon::parse($this->cancellation_deadline_time)->format('H:i:s');
    }

    /**
     * True se esiste almeno un campo override valorizzato per quel giorno.
     */
    public function hasOccurrenceOverrideForDate(Carbon $date): bool
    {
        $s = $this->getOccurrenceSettingForDate($date);
        if (! $s) {
            return false;
        }

        return $s->start_time !== null
            || $s->end_time !== null
            || $s->booking_deadline_time !== null
            || $s->cancellation_deadline_time !== null;
    }

    /**
     * Calcola la prossima occorrenza del corso (data+ora di inizio).
     * Ripetibile: occorrenze ogni 7 giorni da first_occurrence_date.
     * Non ripetibile: solo first_occurrence_date.
     */
    public function getNextOccurrence(?Carbon $from = null): ?Carbon
    {
        $from = $from ?? now();

        if ($this->first_occurrence_date) {
            $firstDay = $this->first_occurrence_date->copy()->startOfDay();
            $first = $this->first_occurrence_date->copy()->setTimeFromTimeString($this->getEffectiveStartTimeForDate($firstDay));
            if (! $this->is_repeatable) {
                if (! $this->isOccurrenceWithinSeries($this->first_occurrence_date->copy())) {
                    return null;
                }

                return $first->gte($from) ? $first : null;
            }
            $fromDate = $from->copy()->startOfDay();
            $current = $this->first_occurrence_date->copy()->startOfDay();
            while ($current->lt($fromDate)) {
                $current->addDays(7);
            }
            $effectiveStart = $this->getEffectiveStartTimeForDate($current);
            $candidate = $current->copy()->setTimeFromTimeString($effectiveStart);
            // Se l'istante corrente coincide con l'orario di inizio, la lezione deve essere quella "prossima".
            if ($candidate->gte($from)) {
                if (! $this->isOccurrenceWithinSeries($current)) {
                    return null;
                }

                return $candidate;
            }
            $current->addDays(7);
            if (! $this->isOccurrenceWithinSeries($current)) {
                return null;
            }

            return $current->copy()->setTimeFromTimeString($this->getEffectiveStartTimeForDate($current));
        }

        $targetDay = self::$dayOfWeekMap[$this->day_of_week] ?? null;
        if ($targetDay === null) {
            return null;
        }
        $candidate = $from->copy()->startOfDay();
        if ($from->dayOfWeek === $targetDay) {
            $effStart = $this->getEffectiveStartTimeForDate($candidate);
            $startTime = Carbon::parse($effStart)->format('H:i');
            $candidate->setTimeFromTimeString($effStart);
            if ($from->format('H:i') < $startTime) {
                if (! $this->isOccurrenceWithinSeries($candidate->copy()->startOfDay())) {
                    return null;
                }

                return $candidate;
            }
            $candidate->addWeek();
        } elseif ($from->dayOfWeek < $targetDay) {
            $candidate->addDays($targetDay - $from->dayOfWeek);
            $candidate->setTimeFromTimeString($this->getEffectiveStartTimeForDate($candidate));
        } else {
            $candidate->addDays(7 - $from->dayOfWeek + $targetDay);
            $candidate->setTimeFromTimeString($this->getEffectiveStartTimeForDate($candidate));
        }

        if (! $this->isOccurrenceWithinSeries($candidate->copy()->startOfDay())) {
            return null;
        }

        return $candidate;
    }

    /**
     * Restituisce tutte le occorrenze del corso in un dato mese.
     * Ogni elemento è un array con: date (Carbon), start_time, end_time, course.
     */
    public function getOccurrencesInMonth(int $year, int $month): array
    {
        if ($this->first_occurrence_date) {
            $occurrences = [];
            $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();

            if (! $this->is_repeatable) {
                $first = $this->first_occurrence_date->copy()->startOfDay();
                if ($first->gte($start) && $first->lte($end) && $this->isOccurrenceWithinSeries($first)) {
                    $effStart = $this->getEffectiveStartTimeForDate($first);
                    $occurrences[] = [
                        'date' => $first->copy()->setTimeFromTimeString($effStart),
                        'occurrence_date' => $first->copy()->startOfDay(),
                        'start_time' => $effStart,
                        'end_time' => $this->getEffectiveEndTimeForDate($first),
                        'course' => $this,
                    ];
                }

                return $occurrences;
            }

            $current = $this->first_occurrence_date->copy()->startOfDay();
            while ($current->lt($start)) {
                $current->addDays(7);
            }
            while ($current->lte($end)) {
                if (! $this->isOccurrenceWithinSeries($current)) {
                    break;
                }
                $effStart = $this->getEffectiveStartTimeForDate($current);
                $occurrences[] = [
                    'date' => $current->copy()->setTimeFromTimeString($effStart),
                    'occurrence_date' => $current->copy()->startOfDay(),
                    'start_time' => $effStart,
                    'end_time' => $this->getEffectiveEndTimeForDate($current),
                    'course' => $this,
                ];
                $current->addDays(7);
            }

            return $occurrences;
        }

        $targetDay = self::$dayOfWeekMap[$this->day_of_week] ?? null;
        if ($targetDay === null) {
            return [];
        }

        $occurrences = [];
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->dayOfWeek === $targetDay && $this->isOccurrenceWithinSeries($current)) {
                $effStart = $this->getEffectiveStartTimeForDate($current);
                $occurrences[] = [
                    'date' => $current->copy()->setTimeFromTimeString($effStart),
                    'occurrence_date' => $current->copy()->startOfDay(),
                    'start_time' => $effStart,
                    'end_time' => $this->getEffectiveEndTimeForDate($current),
                    'course' => $this,
                ];
            }
            $current->addDay();
        }

        return $occurrences;
    }

    /**
     * Restituisce la data+ora di inizio se $date è il giorno corretto per questo corso, altrimenti null.
     */
    public function getOccurrenceAt(Carbon $date): ?Carbon
    {
        if ($this->last_lesson_date && $date->copy()->startOfDay()->gt($this->last_lesson_date->copy()->startOfDay())) {
            return null;
        }

        if ($this->first_occurrence_date) {
            $dateOnly = $date->copy()->startOfDay();
            $firstOnly = $this->first_occurrence_date->copy()->startOfDay();
            if (! $this->is_repeatable) {
                return $dateOnly->eq($firstOnly)
                    ? $date->copy()->setTimeFromTimeString($this->getEffectiveStartTimeForDate($dateOnly))
                    : null;
            }
            $diffDays = $firstOnly->diffInDays($dateOnly, false);
            if ($diffDays < 0 || $diffDays % 7 !== 0) {
                return null;
            }

            return $date->copy()->setTimeFromTimeString($this->getEffectiveStartTimeForDate($dateOnly));
        }

        $targetDay = self::$dayOfWeekMap[$this->day_of_week] ?? null;
        if ($targetDay === null) {
            return null;
        }
        if ($date->dayOfWeek !== $targetDay) {
            return null;
        }

        return $date->copy()->setTimeFromTimeString($this->getEffectiveStartTimeForDate($date->copy()->startOfDay()));
    }

    /**
     * RELAZIONE: Iscrizioni per occorrenza (giorno specifico)
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Numero di iscritti per una data occorrenza (stesso giorno della lezione).
     */
    public function enrollmentCountForOccurrence(Carbon $occurrenceDate): int
    {
        return $this->enrollments()
            ->whereDate('occurrence_date', $occurrenceDate->format('Y-m-d'))
            ->count();
    }

    /**
     * Istante di chiusura prenotazioni per un giorno di lezione specifico.
     */
    public function getBookingDeadlineAtForOccurrenceDate(Carbon $occurrenceDate): ?Carbon
    {
        $start = $this->getOccurrenceAt($occurrenceDate);
        $day = $occurrenceDate->copy()->startOfDay();
        $deadlineTime = $this->getEffectiveBookingDeadlineTimeForDate($day);
        if (! $start || ! $deadlineTime) {
            return null;
        }

        return $start->copy()->startOfDay()->setTimeFromTimeString($deadlineTime);
    }

    /**
     * Istante di chiusura annullamenti per un giorno di lezione specifico.
     */
    public function getCancellationDeadlineAtForOccurrenceDate(Carbon $occurrenceDate): ?Carbon
    {
        $start = $this->getOccurrenceAt($occurrenceDate);
        $day = $occurrenceDate->copy()->startOfDay();
        $deadlineTime = $this->getEffectiveCancellationDeadlineTimeForDate($day);
        if (! $start || ! $deadlineTime) {
            return null;
        }

        return $start->copy()->startOfDay()->setTimeFromTimeString($deadlineTime);
    }

    /**
     * Prenotazione aperta per quell'occorrenza (boundary inclusivo: now <= deadlineAt).
     */
    public function isEnrollmentOpenForOccurrenceDate(Carbon $occurrenceDate): bool
    {
        if (! $this->getOccurrenceAt($occurrenceDate)) {
            return false;
        }

        $deadlineAt = $this->getBookingDeadlineAtForOccurrenceDate($occurrenceDate);
        if (! $deadlineAt) {
            return false;
        }

        return now()->lte($deadlineAt);
    }

    /**
     * Annullamento aperto per quell'occorrenza (boundary inclusivo).
     */
    public function isCancellationOpenForOccurrenceDate(Carbon $occurrenceDate): bool
    {
        if (! $this->getOccurrenceAt($occurrenceDate)) {
            return false;
        }

        if ($this->areClientCancellationsGloballyClosed()) {
            return false;
        }

        $deadlineAt = $this->getCancellationDeadlineAtForOccurrenceDate($occurrenceDate);
        if (! $deadlineAt) {
            return false;
        }

        return now()->lte($deadlineAt);
    }

    /**
     * Tutti i giorni tra $from e $to (inclusi) in cui il corso ha un'occorrenza.
     *
     * @return array<int, Carbon>
     */
    public function occurrenceDatesBetween(Carbon $from, Carbon $to): array
    {
        $dates = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        if ($this->last_lesson_date) {
            $cap = $this->last_lesson_date->copy()->startOfDay();
            if ($cap->lt($end)) {
                $end = $cap;
            }
        }

        while ($cursor->lte($end)) {
            if ($this->getOccurrenceAt($cursor)) {
                $dates[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * RELAZIONE: Snapshot delle occorrenze passate (conteggio iscritti)
     */
    public function occurrenceSnapshots(): HasMany
    {
        return $this->hasMany(CourseOccurrenceSnapshot::class);
    }

    /**
     * Verifica se le iscrizioni sono ancora aperte (non siamo troppo vicini all'inizio).
     */
    public function isEnrollmentOpen(): bool
    {
        $next = $this->getNextOccurrence();
        if (! $next) {
            return false;
        }

        $deadlineAt = $this->getBookingDeadlineAt();
        if (! $deadlineAt) {
            return false;
        }

        $now = now();

        // Boundary inclusiva: se ora == deadlineAt, prenotazioni consentite.
        return $now->lte($deadlineAt);
    }

    /**
     * Calcola l'istante di chiusura delle prenotazioni per la prossima occorrenza.
     * Se non esiste una prossima occorrenza, ritorna null.
     */
    public function getBookingDeadlineAt(?Carbon $from = null): ?Carbon
    {
        $next = $this->getNextOccurrence($from);
        if (! $next) {
            return null;
        }

        $day = $next->copy()->startOfDay();
        $deadlineTime = $this->getEffectiveBookingDeadlineTimeForDate($day);
        if (! $deadlineTime) {
            return null;
        }

        return $next->copy()->startOfDay()->setTimeFromTimeString($deadlineTime);
    }

    /**
     * Calcola l'istante di chiusura annullamenti per la prossima occorrenza.
     */
    public function getCancellationDeadlineAt(?Carbon $from = null): ?Carbon
    {
        $next = $this->getNextOccurrence($from);
        if (! $next) {
            return null;
        }

        $day = $next->copy()->startOfDay();
        $deadlineTime = $this->getEffectiveCancellationDeadlineTimeForDate($day);
        if (! $deadlineTime) {
            return null;
        }

        return $next->copy()->startOfDay()->setTimeFromTimeString($deadlineTime);
    }

    /**
     * Verifica se le cancellazioni sono ancora consentite.
     */
    public function isCancellationOpen(): bool
    {
        if ($this->areClientCancellationsGloballyClosed()) {
            return false;
        }

        $next = $this->getNextOccurrence();
        if (! $next) {
            return false;
        }

        $deadlineAt = $this->getCancellationDeadlineAt();
        if (! $deadlineAt) {
            return false;
        }
        $now = now();

        // Boundary inclusiva: se ora == deadlineAt, annullo consentito.
        return $now->lte($deadlineAt);
    }

    /**
     * Label del giorno in italiano (es. "Lunedì" invece di "Monday").
     */
    public function getDayLabelAttribute(): string
    {
        if ($this->first_occurrence_date) {
            return $this->first_occurrence_date->locale('it')->dayName;
        }

        $labels = [
            'Monday' => 'Lunedì',
            'Tuesday' => 'Martedì',
            'Wednesday' => 'Mercoledì',
            'Thursday' => 'Giovedì',
            'Friday' => 'Venerdì',
            'Saturday' => 'Sabato',
            'Sunday' => 'Domenica',
        ];

        return $labels[$this->day_of_week] ?? $this->day_of_week ?? '';
    }

    /**
     * RELAZIONE: Il Coach che tiene il corso
     * (Un corso appartiene a un solo User/Coach)
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * RELAZIONE: I Clienti iscritti al corso
     * (tramite course_enrollments, una riga per occorrenza)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'user_id')
            ->withPivot('occurrence_date')
            ->withTimestamps()
            ->withCasts(['occurrence_date' => 'date']);
    }
}
