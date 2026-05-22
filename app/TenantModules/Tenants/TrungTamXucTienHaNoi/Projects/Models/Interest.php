<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use UsesTenantConnection;

    protected $table = 'interests';

    protected $guarded = [];
}
