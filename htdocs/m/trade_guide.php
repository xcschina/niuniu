<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('trade_guide_mobile');

$bo = new trade_guide_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'index':
        $bo->index_view();
        break;
    case 'article':
        $type = paramUtils::intByGET('type',false);
        $bo->article_type($type);
        break;
    case 'get_article_info':
        $id = paramUtils::intByGET('id',false);
        $bo->get_article_info($id);
        break;
    case 'get_article_type':
        $type = paramUtils::intByGET('type',false);
        $bo->article_type($type);
        break;
    case 'get_article_data':
        $bo->get_article_data();
        break;
    case 'get_all_type':
        $bo->get_all_type();
        break;
    default:
        $bo->index_view();
        break;

}