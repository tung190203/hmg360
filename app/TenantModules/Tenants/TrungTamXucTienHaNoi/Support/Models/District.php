<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class District extends Model
{
    protected $connection = 'tenant';

    use HasTranslations;
    protected $table = 'districts';
    protected $casts = [
        'boundary' => 'array',
    ];
    
    protected $fillable = [
        'name',
        'boundary',
    ];
    public $translatable = [
        'name',
    ];
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_district');
    }
}
