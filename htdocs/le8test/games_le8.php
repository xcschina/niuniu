<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 乐吧账号登入验证
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$bo = new le8_pay_web();
$bo->le8_games();