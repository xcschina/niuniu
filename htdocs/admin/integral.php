<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('integral_admin','auto_pack_admin');
$act = paramUtils::strByGET("act", false);

$bo = new integral_admin();
switch ($act) {
    case"list":
        $bo->list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"edit_view":
        $id = paramUtils::intByGET('id',false);
        $bo->edit_view($id);
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"del_view":
        $id = paramUtils::intByGET('id',false);
        $bo->del_view($id);
        break;
    case"do_del":
        $id = paramUtils::intByGET('id',false);
        $bo->do_del($id);
        break;
}
