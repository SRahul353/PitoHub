<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseEntity extends Model
{
    use HasFactory;

    protected $table = 'hosting_databases';

    protected $fillable = [
        'hosting_account_id',
        'database_name',
        'db_user',
        'size_mb',
    ];

    protected function casts(): array
    {
        return [
            'size_mb' => 'float',
        ];
    }

    public function hostingAccount(): BelongsTo
    {
        return $this->belongsTo(HostingAccount::class);
    }
}
