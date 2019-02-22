<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index');
$bo = new index();
$bo->redirect('account_shop','qq_do_login'); 