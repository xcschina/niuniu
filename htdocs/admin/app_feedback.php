<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");

BO('app_feedback_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new app_feedback_admin();
switch ($act){
    case "list":
        $bo->list_view();
        break;
    case "edit":
        $bo->edit_view($id);
        break;
    case "do_edit":
        $bo->do_edit($id);
        break;
}