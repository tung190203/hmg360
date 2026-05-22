<?php

namespace App\Console\Commands\Tenant;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    protected $signature = 'tenant:module-enable {tenant} {module}';

    protected $description = 'Enable a tenant module.';

    public function handle(): int
    {
        $tenant = Tenant::where('slug', $this->argument('tenant'))->firstOrFail();
        Module::where('tenant_id', $tenant->id)->where('slug', $this->argument('module'))->firstOrFail()
            ->forceFill(['is_enabled' => true])
            ->save();

        $this->info("Enabled {$this->argument('module')} for {$tenant->slug}");

        return self::SUCCESS;
    }
}
