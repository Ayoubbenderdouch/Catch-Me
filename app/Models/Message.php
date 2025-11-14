<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'status',      // WhatsApp-style: sent, delivered, read
        'is_read',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope to get unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get messages between two users.
     */
    public function scopeBetweenUsers($query, int $user1Id, int $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)->where('receiver_id', $user2Id);
        })->orWhere(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)->where('receiver_id', $user1Id);
        })->orderBy('created_at', 'asc');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark message as delivered (two gray checkmarks).
     */
    public function markAsDelivered(): void
    {
        if ($this->status === 'sent') {
            $this->update(['status' => 'delivered']);
        }
    }

    /**
     * Check if message has been delivered.
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    /**
     * Check if message has been read.
     */
    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    /**
     * Scope to get messages by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
