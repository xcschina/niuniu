<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");
BO('business_stock_admin');
$act = paramUtils::strByGET("act", false);

$bo = new business_stock_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"input_view":
        $id = paramUtils::intByGET('id',false);
        $bo->input_view($id);
        break;
    case"do_input":
        $bo->do_input();
        break;
    case"input_detail":
        $id = paramUtils::intByGET('id',false);
        $user_id = paramUtils::intByGET('user_id');
        $bo->input_detail($id,$user_id);
        break;
    case"detail_show":
        $bo->detail_show();
        break;
    case"admins":
        $bo->admins();
        break;
    //商会初始库存信息
    case"initial_value":
        $bo->initial_value();
        break;
    case"group_info":
        $id = paramUtils::intByGET('id',false);
        $bo->group_info($id);
        break;
    case"group_input":
        $id = paramUtils::intByGET('id',false);
        $bo->group_input($id);
        break;
    case"do_group_input":
        $bo->do_group_input();
        break;
    case"record_list":
        $bo->record_list();
        break;
}