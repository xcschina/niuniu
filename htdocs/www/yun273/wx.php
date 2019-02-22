<?php
ini_set("display_errors","On");
error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

BO("account_web");
COMMON('paramUtils');
$bo = new account_web();
$data = array(
    'appid'=>'wx145b6dcbc2f651b5',
    'mch_id'=>'1503405421',
    'nonce_str'=>time().rand(11111,22222),
    'body'=>'H5支付-测试支付',
    'out_trade_no'=>time().rand(10,10000),
    'total_fee'=>'1',
    'spbill_create_ip'=>$bo->client_ip(),
    'notify_url'=>'http://www.yun273.com/wx_notify.php',
    'trade_type'=>'MWEB'
//    'trade_type'=>'NATIVE'
);
$wx = new wx();

$new_data = $wx->make_wx($data);
$xml_data = $wx->array_to_xml($new_data);
$request = $bo->request('https://api.mch.weixin.qq.com/pay/unifiedorder',$xml_data,array('Content-type: text/xml'));
$request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

$redirect_url='http://website.yun273.com';
//$url= $request_data['code_url'];
//var_dump($url);
//exit();
$url= $request_data['mweb_url'];
//$url= $request_data['mweb_url'].'&redirect_url='.urlencode($redirect_url);
//header("Referer: www.yun273.com");
//header("Location: $url");
//exit();
$bo->V->assign('url',$url);
$bo->V->display('wx/index.html');
class wx {
    public function make_wx($data){
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=45490923012266169037366179732522";
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

}