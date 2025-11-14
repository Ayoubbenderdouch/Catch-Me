<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data',
        'is_sent',
        'sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_sent' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the user this notification belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get unsent notifications.
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope to get sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }
}
