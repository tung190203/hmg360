<?php

use App\Models\Project;

function getDataVrtour($_url)
{
    $ch             = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response       = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function createFile($folder, $file)
{
    // Tạo thư mục
    if (!Illuminate\Support\Facades\File::exists(public_path($folder))) {
        Illuminate\Support\Facades\File::makeDirectory(public_path($folder), 0755, true);
    }
    // Tạo file
    if (!Illuminate\Support\Facades\File::exists(public_path($folder.'/'.$file))) {
        Illuminate\Support\Facades\File::put(public_path($folder.'/'.$file), '');
    }
    return "Đã tạo file thành công!";
}

function formatDecimalByLocale($value, ?string $locale = null, int $fractionDigits = 2): string
{
    $locale = $locale ?: app()->getLocale();
    $formatterLocale = match ($locale) {
        'vn', 'vi' => 'vi_VN',
        'en' => 'en_US',
        default => $locale,
    };

    if (class_exists(\NumberFormatter::class)) {
        $formatter = new \NumberFormatter($formatterLocale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $fractionDigits);

        return $formatter->format($value ?? 0);
    }

    $decimalSeparator = str_starts_with($formatterLocale, 'en') ? '.' : ',';
    $thousandSeparator = str_starts_with($formatterLocale, 'en') ? ',' : '.';

    return number_format((float) ($value ?? 0), $fractionDigits, $decimalSeparator, $thousandSeparator);
}
