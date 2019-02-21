<?php
/**
 * Created by PhpStorm.
 * User: may
 * Date: 17/5/18
 * Time: 19:59
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("app_guide");
$bo = new app_guide();
$bo->guide_view();
