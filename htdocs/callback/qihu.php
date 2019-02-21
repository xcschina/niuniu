<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$params = $data = $_GET;
$bo->err_log($bo->client_ip()."\r".var_export($_GET,1),'qihu_super_callback');
$result = array('status'=>'error','delivery'=>'other','msg'=>'订单信息异常！');
if(empty($super_id)){
    $result['msg'] = '缺少必要参数.';
    die(json_encode($result));
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    $result['msg'] = '应用信息获取失败.';
    die(json_encode($result));
}

if($params['gateway_flag']=='success'){
    unset($data['id']);
    unset($data['sign_return']);
    unset($data['sign']);
    foreach($data as $k=>$v){
        if(empty($v)){
            unset($data[$k]);
        }
    }
    ksort($data);
    $sign_str = implode('#',$data);
    $sign_str = $sign_str.'#'.$ch_app_info['param1'];
    if($params['sign'] != md5($sign_str)){
        $result['msg'] = 'sign验证失败.';
        die(json_encode($result));
    }
    $order_info = $DAO->get_super_order($params['app_order_id']);
    if(!$order_info){
        $result['msg'] = '订单号查询失败.';
        die(json_encode($result));
    }

    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($params['app_order_id'],time(),$params['1810247276583361841']);
    }
    $result['status'] = 'ok';
    $result['delivery'] = 'success';
    $result['msg'] = '订单完成';
}
die(json_encode($result));