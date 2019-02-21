<?php

class sdkCore{
	
    public $V;
    public $POST;
    public $GET;
    public $page;
    public $is_request_debug = false;
    public $usr_params;

    public function __construct(){
        try{
            date_default_timezone_set("PRC");
            $compile_dir = CACHE.'templates'.DS.APP.DS;
            $cache_dir = CACHE.'cache'.DS.APP.DS;
            $this->V = new Smarty();
            $this->V->template_dir  = VIEW;
            $this->V->compile_dir   = $compile_dir;
            $this->V->cache_dir     = $cache_dir;
            $this->V->config_dir    = COMMON.'smarty'.DS.'configs'.DS;
            $this->V->caching       = false;
            $this->cons_usr_header();
            $this->format_page();
        }catch(Exception $e) {
            print $e->getMessage();
            exit();
        }
        $this->assign("ip", $this->client_ip());
    }

    public function  __destruct() {
        unset($this->V, $this->GET, $this->POST);
    }

    // 获得分页显示对象
    function pageshow($page,$url){
    	// 格式化页码
    	if(!$page || intval($page) < 1) {
    		$page = 1;
    	} else if ($page>(ceil($this->DAO->total/PERPAGE))) {
    		$page=ceil($this->DAO->total/PERPAGE);
    	}
        
    	// 注：如果在pageCore()中加入ajax，则ajax对应的参数值表示，要异步调用的分页方法名，url为方法参数
    	// 如： new pageCore(array(..'ajax'=> 'doPage','url'=> '20, 3'));则客户调用为doPage(20,3);
    	$page = new pageCore(array('total'=>$this->DAO->total,
                                                           'perpage'=>PERPAGE,
                                                           'nowindex'=>$page,
                                                           'url'=>$url));
        return $page;
    }
    
    // 格式化页码
    function format_page() {
        if(!$this->page){$this->page=1;}
        if(isset($_GET["page"])){
            if(intval($_GET['page']) && intval($_GET['page']) > 0){
                $this->page = $_GET['page'];
            }
        }
    }

    // 重定向
    public function redirect($url){
        header("Location:http://".SITEURL."/".$url);
        exit();
    }

    public function object_to_array($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    public function orderid($app_id){
        $order_id = "88".$app_id.date('YmdHis').rand(10000000000,99999999999);
        $_SESSION['order_id'] = $order_id;
        return $order_id;
    }

    public function super_orderid($app_id){
        $order_id = "sp".$app_id.date('YmdHis').rand(10000000000,99999999999);
        $_SESSION['order_id'] = $order_id;
        return $order_id;
    }

    public function niu_orderid($app_id){
        $order_id = "66".$app_id.date('YmdHis').rand(10000000000,99999999999);
        $_SESSION['niu_order_id'] = $order_id;
        return $order_id;
    }

    public function client_ip(){
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    public function create_guid() {
    	$charid = strtolower(md5(uniqid(mt_rand(), true)));
    	$hyphen = chr(45);// "-"
    	$uuid = substr($charid, 0, 8).$hyphen
    	.substr($charid, 8, 4).$hyphen
    	.substr($charid,12, 4).$hyphen
    	.substr($charid,16, 4).$hyphen
    	.substr($charid,20,12);
    	return $uuid;
    }

    public function is_mobile(){
        $regExp="/nokia|iphone|ipod|android|samsung|htc|motorola|blackberry|ericsson|huawei|dopod|amoi|gionee|^sie\-|^bird|^zte\-|haier|";
        $regExp.="blazer|netfront|helio|hosin|novarra|techfaith|palmsource|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
        $regExp.="symbian|smartphone|midp|wap|phone|windows ce|CoolPad|webos|iemobile|^spice|longcos|pantech|portalmmm|";
        $regExp.="alcatel|ktouch|nexian|^sam\-|s[cg]h|^lge|philips|sagem|wellcom|bunjalloo|maui|";
        $regExp.="jig\s browser|hiptop|ucweb|ucmobile|opera\s*mobi|opera\*mini|mqqbrowser|^benq|^lct";
        $regExp.="480×640|640x480|320x320|240x320|320x240|176x220|220x176/i";
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            return true;
        }else{
            return @$_GET['mobile'] || isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']) || preg_match($regExp, strtolower($_SERVER['HTTP_USER_AGENT']));
        }
    }

    protected function up_img($img_name, $target_folder, $redirect, $thum_size = array(), $del_src_img = 1, $thum_ratio = 1, $new_file_name='') {
   		$upFile = new uploadHelper($img_name, $target_folder);
   		
   		if(count($thum_size) > 0){
   			$upFile->thum_size = $thum_size;
   			$upFile->del_src_img = $del_src_img;
   			$upFile->thum_ratio = $thum_ratio;
   		}
   		$upFile->upload($new_file_name);

   		if ($upFile->occur_err()) {
   			$error = $upFile->get_err_msgs();
   			$_SESSION['msg'] = $error[0];
   			$this->redirect($redirect);
   		} else {
   			return $upFile->get_rel_file_path();
   		}
   	}

    public function request($url, $post='', $header = array(), $timeout = 3){
        if($this->is_request_debug){
            $starttime = time();
        }
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

            if($post){
                curl_setopt($ch, CURLOPT_POST, 1);
                if(is_array($post)){
                    $post = http_build_query($post);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            if($header){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }

            $result = curl_exec($ch);
            $this->curl_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);

            if($this->is_request_debug){
                $curl_info = curl_getinfo($ch);
                $endtime = time();
                $dur = $endtime - $starttime;
                file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_url.log','开始时间:'.date('Y-m-d H:i:s', $starttime).'|结束时间:'.date('Y-m-d H:i:s', $endtime).'|用时:'.$dur.
                    '|结果:'.$result.'|curl_info:'.json_encode($curl_info)."\r\n",FILE_APPEND);
            }
            curl_close($ch);
            return $result;
        }catch (Exception $e){
            print_r($e);
        }
    }

