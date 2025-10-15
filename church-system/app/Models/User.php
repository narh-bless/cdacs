<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'member_since',
        'marital_status',
        'occupation',
        'emergency_contact',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'member_since' => 'date',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the announcements created by the user.
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    /**
     * Get the events organized by the user.
     */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get the contributions made by the user.
     */
    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Get the contributions recorded by the user.
     */
    public function recordedContributions()
    {
        return $this->hasMany(Contribution::class, 'recorded_by');
    }

    /**
     * Get the ministries led by the user.
     */
    public function ledMinistries()
    {
        return $this->hasMany(Ministry::class, 'leader_id');
    }

    /**
     * Get the ministries that the user belongs to.
     */
    public function ministries()
    {
        return $this->belongsToMany(Ministry::class, 'user_ministries')
                    ->withPivot('role', 'joined_date', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return $this->roles->contains($role);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdministrator()
    {
        return $this->hasRole('administrator');
    }

    /**
     * Check if the user is a pastor.
     */
    public function isPastor()
    {
        return $this->hasRole('pastor');
    }

    /**
     * Check if the user is a finance committee member.
     */
    public function isFinanceCommittee()
    {
        return $this->hasRole('finance_committee');
    }

    /**
     * Check if the user is a regular member.
     */
    public function isMember()
    {
        return $this->hasRole('user');
    }
}
