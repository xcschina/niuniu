<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("site_index_web");

$id = paramUtils::intByGET("id",false);
$bo = new site_index_web();
$bo->article_view($id);