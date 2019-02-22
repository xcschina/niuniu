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
                    "type":"view",
                    "name":"我要买",
                    "url":"http://m.66173.cn"
                },
                {
                    "type":"view",
                    "name":"游戏礼包",
                    "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid='.WX_APPID.'&redirect_uri='.urlencode("http://wx.kuyoo.com//gifts.php").'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                },
                {
                    "name":"个人中心",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "我的礼包",
                            "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid='.WX_APPID.'&redirect_uri='.urlencode("http://wx.kuyoo.com/my.php").'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        },
                        {
                            "type": "click",
                            "name": "联系客服",
                            "key": "service"
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