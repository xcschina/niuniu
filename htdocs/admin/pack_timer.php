<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('pack_admin');
$act = paramUtils::strByGET("act", false);
$bo = new pack_admin();
$bo->pack();