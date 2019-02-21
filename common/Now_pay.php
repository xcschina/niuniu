<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/10
 * Time: 19:09
 */
class NowPay{
    function __construct() {
        //应用Id
        $this->AppId = '151141772239068';
        //请求地址
        $this->Url = 'https://dby.ipaynow.cn/sms';
    }
}