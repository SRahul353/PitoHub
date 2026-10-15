<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'server_type',
        'ip_address',
        'port',
        'protocol',
        'username',
        'api_token',
        'password',
        'auth_type',
        'status',
        'mock_mode',
        'nameservers',
        'max_accounts',
        'last_synced_at',
        'notes',
    ];

    protected $hidden = [
        'api_token',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'mock_mode' => 'boolean',
            'nameservers' => 'array',
            'max_accounts' => 'integer',
            'last_synced_at' => 'datetime',
            'api_token' => 'encrypted',
            'password' => 'encrypted',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function getActiveAccountsCountAttribute(): int
    {
        return $this->accounts()->where('is_suspended', false)->count();
    }

    public function getSuspendedAccountsCountAttribute(): int
    {
        return $this->accounts()->where('is_suspended', true)->count();
    }

    public function getBaseUrlAttribute(): string
    {
        return "{$this->protocol}://{$this->hostname}:{$this->port}";
    }
}
