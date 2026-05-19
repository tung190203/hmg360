<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ProjectIndustries extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'project_industries';

    protected $fillable = [
        'name',
    ];
    public $translatable = [
        'name',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'industry_number', 'id');
    }
}
