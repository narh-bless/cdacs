<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ministry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'leader_name',
        'leader_id',
        'status',
    ];

    /**
     * Get the leader of the ministry.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the members of the ministry.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ministry_members')
                    ->withPivot('role', 'joined_date')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active ministries.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive ministries.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the number of members in the ministry.
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }
}
