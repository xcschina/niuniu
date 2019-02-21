
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('qb_order_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new qb_order_admin();
switch ($act){
    case'order_list':
        $bo->order_list();
        break;
    case'order_details':
        $bo->order_details($id);
        break;
    case'detail_edit':
        $bo->detail_edit();
        break;

}