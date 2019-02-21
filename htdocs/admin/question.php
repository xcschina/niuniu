<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");
BO('question_admin');

$act = paramUtils::strByGET("act", false);

$bo = new question_admin();
switch ($act){
    case"list":
        $bo->question_list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"edit":
        $id = paramUtils::strByGET("id",false);
        $bo->edit_view($id);
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"del_view":
        $id = paramUtils::strByGET("id",false);
        $bo->del_view($id);
        break;
    case"do_del":
        $id = paramUtils::strByGET("id",false);
        $bo->do_del($id);
        break;

}