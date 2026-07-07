<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'consultant_id',
        'full_name',
        'email',
        'password',
        'role',
        'permissions',
        'phone',
        'country_of_origin',
        'nationality',
        'profile_photo_url',
        'is_verified',
        'is_active',
        'last_login_at',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The permissions that belong to the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') return true; // Admin has all permissions
        
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Get the consultant associated with the student.
     */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    /**
     * Get the students associated with the consultant.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'consultant_id');
    }

    /**
     * Get the student profile associated with the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Get the schedules for the consultant.
     */
    public function consultantSchedules(): HasMany
    {
        return $this->hasMany(ConsultantSchedule::class, 'consultant_id');
    }

    /**
     * Get the appointments for the student.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'student_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
