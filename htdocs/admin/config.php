<?php
define("APP", "admin");
//ini_set("display_errors","On");
require_once '../../config.php';
define('PERPAGE',20);
define('GAME_ICON',"images/game_icon");
define('CHANNEL_ICON',"images/channel_icon");
define('PRODUCT_IMG',"images/product_img");
define('INTRO_IMG',"images/intro_img");
define('ORDER_IMG',"images/order_img");
define('LINK_ICON',"images/link_icon");
define("IMG_DOMAIN", "static.66173.cn");
//ini_set("display_errors","On");
//error_reporting(E_ALL);


define('luoke_merchant_id','1000000');
define('luoke_key','b06f0fc901c595e197466650b731c7e9');
define("luoke_NOTIFY_URL", "http://charge.66173.cn/luoke_notify.php");

define('b_luoke_merchant_id','1000002');
define('b_luoke_key','f748e50eeab351b0191b13b618aff677');
define("b_luoke_NOTIFY_URL", "http://charge.66173.cn/luoke_b_notify.php");

define('shushan_merchant_id','10018');
define('shushan_key','nh5wa9j0coga5xi');
define("shushan_NOTIFY_URL", "http://charge.66173.cn/shushan_notify.php");
//净蓝Q币对私
define('JL_merchant_Account','1000000270');
define('jinglan_NOTIFY_URL','http://charge.66173.cn/jinglan_notify.php');
define('jinglan_key','792040659dbe6920ee43aa42f1894b74');
//净蓝Q币对公
define('JL_merchant_Account_P','1000000286');
define('jinglan_NOTIFY_URL_P','http://charge.66173.cn/jinglan_public_notify.php');
define('jinglan_key_P','ab6a6ca80fa09604237a42f0a0f89721');
//净蓝Q币测试商户
//define('JL_merchant_Account_P','test');
//define('jinglan_key_P','0cbc6611f5540bd0809a388dc95a615b');
//云之盟Q币对公，旧后台的参数
define('YZM_merchant_id_old','10015');
define('YZM_key_old','yjc24e3ymth411f');
//云之盟Q币对公，新后台参数
define('YZM_merchant_id','100006');
define('YZM_key','b181931c506d74262fc88f075be40d44');
define('YZM_notify_url','http://charge.66173.cn/yzm_notify.php');
//云之盟Q币对私
define('YZM_merchant_id_P','100005');
define('YZM_key_P','d7d8d2105d5780c7e72515a925b8ddd8');
//游戏久Q币对公
define('youxi9_merchant_id','335');
define('youxi9_key','47ef263fb8361e69dc28a5');
define('youxi9_notify_url','http://charge.66173.cn/youxi9_notify.php');
//游戏久Q币对私
define('youxi9_merchant_id_P','869');
define('youxi9_key_P','403558bc34fe3822dc5232');

?>