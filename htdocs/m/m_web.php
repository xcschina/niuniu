<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('weekactivity_mobile');
$bo = new weekactivity_mobile();
$app_id = paramUtils::strByGET("app_id");
$apple_id = paramUtils::strByGET("apple_id");
$bo->m_web($app_id,$apple_id);
