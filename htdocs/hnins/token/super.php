<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_super_web");
$bo = new game_super_web();
$data = array();
$data['app_id'] = paramUtils::strByGET("app_id",false);
$data['user_id'] = paramUtils::strByGET("user_id");
$data['platform'] = paramUtils::strByGET("platform");
$data['token'] = paramUtils::strByGET("token");
$data['sign'] = paramUtils::strByGET("sign");
$bo->token($data);
