<?php
define("APP", "mobile");
require_once '../../config.php';
define('PERPAGE',10);
define('GAME_ICON',"images/game_icon");
define('CHANNEL_ICON',"images/channel_icon");
define('PRODUCT_IMG',"images/product_img");
define('INTRO_IMG',"images/intro_img");
define('ORDER_IMG',"images/order_img");
define("ALI_MQB_notify_url", "http://charge.66173.cn/ali_qb_notify.php");
define("ALI_MQB_call_back_url", "http://m.66173.cn/ali_qb_return.php");

header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define('WX_APPID','wxbaed68c7f2f3a62c');
define('WX_APPSECRET','8bae09f8cf665a6b1267c0ccbd77cd61');
define('WX_TOKEN','x25e2873d11ddb11f392dc870cbe521f');
define('WX_AESkey','XmmZ7sfvBMem3OLf8QBLNbC4iD3pJRaKJO8WZHomeER');
define("SITE_URL", "http://m.66173.cn/");
//测试
define("XZ_WX_APPID",'151615968723632');
define("XZ_WX_APPKEY",'BUVHSl8PYxkdr56LT50j1Z1d5NF2mIE2');
define("XZ_WX_Notify_Url",'http://www.baidu.com');
define("XZ_WX_Front_Notify_Url",'http://www.baidu.com');
?>