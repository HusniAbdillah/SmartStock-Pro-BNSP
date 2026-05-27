<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $unreadNotifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $readNotifications
 *
 * @method bool isAdmin()
 * @method bool isManagerGudang()
 * @method bool isStafGudang()
 * @method bool isViewer()
 * @method bool canModify()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isManagerGudang(): bool
    {
        return $this->role === 'Manajer Gudang';
    }

    public function isStafGudang(): bool
    {
        return $this->role === 'Staf Gudang';
    }

    public function isViewer(): bool
    {
        return $this->role === 'Viewer';
    }

    public function canModify(): bool
    {
        return in_array($this->role, ['Admin', 'Manajer Gudang', 'Staf Gudang']);
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            'Admin'          => 'bg-red-100 text-red-800',
            'Manajer Gudang' => 'bg-amber-100 text-amber-800',
            'Staf Gudang'    => 'bg-blue-100 text-blue-800',
            'Viewer'         => 'bg-slate-100 text-slate-600',
            default          => 'bg-slate-100 text-slate-600',
        };
    }

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'operator_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
