<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
//ini_set("display_errors","On");
//error_reporting(E_ALL);
//审核
$bo->V->display("h5game/wk_examine_sever.html");
