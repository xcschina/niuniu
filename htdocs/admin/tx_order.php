<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

COMMON("paramUtils");
BO('tx_order_admin');
$act = paramUtils::strByGET("act", false);
$bo = new tx_order_admin();
switch ($act) {
    case"list":
        $bo->list_view();
        break;
    case"edit_view":
        $id = paramUtils::intByGET('id',false);
        $bo->edit_view($id);
        break;
    case"do_edit":
        $bo->do_edit();
        break;
}