<?php

namespace App\Core\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class TenantDatabase extends Model
{
    protected $fillable = [
        'tenant_id',
        'driver',
        'host',
        'port',
        'database_name',
        'username',
        'password',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPasswordAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
