<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'ministry_id',
        'subject',
        'content',
        'message_type',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the ministry associated with the message.
     */
    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include personal messages.
     */
    public function scopePersonal($query)
    {
        return $query->where('message_type', 'personal');
    }

    /**
     * Scope a query to only include ministry messages.
     */
    public function scopeMinistry($query)
    {
        return $query->where('message_type', 'ministry');
    }

    /**
     * Scope a query to only include broadcast messages.
     */
    public function scopeBroadcast($query)
    {
        return $query->where('message_type', 'broadcast');
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
