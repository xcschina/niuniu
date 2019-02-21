<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set('display_errors', 'On');
COMMON("paramUtils", "loginCheck");
BO('user_withdraw_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new user_withdraw_admin();
switch ($act) {
    case"user_withdraw_list":
        $bo->user_withdraw_list();
        break;
    case'upload_view':
        $bo->upload_view($id);
        break;
    case'do_upload_view':
        $bo->do_upload_view();
        break;
    case"refuse":
        $bo->refuse($id);
        break;
    case'do_refuse':
        $bo->do_refuse();
        break;
}