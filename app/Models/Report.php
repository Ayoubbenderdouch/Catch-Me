<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who made the report.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user who was reported.
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the admin who reviewed the report.
     */
    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Scope to get pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get reviewed reports.
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['reviewed', 'actioned']);
    }

    /**
     * Mark report as reviewed.
     */
    public function markAsReviewed(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}
