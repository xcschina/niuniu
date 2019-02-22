<?php
//微信菜单设置
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils','weixin.class');
$ret=wxcommon::getToken();
$ACCESS_TOKEN=$ret['access_token'];
$menuPostData='{
                "button":[
                {
                    "name":"我要买",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "首充号",
                            "url":"http://m.66173.cn/character.php"
                        },
                        {
                            "type": "view",
                            "name": "代充",
                            "url":"http://m.66173.cn/topup.php"
                        }
                    ]
                },
                {
                    "type":"view",
                    "name":"游戏礼包",
                    "url":"http://wx.66173.cn/gifts.php"
                },
                {
                    "name":"新闻资讯",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "游戏资讯",
                            "url":"http://wx2.66173.cn/article.php?act=list&part_id=16"
                        },
                        {
                            "type": "view",
                            "name": "游戏攻略",
                            "url":"http://wx2.66173.cn/article.php?act=list&part_id=18"
                        },
                        {
                            "type": "view",
                            "name": "新游推荐",
                            "url":"http://wx2.66173.cn/article.php?act=list&part_id=13"
                        }
                    ]
                },
                ]}';

// create new menu
$wxmenu=new wxmenu($ACCESS_TOKEN);
$create=$wxmenu->createMenu($menuPostData);

$act = paramUtils::strByGET("act");
switch($act){
    case "c":
        //get current menu
        $get=$wxmenu->getMenu();
        var_dump($get);
        break;
    case "d":
        //delete current menu
        $del=$wxmenu->deleteMenu();
        var_dump($del);
        break;
}