<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('user_info_admin');

$act = paramUtils::strByGET("act", false);

$bo = new user_info_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"user_info_detail":
        $user_id = paramUtils::intByGET('id',false);
        $bo->user_info_detail($user_id);
        break;
    case"do_user_info":
        $user_id = paramUtils::intByGET('id',false);
        $bo->do_user_info($user_id);
        break;

}