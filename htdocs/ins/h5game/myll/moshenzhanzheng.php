<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
$bo->client_ip();
$new_ip = explode(".",$bo->client_ip());
$bo->V->display("h5game/myll/moshenzhanzheng.html");
