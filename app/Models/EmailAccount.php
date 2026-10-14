<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAccount extends Model
{
    use HasFactory;

    protected $table = 'hosting_email_accounts';

    protected $fillable = [
        'hosting_account_id',
        'email',
        'login',
        'domain',
        'quota_mb',
        'disk_used_mb',
        'suspended_login',
        'suspended_incoming',
    ];

    protected function casts(): array
    {
        return [
            'quota_mb' => 'integer',
            'disk_used_mb' => 'float',
            'suspended_login' => 'boolean',
            'suspended_incoming' => 'boolean',
        ];
    }

    public function hostingAccount(): BelongsTo
    {
        return $this->belongsTo(HostingAccount::class);
    }
}
