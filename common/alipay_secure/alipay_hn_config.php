<?php
/**
	*功能：设置帐户有关信息及返回路径（基础配置页面）
	*版本：1.0
	*日期：2011-09-15
	*说明：
	*以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
	*该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/

//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

//合作伙伴ID
$partner = "2088521670831324";
//签约支付宝账号或卖家支付宝帐户
$seller = "zl@66173.cn";
//异步返回消息通知页面，用于告知商户订单状态
$notify_url = "http://ins.66173yx.com/secure_notify.php";
//同步返回消息通知页面，用于提示商户订单状态
$call_back_url = "http://ins.66173yx.com/";
//产品名称
$subject = "test";
//请与贵网站订单系统中的唯一订单号匹配，为方便演示使用时间随机
$out_trade_no = date("Ymdhms");
//字符编码格式
$_input_charset = "utf-8";
?>