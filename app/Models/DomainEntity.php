<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainEntity extends Model
{
    use HasFactory;

    protected $table = 'hosting_domains';

    protected $fillable = [
        'hosting_account_id',
        'domain_name',
        'type',
        'document_root',
        'has_ssl',
    ];

    protected function casts(): array
    {
        return [
            'has_ssl' => 'boolean',
        ];
    }

    public function hostingAccount(): BelongsTo
    {
        return $this->belongsTo(HostingAccount::class);
    }
}
