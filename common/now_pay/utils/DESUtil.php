<?php

//require_once '../conf/Config.php';
COMMON('now_pay/conf/Config');
/**
 * Created by PhpStorm.
 * User: John
 * Date: 2015/9/24
 * Time: 14:26
 */
class DESUtil
{
    public static function encrypt($data){

        if($len = strlen($data) % 8) {
            for($i=0;$i<8-$len;$i++) {
                $data .= ' ';
            }
        }

        return openssl_encrypt(
            $data,
            'DES-EDE3' ,
            Config::$des_key,
            OPENSSL_RAW_DATA | OPENSSL_NO_PADDING ,
            ''
        );

    }

    public static function decrypt($data){
        return openssl_decrypt(
            $data,
            'DES-EDE3' ,
            Config::$des_key,
            OPENSSL_RAW_DATA | OPENSSL_NO_PADDING ,
            ''
        );

    }
}
