<?php

COMMON('now_pay/services/Service','now_pay/conf/Config','now_pay/utils/Log');
/**
 * Created by PhpStorm.
 * User: John
 * Date: 2015/9/24
 * Time: 13:56
 */
class Check{
    //行业模版短信下发接口
    public function toIndustryTemp($mobile,$code,$temp_id){
        $req = array();
        $req["funcode"] = 'INDUSTRY_TEMP';
		$req["appId"] = config::$app_id;
        $req["mhtOrderNo"] = date("YmdHis");
        $req["mobile"] = $mobile;
        $temp = array(
            'tempCode'=>$temp_id,
            'param'=>array('code'=>$code)
        );
        $req['templateInfo'] = json_encode($temp);  //填入模板值和code
		$req["notifyUrl"] = "http://www.baidu.com";
        $req_content = Services::buildAndSendCheckReq($req);
        Log::outLog("验证信息接口应答:",$req_content);
        echo $req_content;
    }

    //行业短信下发接口
    function toIndustry($mobile,$code){
        $req = array();
        $req["funcode"] = 'S01';
        $req["appId"] = config::$app_id;
        $req["mhtOrderNo"] = date("YmdHis").rand(1001, 9999);
        $req["mobile"] = $mobile;
        $req['content'] = "【牛牛网络】您的验证码是".$code."。5分钟之内有效";  //填入短信内容
        $req["notifyUrl"] = "http://www.baidu.com";
        $req_content = Services::buildAndSendCheckReq($req);
        Log::outLog("验证信息接口应答:",$req_content);
        echo $req_content;
    }

    //行业短信批量下发接口
    function toIndustryBatch($mobile,$code){
        $req = array();
        $req["funcode"] = 'S02';
        $req["appId"] = config::$app_id;
        $content = array();
        foreach($mobile as $key=>$data){
            $content[$key]['content'] = "【牛牛网络】您的验证码是".$code."。5分钟之内有效";
            $content[$key]['mchOrderNo'] = date("YmdHis").rand(1001, 9999);
            $content[$key]['phone'] = $data;
        }
        $req['contents'] = json_encode($content);  //填入短信内容
        $req["notifyUrl"] = "http://www.baidu.com";
        $req_content = Services::buildAndSendCheckReq($req);
        Log::outLog("验证信息接口应答:",$req_content);
        echo $req_content;
    }

    //营销短信下发接口
    function toMarketing($mobile,$code,$temp_id){
        $req = array();
        $req["funcode"] = 'MARKET_TEMP';
        $req["appId"] = config::$app_id;
        $req["mhtOrderNo"] = date("YmdHis");
        $req["mobile"] = $mobile;
        $temp = array(
            'tempCode'=>$temp_id,
            'param'=>array('code'=>$code)
        );
        $req['templateInfo'] = json_encode($temp);  //填入模板值和code
        $req["notifyUrl"] = "http://www.baidu.com";
        $req_content = Services::buildAndSendCheckReq($req);
        Log::outLog("验证信息接口应答:",$req_content);
        echo $req_content;
    }

    //营销批量短信下发接口
    function toMarketingBatch(){

    }
}