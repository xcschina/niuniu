
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('reserve_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new reserve_admin();
switch ($act){
    case'reserve_list':
        $bo->reserve_list();
        break;
    case'reserve_add':
        $bo->reserve_add();
        break;
    case'reserve_save':
        $bo->reserve_save();
        break;
    case'reserve_edit_view':
        $bo->reserve_edit_view($id);
        break;
    case'reserve_edit':
        $bo->reserve_edit();
        break;
    case'del_reserve':
        $bo->del_reserve($id);
        break;
    case'prize_name_edit':
        $bo->prize_name_edit($id);
        break;
    case'do_save_prize':
        $bo->do_save_prize();
        break;
    case'image_list':
        $bo->image_list();
        break;
    case'upload_image':
        $bo->upload_image();
        break;
    case'reserve_draw_log':
        $bo->reserve_draw_log($id);
        break;
    case'reserve_log':
        $bo->reserve_log($id);
        break;
    case'gift_info':
        $game_id = paramUtils::intByGET("game_id",false);
        $bo->gift_info($game_id);
        break;


}