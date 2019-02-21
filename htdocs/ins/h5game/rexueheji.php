<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
$url = 'https://mfrk.lyzbh5.jiulingwan.com/59/login_android.php?t='.time();
die(header("Location: ".$url));
