<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'group_id',
        'action',
        'description',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Helper cepat untuk mencatat aktivitas sistem
     */
    public static function log(string $action, string $description, ?array $properties = null, ?int $groupId = null, ?int $userId = null): self
    {
        return static::create([
            'user_id' => $userId ?? Auth::id(),
            'group_id' => $groupId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
