<?php
// proxy.php
if (!isset($_GET['url'])) {
    http_response_code(400);
    exit('Thiếu URL');
}
$url = $_GET['url'];
var_dump($url);
// header CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: audio/mp3");

// lấy và trả lại file mp3
readfile($url);