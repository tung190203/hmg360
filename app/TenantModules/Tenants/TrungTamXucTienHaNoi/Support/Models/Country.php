<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'tenant';

    protected $table = 'countries';

    public static function getAll()
    {
        return Country::where('status', 1)->get();
    }
}
