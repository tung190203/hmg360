<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\HtmlString;

class ProductType extends Model
{
    protected $connection = 'tenant';

    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
    ];
    public $translatable = [
        'name',
    ];

    public function industrialProjects()
    {
        return $this->hasMany(IndustrialProject::class, 'product_type', 'id');
    }

    public static function makeOptions($productTypes, $selected = null)
    {
        $html = '<option value="">-- Chọn loại sản phẩm --</option>';

        foreach ($productTypes as $item) {
            $isSelected = ($item->id == $selected) ? 'selected' : '';
            $html .= "<option value=\"{$item->id}\" {$isSelected}>{$item->name}</option>";
        }

        return new HtmlString($html);
    }
}
