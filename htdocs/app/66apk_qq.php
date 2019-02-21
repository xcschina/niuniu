<?php
header("Content-Type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
require_once 'config.php';
//正式环境地址 http://maapi.3g.qq.com:8080/v1/
define("APIADDRESS","http://maapi.3g.qq.com:8080/v1/");
COMMON("paramUtils");
BO('qq_web');
$act = paramUtils::strByGET("act", false);
$bo = new qq_web();
switch ($act){
    case "recommend_game":
        $bo->recommend_game();
        break;
    case "recommend_list":
        $page = paramUtils::intByGET("page",false);
        $pageContext = urldecode(paramUtils::strByGET("pageContext",true));
        $bo->recommend_list($page,$pageContext);
        break;
    case "app_list":
        $page = paramUtils::intByGET("page",false);
        $pageContext = urldecode(paramUtils::strByGET("pageContext",true));
        $type = paramUtils::intByGET("type",true);
        $bo->app_list($page,$pageContext,$type);
        break;
    case "rank_app_list":
        $rank_id = paramUtils::intByGET("rankid",false);
        $page = paramUtils::intByGET("page",false);
        $pageContext = urldecode(paramUtils::strByGET("pageContext",true));
        $bo->rank_app_list($rank_id,$page,$pageContext);
        break;
    case "app_detail":
        $apk_name = paramUtils::strByGET("apkname",false);
        $bo->app_detail($apk_name);
        break;
    case"app_gift":
        $apk_name = paramUtils::strByGET("apkname",false);
        $bo->app_gift($apk_name);
        break;
    case "category_list":
        $bo->category_list();
        break;
    case "app_list_by_tagid":
        $tag_id = paramUtils::intByGET("tagid",false);
        $page = paramUtils::intByGET("page",false);
        $pageContext = urldecode(paramUtils::strByGET("pageContext",true));
        $bo->app_list_by_tagid($tag_id,$page,$pageContext);
        break;
    case "search_list":
        $bo->search_list();
        break;
    case"fine_list":
        $page = paramUtils::intByGET("page",false);
        $type = paramUtils::intByGET("type",false);
        $bo->fine_list($type,$page);
        break;
    case "re_niuguo_game":
        $bo->re_niuguo_game();
        break;
    case "report_click":
        $data = urldecode(paramUtils::strByGET("data",true));
        $type = paramUtils::intByGET("type",false);
        $bo->report_click($data,$type);
        break;
    case "report_download":
        $data = urldecode(paramUtils::strByGET("data",true));
        $bo->report_download($data);
        break;
    case "report_install":
        $data = urldecode(paramUtils::strByGET("data",true));
        $bo->report_install($data);
        break;
    case "search_suggest":
        $keyword = paramUtils::strByGET("keyword",false);
        $bo->search_suggest($keyword);
        break;
    case "category_list_new":
        $bo->category_list_new();
        break;
    case "app_list_by_category":
        $category_id = paramUtils::intByGET("categoryid",false);
        $parent_id = paramUtils::intByGET("parentid",false);
        $page = paramUtils::intByGET("page",false);
        $pageContext = urldecode(paramUtils::strByGET("pageContext",true));
        $bo->app_list_by_category($category_id,$parent_id,$page,$pageContext);
        break;
    case"chosen_game":
        $bo->chosen_game();
        break;
    case"chosen_list":
        $page = paramUtils::intByGET("page",false);
        $type = paramUtils::intByGET("type",false);
        $bo->chosen_list($type,$page);
        break;
}
?>