<?php

namespace App\Core\Module;

use App\Core\Tenant\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ModuleManager
{
    public function enabledForTenant(?Tenant $tenant): Collection
    {
        if (! $tenant) {
            return collect();
        }

        return Module::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function menuForTenant(?Tenant $tenant): Collection
    {
        return $this->enabledForTenant($tenant)
            ->map(function (Module $module) {
                $configuredMenu = $module->config['menu'] ?? null;

                if (is_array($configuredMenu)) {
                    return [
                        'slug' => $module->slug,
                        'title' => $configuredMenu['title'] ?? $module->name,
                        'icon' => $configuredMenu['icon'] ?? 'fas fa-cube',
                        'route' => $configuredMenu['route'] ?? null,
                        'items' => $configuredMenu['items'] ?? [],
                        'sort_order' => $module->sort_order,
                        'section' => $configuredMenu['section'] ?? 'content',
                        'module' => $module,
                    ];
                }

                $manifest = $module->manifest();
                $menus = $manifest['menu_items'] ?? null;

                if (! is_array($menus)) {
                    $menu = $manifest['menu'] ?? null;
                    $menus = is_array($menu) ? [$module->slug => $menu] : [];
                }

                return collect($menus)
                    ->map(function (array $menu, string|int $key) use ($module) {
                        $menuKey = is_string($key) ? $key : ($menu['slug'] ?? $module->slug);
                        $menuOverrides = $module->config['menu_items'][$menuKey] ?? [];
                        $menu = array_replace_recursive($menu, is_array($menuOverrides) ? $menuOverrides : []);

                        return [
                            'slug' => $menuKey,
                            'title' => $menu['title'] ?? $module->name,
                            'icon' => $menu['icon'] ?? 'fas fa-cube',
                            'route' => $menu['route'] ?? null,
                            'items' => $menu['items'] ?? [],
                            'sort_order' => $menu['sort_order'] ?? $module->sort_order,
                            'section' => $menu['section'] ?? 'content',
                            'module' => $module,
                        ];
                    })
                    ->first();
            })
            ->filter(fn (?array $menu) => is_array($menu) && $this->menuHasValidRoute($menu))
            ->sortBy([
                ['sort_order', 'asc'],
                ['title', 'asc'],
            ])
            ->values();
    }

    private function menuHasValidRoute(array $menu): bool
    {
        if (! empty($menu['items']) && is_array($menu['items'])) {
            return collect($menu['items'])->contains(
                fn (array $item) => empty($item['route']) || Route::has($item['route'])
            );
        }

        return empty($menu['route']) || Route::has($menu['route']);
    }
}
