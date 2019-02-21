<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//ini_set("display_errors","off");
BO('message_admin');

$act = paramUtils::strByGET("act", false);

$bo = new message_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case"add_save":
        $bo->add_save();
        break;
    case"edit":
        $id = paramUtils::intByGET("id",false);
        $bo->edit_view($id);
        break;
    case"edit_save":
        $bo->edit_save();
        break;
    case"push_view":
        $id = paramUtils::intByGET("id",false);
        $bo->push_view($id);
        break;
    case"push_save":
        $bo->push_save();
        break;
    case"uploaded_file":
        $bo->uploaded_file();
        break;
    case"do_offline":
        $bo->do_offline();
        break;

}