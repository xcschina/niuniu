<?php
header("Content-Type: text/html; charset=utf-8");
die("域名绑定成功");
require_once 'config.php';
BO("website_web");
COMMON('paramUtils');
$bo = new website_web();
$bo->general_view('1130');//百度推广
