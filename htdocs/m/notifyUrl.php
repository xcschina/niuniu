<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/18
 * Time: 14:44
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO('notify_url');
$bo = new notify_url();
$bo->index();
