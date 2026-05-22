<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        Paginator::useBootstrapFour();

        // Chỉ log hành động của người dùng, bỏ qua system (không có causer)
        \Spatie\Activitylog\Models\Activity::creating(function (\Spatie\Activitylog\Models\Activity $activity) {
            if (!$activity->causer_id) {
                return false;
            }
        });

        //Set route local
        $firstSegment = $request->segment(1);
        $availableLocales = config('app.available_locales');
        if (in_array($firstSegment, $availableLocales)) {
            $locale = $firstSegment;
        } else {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        $this->registerTenantModuleViews();

        $locale = $locale == config('app.fallback_locale') ? '' : $locale;
        // Tự động thêm prefix locale cho tất cả route
        Route::macro('localized', function ($callback) use ($availableLocales, $locale) {
            Route::group(['prefix' => $locale], function () use ($callback) {
                $callback();
            })->where(['locale' => implode('|', $availableLocales)]);
        });
    }

    private function registerTenantModuleViews(): void
    {
        foreach ($this->tenantModuleViewPaths() as $viewsPath) {
            $modulePath = dirname($viewsPath, 2);
            $manifestPath = $modulePath . '/module.php';
            $manifest = is_file($manifestPath) ? require $manifestPath : [];
            $relativePath = trim(str_replace(app_path('TenantModules'), '', $modulePath), DIRECTORY_SEPARATOR);
            $namespace = $manifest['view_namespace'] ?? 'tenant-' . Str::slug(str_replace(DIRECTORY_SEPARATOR, '-', $relativePath));

            $this->loadViewsFrom($viewsPath, $namespace);
        }
    }

    private function tenantModuleViewPaths(): array
    {
        $root = app_path('TenantModules');

        if (! is_dir($root)) {
            return [];
        }

        $paths = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir() && $item->getFilename() === 'views' && basename($item->getPath()) === 'Resources') {
                $paths[] = $item->getPathname();
            }
        }

        return $paths;
    }
}
