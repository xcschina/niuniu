<?php
define('APP', 'wx');
require_once '../../config.php';
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
session_start();
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define('WX_APPID','wxbaed68c7f2f3a62c');
define('WX_APPSECRET','8bae09f8cf665a6b1267c0ccbd77cd61');
define('WX_TOKEN','x25e2873d11ddb11f392dc870cbe521f');
define('WX_AESkey','XmmZ7sfvBMem3OLf8QBLNbC4iD3pJRaKJO8WZHomeER');

define("WX_API_url", "https://api.mch.weixin.qq.com/pay/unifiedorder");
define("WX_SECURE_notify_url", "http://ins.66173.cn/wx_secure_notify.php");
define("WX_APP_KEY", "e5467d037119f7bf072b27d93e58d2fa");
