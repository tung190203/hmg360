<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProjectIndustry extends Model
{
    use HasTranslations;
    use UsesTenantConnection;

    protected $table = 'project_industries';

    protected $fillable = ['name'];

    public array $translatable = ['name'];

    public function projects()
    {
        return $this->hasMany(Project::class, 'industry_number');
    }
}
