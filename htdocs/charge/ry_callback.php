<?php
require_once 'config.php';
COMMON('baseCore');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_GET,1),'ry_callback');
//$securitykey = '447ad97d40c592fbda322ec14794132a';
//$skey_str =  strtoupper($_GET['activetime'].'_'.strtoupper($_GET['appkey']).'_'.$securitykey);
//if(md5($skey_str) == $_GET['skey']){
//    $dao = new index_dao();
//    $dao->sql = "insert into `niuniu`.ry_callback(spreadurl,spreadname, channel, clicktime, ua, uip,
//                                                appkey,activetime,osversion,devicetype,idfa,mac,imei,skey,addtime)
//                values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
//    $dao->params = array($_GET['spreadurl'], $_GET['spreadname'], $_GET['channel'], $_GET['clicktime'], $_GET['ua'], $_GET['uip'],
//        $_GET['appkey'],$_GET['activetime'],$_GET['osversion'],$_GET['devicetype'],$_GET['idfa'],$_GET['mac'],$_GET['imei'],$_GET['skey'],time());
//    $dao->doInsert();
//    echo "success";
//}
if(!empty($_GET['appkey'])){
    $dao = new index_dao();
    $dao->sql = "insert into `niuniu`.ry_callback(spreadurl,spreadname, channel, clicktime, ua, uip, 
                                                appkey,activetime,osversion,devicetype,idfa,mac,imei,skey,addtime)
                values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $dao->params = array($_GET['spreadurl'], $_GET['spreadname'], $_GET['channel'], $_GET['clicktime'], $_GET['ua'], $_GET['uip'],
        $_GET['appkey'],$_GET['activetime'],$_GET['osversion'],$_GET['devicetype'],$_GET['idfa'],$_GET['mac'],$_GET['imei'],$_GET['skey'],time());
    $dao->doInsert();
    echo "success";
}else{
    die('fail');
}

