<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('super_qa');

$act = paramUtils::strByGET("act", false);

$bo = new super_qa();
switch ($act) {
    case"device":
        $bo->qa_device_view();
        break;
    case"role":
        $bo->qa_role_view();
        break;
    case"login":
        $bo->qa_login_view();
        break;
    case"role_export":
        $bo->role_export();
        break;
    case"h5_device":
        $bo->qa_h5_device();
        break;
    case"h5_role":
        $bo->qa_h5_role();
        break;
    case"h5_login":
        $bo->qa_h5_login();
        break;
    case "index":
        $bo->index();
        break;
    case "idx_game_data":
        $bo->idx_game_data();
        break;
    case "all_channel":
        $appid = paramUtils::intByREQUEST("appids", false);
        $bo->all_channel($appid);
        break;
}