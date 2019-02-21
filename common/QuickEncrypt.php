<?php

/**
 * QuickSDK游戏同步加解密算法描述
 * @copyright quicksdk 2015
 * @author quicksdk
 * @version quicksdk v 0.0.1 2014/9/2
 */

class QuickEncrypt{

    /**
     * 解密方法
     * $strEncode 密文
     * $keys 解密密钥 为游戏接入时分配的 callback_key
     */
    public function decode($strEncode, $keys) {
        if(empty($strEncode)){
            return $strEncode;
        }
        preg_match_all('(\d+)', $strEncode, $list);
        $list = $list[0];
        if (count($list) > 0) {
            $keys = self::getBytes($keys);
            for ($i = 0; $i < count($list); $i++) {
                $keyVar = $keys[$i % count($keys)];
                $data[$i] =  $list[$i] - (0xff & $keyVar);
            }
            return self::toStr($data);
        } else {
            return $strEncode;
        }
    }

    /**
     * 计算游戏同步签名
     */
    public static function getSign($params,$md5Key){

        return md5($params['nt_data'].$params['sign'].$md5Key);
    }

    /**
     * 转成字符数据
     */
    private static function getBytes($string) {
        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    /**
     * 转化字符串
     */
    private static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
}

?>