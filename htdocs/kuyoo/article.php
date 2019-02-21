<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("article_web");
COMMON('paramUtils');
$bo = new article_web();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'articles_list':
        $id=paramUtils::intByGET("part_id",false);
        // $bo->get_articles_list();
        $bo->get_articles_list($id);
        break;
    case'detail':
        $id=paramUtils::strByGET("id");
        // $bo->article_detail();
        $bo->get_article($id);
        break;
}