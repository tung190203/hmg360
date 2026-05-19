<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

function load_js($file_name)
{
    $folder = isset($_REQUEST['folder']) ? $_REQUEST['folder'] : '';
    $path   = 'vrtour/' . $folder . '/' . $file_name . '.js';
   
    if (!file_exists($path)) {
        return null;
    }

    $content = @file_get_contents($path);

    if ($content === false || trim($content) === '') {
        return null;
    }
    // Parse JSON
    $json = json_decode($content, true);

    return (json_last_error() === JSON_ERROR_NONE) ? $json : null;

}

// ========== LOAD DATA ==========

$vr_hotspot = load_js('hotspot');
$list_hotspot = [];

if (is_array($vr_hotspot)) {
    foreach($vr_hotspot as $k => $val){
        $list_hotspot[$k] = [
            'potision'   => $val['potision'] ?? null,
            'url'        => $val['url'] ?? null,
            'url_en'     => $val['url_en'] ?? null,
            'opacity'    => $val['opacity'] ?? null,
            'tooltip'    => $val['tooltip'] ?? null,
            'tooltip_en' => $val['tooltip_en'] ?? null,
        ];
    }
}

// Load các file khác (trả về null nếu không có)
$connectmap = load_js('connectmap');
$location   = load_js('location');
$investor   = load_js('investor');
$pano       = load_js('pano');
$screen     = load_js('welcome_screen');
$plan       = load_js('plan');
$document   = load_js('document');

// ========== PROCESS INVESTOR HTML ==========

if (is_array($investor)) {
    $start_html         = '<div style="text-align: left; color: #000;">
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <p style="margin: 0; line-height: 16.76px;"><br style="display: inline-block; letter-spacing: 0px; white-space: pre-wrap; color: #000000; font-size: 46.17px; font-family: Arial, Helvetica, sans-serif;" /></p>
    <div style="text-align: center; font-size: 36px;">
        <span STYLE="display:inline-block; letter-spacing:0px; white-space:pre-wrap;color:#000000;font-family:Arial, Helvetica, sans-serif;">
            <span STYLE="color:#0176bf;font-size:36px;font-family:SVN-Gotham;">';
$end_html           = '</span>
        </span>
    </div>
</div>';

    $investor['name']    = $start_html . ($investor['name'] ?? '') . $end_html;
    $investor['name_en'] = $start_html . ($investor['name_en'] ?? '') . $end_html;
}

// ========== PROCESS SCREEN HTML ==========

if (is_array($screen)) {
    $start_html1        = '<div style="text-align:left; color:#000; "><DIV STYLE="line-height:100%;text-align:left;font-size:1.984564498346196vmin;"><SPAN STYLE="display:inline-block; letter-spacing:0vmin; white-space:pre-wrap;color:#000000;font-family:Arial, Helvetica, sans-serif;"><SPAN STYLE="color:#ffffff;font-size:1.98vmin;font-family:SVN-Gotham;">';
    $end_html1          = '</SPAN></SPAN></DIV></div>';

    $screen['description'] = $start_html1 . ($screen['description'] ?? '') . $end_html1;
}

// ========== OUTPUT JSON ==========

echo json_encode([
    'list_hotspot' => $list_hotspot,
    'connectmap'   => $connectmap,
    'location'     => $location,
    'investor'     => $investor,
    'pano'         => $pano,
    'screen'       => $screen,
    'plan'         => $plan,
    'document'     => $document
], JSON_UNESCAPED_UNICODE);

