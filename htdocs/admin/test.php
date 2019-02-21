<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("kamen_admin");
//http://api.hengjing1688.com
//niuniuwl
//66173niuniu.
$url = "http://api.hengjing1688.com";
$user = 'niuniuwl';
$pwd = '66173niuniu.';

$merchant_id = 10003;
$key = 'brsqkrbqcjcwiac';

$bo = new kamen_admin();
error_reporting(E_ALL);
ini_set("display_errors","on");
$products = $bo->request("api.hengjing1688.com/Api/QueryAllProduct");

$products = (array)simplexml_load_string($products, 'SimpleXMLElement', LIBXML_NOCDATA);

$QBs = array();
foreach($products['product'] as $k => $v){
    if(strrpos($v->name, 'QB') !== FALSE){
        $QBs[] = $bo->object_to_array($v);
    }
}

$qbs = array();
foreach ($QBs as $qb) {
    $qbs[] = $qb['price'];
}

array_multisort($qbs, SORT_ASC, $QBs);
print_r($QBs);
