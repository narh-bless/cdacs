<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ministry_id',
        'amount',
        'type',
        'payment_method',
        'reference_number',
        'notes',
        'contribution_date',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contribution_date' => 'date',
        ];
    }

    /**
     * Get the user who made the contribution.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ministry associated with the contribution.
     */
    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * Get the user who recorded the contribution.
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope a query to only include tithes.
     */
    public function scopeTithes($query)
    {
        return $query->where('type', 'tithe');
    }

    /**
     * Scope a query to only include offerings.
     */
    public function scopeOfferings($query)
    {
        return $query->where('type', 'offering');
    }

    /**
     * Scope a query to only include donations.
     */
    public function scopeDonations($query)
    {
        return $query->where('type', 'donation');
    }

    /**
     * Scope a query to only include special contributions.
     */
    public function scopeSpecial($query)
    {
        return $query->where('type', 'special');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('contribution_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by ministry.
     */
    public function scopeByMinistry($query, $ministryId)
    {
        return $query->where('ministry_id', $ministryId);
    }
}
