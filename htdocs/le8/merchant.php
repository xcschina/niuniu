<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 牛币支付首页
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");
die("会话已经过期,请关闭。");
//$_SESSION['user_id'] = $user_info['user_id'];
//$_SESSION['le8_user_id'] = $user_info['le8_user_id'];
//$_SESSION['nnb'] = $user_info['nnb'];
//$_SESSION['mobile'] = $user_info['mobile'];
//$_SESSION['nick_name'] = $user_info['nick_name'];
//$_SESSION['trade_order_id'] = $trade_order_id;
//$_SESSION['le_appid'] = $appid;
//$_SESSION['pay_money'] = $pay_money/100;
//$_SESSION['goods_name'] = urldecode($goods_name);
//
//if(!$_SESSION['user_id'] || !$_SESSION['le8_user_id'] || !$_SESSION['nnb'] || !$_SESSION['mobile'] || !$_SESSION['nick_name']
//|| !$_SESSION['trade_order_id'] || !$_SESSION['le_appid'] || !$_SESSION['pay_money'] || !$_SESSION['goods_name'] || !$_SESSION['le8_sign']){
//die("会话已经过期,请关闭。");
//}
//
//$bo = new le8_pay_web();
//$bo->pay_index_view($_SESSION['le8_user_id'], $_SESSION['pay_money']*100, $_SESSION['trade_order_id'], $_SESSION['le_appid'], $_SESSION['goods_name'], $_SESSION['le8_sign'], strto);