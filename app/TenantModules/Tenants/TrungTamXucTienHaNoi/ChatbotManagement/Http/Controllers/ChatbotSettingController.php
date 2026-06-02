<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class ChatbotSettingController extends Controller
{
    public function __construct()
    {
        $this->selectedMainMenu = 'chatbot_management';

        parent::__construct();

        if (! Gate::allows('chatbot_management')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index()
    {
        return redirect()->route('tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.basic');
    }

    private function renderTab(string $tab)
    {
        if (! Gate::allows("chatbot_management/{$tab}")) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        View::share('selectedMainMenu', $this->selectedMainMenu);
        $this->selectedSubMenu($tab);

        $settings = Setting::getAllSetting();

        return view('ttxt-ai-chatbot::setting.chatbot', compact('settings', 'tab'));
    }

    public function basic()
    {
        return $this->renderTab('basic');
    }

    public function sync()
    {
        return $this->renderTab('sync');
    }

    public function prompts()
    {
        return $this->renderTab('prompts');
    }

    public function blacklist()
    {
        return $this->renderTab('blacklist');
    }

    public function sessions()
    {
        return $this->renderTab('sessions');
    }

    public function knowledge()
    {
        return $this->renderTab('knowledge');
    }

    public function usage()
    {
        return $this->renderTab('usage');
    }
}
