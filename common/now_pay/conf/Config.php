<?php
    /**
     *a 
     * @author Jupiter
     * 
     * 应用配置类
     */
    class Config{
        static $timezone="Asia/Shanghai";

        static $app_id="149086399857367";//该处配置您的APPID
        static $md5_key="bdgIyUGIfiLSp1G2T184tFC94Fl6UglF";//该处配置您的应用秘钥
        static $des_key="6CX4ZYK3Yb4RKzSiJBNsQHRc";//该处配置您的DES秘钥  行业

        static $trans_url="https://dby.ipaynow.cn/sms";
//        static $trans_url="https://sms.ipaynow.cn";
        static $query_url="https://dby.ipaynow.cn/sms";
        static $zfbAppId="147868777472129";
        static $zfbKey="1FZMAlAplOTamX6OARDVV8hrswhbGEVg";//支付宝测试参数
//        static $wxAppId="153364169308830";
//        static $wxKey="UYko5ZEZHb9WrGL8Jws6Qr14kGkOBrxf";//微信测试参数
        //盈趣游戏参数
        static $wxAppId1="154147070627754";
        static $wxKey1="mCa1RPXlEcxSBhP3ac9QcRCyHd66RCva";//微信参数
        //奥米加游戏
        static $wxAppId="154147077418173";
        static $wxKey="xbWAG6urNEMWoZDIBvqVWTlKopO3J2w5";//微信测试参数


        static $ylAppId="149967852972225";
        static $ylKey="Mr0rsEanxcnVJFCQPbj8NmfLw8Jx1OyZ";//银联测试参数
        static $trade_time_out="3600";
        static $front_notify_url="http://106.75.78.43:7890/front_notify.php";
        static $back_notify_url="http://106.75.78.43:7890/notify.php";

        const TRADE_URL="https://pay.ipaynow.cn";//正式交易接口地址
        const QUERY_URL="https://pay.ipaynow.cn";//正式查询接口地址

//        const TRADE_URL="https://p.ipaynow.cn";//测试交易接口地址
//        const QUERY_URL="https://p.ipaynow.cn";//测试查询接口地址

        const VERIFY_HTTPS_CERT=false;
        const CHECK_FUNCODE="INDUSTRY_TEMP";
        const QUERY_FUNCODE="ID01_Query";
        const CHARSET="UTF-8";
        const QSTRING_EQUAL="=";
        const QSTRING_SPLIT="&";
        const SERVER_PARSE_SUCCESS="1";
        const SERVER_PARSE_FAIL="0";
        const TRADE_FUNCODE="WP001";
        const NOTIFY_FUNCODE="N001";
        const FRONT_NOTIFY_FUNCODE="N002";
        const TRADE_TYPE="01";
        const TRADE_CURRENCYTYPE="156";
        const TRADE_DEVICE_TYPE="0601";
        const TRADE_SIGN_TYPE="MD5";
//        const TRADE_QSTRING_EQUAL="=";
//        const TRADE_QSTRING_SPLIT="&";
        const TRADE_FUNCODE_KEY="funcode";
        const TRADE_DEVICETYPE_KEY="deviceType";
        const TRADE_SIGNTYPE_KEY="mhtSignType";
        const TRADE_SIGNATURE_KEY="mhtSignature";
        const SIGNATURE_KEY="signature";
        const SIGNTYPE_KEY="signType";

    }
