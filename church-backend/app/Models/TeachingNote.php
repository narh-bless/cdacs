<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'scripture_references',
        'teaching_date',
        'teacher_id',
        'file_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'teaching_date' => 'date',
        ];
    }

    /**
     * Get the teacher of the teaching note.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Scope a query to only include published teaching notes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft teaching notes.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived teaching notes.
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
        return $query->whereBetween('teaching_date', [$startDate, $endDate]);
    }
}
