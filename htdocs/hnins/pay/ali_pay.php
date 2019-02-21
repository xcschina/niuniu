<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,User-Agent1');
//header("Content-Type: text/html; charset=utf-8");
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('ali_pay_web');
$bo = new ali_pay_web();
$bo->downloadurl_query();