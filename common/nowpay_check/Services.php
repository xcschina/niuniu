<?php
COMMON('sdkCore','nowpay_check/Config','nowpay_check/Tools');
class Services extends sdkCore{
    public function buildAndSendCheckReq($req)
    {
        $req_content = Services::buildReqTemplate(Config::CHECK_FUNCODE, $req);
        $resp_content = $this->request(Config::$trans_url,$req_content,array(),5);
        return self::parseResp($resp_content);
    }

    public function buildAndSendQueryReq($req)
    {
        $req_content = Services::buildReqTemplate(Config::QUERY_FUNCODE, $req);
        $resp_content = $this->request(Config::$query_url,$req_content,array(),5);
        return self::parseResp($resp_content);
    }

    private static function buildReqTemplate($funcode,$req_content)
    {
        $original_text = Tools::createLinkString($req_content, true, false);
        $header = "funcode=" . $funcode;
        $message_data_one = base64_encode('appId='. Config::$app_id);
        $message_data_two = base64_encode(mcrypt_encrypt(MCRYPT_3DES,Config::$des_key,$original_text,MCRYPT_MODE_ECB));
        $message_data_three = base64_encode(md5($original_text . '&' . Config::$md5_key));
        $message=urlencode($message_data_one . '|' . $message_data_two . '|' . $message_data_three);
        return $header . '&message=' . $message;
    }

    private static function parseResp($resp_content)
    {
        $resp = explode("|", $resp_content);
        if ($resp[0]==Config::SERVER_PARSE_SUCCESS) {
            //现在支付服务器解析成功后的处理
            return self::parseSuccessResp($resp);
        } else {
            //现在支付服务器解析失败后的处理
            return self::parseErrorResp($resp);
        }
    }

    private static function parseSuccessResp($resp){
        $original_text=trim(mcrypt_decrypt(MCRYPT_3DES,Config::$des_key,base64_decode($resp[1]),MCRYPT_MODE_ECB));
        $server_sign=base64_decode($resp[2]);
        if(self::isCheckSignatureSucess($original_text,$server_sign)){
            //签名验证成功处理
            return $original_text;
        }else{
            //签名验证失败处理
            return "CHECK_SIGN_FAILS";
        }
    }

    private static function parseErrorResp($resp){
        return base64_decode($resp[1]);
    }

    /**
     * 判断是否验证签名成功
     * @param $original_text
     * @param $server_sign
     * @return bool
     */
    private static function isCheckSignatureSucess($original_text,$server_sign){
        $local_sign=md5($original_text."&".Config::$md5_key);
        if($local_sign==$server_sign){
            //验证签名成功
            return true;
        }else{
            //验证签名失败
            return false;
        }
    }
}