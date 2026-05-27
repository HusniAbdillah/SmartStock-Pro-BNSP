<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'severity',
        'message',
        'stack_trace',
        'context',
        'source',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context'     => 'array',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'created_at'  => 'datetime',
        ];
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getSeverityBadgeColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'bg-red-100 text-red-800 border-red-200',
            'warning'  => 'bg-amber-100 text-amber-800 border-amber-200',
            'info'     => 'bg-blue-100 text-blue-800 border-blue-200',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    public function getSeverityRowColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'border-l-4 border-red-500',
            'warning'  => 'border-l-4 border-amber-400',
            'info'     => 'border-l-4 border-blue-400',
            default    => '',
        };
    }

    // Scopes
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    public function scopeOfSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }
}
