<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use App\Libs\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Spatie\Translatable\HasTranslations;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    protected $connection = 'tenant';

    use HasTranslations, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $table = "categories";
    public array $parents = [];

    public $translatable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    const CATEGORY_TYPE_POST = 1;
    const CATEGORY_TYPE_INVESTMENT_HANDBOOK = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = ['slug', 'name', 'type'];

    const OPTIONS_CATEGORY = [
        self::CATEGORY_TYPE_POST => 'Danh mục bài viết',
        self::CATEGORY_TYPE_INVESTMENT_HANDBOOK => 'Danh mục cẩm nang đầu tư'
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function getSimpleField()
    {
        return [
            'id',
            'name',
            'slug',
            'type',
            'priority',
            'parent_id',
            'image',
            'approval_level',
            'max_approval',
            'is_draft',
            'main_id',
            'status_approve',
        ];
    }

    public function getUrl(): string
    {
        return Util::url_category($this);
    }

    public function getAll($type = -1)
    {
        $query = Category::select($this->getSimpleField())
            ->where('status', 1);
        if ($type > -1) {
            $query->where('type', $type);
        }
        return $query->orderBy('type')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    public function showCategories($categories, $parent_id = 0, $char = '')
    {
        foreach ($categories as $key => $category) {
            if ($category->parent_id == $parent_id) {
                $categories1 = $categories->firstWhere('parent_id', $parent_id);
                if ($categories1) {
                    $categories1->name = $char . $categories1->name;
                    $categories[] = $categories1;
                }

                unset($categories[$key]);
                $this->showCategories($categories, data_get($category, 'id'), '&brvbar;--- ' . $char);
            }
        }
        return $categories->values();
    }

    public static function makeListCategoryForInvestMent($parent_id = 0, $type = -1, $selected_id = '', $include_default = false)
    {
        $parentIds = Category::where('parent_id', 2)->where('status_approve', 'approved')->pluck('id')->toArray();
        $query = Category::whereIn('id', [2, ...$parentIds]);
        if ($type > -1) {
            $query = $query->where('type', $type);
        }
        $categories = $query->orderBy('priority')->orderBy('name')->get(['id', 'parent_id', 'name']);
        $html = '';

        if ($include_default) {
            $html .= "<option value='0'>__ROOT__</option>";
        }

        $list_categories = (new self())->showCategories($categories, $parent_id);
        foreach ($list_categories as $category) {
            if (is_array($selected_id)) {
                $selected = in_array($category->id, $selected_id) ? 'selected' : '';
            } else {
                $selected = ($category->id == $selected_id) ? 'selected' : '';
            }
            $html .= "<option value=\"$category->id\" $selected>" . $category->name . "</option>";
        }
        return $html;
    }

    public static function makeListCategoryForPost($parent_id = 0, $type = -1, $selected_id = '', $include_default = false)
    {
        $query = Category::where('id', 1)->where('status_approve', 'approved');
        if ($type > -1) {
            $query = $query->where('type', $type);
        }
        $categories = $query->orderBy('priority')->orderBy('name')->get(['id', 'parent_id', 'name']);
        $html = '';

        if ($include_default) {
            $html .= "<option value='0'>__ROOT__</option>";
        }

        $list_categories = (new self())->showCategories($categories, $parent_id);
        foreach ($list_categories as $category) {
            if (is_array($selected_id)) {
                $selected = in_array($category->id, $selected_id) ? 'selected' : '';
            } else {
                $selected = ($category->id == $selected_id) ? 'selected' : '';
            }
            $html .= "<option value=\"$category->id\" $selected>" . $category->name . "</option>";
        }
        return $html;
    }

    public static function makeListCategory($parent_id = 0, $type = -1, $selected_id = '', $include_default = false)
    {
        $query = Category::where('status_approve', 'approved');
        if ($type > -1) {
            $query = $query->where('type', $type);
        }
        $categories = $query->orderBy('priority')->orderBy('name')->get(['id', 'parent_id', 'name']);
        $html = '';

        if ($include_default) {
            $html .= "<option value='0'>__ROOT__</option>";
        }

        $list_categories = (new self())->showCategories($categories, $parent_id);
        foreach ($list_categories as $category) {
            if (is_array($selected_id)) {
                $selected = in_array($category->id, $selected_id) ? 'selected' : '';
            } else {
                $selected = ($category->id == $selected_id) ? 'selected' : '';
            }
            $html .= "<option value=\"$category->id\" $selected>" . $category->name . "</option>";
        }
        return $html;
    }

    public static function makeArrayListCategory($parent_id = 0, $type = -1): array
    {
        $query = Category::query();

        if ($type > -1) {
            $query = $query->where('type', $type);
        }

        $categories = $query->orderBy('priority')->orderBy('name')->get(['id', 'parent_id', 'name']);
        $list_categories = (new self())->showCategories($categories, $parent_id, '');
        $results = [];
        if ($list_categories) {
            foreach ($list_categories as $category) {
                $results[$category->id] = $category->name;
            }
        }
        return $results;
    }

    public static function getAllMenuLink($parent_id = 0, $type = -1): array
    {
        static $cached = [];
        $cache_key = __FUNCTION__ . 'categories';
        if (isset($cached[$cache_key])) {
            $categories = $cached[$cache_key];
        } else {
            $categories = (new self)->getAll(-1);;
            $cached[$cache_key] = $categories;
        }
        $result = [];
        foreach ($categories as $key => $category) {
            if ($type > -1) {
                if ($category->type != $type) {
                    continue;
                }
            }
            $item = [];
            if ($category->parent_id == $parent_id) {
                $item['id'] = $category->id;
                $item['parent_id'] = $category->parent_id;
                $item['name'] = $category->name;
                $item['image'] = $category->image;
                $item['href'] = Util::url_category($category);
                $item['type'] = 'category';
                $item['children'] = self::getAllMenuLink($category->id, $category->type);
                $result[$key] = $item;
            }
        }
        return $result;
    }

    /**
     * @param int $cat_id
     * @return array breadcrumb
     */
    public function getAllParentCatArr(int $cat_id = 0): array
    {
        $list = [];
        $i = $cat_id;
        while ($this->parents[$i] > 0) {
            $arrCat = $this->getOneLink($this->parents[$i]);
            $list[] = $arrCat;
            $i = $this->parents[$i];
        }
        return array_reverse($list);
    }

    public function getOneLink($cat_id): array
    {
        $category = $this->find($cat_id);
        $result = [];
        $result['name'] = $category->name;
        $result['href'] = Util::url_category($category);
        return $result;
    }

    public function getParentArray(): array
    {
        if (!empty($this->parents)) {
            return $this->parents;
        }

        $list_categories = self::where('status', 1)->orderBy('priority')->select('id', 'name', 'parent_id')->get();
        if ($list_categories) {
            foreach ($list_categories as $v) {
                $this->parents[$v->id] = $v->parent_id;
            }
        }

        return $this->parents;
    }

    public function getAllCatStr($cat_id = 0): array
    {
        $arr_cat = [];
        foreach ($this->parents as $k => $v) {
            if ($v == $cat_id) {
                $arr_cat[] = $k;
                $arr_cat[] = $this->getAllCatStr($k);
            }
        }
        return Arr::flatten($arr_cat);
    }

    public static function makeOptionColumnButton(): array
    {
        $options = [];

        foreach (['edit', 'delete'] as $action) {
            if (Gate::allows('category/' . $action)) {
                $options[$action] = [
                    'route' => 'tenant.trung_tam_xuc_tien_ha_noi.category.' . $action,
                ];
            }
        }

        return $options;
    }

    //code dự án
    public function getChildrenCategories($type = self::CATEGORY_TYPE_POST)
    {
        $children = Category::where('status', 1)
            ->where('parent_id', data_get($this, 'id', 0))
            ->where('type', $type)
            ->orderBy('priority', 'DESC')
            ->orderBy('name')
            ->get();

        if (!$children->count()) {
            $children = Category::where('status', 1)
                ->where('parent_id', data_get($this, 'parent_id', 0))
                ->where('type', $type)
                ->orderBy('name')
                ->get();
        }

        return $children;
    }

    public function getParentCategory()
    {
        $parent_id = data_get($this, 'parent_id', 0);

        if (!$parent_id) {
            return null;
        }

        return Category::where('status', 1)
            ->where('id', $parent_id)
            ->first();
    }

    public function getCategoryPostHome($limit_post = 12)
    {
        $clsPost = new Post();
        $category = Category::where('at_home', 1)
            ->where('status', 1)
            ->where('type', Category::CATEGORY_TYPE_POST)
            ->orderBy('priority')
            ->orderBy('id', 'DESC')
            ->first();
        if ($category) {
            $category->posts = $clsPost->getAllPostByCatID($category->id, $limit_post);
        }
        return $category;
    }

    public static function getCategoryPostHomeById($category_id, $limit_post = 8)
    {
        if (!$category_id) {
            return null;
        }
        $clsPost = new Post();
        $category = Category::where('id', $category_id)->first();
        if ($category) {
            $category->posts = $clsPost->getAllPostByCatID($category->id, $limit_post);
        }
        return $category;
    }

    public function groupCategories($categories, $cat_id): array
    {
        $results = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $cat_id) {
                $results[] = $category;
            }
        }
        return $results;
    }
    public function draft()
    {
        return $this->hasOne(Category::class, 'main_id')->where('is_draft', true);
    }

    public function main()
    {
        return $this->belongsTo(Category::class, 'main_id');
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
