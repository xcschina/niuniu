<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
$url = 'http://cdn0.myh5.90wmoyu.com/index.nnandroid.html?t='.time();
die(header("Location: ".$url));
