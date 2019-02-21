<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$input = file_get_contents("php://input");//$GLOBALS['HTTP_RAW_POST_DATA'];
parse_str($input, $data);
$params = json_decode(array_keys($data)[0],true);
$bo->err_log($bo->client_ip().":".var_export($data,1),'youxi9_notify');
$bo->err_log($bo->client_ip().":".var_export($params,1),'youxi9_notify1');
$dao = new index_dao();
if(!$params['status']){
    die('Error');
}else{
    $dao->sql = "select * from youxi9 where order_id=?";
    $dao->params = array($params['orderId']);
    $dao->doResult();
    $info = $dao->result;
    if(!$info || $info['status'] == '2' || $info['status'] == '3'){
        die('Error');
    }else{
        $dao->sql = "update youxi9 set status=?, merchant_order_id=?, callback_time=? where order_id=?";
        $dao->params = array($params['status'],$params['productId'], time(), $params['orderId']);
        $dao->doExecute();
        die('True');
    }
}