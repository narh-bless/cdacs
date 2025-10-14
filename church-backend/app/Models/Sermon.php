<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sermon extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'scripture_references',
        'sermon_date',
        'preacher_id',
        'audio_file',
        'video_file',
        'notes_file',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sermon_date' => 'date',
        ];
    }

    /**
     * Get the preacher of the sermon.
     */
    public function preacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preacher_id');
    }

    /**
     * Scope a query to only include published sermons.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft sermons.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived sermons.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sermon_date', [$startDate, $endDate]);
    }
}
