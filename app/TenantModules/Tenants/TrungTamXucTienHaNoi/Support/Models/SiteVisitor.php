<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisitor extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['ip_address', 'visit_date', 'hits'];
}
