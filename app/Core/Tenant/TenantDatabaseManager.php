<?php

namespace App\Core\Tenant;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantDatabaseManager
{
    public function connect(Tenant $tenant): void
    {
        $database = $tenant->database;

        if (! $database) {
            throw new RuntimeException('Tenant chưa có cấu hình database.');
        }

        Config::set('database.connections.tenant', [
            'driver' => $database->driver,
            'host' => $database->host,
            'port' => $database->port,
            'database' => $database->database_name,
            'username' => $database->username,
            'password' => $database->password,
            'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
            'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function test(Tenant $tenant): array
    {
        $startedAt = microtime(true);

        $this->connect($tenant);
        DB::connection('tenant')->select('select 1 as connection_test');

        return [
            'database' => $tenant->database->database_name,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    public function testConfig(array $database): array
    {
        $startedAt = microtime(true);

        Config::set('database.connections.tenant_test', [
            'driver' => $database['driver'],
            'host' => $database['host'],
            'port' => $database['port'],
            'database' => $database['database_name'],
            'username' => $database['username'],
            'password' => $database['password'] ?? null,
            'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
            'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant_test');
        DB::connection('tenant_test')->select('select 1 as connection_test');
        DB::disconnect('tenant_test');

        return [
            'database' => $database['database_name'],
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }
}
