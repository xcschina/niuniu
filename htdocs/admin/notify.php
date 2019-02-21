<?php
require_once 'config.php';
COMMON('baseCore');
$bo = new baseCore();
$request=file_get_contents('php://input');
parse_str($request,$data);
$bo->err_log($bo->client_ip().":".var_export($data,1),'XZZF_notify');
//$params = json_decode(json_encode(json_decode(html_entity_decode($_POST['data']))),TRUE);

echo $data;
echo('回调成功');