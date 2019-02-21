<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('agent_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new agent_admin();
switch ($act){
    case 'list':
        $bo->list_view();
        break;
    case 'view':
        $bo->item_view($id);
        break;
    case 'do_edit':
        $bo->do_edit($id);
        break;
    case 'add_view':
        $bo->add_view();
        break;
    case "do_save":
        $bo->do_save();
        break;
//    case'save':
//        print_r($_POST);
//        print_r($_GET);
//        break;
    default:
        $bo->list_view();
        break;
}