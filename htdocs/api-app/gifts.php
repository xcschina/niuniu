<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("gift_web");
$bo = new gift_web();
$act = paramUtils::strByGET("act");

switch($act){
    case 'item':
        $id = paramUtils::intByGET("id", false);
        $bo->item_view($id);
        break;
    case "get_code":
        $id = paramUtils::intByGET("id", false);
        $csrf = paramUtils::strByGET("csrf", false);
        $bo->get_code($id, $csrf);
        break;
    default:
        $bo->list_view();
        break;
}
