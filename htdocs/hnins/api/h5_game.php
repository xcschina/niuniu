<?php
header("Content-Type: text/html; charset=utf-8");
require_once '../config.php';
COMMON('paramUtils');
BO("game_super_web");
$bo = new game_super_web();
$app_id = paramUtils::strByGET("app_id",false);
$bo->game_view($app_id);
