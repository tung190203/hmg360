<?php

namespace App\Models;

use App\Libs\Util;
use App\Traits\HasGlobalScopes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Spatie\Translatable\HasTranslations;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class InvestmentGuide extends Model
{
    use HasGlobalScopes, HasTranslations, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['view_num']);
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties;
        $attributes = $properties->get('attributes', []);
        $old = $properties->get('old', []);
        unset($attributes['view_num'], $old['view_num']);
        $activity->properties = $properties
            ->put('attributes', $attributes)
            ->put('old', $old);
    }

    protected $table = 'investment_guides';

    protected $fillable = [
        'name',
        'slug',
        'cat_id',
        'relic_id',
        'image',
        'priority',
        'description',
        'content',
        'source',
        'status',
        'is_hot',
        'view_num',
        'files',
        'short_file_descs',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'language',
        'project_type',
        'project_id',
        'published_at',
        'approval_level',
        'max_approval',
        'is_draft',
        'parent_id',
        'status_approve',
        'extracted_text',
        'extracted_summary',
        'extracted_language',
        'extracted_at',
    ];

    public $translatable = [
        'name',
        'slug',
        'description',
        'content',
        'files',
        'short_file_descs',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'published_at'];

    const INVESTMENT_PER_PAGE = 10;
    const INVESTMENT_TAKE = 10;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = 2;

    const STATUS_ARRAY = [
        self::STATUS_ACTIVE => 'Kích hoạt',
        self::STATUS_INACTIVE => 'Chưa kích hoạt',
        self::STATUS_DELETED => 'Đã xóa',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($investment_guide) {});

        static::saved(function ($investment_guide) {});
    }

    public function draft()
    {
        return $this->hasOne(InvestmentGuide::class, 'parent_id')->where('is_draft', true);
    }

    public function parent()
    {
        return $this->belongsTo(InvestmentGuide::class, 'parent_id');
    }

    public function interests()
    {
        return $this->morphMany(Interest::class, 'interestable');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'investment_guide_project');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function getUrl(): string
    {
        return Util::url_investment($this);
    }

    public function getAllTags(): array
    {
        $language = App::getLocale();
        $investment_guides = InvestmentGuide::select('meta_keywords')
            ->where('language', $language)
            ->where('meta_keywords', '<>', '')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
        $list_tags = [];
        foreach ($investment_guides as $investment_guide) {
            $list_tags = array_merge($list_tags, preg_split("/\s?+,\s?+/", $investment_guide->meta_keywords));
        }

        $list_tags = array_filter($list_tags, function ($tag) {
            return !empty($tag);
        });

        return array_unique($list_tags);
    }

    public function getTags(): array
    {
        return $this->meta_keywords ? preg_split("/\s?+,\s?+/", $this->meta_keywords) : [];
    }

    public function getNextInvest()
    {
        return InvestmentGuide::where('status', 1)
            ->where('language', App::getLocale())
            ->where('id', '>', $this->id)
            ->orderBy('id')
            ->first();
    }

    public function getPreviousInvest()
    {
        return InvestmentGuide::where('status', 1)
            ->where('language', App::getLocale())
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function scopePopular($query, $limit = 5)
    {
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->language()
            ->active()
            ->orderBy('view_num', 'desc')
            ->limit($limit);
    }

    public function getOtherInvest($limit)
    {
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->where('id', '<>', $this->id)
            ->where('cat_id', $this->cat_id)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getHotInvestsInCategory($limit)
    {
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->where('id', '<>', $this->id)
            ->where('cat_id', $this->cat_id)
            ->orderBy('view_num', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getListInvestHot($limit)
    {
        $language = App::getLocale();
        return InvestmentGuide::with('category')->select($this->getSimpleField())
            ->where('language', $language)
            ->where('status', 1)
            ->where('is_hot', 1)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getListLatestInvest($limit = 8)
    {
        $language = App::getLocale();
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->where('language', $language)
            ->where('status', 1)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getListRandomInvest($limit)
    {
        $language = App::getLocale();
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->where('language', $language)
            ->where('status', 1)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getListRelatedInvestByKeyword($meta_keywords, $limit = 5): Collection|array
    {
        $keywords = $meta_keywords ? preg_split("/\s?+,\s?+/", $meta_keywords) : [];
        if (empty($keywords)) {
            return [];
        }

        $query = InvestmentGuide::with('category')
            ->where('language', App::getLocale())
            ->where('status', 1);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->orWhere('name', 'like', "%$keyword%");
            }
        });

        return $query->orderBy('id', 'DESC')
            ->limit($limit)->get();
    }

    public function getSimpleField()
    {
        return [
            'id',
            'name',
            'slug',
            'cat_id',
            'priority',
            'description',
            'image',
            'view_num',
            'project_id',
            'created_at'
        ];
    }

    public function getAllInvestByCatID($cat_id = 0, $limit = 5)
    {
        $language = App::getLocale();
        $clsCategory = new Category();
        $clsCategory->getParentArray();
        $cat_ids = $clsCategory->getAllCatStr($cat_id);
        $cat_ids[] = (int)$cat_id;
        return InvestmentGuide::with('category')
            ->select($this->getSimpleField())
            ->where('status', 1)
            ->where('language', $language)
            ->whereIn('cat_id', $cat_ids)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function makeOptionColumnButton(): array
    {
        $options = [];

        foreach (['edit', 'delete', 'clone'] as $action) {
            if (Gate::allows('investment_guide/' . $action)) {
                $options[$action] = [
                    'route' => 'backend_investment_guide_' . $action,
                ];
            }
        }

        return $options;
    }
    public function scopeVisibleFor($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            if ($user->is_super_admin || $user->is_approve) {
                $q->where(function ($sub) {
                    $sub->where('is_draft', false)
                        ->where(function ($s) {
                            $s->whereDoesntHave('draft')
                                ->orWhereHas('draft', function ($d) {
                                    $d->where('status_approve', 'rejected');
                                });
                        });
                })
                    ->orWhere(function ($sub) {
                        $sub->where('is_draft', true)
                            ->where('status_approve', '!=', 'rejected');
                    });
            } else {
                $q->where(function ($sub) {
                    $sub->where('is_draft', false)
                        ->whereDoesntHave('draft');
                })
                    ->orWhere(function ($sub) {
                        $sub->where('is_draft', true);
                    });
            }
        });
    }
}
