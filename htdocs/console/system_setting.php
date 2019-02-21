<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils");
BO('system_setting_web');

$act = paramUtils::strByGET("act", false);
$usr_id=paramUtils::strByGET("id");
$bo = new system_setting_web();
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act){
        case "menu_view":
            $bo->menu_list();
            break;
        case'perm_view':
            $bo->perm_list($usr_id);
            break;
        default:
            $bo->menu_list();
            break;
    }
}else{
    switch ($act){
        case "sava_menu":
            $bo->sava_menu();
            break;
        case'del_menu':
            $bo->del_menu();
            break;
        case "sava_perm";
            $bo->sava_perm();
            break;
    }
}