<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'gender',
        'latitude',
        'longitude',
        'is_visible',
        'account_type',
        'is_banned',
        'profile_image',
        'photos',
        'bio',
        'language',
        'fcm_token',
        'google_id',
        'apple_id',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'is_visible' => 'boolean',
            'is_banned' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'photos' => 'array',
        ];
    }

    /**
     * Get likes sent by this user.
     */
    public function likesSent()
    {
        return $this->hasMany(Like::class, 'from_user_id');
    }

    /**
     * Get likes received by this user.
     */
    public function likesReceived()
    {
        return $this->hasMany(Like::class, 'to_user_id');
    }

    /**
     * Get messages sent by this user.
     */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get reports made by this user.
     */
    public function reportsMade()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get reports against this user.
     */
    public function reportsAgainst()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    /**
     * Get notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get matches (mutual likes) for this user.
     */
    public function matches()
    {
        return $this->likesSent()
            ->where('status', 'accepted')
            ->with('toUser');
    }

    /**
     * Check if user has location data.
     */
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Scope to get visible users only (not in ghost mode).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope to get public account users only (not private).
     */
    public function scopePublicAccount($query)
    {
        return $query->where(function ($q) {
            $q->where('account_type', 'public')
              ->orWhereNull('account_type'); // Default to public if not set
        });
    }

    /**
     * Scope to get non-banned users.
     */
    public function scopeNotBanned($query)
    {
        return $query->where('is_banned', false);
    }

    /**
     * Scope to get active users (active within last 30 minutes).
     */
    public function scopeActive($query, $minutes = 30)
    {
        return $query->where('last_active_at', '>=', now()->subMinutes($minutes));
    }
}
