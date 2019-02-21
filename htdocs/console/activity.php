<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('activity_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new activity_web();
switch ($act) {
    case"activity_list":
        $bo->activity_list();
        break;
    case"activity_add_view":
        $bo->activity_add_view();
        break;
    case"add_activity":
        $bo->add_activity();
        break;
    case"activity_edit_view":
        $bo->activity_edit_view($id);
        break;
    case"edit_activity":
        $bo->edit_activity();
        break;
    case"del_activity":
        $bo->del_activity($id);
        break;
    case"pop_list":
        $bo->pop_list();
        break;
    case'pop_add':
        $bo->pop_add();
        break;
    case'pop_save':
        $bo->pop_save();
        break;
    case'pop_edit':
        $id = paramUtils::intByGET("id",false);
        $bo->pop_edit($id);
        break;
    case'pop_edit_save':
        $bo->pop_edit_save();
        break;
    case'del_pop':
        $id = paramUtils::intByGET("id",false);
        $bo->del_pop($id);
        break;
}