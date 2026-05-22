<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleTableController extends Controller
{
    public function index(string $module)
    {
        $config = $this->moduleConfig($module);

        return view('ttxt-module-table::index', [
            'module' => $module,
            'config' => $config,
            'tables' => collect($config['tables'])->map(function (string $table) {
                $exists = Schema::connection('tenant')->hasTable($table);

                return [
                    'name' => $table,
                    'exists' => $exists,
                    'count' => $exists ? DB::connection('tenant')->table($table)->count() : null,
                ];
            }),
        ]);
    }

    public function table(string $module, string $table)
    {
        $config = $this->moduleConfig($module);
        abort_unless(in_array($table, $config['tables'], true), 404);
        abort_unless(Schema::connection('tenant')->hasTable($table), 404);

        $columns = Schema::connection('tenant')->getColumnListing($table);
        $rows = DB::connection('tenant')
            ->table($table)
            ->latest('id')
            ->limit(50)
            ->get();

        return view('ttxt-module-table::table', [
            'module' => $module,
            'config' => $config,
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows,
        ]);
    }

    private function moduleConfig(string $module): array
    {
        $manifest = request()->attributes->get('tenantModule')?->manifest() ?? [];
        abort_unless(($manifest['slug'] ?? null) === $module, 404);

        return [
            'name' => $manifest['name'] ?? $module,
            'route' => $manifest['menu']['route'] ?? null,
            'table_route' => $manifest['table_route'] ?? null,
            'tables' => $manifest['tables'] ?? [],
        ];
    }
}
