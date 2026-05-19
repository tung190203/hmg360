<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Spatie\Translatable\HasTranslations;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Setting extends Model
{
    use HasTranslations, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $table = 'settings';

    // Đảm bảo trường svalue được Spatie xử lý đa ngôn ngữ
    public $translatable = [
        'svalue',
    ];

    // Sử dụng cache tĩnh để tránh truy vấn nhiều lần
    public static $cached = [];

    public static function check_exists_skey($skey = '')
    {
        if ($skey == '') {
            return false;
        }
        // Kiểm tra sự tồn tại không cần dịch, nên dùng where('skey', $skey)
        $setting = self::where('skey', $skey)->first();
        if (isset($setting)) {
            return true; // Đã tồn tại
        }
        return false;
    }

    public static function getAllSetting()
    {
        if (isset(self::$cached['all_setting'])) {
            return self::$cached['all_setting'];
        }

        $settings = self::get();
        $results = [];

        foreach ($settings as $setting) {
            // Danh sách các key đa ngôn ngữ đơn giản (cần lấy full mảng ['vi' => '...', 'en' => '...'] cho View)
            $multiLangKeys = ['site_name', 'footer_info', 'copyright_notice', 'copyright', 'address', 'social_title', 'logo', 'chatbot_name', 'chatbot_tooltip', 'chatbot_welcome_message'];

            if ($setting->skey === 'banners' || $setting->skey === 'features') {
                // Các trường phức tạp (JSON array): Chỉ lấy giá trị của ngôn ngữ hiện tại
                $results[$setting->skey] = !empty($setting->getTranslation('svalue', App::getLocale()))
                    ? json_decode($setting->getTranslation('svalue', App::getLocale()), true)
                    : [];
            } elseif (in_array($setting->skey, $multiLangKeys)) {
                // Các trường đa ngôn ngữ đơn giản: Lấy toàn bộ mảng dịch
                $results[$setting->skey] = $setting->getTranslations('svalue');
            } else {
                // Các trường đơn ngữ (logo, email, phone,...)
                $results[$setting->skey] = $setting->svalue;
            }
        }

        self::$cached['all_setting'] = $results;
        return $results;
    }

    public static function getSettingByKey($key, $default = '')
    {
        // Hàm này sẽ tự động trả về giá trị đã dịch bởi Spatie
        $setting = Setting::where('skey', $key)->first();
        return data_get($setting, 'svalue', $default);
    }
}
