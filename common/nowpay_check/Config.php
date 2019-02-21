<?php
class Config{
    static $app_id="151141772239068";//该处配置您的APPID
    static $md5_key="w7ziHWgYkA4QNwPpCslZkJrDSazUxPuu";//该处配置您的应用秘钥
    static $des_key="KFQPeF5tpW8W2WwTJmfRfQas";//该处配置您的DES秘钥
    static $trans_url="https://s.ipaynow.cn/auth";
    static $query_url="https://s.ipaynow.cn/auth";

    const CHECK_FUNCODE="ID01";
    const QUERY_FUNCODE="ID01_Query";
    const QSTRING_EQUAL="=";
    const QSTRING_SPLIT="&";
    const SERVER_PARSE_SUCCESS="1";
    const SERVER_PARSE_FAIL="0";
}