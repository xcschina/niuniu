<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$prarms = $post_data = $_POST;
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'x7sy_super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("缺少必要参数。");
}
$key = $ch_app_info['app_key'];
$public_key = $ch_app_info['param2'];
$post_sign_data = base64_decode($post_data["sign_data"]);
unset($post_data["sign_data"]);
ksort($post_data);
$sourcestr=http_build_query_noencode($post_data);
$publicKey = ConvertPublicKey($public_key);
$verify = Verify($sourcestr, $post_sign_data,$publicKey);
if($verify!=1){
    ReturnResult('sign_data_verify_failed');
}

$post_encryp_data_decode = base64_decode($post_data["encryp_data"]);
$decode_encryp_data = PublickeyDecodeing($post_encryp_data_decode,$publicKey);
parse_str($decode_encryp_data,$encryp_data_arr);
if(!isset($encryp_data_arr["pay_price"]) || !isset($encryp_data_arr["guid"]) || !isset($encryp_data_arr["game_orderid"])){
    ReturnResult('encryp_data_decrypt_failed');
}

if(!isset($encryp_data_arr['game_orderid']) || $encryp_data_arr['game_orderid']!=$post_data['game_orderid']){
    ReturnResult("failed:".$needCompareData["game_orderid"]);
}
$post_data+=$encryp_data_arr;
foreach($needCompareData as $key => $value){
    if($key=="pay_price"){
        if(bccomp($post_data[$key],$arr[$key],2)!=0){
            ReturnResult("failed:".$value);
        }
    }else if($arr[$key]!=$post_data[$key]){
        ReturnResult("failed:".$value);
    }
}
$order_info = $DAO->get_super_order($post_data['game_orderid']);
if(!$order_info){
    ReturnResult("failed:".'error');
}
if($order_info['status'] != '2'){
    $DAO->update_super_order_info($post_data['game_orderid'],time(),$post_data['xiao7_goid']);
    ReturnResult("success");
}
ReturnResult("success");


function ConvertPublicKey($public_key){
    $public_key_string = "";
    $count=0;
    for($i=0;$i<strlen($public_key);$i++){
        if($count<64){
            $public_key_string.=$public_key[$i];
            $count++;
        }else{
            $public_key_string.=$public_key[$i]."\r\n";
            $count=0;
        }
    }
    $public_key_header = "-----BEGIN PUBLIC KEY-----\r\n";
    $public_key_footer = "\r\n-----END PUBLIC KEY-----";
    $public_key_string = $public_key_header.$public_key_string.$public_key_footer;
    return $public_key_string;
}

function http_build_query_noencode($queryArr){
    if(empty($queryArr)){
        return "";
    }
    $returnArr=array();
    foreach($queryArr as $key => $value){
        $returnArr[]=$key."=".$value;
    }
    return implode("&",$returnArr);
}


function Verify($sourcestr, $sign_dataature, $publickey){
    $pkeyid = openssl_get_publickey($publickey);
    $verify = openssl_verify($sourcestr, $sign_dataature, $pkeyid);
    openssl_free_key($pkeyid);
    return $verify;
}
function PublickeyDecodeing($crypttext, $publickey){
    $pubkeyid = openssl_get_publickey($publickey);
    if (openssl_public_decrypt($crypttext, $sourcestr, $pubkeyid, OPENSSL_PKCS1_PADDING)){
        return $sourcestr;
    }
    return FALSE;
}

function ReturnResult($text){
    echo $text;
    exit();
}