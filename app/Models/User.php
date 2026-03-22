<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Intervention\Image\Laravel\Facades\Image;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * I campi che possono essere popolati (Mass Assignment)
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'profile_photo',
        'profile_photo_mime',
    ];

    /**
     * I campi da nascondere nelle risposte JSON o array.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo',
    ];

    /**
     * Casting dei tipi di dati.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ACCESSOR: Nome Completo
     * Permette di usare $user->full_name nelle View senza avere una colonna 'name' nel DB.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * ACCESSOR: URL assoluto della foto profilo
     * Restituisce l'URL dell'endpoint che serve la foto dal DB, o l'immagine default.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->attributes['profile_photo'] ?? null)
                ? route('profile.photo', $this->id)
                : '/images/foto-profilo-default-media.jpg',
        );
    }

    /**
     * ACCESSOR: URL foto profilo in risoluzione piccola (per tabelle/thumbnail)
     * Stesso URL dell'immagine compressa (150x150).
     */
    protected function profilePhotoUrlSmall(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->attributes['profile_photo'] ?? null)
                ? route('profile.photo', $this->id)
                : '/images/foto-profilo-default-piccola.jpg',
        );
    }

    /**
     * Processa un file caricato: ridimensiona a 150x150, comprime in JPEG 75%.
     * Restituisce ['profile_photo' => binary, 'profile_photo_mime' => 'image/jpeg'].
     */
    public static function processProfilePhotoFromUpload(UploadedFile $file): array
    {
        $image = Image::read($file)->cover(150, 150);
        $encoded = $image->toJpeg(75);

        return [
            'profile_photo' => (string) $encoded,
            'profile_photo_mime' => 'image/jpeg',
        ];
    }

    /**
     * RELAZIONE: Iscrizioni del client (una per occorrenza)
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * RELAZIONE: I corsi a cui un CLIENTE è iscritto
     * (Molti-a-Molti tramite course_enrollments con occurrence_date)
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'user_id', 'course_id')
            ->withPivot('occurrence_date')
            ->withTimestamps()
            ->withCasts(['occurrence_date' => 'date']);
    }

    /**
     * RELAZIONE: I corsi gestiti/creati da un COACH
     * (Uno-a-Molti: un coach crea molti corsi)
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'user_id');
    }

    /**
     * Conversazioni in cui l'utente è il coach
     */
    public function conversationsAsCoach(): HasMany
    {
        return $this->hasMany(Conversation::class, 'coach_id');
    }

    /**
     * Conversazioni in cui l'utente è il client
     */
    public function conversationsAsClient(): HasMany
    {
        return $this->hasMany(Conversation::class, 'client_id');
    }

    /**
     * Conversazioni in cui l'utente è l'admin
     */
    public function conversationsAsAdmin(): HasMany
    {
        return $this->hasMany(Conversation::class, 'admin_id');
    }

    /**
     * Totale messaggi non letti per questo utente (in tutte le sue conversazioni).
     * Include conversazioni coach-client e conversazioni admin-utente (admin o other_user).
     * Query unica con subquery per evitare due round-trip al DB.
     */
    public function unreadMessagesCount(): int
    {
        $conversationIds = Conversation::where('coach_id', $this->id)
            ->orWhere('client_id', $this->id)
            ->orWhere('admin_id', $this->id)
            ->orWhere('other_user_id', $this->id)
            ->select('id');

        return Message::whereIn('conversation_id', $conversationIds)
            ->unreadBy($this->id)
            ->count();
    }
}
