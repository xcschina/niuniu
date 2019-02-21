
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('rank_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new rank_admin();
switch ($act){
    case'rank_list':
        $bo->rank_list();
        break;
    case'rank_add_view':
        $bo->rank_add_view();
        break;
    case'rank_save':
        $bo->rank_save();
        break;
    case'rank_edit':
        $bo->rank_edit($id);
        break;
    case'rank_update';
        $bo->rank_update();
        break;
}