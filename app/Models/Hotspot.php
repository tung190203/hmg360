<?php

namespace App\Models;

use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;

class Hotspot extends Model
{
    protected $table = "hotspot";

    public function project()
    {
        return $this->belongsTo(Project::class, 'vrtour_id', 'id');
    }

    public function IndustrialProject()
    {
        return $this->hasOne(IndustrialProject::class, 'code', 'potision');
    }

    public static function makeUnitOptions($selected = null)
    {
        $units = [
            0 => 'ha',
            1 => 'km'
        ];

        $html = '<option value="">-- Chọn đơn vị --</option>';

        foreach ($units as $value => $label) {
            $isSelected = ($selected !== null && $selected == $value) ? 'selected' : '';
            $html .= "<option value=\"{$value}\" {$isSelected}>{$label}</option>";
        }

        return new HtmlString($html);
    }

}
