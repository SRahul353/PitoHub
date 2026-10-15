<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'user_id',
        'domain',
        'username',
        'contact_email',
        'plan',
        'disk_limit_mb',
        'disk_used_mb',
        'bandwidth_limit_mb',
        'bandwidth_used_mb',
        'ip_address',
        'is_suspended',
        'suspension_reason',
        'suspended_at',
        'cpanel_created_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_suspended' => 'boolean',
            'disk_limit_mb' => 'integer',
            'disk_used_mb' => 'float',
            'bandwidth_limit_mb' => 'integer',
            'bandwidth_used_mb' => 'float',
            'suspended_at' => 'datetime',
            'cpanel_created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    public function databases(): HasMany
    {
        return $this->hasMany(DatabaseEntity::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(DomainEntity::class);
    }

    public function cronJobs(): HasMany
    {
        return $this->hasMany(CronJobEntity::class);
    }

    public function getDiskUsagePercentageAttribute(): float
    {
        if ($this->disk_limit_mb <= 0) {
            return 0;
        }

        return round(min(100, ($this->disk_used_mb / $this->disk_limit_mb) * 100), 1);
    }

    public function getBandwidthUsagePercentageAttribute(): float
    {
        if ($this->bandwidth_limit_mb <= 0) {
            return 0;
        }

        return round(min(100, ($this->bandwidth_used_mb / $this->bandwidth_limit_mb) * 100), 1);
    }
}
