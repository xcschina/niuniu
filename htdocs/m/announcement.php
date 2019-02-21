<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('announcement_mobile');

$bo  = new announcement_mobile();
$act = paramUtils::strByGET("act");
switch($act){
    case 'get_article_info':
        $id = paramUtils::intByGET("id", false);
        $bo->get_article_info($id);
        break;
}