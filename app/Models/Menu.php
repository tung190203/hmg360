<?php

namespace App\Models;

use App\Libs\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Menu extends Model
{
    use HasTranslations, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $table = 'menus';

    protected $fillable = [
        'name',
        'slug',
        'custom_link',
        'image',
        'page_id',
        'cat_id',
        'parent_id',
        'priority',
        'status',
        'type',
        'is_mega',
        'language',
        'approval_level',
        'max_approval',
        'is_draft',
        'main_id',
        'status_approve',
    ];

    public $translatable = [
        'name',
        'slug',
        'custom_link',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        // static::saving(function ($menu) {
        //     if (empty($menu->slug)) {
        //         $menu->slug = Str::slug($menu->name);
        //     }
        // });
    }

    public function showMenus($menus, $parent_id = 0, $char = '')
    {
        foreach ($menus as $key => $menu) {
            if ($menu->parent_id == $parent_id) {
                $menu1 = $menus->firstWhere('parent_id', $parent_id);
                if ($menu1) {
                    $menu1->name = $char . $menu1->name;
                    $menus[] = $menu1;
                }
                unset($menus[$key]);
                $this->showMenus($menus, $menu->id, '&brvbar;--- ' . $char);
            }
        }
        return $menus->values();
    }

    public static function makeListMenu($parent_id = 0, $type = 'main', $selected_id = '', $include_default = false)
    {
        $language = App::getLocale();
        $query = Menu::where('language', $language)->where('type', $type)->where('status_approve', 'approved');
        $list_menu = $query->get(['id', 'parent_id', 'name']);
        $menus = (new self())->showMenus($list_menu, $parent_id);
        $html = '';

        if ($include_default) {
            $html .= "<option value='0'>__ROOT__</option>";
        }

        foreach ($menus as $key => $value) {
            $selected = ($value->id == $selected_id) ? 'selected' : '';
            $html .= "<option value=\"$value->id\" $selected>" . $value->name . "</option>";
        }
        return $html;
    }

    public static function getAllMenuLink($type = 'main', $parent_id = 0)
    {
        static $cached = [];
        $cache_key = __FUNCTION__ . 'menu';
        if (isset($cached[$cache_key])) {
            $menus = $cached[$cache_key];
        } else {
            $menus = Menu::with(['category'])
                ->where('is_draft', false)
                ->where('status_approve', 'approved')
                ->where('status', 1)
                ->orderBy('priority')
                ->orderBy('created_at')
                ->get();
            $cached[$cache_key] = $menus;
        }

        $results = [];
        foreach ($menus as $key => $menu) {
            if ($menu->type != $type) {
                continue;
            }
            $v1 = [];
            if ($menu->parent_id == $parent_id) {
                $v1['name'] = $menu->name;
                $v1['id'] = $menu->id;
                $v1['is_mega'] = $menu->is_mega;
                $v1['image'] = $menu->image;
                if ($menu->page_id > 0) {
                    $page = $menu->page;
                    $v1['href'] = Util::url_page($page);
                    $v1['type'] = 'page';
                    $sub_menu = self::getAllMenuLink($type, $menu->id);
                    $v1['children'] = $sub_menu;
                } else {
                    if ($menu->cat_id > 0) {
                        $category = $menu->category;
                        $v1['href'] = Util::url_category($category);
                        $v1['type'] = 'category';
                        $sub1 = (new Category())->getAllMenuLink($menu->cat_id, -1);
                        $sub2 = self::getAllMenuLink($type, $menu->id);
                        $v1['children'] = array_merge($sub1, $sub2);
                    } else {
                        $v1['href'] = ($menu->custom_link != "") ? $menu->custom_link : route('backend_dashboard');
                        $v1['type'] = 'custom_link';
                        if ((!str_contains($v1['href'], 'http://') && !str_contains($v1['href'], 'https://')) && $v1['href'] != '#!') {
                            $v1['href'] = url($v1['href']);
                        }
                        $v1['children'] = self::getAllMenuLink($type, $menu->id);
                    }
                }
                $results[$key] = $v1;
            }
        }
        return $results;
    }

    public function draft()
    {
        return $this->hasOne(Menu::class, 'main_id')->where('is_draft', true);
    }

    public function main()
    {
        return $this->belongsTo(Menu::class, 'main_id');
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
