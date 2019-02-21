<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

BO('kksc');
$bo = new kksc();
$bo->kksc();
