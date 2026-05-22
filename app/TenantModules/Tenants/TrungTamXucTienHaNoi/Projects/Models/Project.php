<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasTranslations;
    use UsesTenantConnection;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'slug',
        'short_desc',
        'description',
        'lat',
        'lng',
        'area',
        'unit',
        'type_number',
        'industry_number',
        'price',
        'link',
        'banner_image',
        'detail_image',
        'location_image',
        'advantage_images',
        'advantage_titles',
        'advantage_descriptions',
        'link_vrtour',
        'design_short_desc',
        'design_images',
        'design_description',
        'legal_short_desc',
        'legal_file',
        'legal_description',
        'layout_id',
        'is_invest',
        'is_pinned',
        'pin_order',
        'link_sand_table',
        'approval_level',
        'max_approval',
        'is_draft',
        'parent_id',
        'vrtour_code',
        'status',
        'view_num',
        'views_month',
        'views_month_code',
        'boundary',
        'is_hidden',
    ];

    protected $casts = [
        'is_invest' => 'boolean',
        'is_pinned' => 'boolean',
        'is_draft' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public array $translatable = [
        'name',
        'slug',
        'short_desc',
        'description',
        'advantage_titles',
        'advantage_descriptions',
        'design_short_desc',
        'design_description',
        'legal_short_desc',
        'legal_description',
    ];

    public const LAYOUTS = [
        1 => 'Layout 1',
        2 => 'Layout 2',
        3 => 'Layout 3',
    ];

    public const KILOMETERS = 1;
    public const HECTARES = 2;

    public const UNIT_OPTIONS = [
        self::KILOMETERS => 'km',
        self::HECTARES => 'ha',
    ];

    protected $appends = ['unit_type_text'];

    public function getUnitTypeTextAttribute(): string
    {
        return self::UNIT_OPTIONS[$this->unit] ?? '';
    }

    public function type()
    {
        return $this->belongsTo(ProjectType::class, 'type_number');
    }

    public function industry()
    {
        return $this->belongsTo(ProjectIndustry::class, 'industry_number');
    }

    public function districts()
    {
        return $this->belongsToMany(District::class, 'project_district');
    }

    public function interests()
    {
        return $this->morphMany(Interest::class, 'interestable');
    }

    public function draft()
    {
        return $this->hasOne(Project::class, 'parent_id')->where('is_draft', true);
    }

    public function parent()
    {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['type', 'industry', 'districts', 'interests']);
    }

    public function scopeVisibleFor($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            if (($user->is_platform_owner ?? false) || ($user->is_super_admin ?? false) || ($user->is_approve ?? false)) {
                $q->where(function ($sub) {
                    $sub->where('is_draft', false)
                        ->where(function ($s) {
                            $s->whereDoesntHave('draft')
                                ->orWhereHas('draft', fn ($d) => $d->where('status', 'rejected'));
                        });
                })->orWhere(function ($sub) {
                    $sub->where('is_draft', true)->where('status', '!=', 'rejected');
                });

                return;
            }

            $q->where(function ($sub) {
                $sub->where('is_draft', false)->whereDoesntHave('draft');
            })->orWhere(fn ($sub) => $sub->where('is_draft', true));
        });
    }

    public function scopeFilterAdmin($query, Request $request)
    {
        return $query
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%' . $request->string('name') . '%'))
            ->when($request->filled('type_number'), fn ($q) => $q->where('type_number', $request->integer('type_number')))
            ->when($request->filled('industry_number'), fn ($q) => $q->where('industry_number', $request->integer('industry_number')))
            ->when($request->filled('district_id'), function ($q) use ($request) {
                $q->whereHas('districts', fn ($district) => $district->where('district_id', $request->integer('district_id')));
            });
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->slug) && ! empty($project->name)) {
                $project->slug = Str::slug(is_array($project->name) ? reset($project->name) : $project->name);
            }
        });
    }
}
