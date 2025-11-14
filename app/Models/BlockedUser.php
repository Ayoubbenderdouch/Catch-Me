<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'reason',
    ];

    /**
     * Get the user who blocked
     */
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * Get the blocked user
     */
    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    /**
     * Check if user1 has blocked user2
     *
     * @param int $blockerId
     * @param int $blockedId
     * @return bool
     */
    public static function isBlocked(int $blockerId, int $blockedId): bool
    {
        return self::where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->exists();
    }

    /**
     * Check if either user has blocked the other
     *
     * @param int $user1Id
     * @param int $user2Id
     * @return bool
     */
    public static function isBlockedEitherWay(int $user1Id, int $user2Id): bool
    {
        return self::where(function ($query) use ($user1Id, $user2Id) {
            $query->where('blocker_id', $user1Id)
                ->where('blocked_id', $user2Id);
        })->orWhere(function ($query) use ($user1Id, $user2Id) {
            $query->where('blocker_id', $user2Id)
                ->where('blocked_id', $user1Id);
        })->exists();
    }
}
