<?php
header("Content-Type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
require_once 'config.php';
COMMON("paramUtils");
BO('disc_web');
$act = paramUtils::strByGET("act", false);

$bo = new disc_web();
switch ($act){
    case "top_banner":
        $bo->top_banner();
        break;
    case "game_find":
        $bo->game_find();
        break;
    case "theme":
        $is_entry = paramUtils::strByGET("isEntry",false);
        if ($is_entry=='true'){
            $bo->theme_entry();
        }else{
            $page = paramUtils::intByGET("page",false);
            $bo->theme_list($page);
        }
        break;
    case "game_recommend":
        $is_entry = paramUtils::strByGET("isEntry",false);
        if ($is_entry=='true'){
            $bo->game_recommend_entry();
        }else{
            $page = paramUtils::intByGET("page",false);
            $bo->game_recommend_list($page);
        }
        break;
    case "game_theme":
        $theme_id = paramUtils::intByGET("themeid",false);
        $page = paramUtils::intByGET("page",false);
        $bo->game_theme_list($theme_id,$page);
        break;
    case "game_article":
        $game_id = paramUtils::intByGET("gameid",false);
        $bo->game_article($game_id);
        break;
    case "game_article_view":
        $game_id = paramUtils::intByGET("game_id",false);
        $bo->game_article_view($game_id);
        break;
    default:
        $result = array("result" => "0", "desc" => "接口参数异常。");
        die("0".base64_encode(json_encode($result)));
        break;
}
?>