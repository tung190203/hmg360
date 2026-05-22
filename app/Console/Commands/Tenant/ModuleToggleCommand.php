<?php

namespace App\Console\Commands\Tenant;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use Illuminate\Console\Command;

class ModuleToggleCommand extends Command
{
    protected $signature = 'tenant:module-toggle {tenant} {module} {--enabled=1}';

    protected $description = 'Enable or disable a tenant module.';

    public function handle(): int
    {
        $tenant = Tenant::where('slug', $this->argument('tenant'))->firstOrFail();
        $module = Module::where('tenant_id', $tenant->id)->where('slug', $this->argument('module'))->firstOrFail();

        $module->forceFill(['is_enabled' => (bool) $this->option('enabled')])->save();

        $this->info(($module->is_enabled ? 'Enabled' : 'Disabled') . " {$module->slug} for {$tenant->slug}");

        return self::SUCCESS;
    }
}
