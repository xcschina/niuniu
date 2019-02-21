
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('game_template_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new game_template_admin();
switch ($act){
    case"template_list":
        $bo->template_list();
        break;
    case"add_view":
        $bo->add_view();
        break;
    case"add_template":
        $bo->add_template();
        break;
    case"edit_view":
        $bo->edit_view($id);
        break;
    case"template_edit":
        $bo->template_edit();
        break;
    case"del_template":
        $bo->del_template($id);
        break;

}