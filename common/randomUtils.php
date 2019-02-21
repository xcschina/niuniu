<?php
class randomUtils {
	
	const TIME_DICT = '0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f|g|h|i|j|k|l|m|n|o|p|q|r|s|t|u|v|w|x|y|z|A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z';
	
	// GUID
	public static function guid(){
	    if (function_exists('com_create_guid')){
	        $uuid = com_create_guid();
	        return substr($uuid, 1, count($uuid) - 2);
	    }else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = // chr(123)// "{"
	                substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12);
	                // .chr(125);// "}"
	        return $uuid;
	    }
	}
	
	// 时间主键
	public static function getTimeID() {
		$dict = explode('|', self::TIME_DICT);

		$date = date("Ymd");
		$hours = $dict[date("G")];
		$minutes = $dict[intval(date("i"))];
		$seconds = $dict[intval(date("s"))];
		
		$miliseconds = time();
		$additional = substr($miliseconds, strlen($miliseconds) - 1);
		
		return 'ykt'.$date.$hours.$minutes.$seconds.$additional;
	}
	
	// 超强订单号
	// date("YmdHis").rand(100000,999999);
	public static function uniqueCode() {
		$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
		return $yCode[intval(date('Y')) - 2010] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)); 
	}
	
}
?>
