
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('weekactivity_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new weekactivity_admin();
switch ($act){
    case'add_activity':
        $bo->add_view();
        break;
    case'activity_list':
        $bo->list_view();
        break;
    case'activity_save':
        $bo->do_save();
        break;
    case'activity_edit':
        $bo->edit_view();
        break;
    case'activity_do_edit':
        $bo->do_edit();
        break;
    case'batch_view':
        $bo->batch_view();
        break;
    case'add_batch_activity':
        $bo->add_batch_activity();
        break;
    case'activity_batch_save':
        $bo->activity_batch_save();
        break;
    case'activity_batch_edit':
        $bo->batch_edit_view();
        break;
    case'activity_do_batch_edit':
        $bo->do_batch_edit();
        break;
    case'update_weekactivity':
        $bo->update_weekactivity();
        break;
    case'index':
        $bo->list_view();
        break;
    default:
        $bo->list_view();
        break;
}