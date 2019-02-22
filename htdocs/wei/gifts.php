<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("gifts_wx");
$bo = new gifts_wx();
$act = paramUtils::strByGET("act");

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    switch($act){
        case 'item':
            $id = paramUtils::intByGET("id",false);
            $bo->item_view($id);
            break;
        case 'game':
            $id = paramUtils::intByGET("id",false);
            $bo->game_view($id);
            break;
        case "get_code":
            $id = paramUtils::intByGET("id",false);
            $csrf = paramUtils::strByGET("csrf",false);
            $bo->get_code($id, $csrf);
            break;
        case "do-login":
            $id = paramUtils::intByGET("id",false);
            $bo->do_login($id);
            break;
        default:
            $bo->list_view();
            break;
    }
}else{
    die("xx");
}