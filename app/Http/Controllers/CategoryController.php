<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request, $slug)
    {
        $locale = app()->getLocale();
        $availableLocales = config('app.available_locales', ['vi', 'en']);
    
        $category = Category::where('status', 1)
            ->where(function ($query) use ($slug, $locale, $availableLocales) {
                $query->where("slug->{$locale}", $slug);
                foreach ($availableLocales as $loc) {
                    if ($loc !== $locale) {
                        $query->orWhere("slug->{$loc}", $slug);
                    }
                }
            })
            ->firstOrFail();

        $type = $category->type;

        switch ($type) {
            case Category::CATEGORY_TYPE_POST:
                $controller = new PostController();
                return $controller->index($request, $category);
            case Category::CATEGORY_TYPE_INVESTMENT_HANDBOOK:
                $controller = new HomeController();
                return $controller->introducePotential($request);
    
            default:
                return view('errors.404');
        }
    }
}
