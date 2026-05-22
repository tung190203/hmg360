<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Nation extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['name', 'iso_code'];
}
