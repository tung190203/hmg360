<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class District extends Model
{
    use HasTranslations;
    use UsesTenantConnection;

    protected $table = 'districts';

    protected $fillable = ['name', 'boundary'];

    protected $casts = ['boundary' => 'array'];

    public array $translatable = ['name'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_district');
    }
}
