<?php

namespace App\Core\Module;

use App\Core\Tenant\Tenant;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'path',
        'is_enabled',
        'sort_order',
        'config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'config' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function modulePath(): string
    {
        return app_path('TenantModules/' . trim($this->path, '/'));
    }

    public function manifest(): array
    {
        $manifestPath = $this->modulePath() . '/module.php';

        if (! file_exists($manifestPath)) {
            return [];
        }

        $manifest = require $manifestPath;

        return is_array($manifest) ? $manifest : [];
    }
}
