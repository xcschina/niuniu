<?php
COMMON('now_pay/conf/Config');
	if (function_exists("date_default_timezone_set")) {
        date_default_timezone_set(Config::$timezone);
    }
/**
 * 
 * 签名接口
 * 用于对支付信息进行重组和签名
 * 
 */	

class Signature{
	//获取参数
	function getDate($arr) {
		$result  = array();
		$funcode = $arr[Config::TRADE_FUNCODE_KEY];
		foreach($arr as $key => $value) {
			if (($funcode==Config::TRADE_FUNCODE)&&!($key==Config::TRADE_SIGNATURE_KEY||$key==Config::SIGNATURE_KEY)){
                $result[$key]=$value;
                continue;
            }
            if(($funcode==Config::NOTIFY_FUNCODE||$funcode==Config::FRONT_NOTIFY_FUNCODE)&&!($key==Config::SIGNATURE_KEY)){
                $result[$key]=$value;
                continue;
            }
            if (($funcode==Config::QUERY_FUNCODE)&&!($key==Config::TRADE_SIGNATURE_KEY||$key==Config::SIGNATURE_KEY)) {
                $result[$key]=$value;
                continue;
            }
		}
		return $result;
	}
	//获取签名
	function createSignaTure($arr, $key) {
		$date = $this -> getDate($arr);
		ksort($date);
		$str = '';
		foreach($date as $k => $v) {
			if($v != '') {
				$str .= $k.Config::QSTRING_EQUAL.$v.Config::QSTRING_SPLIT;
			}
		}
		$str .= strtolower(md5($key));
		return(strtolower(md5($str)));
	}

	//获取请求参数串
	function getToStr($arr, $key) {
		$date = $this -> getDate($arr);
		$info = $this -> createSignaTure($arr, $key);
		$str = '';
		foreach($date as $k => $v) {
			if($v != '') {
				$str .= $k.Config::QSTRING_EQUAL.urlencode($v).Config::QSTRING_SPLIT;
			}	
		}
		$str .= Config::TRADE_SIGNATURE_KEY.Config::QSTRING_EQUAL.$info;
		file_put_contents('../baowen.log', $str."\r\n");
		return $str;
	}
}