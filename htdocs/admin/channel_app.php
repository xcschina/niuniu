<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('super_app');

$act = paramUtils::strByGET("act", false);

$bo = new super_app();
switch ($act){
    case "list":
        $bo->ch_app_list();
        break;
    case "add":
        $bo->ch_app_add();
        break;
    case "do_add":
        $bo->do_ch_app_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->ch_app_edit($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_ch_app_edit($id);
        break;
    case"channel_list":
        $bo->channel_list();
        break;
    case"channel_add":
        $bo->channel_add();
        break;
    case"channel_save":
        $bo->channel_save();
        break;
    case"money_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->ch_money_edit($id);
        break;
    case"ratio_set":
        $id = paramUtils::intByGET("id", false);
        $bo->ch_ratio_set($id);
        break;
    case"ratio_save":
        $bo->ch_ratio_save();
        break;
    case"money_set":
        $id = paramUtils::intByGET("id", false);
        $bo->ch_money_set($id);
        break;
    case"money_save":
        $bo->ch_money_save();
        break;
    case"add_money":
        $id = paramUtils::intByGET("id", false);
        $bo->add_money($id);
        break;
    case"do_add_money":
        $bo->do_add_money();
        break;
    case"do_money_edit":
        $bo->do_ch_money_edit();
        break;
    case"ch_list": //下游管理
        $bo->ch_list();
        break;
    case"edit_notice":
        $id = paramUtils::intByGET("id", false);
        $bo->app_notice_edit_view($id);
        break;
    case "do_edit_notice":
        $id = paramUtils::intByGET("id", false);
        $bo->do_app_notice_edit($id);
        break;
    case"version_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->version_edit($id);
        break;
    case "version_update":
        $id = paramUtils::intByGET("id", false);
        $bo->version_update($id);
        break;
    default:
        $bo->ch_app_list();
        break;
}