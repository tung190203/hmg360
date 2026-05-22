<?php

namespace App\Console\Commands\Tenant;

use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
        {name}
        {--slug=}
        {--db=}
        {--host=127.0.0.1}
        {--port=3306}
        {--username=}
        {--password=}
        {--driver=mysql}
        {--organizer-email=}
        {--organizer-name=Organizer}
        {--organizer-password=123456}';

    protected $description = 'Create a tenant and tenant database config in the platform database.';

    public function handle(TenantProvisioner $provisioner): int
    {
        $name = $this->argument('name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $databaseName = $this->option('db') ?: 'tenant_' . str_replace('-', '_', $slug);
        $username = $this->option('username') ?: config('database.connections.mysql.username');
        $tenant = Tenant::where('slug', $slug)->first();

        $tenant = $provisioner->provision([
            'name' => $name,
            'slug' => $slug,
            'status' => Tenant::STATUS_ACTIVE,
            'database' => [
                'driver' => $this->option('driver'),
                'host' => $this->option('host'),
                'port' => (int) $this->option('port'),
                'database_name' => $databaseName,
                'username' => $username,
                'password' => $this->option('password') ?? '',
            ],
            'create_organizer' => filled($this->option('organizer-email')),
            'organizer' => [
                'email' => $this->option('organizer-email'),
                'name' => $this->option('organizer-name'),
                'password' => $this->option('organizer-password'),
            ],
        ], $tenant);

        $this->info("Tenant ready: {$tenant->slug} ({$databaseName})");
        $this->info('Tenant module namespace: app/TenantModules/Tenants/' . Str::studly($tenant->slug));

        return self::SUCCESS;
    }
}
