<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    protected $connection = 'tenant';

    protected $table = "investor";
}
