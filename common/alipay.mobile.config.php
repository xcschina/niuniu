<?php
/**
	*功能：设置帐户有关信息及返回路径（基础配置页面）
	*版本：2.0
	*日期：2011-11-04
	*说明：
	*以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
	*该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/

//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
define("ALI_MOBILE_partner", "2088911899697331");
define("ALI_MOBILE_key", "t1ek1l0mt2b7clnp383ugj5zt597ldtn");
define("ALI_MOBILE_seller_email", "crab17173@163.com");
define("ALI_MOBILE_sec_id", "MD5");
define("ALI_MOBILE_notify_url", "http://charge.66173.cn/ali_h5_notify.php");
define("ALI_MOBILE_call_back_url", "http://m.66173.cn/ali_return.php");
define("ALI_MOBILE_merchant_url", "http://m.66173.cn/");
define("ALI_MOBILE_Service_Create", "alipay.wap.trade.create.direct");
define("ALI_MOBILE_Service_authAndExecute", "alipay.wap.auth.authAndExecute");
define("ALI_MOBILE_format", "xml");
define("ALI_MOBILE_v", "2.0");
define("ALI_MOBILE_input_charset", "utf-8");
define("ALI_SECURE_notify_url", "http://charge.66173.cn/ali_notify.php");

define("ALI_SHOP_MOBILE_merchant_url", "http://shop.66173.cn/"); 
define("ALI_SHOP_MOBILE_call_back_url", "http://shop.66173.cn/ali_return.php");

//乐8独立业务
define("ALI_LE8_notify_url", "http://charge.66173.cn/ali_le8_notify.php");
define("ALI_LE8_call_back_url", "http://le8test.66173.cn/ali_return.php");
define("ALI_LE8_merchant_url", "http://le8test.66173.cn/merchant.php");
?>