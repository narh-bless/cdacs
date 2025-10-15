<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ministry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'leader_id',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the leader of the ministry.
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the members of the ministry.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'user_ministries')
                    ->withPivot('role', 'joined_date', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get the messages sent to this ministry.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the contributions made to this ministry.
     */
    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Scope a query to only include active ministries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get active members of the ministry.
     */
    public function activeMembers()
    {
        return $this->members()->wherePivot('is_active', true);
    }

    /**
     * Get leaders of the ministry.
     */
    public function leaders()
    {
        return $this->members()->wherePivot('role', 'leader');
    }

    /**
     * Get coordinators of the ministry.
     */
    public function coordinators()
    {
        return $this->members()->wherePivot('role', 'coordinator');
    }
}
