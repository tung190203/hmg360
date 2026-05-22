<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use UsesTenantConnection;

    protected $table = 'plan';

    protected $guarded = [];
}
