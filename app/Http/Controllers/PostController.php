<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class PostController extends Controller
{
    public function index(Request $request, Category $category)
    {
        $clsCategory = new Category();
        $clsPost = new Post();
        $clsCategory->getParentArray();
        $cat_ids = $clsCategory->getAllCatStr($category->id);
        $cat_ids[] = (int)$category->id;

        $query_post = Post::with(['category', 'interests'])
            ->whereNull('parent_id')
            ->where('status_approve','approved')
            ->where('published_at' , '<=', Carbon::now())
            ->where('status', Post::STATUS_ACTIVE)
            ->whereIn('cat_id', $cat_ids)
            ->orderBy('published_at', 'desc')
            ->orderBy('priority')
            ->orderBy('id', 'desc');
        $posts = $query_post->paginate(Post::POSTS_PER_PAGE);

        $guestId = Auth::guard('guest')->id();
        $posts->getCollection()->transform(function ($item) use ($guestId) {
            $item->is_interested = $item->interests()
            ->where('guest_id', $guestId)
            ->exists();
            return $item;
        });

        $children_category = $category->getChildrenCategories();
        $parent_category = $category->getParentCategory();

        $setting = Setting::getAllSetting();
        $setting['meta_title'] = ($category->meta_title) ?: $category->name;
        $setting['meta_keywords'] = ($category->meta_keywords) ?: $setting['meta_keywords'];
        $setting['meta_description'] = ($category->meta_description) ?: $setting['meta_description'];
        $setting['menu_active'] = $category->slug;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.home.partials.news_list', compact('posts', 'setting'))->render()
            ]);
        }

        return view('frontend.home.news',
            compact(
                'category',
                'posts',
                'children_category',
                'parent_category',
                'setting'
            )
        );
    }

    public function detail(Request $request, $slug, $id)
    {
        /* @var $post Post */
        $post = Post::where('id', $id)->firstOrFail();

        $category = Category::where('id', data_get($post, 'cat_id'))->first();

        $post->increment('view_num');

        //SEO MOZ
        $setting = Setting::getAllSetting();
        $setting['meta_title'] = ($post->meta_title) ?: $post->name;
        $setting['menu_active'] = __('app.news_link');
        $setting['meta_keywords'] = ($post->meta_keywords) ?: $setting['meta_keywords'];
        $setting['meta_description'] = ($post->meta_description) ?: $setting['meta_description'];
        $setting['og_image'] = ($post->image) ?: ($setting['og_image'] ?? '');
        $list_post_popular = Post::with('interests')->where('status', Post::STATUS_ACTIVE)
            ->whereNull('parent_id')
            ->where('status_approve','approved')
            ->where('published_at' , '<=', Carbon::now())
            ->where('id', '<>', $post->id)
            ->orderBy('view_num', 'desc')
            ->take(Post::POSTS_TAKE)
            ->get()
            ->transform(function ($item) {
                $item->is_interested = $item->interests()
                ->where('guest_id', Auth::guard('guest')->id())
                ->exists();
                return $item;
            });

        $backUrl = url()->previous();
        $backLabel = $request->get('ref');
        if ($backLabel && Lang::has($backLabel)) {
            $backLabel = __($backLabel); 
        } else {
            $backLabel = $backLabel;
        }
        if (rtrim($backUrl, '/') === rtrim(url('/'), '/')) {
            $backUrl = null;
            $backLabel = null;
        }

        return view('frontend.home.new_detail',
            compact(
                'setting',
                'post',
                'category',
                'list_post_popular'
                ,'backUrl'
                ,'backLabel'
            )
        );
    }
}
