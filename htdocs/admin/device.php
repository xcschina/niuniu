<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('device_admin');

$act = paramUtils::strByGET("act", false);

$bo = new device_admin();
switch ($act){
    case"device_list":
        $bo->device_list();
        break;
    case"device_black_add":
        $bo->device_black_add();
        break;
    case"do_device_black_add":
        $bo->do_device_black_add();
        break;
    case"relieve_device":
        $bo->relieve_device();
        break;
    case"do_relieve_device":
        $bo->do_relieve_device();
        break;
    case "import":
        $bo->import_view();
        break;
    case "do_import":
        $bo->do_import();
        break;
}