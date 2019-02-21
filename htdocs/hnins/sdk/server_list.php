<?php
header("Content-Type: text/html; charset=utf-8");
$server=array(
    array(
        'serv_id'=>'1260001',
        'serv_name'=>'五行山'
    ),
    array(
        'serv_id'=>'1260002',
        'serv_name'=>'长安城'
    ),
    array(
        'serv_id'=>'1260003',
        'serv_name'=>'3区'
    )
);
echo json_encode(array(
    'err_code'=>0,
    'serv'=>$server
));