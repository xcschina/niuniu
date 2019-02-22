<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'on');
COMMON("paramUtils");
BO("article_wx");
$bo = new article_wx();

$act = paramUtils::strByGET("act", false);
switch ($act){
    case"list":
        $part_id = paramUtils::intByGET("part_id",false);
        $bo->article_list($part_id);
        break;
    case"item":
        $id = paramUtils::intByGET("id",false);
        $bo->article_item($id);
        break;
    default:
        break;
}