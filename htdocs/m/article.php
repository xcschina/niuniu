<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("article_mobile");
COMMON('paramUtils');
$bo = new article_mobile();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'articles_list':
        $id=paramUtils::intByGET("part_id",false);
        $bo->get_articles_list();
        break;
    case'detail':
        $id=paramUtils::intByGET("id",false);
        $bo->article_detail();
        break;
}