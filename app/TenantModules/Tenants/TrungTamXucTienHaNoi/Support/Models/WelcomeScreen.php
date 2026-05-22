<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;

class WelcomeScreen extends Model
{
    protected $connection = 'tenant';

    protected $table = "welcome_screen";
}