    public function set_ua($ua){
        $ua = str_replace(" ","+",$ua);
        $ua = base64_decode(substr($ua,1));
        $ua = explode("&",$ua);
        return $ua;
    }

    public function err_log($word, $filename = 'sdk_log') {
        file_put_contents(PREFIX.DS.'logs/'.$filename.'_'.date('Ymd').'.log', strftime("%Y-%m-%d %H:%M:%S",time())."\r\n".$word."\r\n",FILE_APPEND);
    }

    public function cons_usr_header(){
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            $params = array();
            foreach ($header as $k => $param) {

                $param = explode("=", $param);
                $params[$param[0]] = $param[1];

                //应用id
                if ($param[0] == 'app_id') {
                    $params['pid'] = $param[1];
                }
                //用户id
                if ($param[0] == 'user_id') {
                    $params['usr_id'] = $param[1];
                }
                //系统固件版本
                if ($param[0] == 'osver') {
                    $params['osver'] = $param[1];
                }
                //游戏版本
                if ($param[0] == 'ver') {
                    $params['ver'] = $param[1];
                }

                //游戏网络
                if ($param[0] == 'net') {
                    $params['net'] = $param[1];
                }
                //所用设备
                if ($param[0] == 'mtype') {
                    $params['devicetype'] = $param[1];
                }
                //系统名
                if ($param[0] == 'dt') {
                    $params['systemnmae'] = $param[1];
                }
                //登录类型
                if ($param[0] == 'mode') {
                    $params['logintype'] = $param[1];
                }
                //sdk版本
                $sdkver = ''; //有的版本没有sdk头选项，防止出错
                if ($param[0] == 'sdkver') {
                    $params['sdkver'] = $param[1];
                }

                //mac
                if ($param[0] == 'mac') {
                    $params['mac'] = $param[1];
                }

                //adtid
                if ($param[0] == 'adtid') {
                    $params['adtid'] = $param[1];
                }

                //nickname
                if ($param[0] == 'nickname') {
                    $params['nickname'] = $param[1];
                }

                //md5
                if ($param[0] == 'md5') {
                    $params['md5'] = $param[1];
                }

                //md5
                if ($param[0] == 'channel') {
                    $params['channel'] = $param[1];
                }

                if ($param[0] == 'sid') {
                    $params['sid'] = $param[1];
                }
                //md5
                if ($param[0] == 'apple_id') {
                    $params['apple_id'] = $param[1];
                }
            }
            $this->usr_params = $params;

        }
    }

    public function out_put($str){
        die('0'.base64_encode($str));
    }

    protected function clearcr($str){
        //$str = str_replace(array("。",".","；",";","!","！"),$str."\r\n",$str);
        $str=preg_replace("{\r\n\r\n}","\r\n",$str);
        $str=preg_replace("{\r\r}","\r",$str);
        $str=preg_replace("{\n\n}","\n",$str);
        return $str;
    }

    function nl2p($text) {
        return str_replace("\n", "</p><p>", $text);
    }

    public function display($view_file){
        $this->V->display($view_file);
    }

    public function assign($var, $value){
        $this->V->assign($var, $value);
    }

    public function open_debug(){
        ini_set("display_errors","On");
        error_reporting(E_ALL);
    }
}