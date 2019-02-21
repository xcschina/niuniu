<?php

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","ON");
COMMON('paramUtils');
BO("game_admin");
$bo = new game_admin();
$act = paramUtils::strByGET("act", false);
switch ($act) {
    case'list':
        $bo->list_view();
        break;
    case'detail':
        $id = paramUtils::intByGET("id",false);
        $bo->detail_view($id);
        break;
    case"ajax":
        $bo->ajax();
        break;
    default:
        die("缺少参数");
        break;
}