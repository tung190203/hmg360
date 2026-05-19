<?php

namespace App\Http\Controllers\Backend;

use App\Models\Page;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;


class SettingController extends Controller
{
    private Setting $setting;
    // Định nghĩa các key cần được coi là đa ngôn ngữ đơn giản (vi, en)
    const MULTI_LANGUAGE_KEYS = ['site_name', 'footer_info', 'copyright_notice', 'copyright', 'address', 'social_title', 'logo', 'chatbot_name', 'chatbot_tooltip', 'chatbot_welcome_message']; 

    // Định nghĩa các key là mảng JSON phức tạp (chứa đa ngôn ngữ bên trong nó)
    const COMPLEX_JSON_KEYS = ['banners', 'features']; 

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
        $this->selectedMainMenu = 'setting';

        parent::__construct();

        if (!Gate::allows('setting') && !Gate::allows('chatbot_management')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function general()
    {
        if (!Gate::allows('setting/general')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->selectedSubMenu('general');
        $settings = Setting::getAllSetting();

        return view('backend.setting.general', compact('settings'));
    }

    public function social()
    {
        if (!Gate::allows('setting/social')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->selectedSubMenu('social');
        $settings = Setting::getAllSetting();
        return view('backend.setting.social', compact('settings'));
    }

    public function seo()
    {
        if (!Gate::allows('setting/seo')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->selectedSubMenu('seo');
        $settings = Setting::getAllSetting();
        return view('backend.setting.seo', compact('settings'));
    }

    public function chatbot()
    {
        return redirect()->route('backend_chatbot_basic');
    }

    private function renderChatbotTab($tab)
    {
        if (!Gate::allows("chatbot_management/{$tab}")) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->selectedMainMenu = 'chatbot_management';
        \Illuminate\Support\Facades\View::share('selectedMainMenu', $this->selectedMainMenu);
        $this->selectedSubMenu($tab);
        $settings = Setting::getAllSetting();
        return view('backend.setting.chatbot', compact('settings', 'tab'));
    }

    public function chatbotBasic() { return $this->renderChatbotTab('basic'); }
    public function chatbotSync() { return $this->renderChatbotTab('sync'); }
    public function chatbotPrompts() { return $this->renderChatbotTab('prompts'); }
    public function chatbotBlacklist() { return $this->renderChatbotTab('blacklist'); }
    public function chatbotSessions() { return $this->renderChatbotTab('sessions'); }
    public function chatbotKnowledge() { return $this->renderChatbotTab('knowledge'); }
    public function chatbotUsage() { return $this->renderChatbotTab('usage'); }


    public function author()
    {
        if (!Gate::allows('setting/author')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->selectedSubMenu('setting_author');
        $settings = Setting::getAllSetting();
        return view('backend.setting.author', compact('settings'));
    }

    public function save(Request $request)
    {
        $arrListKey = $request->settings;
        if (!isset($arrListKey['noindex'])) {
            $arrListKey['noindex'] = 0;
        }

        foreach ($arrListKey as $skey => $svalue) {
            $setting_item = Setting::where('skey', $skey)->first();

            if ($setting_item === null) {
                $setting_item = new Setting;
                $setting_item->skey = $skey;
            }

            // --- Logic xử lý 3 loại input ---
            if (in_array($skey, self::COMPLEX_JSON_KEYS) && is_array($svalue)) {
                // 1. Trường phức tạp (banners, features): Lưu mảng đã encode vào ngôn ngữ hiện tại
                // Input: array của objects, Output DB: JSON string của array đó
                $svalue = array_values($svalue); // Đảm bảo array tuần tự
                $json_value = json_encode($svalue, JSON_UNESCAPED_UNICODE);
                
                // Lưu vào ngôn ngữ hiện tại
                $setting_item->setTranslation('svalue', App::getLocale(), $json_value);

            } elseif (in_array($skey, self::MULTI_LANGUAGE_KEYS) && is_array($svalue)) {
                // 2. Trường đa ngôn ngữ đơn giản (site_name, footer_info,...)
                // Input: mảng ['vi' => '...', 'en' => '...'], Output DB: JSON string {"vi": "...", "en": "..."}
                
                // Spatie sẽ tự động lưu mảng này dưới dạng JSON
                $setting_item->setTranslations('svalue', $svalue);

            } else {
                // 3. Trường đơn ngữ (favicon, logo, address, noindex,...)
                // Input: string, Output DB: JSON string {"vi": "giá trị", "en": "value"}
                
                // Lưu giá trị đơn giản vào ngôn ngữ hiện tại
                $setting_item->setTranslation('svalue', App::getLocale(), $svalue);
            }
            // --- Kết thúc Logic xử lý ---

            $setting_item->save();
        }

        // Xóa cache sau khi lưu thành công
        if (isset(Setting::$cached['all_setting'])) {
            unset(Setting::$cached['all_setting']);
        }
        
        return redirect()->back()->with('success', 'Cập nhật thông tin thành công');
    }
}
