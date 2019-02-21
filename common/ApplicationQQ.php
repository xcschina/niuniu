<?php

class ApplicationQQ{
    private $businessKey;
    private $businessId;
    private $client_ip;
    private $androidId;
    private $imei;
    private $macAddress;
    private $manufacture;
    private $mode;

    public function __construct(){
        $this->businessId = "3555055471_niuguoapp";
        $this->businessKey = "bfd1693962c031a38e97af17aaf7f2a7";
        if ($_SERVER['REMOTE_ADDR']){
            $this->client_ip = $_SERVER['REMOTE_ADDR'];
        }else{
            $this->client_ip = "127.0.0.1";
        }
        //获取公用头部信息
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'android_id') {
                    $this->androidId = $param[1];
                    if (!$this->androidId){
                        $this->androidId = "";
                    }
                }
                if ($param[0] == 'imei') {
                    $this->imei = $param[1];
                    if (!$this->imei){
                        $this->imei = "867677023751933";
                    }
                }
                if ($param[0] == 'mac') {
                    $this->macAddress = $param[1];
                    if (!$this->macAddress){
                        $this->macAddress = "64:a6:51:bc:2d:9a";
                    }
                }
                if ($param[0] == 'mtype') {
                    $this->manufacture = $param[1];
                    if (!$this->manufacture){
                        $this->manufacture = "HUAWEI";
                    }
                }
                if ($param[0] == 'device_model') {
                    $this->mode = $param[1];
                    if (!$this->mode){
                        $this->mode = "G621-TL00";
                    }
                }
            }
        }
    }
    public function sendApi($api,$data,$post=1){
        //构造请求包体
        $req_body = '{
            "body":'.$data.',
            "head":{
                "businessId":"'.$this->businessId.'",
                "callbackPara":"callback01",
                "nonce":'.mt_rand(1000000,9999999).',
                "timestamp":'.time().',
                "client_ip":"'.$this->client_ip.'",
                "terminal":{
                    "androidId":"'.$this->androidId.'",
                    "imei":"'.$this->imei.'",
                    "imsi":"0",
                    "macAddress":"'.$this->macAddress.'",
                    "manufacture":"'.$this->manufacture.'",
                    "mode":"'.$this->mode.'"
                }
            }
        }';
        //生成签名
        $signature = md5($req_body.$this->businessKey);
        //构造接入URL
        $url = APIADDRESS.$api."?output=json&signature=".$signature;
        //初始化curl
        $ch = curl_init();
        //参数设置
        curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, $post);
        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req_body);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec ($ch);
        curl_close($ch);
        return json_decode($res,true);
    }
}