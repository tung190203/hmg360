<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

abstract class Controller
{
    use AuthorizesRequests;

    protected string $selectedMainMenu = '';
    protected string $selectedSubMenu = '';

    public const MESSAGE_UNAUTHORIZED = 'Quyền hạn không đủ để thực hiện thao tác này';

    public function __construct()
    {
        View::share('selectedMainMenu', $this->selectedMainMenu);
        View::share('selectedSubMenu', $this->selectedSubMenu);
        View::share('current_locale', App::getLocale() === config('app.fallback_locale') ? '' : App::getLocale());
    }

    protected function selectedSubMenu(string $menuId): void
    {
        $this->selectedSubMenu = $menuId;
        View::share('selectedSubMenu', $menuId);
    }

    public function responseJsonOk(array $data = [], string $message = 'ok')
    {
        return response()->json(['code' => 200, 'message' => $message, 'data' => $data]);
    }

    public function responseJsonBadRequest(array $data = [], string $message = 'BadRequest')
    {
        return response()->json(['code' => 400, 'message' => $message, 'data' => $data], 400);
    }
}
