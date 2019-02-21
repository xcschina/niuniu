<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('youxi9_admin');
$act = paramUtils::strByGET("act", false);
$bo = new youxi9_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"add":
        $pay_way = paramUtils::intByGET('pay_way');
        $bo->add_view($pay_way);
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"export":
        $bo->export();
        break;
    case"private_list":
        $bo->private_list();
        break;
    case"status_edit":
        $id = paramUtils::intByGET('id',false);
        $ch = paramUtils::strByGET('channel',false);
        $bo->status_edit($id,$ch);
        break;
    case"do_status":
        $bo->do_status();
        break;
    case"import_view":
        $bo->import_view();
        break;
    case"do_import":
        $bo->do_import();
        break;
}