<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'tenant';

    protected $table = "plan";
    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'vrtour_id');
    }
}
