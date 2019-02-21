<?php

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","ON");
BO("feedback_admin");
COMMON('paramUtils');
$act = paramUtils::strByGET("act", false);
$bo = new feedback_admin();
//switch ($act) {
//    case"add_feedback":
//        $bo->add_feedback();
//        break;
//    case"view":
//        $bo->feedback_view();
//        break;
//}