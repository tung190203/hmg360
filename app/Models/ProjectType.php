<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ProjectType extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'project_types';

    protected $fillable = [
        'name',
    ];
    public $translatable = [
        'name',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'type_number', 'id');
    }

    public static function makeListProjectType($selected_id = '')
    {
        $query = ProjectType::select('id', 'name')->get();
        $html = '<option value="">-- Chọn dự án --</option>';
        foreach ($query as $project_type) {
            $isSelected = ($project_type->id == $selected_id) ? 'selected' : '';
            $html .= "<option value=\"{$project_type->id}\" {$isSelected}>{$project_type->name}</option>";
        }

        return $html;
    }
}
