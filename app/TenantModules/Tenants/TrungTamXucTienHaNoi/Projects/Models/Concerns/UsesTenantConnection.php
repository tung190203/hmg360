<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns;

trait UsesTenantConnection
{
    public function getConnectionName()
    {
        return 'tenant';
    }
}
