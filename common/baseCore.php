<?php 
COMMON('formCheck');

class baseCore extends formCheck{
	
    public $V;
    public $POST;
    public $GET;
    public $page;
    public $pageCurrent;//(适用J-UI分页)
    public $is_request_debug = false;
    public $usr_params;
    public $mmc;
    public $user_id;
    public $nick_name;
    public $usr_level;
    public $usr_mobile;
    public $usr_gxgold;
    public $ip;
    public $SMS_TYPE = array(
        'PWD_BACK_VERIFY_CODE'      => 12739,
        'MAIL_CHANGE_VERIFY_CODE'   => 12740,
        'MAIL_CHANGE_SUCCESS'       => 12354,
        'MOBILE_BIND_VERIFY_CODE'   => 12355,
        'MOBILE_CHANGE_VERIFY_CODE' => 12356,
        'SDK_VERIFY_CODE'           => 12717
    );

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
			
            $this->format_page();
            $this->format_pageCurrent();
        }catch(Exception $e) {
            print $e->getMessage();
            exit();
        }
        $this->assign("ip", $this->client_ip());
    }

    public function  __destruct() {
        unset($this->V, $this->GET, $this->POST);
    }

    function pageshow($page, $url){
    	// 格式化页码
    	if(!$page || intval($page) < 1) {
    		$page = 1;
    	} else if ($page>(ceil($this->DAO->total/PERPAGE))) {
    		$page=ceil($this->DAO->total/PERPAGE);
    	}
    	$page = new pageCore(array('total'=>$this->DAO->total,'perpage'=>PERPAGE,'nowindex'=>$page,'url'=>$url));
        return $page;
    }

    function format_page() {
        if(!$this->page){$this->page=1;}
        if(isset($_GET["page"])){
            if(intval($_GET['page']) && intval($_GET['page']) > 0){
                $this->page = $_GET['page'];
            }
        }elseif(isset($_POST["page"])){
            if(intval($_POST['page']) && intval($_POST['page']) > 0){
                $this->page = $_POST['page'];
            }
        }
        $this->V->assign("page",$this->page);
    }

    public function pageInfo($pageCurrent,$page_size=PERPAGE){
        $page['pageCurrent']=$pageCurrent;//当前页
        $page['pageTotal']=$this->DAO->total;//总记录
        $page['pageSize']=$page_size;//每页记录数
        $page['pageNum']=$pageCurrent;//数字页码
        $this->V->assign("page",$page);
    }

    function format_pageCurrent() {
        if(!$this->pageCurrent){$this->pageCurrent=1;}
        if(isset($_POST["pageCurrent"])){
            if(intval($_POST['pageCurrent']) && intval($_POST['pageCurrent']) > 0){
                $this->pageCurrent = $_POST['pageCurrent'];
            }
        }
        return $this->pageCurrent;
    }

    function get_online_qq($qq){
        $new_qq=array();
        foreach ($qq as $key => $data) {
            $url="http://wpa.qq.com/msgrd?v=3&uin=".$data."&site=qq&menu=yes";
            $html=file_get_contents($url);
            $online = strpos($html,"该链接会唤起手机QQ");
            if($online||!empty($online)){
                array_push($new_qq,$data);
            }
        }
        return $new_qq;
    }

    public function redirect($url){
        header("Location:http://".SITEURL."/".$url);
    }

    public function orderid($app_id){
        $order_id = $app_id.date('YmdHis').rand(100000,999999);
        $_SESSION['order_id'] = $order_id;
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

    function get_ip_lookup($ip = ''){
        if(empty($ip)){
            $ip = $this->client_ip();
        }
        $res = $this->request('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip='. $ip);
        if(empty($res)){ return false; }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if(!isset($jsonMatches[0])){ return false; }
        $json = json_decode($jsonMatches[0], true);
        if(isset($json['ret']) && $json['ret'] == 1){
            $json['ip'] = $ip;
            unset($json['ret']);
        }else{
            return false;
        }
        return $json;
    }

    public function create_guid() {
    	$charid = strtolower(md5(uniqid(mt_rand(), true)));
    	$hyphen = chr(45);
    	$uuid = substr($charid, 0, 8).$hyphen
    	.substr($charid, 8, 4).$hyphen
    	.substr($charid,12, 4).$hyphen
    	.substr($charid,16, 4).$hyphen
    	.substr($charid,20,12);
    	return $uuid;
    }

    public function is_mobile_client(){
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

    public function is_ipad(){
        $regExp="/iPad|iPadMini/i";
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            return true;
        }else{
            return preg_match($regExp, strtolower($_SERVER['HTTP_USER_AGENT']));
        }
    }

    public function is_ios(){
        $regExp="/iPad|iPadMini|iphone|ipod|itouch/i";
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            return false;
        }else{
            return preg_match($regExp, strtolower($_SERVER['HTTP_USER_AGENT']));
        }
    }


    public function is_ajax_request(){
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"){//原生ajax加头部xmlhttp.setRequestHeader("X-Requested-With","XMLHttpRequest");
            return true;
        }
        return false;
    }

    protected function up_img($img_name,$target_folder,$thum_size = array(), $del_src_img = 1, $thum_ratio = 1, $new_file_name='') {
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
            die(json_encode($this->error_msg($error[0])));
        } else {
            return $upFile->get_rel_file_path();
        }
    }

    protected function batch_up_img($img_name,$target_folder,$thum_size = array(), $del_src_img = 1, $thum_ratio = 1, $new_file_name='') {
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
            echo json_encode($this->error_msg($error[0]));
        } else {
            return $upFile->get_rel_files_path();
        }
    }

    public function send_voice($mobile, $data){
        COMMON('CCPRestSDK_voice');
        $rest = new REST();
        $result = $rest->voiceVerify($data,3,$mobile);
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_voice.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result || $result->statusCode != '000000') {
            return false;
        }
        return true;
    }

    public function send_sms($mobile, $data, $template_id) {
        COMMON('CCPRestSDK');
        $rest = new REST();
        $result = $rest->sendTemplateSMS($mobile, $data, $template_id);
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_sms.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result || $result->statusCode != 0) {
            return false;
        }
        return true;
    }

    public function bm_send_sms($mobile,$data,$type=''){
        COMMON('BM_sms');
        $rest = new BaMi();
//        $result = $rest->getAccountInfo();     //查询余额接口
        $result = $rest->toIndustry($mobile,$data,$type);    //发送短信接口
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_bm_sms.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result || $result->statusCode != 0){
            return false;
        }else{
            return true;
        }
    }

    public function request($url, $post='', $header = array(), $timeout = 10){
        if($this->is_request_debug){
            $starttime = microtime(true);
        }
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //设置连接时间
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

            if($post){
                curl_setopt($ch, CURLOPT_POST, 1);
                if(is_array($post)){
                    $post = http_build_query($post,'&');
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            if($header){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            if(curl_errno($ch)) {
                $this->err_log($url,'request');
            }

            $result = curl_exec($ch);
            $this->curl_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);

            if($this->is_request_debug){
                $curl_info = curl_getinfo($ch);
                $endtime = microtime(true);
                $dur = $endtime - $starttime;
                file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_url.log','IP：'.$this->client_ip().'时间:'.date('Y-m-d H:i:s', intval($starttime)).'|用时:'.$dur.
                    "\r\n结果:".$result.'|curl_info:'.json_encode($curl_info)."\r\n----------\r\n",FILE_APPEND);
            }
            curl_close($ch);
            return trim($result);
        }catch (Exception $e){
            print_r($e);
        }
    }

    public function sendMail($to, $title, $content, $isHtml = FALSE){
        $mail = new PHPMailer(TRUE); // the true param means it will throw exceptions on errors, which we need to catch
        $mail->IsSMTP(); // telling the class to use SMTP
        try {
            $mail->SMTPDebug  = FALSE;                     // enables SMTP debug information (for testing)
            $mail->Host       = "smtp.qq.com";      // sets GMAIL as the SMTP server
            $mail->Port       = 25;                   // set the SMTP port for the GMAIL server
            $mail->SMTPAuth   = TRUE;                  // enable SMTP authentication
            $mail->Username   = "**";
            $mail->Password   = "**";
            $mail->CharSet="utf-8";
            $mail->Encoding = "base64";
            $mail->SetFrom('**', '**');
            $mail->AddAddress($to);
            $mail->Subject = $title;
            $mail->MsgHTML($content);
            $mail->IsHTML($isHtml);

            return $mail->Send();
        } catch (phpmailerException $e) {
            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
    //模板形式发送
    /*function send_mail($to,$title, $content) {
        $to=array($to);
        $code=array("123456");
        $date=array(date('Y-m-d H:i:s', strtotime("now")));
        $sub=json_encode(array('%code%'=>$code,'%date%'=>$date));
        $replace=json_encode(array('to'=>$to,'sub'=>$sub));
        $params=array(
            'api_user' => 'huangyaohy_test_pUDX17', # 使用api_user和api_key进行验证
            'api_key' => 'UPoYWgfX3dN6lVq7',
            'from' => '5992025@qq.com', # 发信人，用正确邮件地址替代
            'fromname' => $title,
            'subject' => '66173 邮箱验证码',
            'template_invoke_name' => 'EMAIL_CODE_TEMP',
            'substitution_vars'=>$replace
            );
        $url='http://sendcloud.sohu.com/webapi/mail.send_template.json';
        $result=$this->request($url,$params);
        return $result;
    }*/

    function send_mail($to,$title, $content) {
        $url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
        $API_USER = 'send66';
        $API_KEY = 'UPoYWgfX3dN6lVq7';
        $param = array(
            'api_user' => $API_USER, # 使用api_user和api_key进行验证
            'api_key' => $API_KEY,
            'from' => 'send@send.66173.cn', # 发信人，用正确邮件地址替代
            'fromname' => '66173',
            'to' => $to,# 收件人地址, 用正确邮件地址替代, 多个地址用';'分隔
            'subject' => $title,
            'html' =>$content,
            'resp_email_id' => 'true'
        );

        $data = http_build_query($param);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));
        $context  = stream_context_create($options);
        $result = file_get_contents($url, FILE_TEXT, $context);
        return $result;
    }


    public function err_log($word, $filename = 'web_log') {
        file_put_contents(PREFIX.DS.'logs/'.$filename.'_'.date('Ymd').'.log', strftime("%Y-%m-%d %H:%M:%S",time())."\r\n".$word."\r\n",FILE_APPEND);
    }

    public function fig_time($time){
        $ot = time() - strtotime($time);
        if($ot < 60) {
            return '刚刚';
        }elseif($ot < 3600) {
            $ot = ($ot/60) < 1 ? 1 : floor($ot/60);
            return $ot .'分钟前';
        }elseif($ot < 86400) {
            $ot = ($ot/3600)< 1 ? 1 : floor($ot/3600);
            return $ot .'小时前';
        }elseif($ot < 259200000) {
            $ot = ($ot/86400)< 1 ? 1 : floor($ot/86400);
            return $ot .'天前';
        }else{
            return date("Y-m-d",strtotime($time));
        }
    }

    protected function convert_emoji($content){
        $emoji = array("1f620","1f629","1f632","1f61e","1f635","1f630","1f612","1f60d","1f624","1f61c","1f61d","1f60b","1f618","1f61a","1f637",
            "1f633","1f603","1f605","1f606","1f601","1f602","1f60a","263a","1f604","1f622","1f62d","1f628","1f623","1f621","1f60c","1f616",
            "1f614","1f631","1f62a","1f60f","1f613","1f625","1f638","1f639","1f63d","1f63b","1f63f","1f63e","1f63c","1f640","1f42c","1f42d",
            "1f42f","1f431","1f433","1f434","1f435","1f436","1f437","1f43d","1f43b","1f42e","1f430","1f438","1f43c","1f354","1f359","1f370",
            "1f35c","1f35e","1f373","1f362","1f357","1f35a","1f363","1f355","1f369","1f35d","1f364","1f374","1f366","1f35f","1f369","1f36a",
            "1f36b","1f36c","1f36d","2615","1f378","1f37a","1f375","1f376","1f377","1f37b","1f379","1f352","1f34c","1f34e","1f34a","1f353",
            "1f349","1f345","1f346","1f348","1f34d","1f347","1f351","1f34f","1f33d","1f330","2600","2601","2614","26c4","26a1","1f300","1f302",
            "2744","26c5","1f31b","1f31f","1f308","1f460","1f451","1f452","1f4a1","1f4a2","1f4a3","1f4a4","1f4a5","1f4a6","1f4a7","1f4a8","1f4a9",
            "1f4aa","1f4ab","1f4ac","1f551","231a","1f459","1f440","1f442","1f443","1f444","1f445","1f484","1f485","1f486","1f487","1f47b","1f47c",
            "1f47d","1f47e","1f47f","1f480","1f45b","1f52a","1f52b","1f489","1f48a","1f380","1f381","260e","1f4e2","26bd","1f3c0","1f685","1f697",
            "2708","1f680","270a","270b","270c","1f44a","1f44d","261d","1f446","1f447","1f448","1f449","1f44b","1f44f","1f44c","1f44e","1f450",
            "1f1e81f1f3","1f1e91f1ea","1f1ea1f1f8","1f1eb1f1f7","1f1ec1f1e7","1f1ee1f1f9","1f1ef1f1f5","1f1f01f1f7","1f1f71f1fa","1f1fa1f1f8");
        foreach($emoji as $k=>$item){
            //$content = strstr($content,$item,'<span class="emoji emoji'.$item.'"></span>');
            $content = str_replace('{'.$item.'}','<span class="emoji emoji'.$item.'"></span>',$content);
        }
        $content = emoji_html_to_unified($content);
        return $content;
    }

    public function page_hash(){
        $hash = $this->create_guid();
        $_SESSION['page-hash'] = $hash;
        $this->V->assign("pagehash",$hash);
    }

    public function out_put($str){
        echo '0'.base64_encode($str);
        exit();
    }

    public function succeed_msg($message='操作成功',$tabid=''){
        $result['statusCode']="200";
        $result['closeCurrent']="true";
        $result['message']=$message;
        if($tabid){
            $result['tabid']=$tabid;
        }
        return $result;
    }

    public function succeed_del($message='操作成功',$tabid=''){
        $result['statusCode']="200";
        $result['message']=$message;
        if($tabid){
            $result['tabid']=$tabid;
        }
        return $result;
    }

    public function unclose_succeed_msg($message='操作成功'){
        $result['statusCode']="200";
        $result['message']=$message;
        return $result;
    }

    public function error_msg($message='操作失败'){
        $result['statusCode']="300";
        $result['closeCurrent']="false";
        $result['message']=$message;
        return $result;
    }

    public function check_usr_login(){
        if(!isset($_SESSION['user_id']) || !$_SESSION['user_id']){
            $this->redirect("account.php?act=login");
            exit;
        }
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
        error_reporting(E_ALL);
        ini_set("display_errors","On");
    }

    public function object_to_array($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    public function get_http_host(){
        $result = array();
        if(strpos($_SERVER['HTTP_HOST'],'66173.cn')){
            $result['domain_name'] = 1;
            $result['name'] = '66173';
        }elseif(strpos($_SERVER['HTTP_HOST'],'u2590.com')){
            $result['domain_name'] = 2;
            $result['name'] = 'u2590';
        }
        return $result;
    }

    public function isMobile(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {//这里把值遍历一遍，用于查找是否有上述字符串出现过
            if (stristr($user_agent, $device)) { //stristr 查找访客端信息是否在上述数组中，不存在即为PC端。
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }
}
