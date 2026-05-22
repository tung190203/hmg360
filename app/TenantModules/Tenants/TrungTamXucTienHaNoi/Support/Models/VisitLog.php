<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'path',
        'is_bot',
        'visitor_id',
    ];
}
