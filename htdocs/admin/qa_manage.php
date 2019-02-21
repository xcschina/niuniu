<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//ini_set("display_errors","On");
//error_reporting(E_ALL);
BO('qa_manage');
$act = paramUtils::strByGET("act", false);
$bo = new qa_manage();
switch ($act){
    case"black_list":
        $bo->black_list();
        break;
    case"black_edit":
        $id = paramUtils::strByGET("id", false);
        $bo->black_edit($id);
        break;
    case"black_do_edit":
        $bo->black_do_edit();
        break;
    case"mm_list":
        $bo->mm_list();
        break;
//    case"export":
//        $bo->export();
//        break;
    case"log_list":
        $bo->log_list();
        break;
    case"file":
        $bo->file();
        break;
    default:
        die("菜单错误");
        break;
}