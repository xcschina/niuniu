<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");
BO('activity_admin');

$act = paramUtils::strByGET("act", false);

$bo = new activity_admin();
switch ($act){
    case"list":
        $bo->activity_list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"edit_view":
        $id = paramUtils::strByGET("id",false);
        $bo->edit_view($id);
        break;
    case"do_edit":
        $id = paramUtils::strByGET("id",false);
        $bo->do_edit($id);
        break;
    case"reserve_log":
        $bo->reserve_log();
        break;
    case"apply":
        $id = paramUtils::strByGET("id",false);
        $bo->apply($id);
        break;
    case"apply_save":
        $id = paramUtils::strByGET("id",false);
        $guild_id = paramUtils::strByGET("guild_id",false);
        $bo->apply_save($id,$guild_id);
        break;
    case"audit_list":
        $bo->audit_list();
        break;
    case"audit_view":
        $id = paramUtils::strByGET("id",false);
        $bo->audit_view($id);
        break;
    case"do_audit":
        $id = paramUtils::strByGET("id",false);
        $bo->do_audit($id);
        break;
    case"audit_record":
        $id = paramUtils::strByGET("id",false);
        $bo->audit_record($id);
        break;
    case"detail":
        $bo->detail_list();
        break;
    case"detail_view":
        $id = paramUtils::strByGET("id",false);
        $bo->detail_view($id);
        break;
    case"do_detail":
        $id = paramUtils::strByGET("id",false);
        $bo->do_detail($id);
        break;
    case"log_list":
        $bo->log_list();
        break;
    case"export":
        $bo->export();
        break;
}