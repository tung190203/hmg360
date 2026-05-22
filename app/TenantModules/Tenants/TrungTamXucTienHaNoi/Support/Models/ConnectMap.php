<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectMap extends Model
{
    protected $connection = 'tenant';

    protected $table = "connect_map";
}
