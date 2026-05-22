<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['guest_id', 'interestable_id', 'interestable_type'];

    public function interestable()
    {
        return $this->morphTo();
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
