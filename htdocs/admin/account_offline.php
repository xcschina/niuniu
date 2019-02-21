<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('account_offline_admin');
$act = paramUtils::strByGET("act", false);
$bo = new account_offline_admin();

switch ($act){
    case "list":
        $bo->acount_offline_list();
        break;
    case "tpl_down":
        $bo->tpl_down();
        break;
    case "export":
        $bo->export();
        break;
    case "import_view":
        $bo->import_view();
        break;
    case "import":
        $bo->import();
        break;
    case "get_game_area":
        $bo->get_game_area();
        break;
}