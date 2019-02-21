<?php
header("Content-Type: text/html; charset=utf-8");
$order = time().time();
$result = array(
    'err_code'=>0,
    'desc'=>$order
);

echo json_encode($result);