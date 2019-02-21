<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('general_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::strByGET("id");
$bo = new general_admin();
switch ($act) {
    case"general_list":
        $bo->general_list();
        break;
    case"general_add":
        $bo->general_add();
        break;
    case"general_save":
        $bo->general_save();
        break;
    case"general_edit":
        $id = paramUtils::strByGET("id",false);
        $bo->general_edit($id);
        break;
    case"edit_save":
        $id = paramUtils::strByGET("id",false);
        $bo->edit_save($id);
        break;
    case"batch_view":
        $bo->batch_view($id);
        break;
    case"batch_save":
        $bo->batch_save($id);
        break;
    case"del_general":
        $bo->del_general($id);
        break;
    case"log_list":
        $bo->log_list();
        break;
    case"down_log":
        $bo->down_log($id);
        break;
    case"visit_log":
        $bo->visit_log($id);
        break;
    case"preview":
        $id = paramUtils::strByGET("id",false);
        $bo->preview($id);
        break;
    case"export":
        $bo->export();
        break;
}