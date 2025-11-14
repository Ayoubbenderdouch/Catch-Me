<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'metadata',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the admin who performed the action.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Create a log entry.
     */
    public static function log(int $adminId, string $action, string $description, ?array $metadata = null): void
    {
        self::create([
            'admin_id' => $adminId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
