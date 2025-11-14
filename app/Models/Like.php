<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'status',
    ];

    /**
     * Get the user who sent the like.
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who received the like.
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Scope to get pending likes.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get accepted likes (matches).
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get rejected likes.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if this is a match (both users liked each other).
     */
    public function isMatch(): bool
    {
        if ($this->status !== 'accepted') {
            return false;
        }

        return self::where('from_user_id', $this->to_user_id)
            ->where('to_user_id', $this->from_user_id)
            ->where('status', 'accepted')
            ->exists();
    }
}
