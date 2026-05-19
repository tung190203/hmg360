<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use App\Models\InvestmentGuide;
use App\Models\Nation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
use App\Models\Menu;
use App\Models\Popup;
use App\Models\Post;
use App\Models\Project;
use App\Models\ProjectIndustries;
use App\Models\User;

abstract class Controller
{
    use AuthorizesRequests;

    protected string $selectedMainMenu = '';
    public const MESSAGE_UNAUTHORIZED = 'Quyền hạn không đủ để thực hiện thao tác này';

    public function __construct()
    {
        View::share('selectedMainMenu', $this->selectedMainMenu);
        $current_locale = App::getLocale() == config('app.fallback_locale') ? '' : App::getLocale();
        View::share('current_locale', $current_locale);

        if (request()->routeIs('login') || request()->is('login')) {
            return;
        }

        //Code dự án
        $setting = Setting::getAllSetting();
        View::share('setting', $setting);
        View::share('nations', Nation::all());
        View::share('project_industries', ProjectIndustries::orderBy('created_at', 'desc')->get());
        View::share('projects', Project::orderBy('created_at', 'desc')->where('status', 'approved')->get());
        View::share('posts', Post::orderBy('created_at', 'desc')->where('status_approve', 'approved')->get());
        View::share('investment_guides', InvestmentGuide::orderBy('created_at', 'desc')->where('status_approve', 'approved')->get());
        View::share('category',Category::where('status_approve','approved')->get());
        View::share('menus',Menu::where('status_approve','approved')->get());
        View::share('popups',Popup::where('status_approve','approved')->get());
        View::share('users', User::where('status_approve', 'approved')
    ->where('id', '<>', auth('web')->id())
    ->when(auth('web')->check() && !auth('web')->user()->is_super_admin, function($query) {
        $query->where('is_super_admin', false);
    })->get());
        //End code dự án

    }

    protected function selectedSubMenu($menuId): void
    {
        View::share('selectedSubMenu', $menuId);
    }

    public function responseJsonBadRequest($data = [], $message = 'BadRequest')
    {
        return $this->responseCommonJson(400, $message, $data);
    }

    public function responseJsonOk($data = [], $message = 'ok')
    {
        return $this->responseCommonJson(200, $message, $data);
    }

    public function responseJsonNotAllowed($data = [], $message = 'NotAllowed')
    {
        return $this->responseCommonJson(403, $message, $data);
    }

    protected function responseCommonJson($code, $message, $data)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code, [], JSON_PRETTY_PRINT);
    }
}
