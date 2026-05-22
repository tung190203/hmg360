<?php

namespace App\Console\Commands\Tenant;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantDatabaseManager;
use Illuminate\Console\Command;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'tenant:module-migrate {tenant} {module} {--force}';

    protected $description = 'Run a module migration path against a tenant database.';

    public function handle(TenantDatabaseManager $databaseManager): int
    {
        $tenant = Tenant::where('slug', $this->argument('tenant'))->firstOrFail();
        $module = Module::where('tenant_id', $tenant->id)->where('slug', $this->argument('module'))->firstOrFail();
        $migrationPath = $module->modulePath() . '/Database/migrations';

        if (! is_dir($migrationPath)) {
            $this->warn("No migrations found at {$migrationPath}");

            return self::SUCCESS;
        }

        $databaseManager->connect($tenant);

        $this->call('migrate', [
            '--database' => 'tenant',
            '--path' => str_replace(base_path() . '/', '', $migrationPath),
            '--force' => $this->option('force'),
        ]);

        return self::SUCCESS;
    }
}
