<?php
define("APP", "api-app");
require_once '../../config.php';
define('PERPAGE',10);
define("ALI_MOBILE_input_charset", "utf-8");
define("ALI_APP_notify_url", "http://ins.66173.cn/secure_app_notify.php");
define("ALI_SECURE_notify_url", "http://ins.66173.cn/secure_notify.php");
define("WX_APP_notify_url", "http://ins.66173.cn/wx_app_notify.php");
define("WX_SECURE_notify_url", "http://ins.66173.cn/wx_secure_notify.php");
define("WX_APPID", "wxaf110d18e14498e2");
//牛果小市
define("XS_WX_APPID", "wx3fdefedf5eb6dcd5");
//define("XS_WX_APPKEY", "b40a483c34dd65092e3cbf0ee90b8e80");
define("XS_WX_APPKEY", "65dc235f092771f9ccd17be92e204225");
define("XS_MCH_ID","1498445462");
define("XS_WX_NIU_notify_url","http://ins.66173.cn/nnb_xs_wx_notify.php");
define("WX_API_url", "https://api.mch.weixin.qq.com/pay/unifiedorder");

define("XY_APPID", "a20170118000002738");
define("XY_APPKEY", "90972fc9cc8114b04e3277e21d60dd88");
define("XY_MCD_ID", "m20170118000002738");
define("XY_API_url", "https://api.cib.dcorepay.com/pay/unifiedorder");
define("XY_STORE_APPID", "s20170314000002625");
define("XY_STORE_NAME", "福建牛牛网络手游市场");

define("ALI_MQB_notify_url", "http://charge.66173.cn/ali_qb_notify.php");
define("WX_MQB_notify_url", "http://charge.66173.cn/wx_qb_notify.php");

define("ALI_MQB_call_back_url", "http://m.66173.cn/ali_qb_return.php");
?>