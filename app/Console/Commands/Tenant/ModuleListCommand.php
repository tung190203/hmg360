<?php

namespace App\Console\Commands\Tenant;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'tenant:module-list {tenant?}';

    protected $description = 'List registered tenant modules.';

    public function handle(): int
    {
        $query = Module::with('tenant')->orderBy('tenant_id')->orderBy('sort_order');

        if ($slug = $this->argument('tenant')) {
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            $query->where('tenant_id', $tenant->id);
        }

        $this->table(
            ['Tenant', 'Slug', 'Name', 'Path', 'Enabled'],
            $query->get()->map(fn (Module $module) => [
                $module->tenant?->slug,
                $module->slug,
                $module->name,
                $module->path,
                $module->is_enabled ? 'yes' : 'no',
            ])->all()
        );

        return self::SUCCESS;
    }
}
