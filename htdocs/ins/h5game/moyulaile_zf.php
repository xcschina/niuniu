<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//$url = 'https://cdn0.myh5.90wmoyu.com/index.nnzf3.html?t='.time();
$url = 'https://cdn0.myh5.90wmoyu.com/index.nnzf.html?t='.time();
die(header("Location: ".$url));
