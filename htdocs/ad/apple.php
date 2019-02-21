
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("website_web");
COMMON('paramUtils');
$bo = new website_web();
$apple_id = paramUtils::intByGET(apple_id);
$bo->open_apple_url($apple_id);
