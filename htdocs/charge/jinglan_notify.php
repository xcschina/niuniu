<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'jinglan_notify');
$params = json_decode(json_encode(json_decode(html_entity_decode($_POST['data']))),TRUE);
$dao = new index_dao();
if(!$params['orderState']){
    die('Error');
}else{
    $dao->sql = "select * from jinglan where order_id=?";
    $dao->params = array($params['merOrderNo']);
    $dao->doResult();
    $info = $dao->result;
    if(!$info || $info['status'] == '3' || $info['status'] == '4'){
        die('Error');
    }else{
        if($params['orderState'] == '24'){
            $dao->sql = "update jinglan set status=3, jinglan_order_id=?, callback_time=? where order_id=?";
            $dao->params = array($params['orderNo'], time(), $params['merOrderNo']);
            $dao->doExecute();
            die('True');
        }else{
            if($params['orderState']=='23'){
                $status = 4;
            }elseif($params['orderState']=='24'){
                $status = 3;
            }
            $dao->sql = "update jinglan set status=?, jinglan_order_id=?, callback_time=? where order_id=?";
            $dao->params = array($status,$params['orderNo'], time(), $params['merOrderNo']);
            $dao->doExecute();
            die('True');
        }
    }
}