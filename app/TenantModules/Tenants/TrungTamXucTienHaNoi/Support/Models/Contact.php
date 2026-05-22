<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'project_industry_id',
        'message',
    ];

    public function projectIndustry()
    {
        return $this->belongsTo(ProjectIndustries::class, 'project_industry_id');
    }
}
