<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 10:18
 */
COMMON('baseCore','yundamaApi');
class demo_admin extends baseCore{
    public $DAO;
    public $APPID;
    public $access_toke;
    public $refresh_token;
    public $openid;
    public $mch_id;
    public $code;
    public $key;
    private $username;
    private $userpwd;
    private $appkey;
    public function __construct(){
        parent::__construct();
        $this->APPID = "wxbaed68c7f2f3a62c";
        $this->access_toke = "9_alPJ-vcj-5QUZxO0kK9qWB2wWSbJNFVBTEf7am0GRPPA0eEDadtYqpNNdnZi7M3Me9ZdjO0-0zk-P7dfPJx9H96gMVOjVVJIhMMz7DIxyg0";
        $this->refresh_token = "9_VhOJfZhc3cKrGbVFiIAw3FDUkpP3Rl9IzqO8QCX3HKK8AQtBIIpZnpouHRaOeFxofhCEI1sF_vsOXfrfKx2A-nDOT9tHxFdu3UUBkhrXRBg";
        $this->openid = "oHk_htxkiPQHU-CvgQe-kGwwhCws";
        $this->mch_id = "1246578901";
        $this->code = "071SLm9N0DA07528zh7N012s9N0SLm9O";
        $this->key = "e5467d037119f7bf072b27d93e58d2fa";
        $this->username = 'stacy_wxl';
        $this->userpwd = 'stacy_wxl';
        $this->appkey = '8b72b16d87aa1c7c9abed28dd13e8e42';
    }

    function read_url($str){
        $result = '';
        $file=fopen($str,"r");
        while(!feof($file)) {
            $result.=fgets($file,9999);
        }
        fclose($file);
        return $result;
    }

    function save_img($str){
        $result=$this->read_url($str);

        $result=str_replace("\"","",$result);
        $result=str_replace("\'","",$result);
//        var_dump($result);
        preg_match_all('/<img\src=(.*?)(\s(.*?)\>|>)/i',$result,$matches);
//        var_dump($matches);
        $matches[1]=array("http://tg.le890.com/checkcode.php");
        var_dump($matches);
        foreach($matches[1] as $value){
            echo $value."<br>\n";
            $this->GrabImage($value,$filename="");
        }
    }
// $url 是远程图片的完整URL地址，不能为空。
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
// 自动生成.
    function GrabImage($url,$filename="") {
        var_dump($url);
        if($url==""):return false;endif;
        $path="download/"; //指定存储文件夹
        //若文件不存在,则创建;
        if(!file_exists($path)){
            mkdir($path);
        }

        if($filename==""){
            $ext=strrchr($url,".");
            var_dump($ext);
        if($ext!=".gif" && $ext!=".jpg" && $ext!=".png" && $ext!='.php'):return false;endif;
            $filename=$path.date("dMYHis").$ext;
        }
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);

        $fp2=@fopen($filename, "a");
        fwrite($fp2,$img);
        fclose($fp2);

        return $filename;
    }

    public function login_url(){
        //初始化变量
        $cookie_file = "code";
        $login_url = "http://tg.le890.com//?m=login";
        $verify_code_url = "http://tg.le890.com/checkcode.php";

//        echo "正在获取COOKIE...\n";8t7n54be3hf327jel3vgbqpd71
        $curl = curl_init();
        $timeout = 5;
        curl_setopt($curl, CURLOPT_URL, $login_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie_file); //获取COOKIE并存储
        curl_exec($curl);
        curl_close($curl);
//        echo "COOKIE获取完成，正在取验证码...\n";
//取出验证码
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $verify_code_url);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $img = curl_exec($curl);
        curl_close($curl);
        $fp = fopen("checkcode.jpg","w");
        fwrite($fp,$img);
        fclose($fp);
//        echo "验证码取出完成，正在休眠，20秒内请把验证码填入code.txt并保存\n";
//停止运行20秒
        $img = 'checkcode.jpg';
        //单独获取图片ID
        $data = $this->yundamaApi($img);
//        $result = $this->get_result($data);
        var_dump($data);
        echo "打码结果：".$data['data']."\n";
        $code = $data['data'];
//        sleep(10);
//        echo "休眠完成，开始取验证码...\n";
//        $code = file_get_contents("code.txt");
        echo "验证码成功取出：$code\n";
//        echo "正在准备模拟登录...\n";

        $post = "m=login&a=index&act=1&username=niuniuwl001&password=66173niuniu&checkCode=".$code.
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $login_url);
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
//        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
//        $result=curl_exec($curl);
//        curl_close($curl);


        $curl = curl_init();
        //设置提交的url
        curl_setopt($curl, CURLOPT_URL, $login_url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回。而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        $post_data = $post;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
        //运行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //获得数据并返回
        return $data;


//        var_dump($result);
//        http://tg.le890.com/?m=member&a=sociaty
//这一块根据自己抓包获取到的网站上的数据来做判断
        $data_url = "http://tg.le890.com/?m=member&a=sociaty&act=search&username=&_search=false&nd=1531127519513&rows=50&page=1&sidx=&sord=asc";     //数据所在地址
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $data_url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,0);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
        $data = curl_exec($curl);
        curl_close($curl);
//        $this->request($data_url);
        $data=json_decode($data,true);//将json格式转化为数组格式,方便使用
//        var_dump($cookie_file);
//        var_dump($this->request($data_url));
        if(substr_count($data,"登录成功")){
            echo "登录成功\n";
        }else{
            echo "登录失败\n";
            exit;
        }
    }
    public function XZ_send_sms($mobile, $data, $temp_id='') {
        COMMON('now_pay/api/pay');
        $rest = new Check();
//        $result = $rest->toIndustry($mobile, $data);
//        $result = $rest->toIndustryBatch($mobile, $data);
        $result = $rest->toIndustryTemp($mobile, $data, $temp_id);
//        $result = $rest->toMarketing($mobile, $data);
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_xz_sms.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result || $result->statusCode != 0) {
            return false;
        }
        return true;
    }
    public function index(){
//        $this->open_debug();
       $a = $this->bm_send_sms('18105022586',array(123456),6);
//       var_dump($a);
//        var_dump($this->login_url());
//        $img = 'checkcode.jpg';
        //单独获取图片ID
//        $data = $this->get_cid($img);
//        $data = $this->yundamaApi($img);
//        $error = $this->get_error($data);
//        var_dump($data);
//        if(false){
//            $error = $this->get_error($data);
//        }else{
//            $result = $this->get_result($data);
//        }
//        $result = $this->get_result($data);
//        var_dump($data);
//        var_dump($result);
//        var_dump($result);

//        $this->ali_pay();
//        $this->display("demo.html");
    }

    public function get_url($method,$info){
        $posts = array(
            'method'=>$method,
            'username'=>$this->username,
            'password'=> $this->userpwd,
            'appid'=>4978,
            'appkey'=>$this->appkey,
            'cid'=>$info->cid
        );
        if($method =='report'){
            $posts['flag'] = 0;
        }
        $apiurl = 'http://api.yundama.com/api.php';
        $data = $this->http($apiurl, 'POST',$posts);
        $data = json_decode($data);
        return $data;
    }

    public function get_result($info){
        $posts = array('cid'=>$info->cid);
        $apiurl = 'http://api.yundama.com/api.php?method=result';
        $data = $this->http($apiurl, 'POST',$posts);
        $data = json_decode($data);
        return $data;
    }

    public function get_cid($img){
        $posts = array(
            'method'=> 'upload',
            'username'=> $this->username,
            'password'=> $this->userpwd,
            'appid'=> 4978,
            'appkey'=>$this->appkey,
            'codetype'=>'4004',
            'timeout'=>60,
            'file'=>'@'.$img
        );
        $apiurl = 'http://api.yundama.com/api.php';
        $data = $this->http($apiurl, 'POST',$posts);
        $data = json_decode($data);
        return $data;
    }

    public function get_error($info){
        $posts = array(
            'username'=> $this->username,
            'password'=> $this->userpwd,
            'appid'=> 4978,
            'appkey'=>$this->appkey,
            'cid'=>$info->cid,
            'flag'=>0
        );
        $apiurl = 'http://api.yundama.com/api.php?method=report';
        $data = $this->http($apiurl, 'POST',$posts);
        $data = json_decode($data);
        if(is_object($data) && property_exists($data, 'text') && $data->text) {
            $validate['status'] = 1;
            $validate['data'] = $data->text;
            $validate['cid'] = $data->cid;
        }
        return $validate;
    }


    public function yundamaApi($imgPath){
        $validate = array('status'=>0,'data'=>'');
        $username = $this->username;
        $pwd = $this->userpwd;
        $posts = array('method'=> 'upload',
            'username'=> $username,
            'password'=> $pwd,
            'appid'=> 4978,
            'appkey'=>$this->appkey,
            'codetype'=>'4004',
            'timeout'=>60,
            'file'=>'@'.$imgPath
        );
        $apiUrl = 'http://api.yundama.com/api.php';
        $data = $this->http($apiUrl, 'POST',$posts);
        $data = json_decode($data);
        if(is_object($data) && property_exists($data, 'cid')){
            $cid = $data->cid;
            for($i=1;$i>0;){
                    $posts = array(
                        'method' => 'result',
                        'username' => $username,
                        'password' => $pwd,
                        'appid' => 4978,
                        'appkey' => $this->appkey,
                        'cid' => $cid);
                    $data = $this->http($apiUrl, 'POST', $posts);
                    $data = json_decode($data);
                    if (is_object($data) && property_exists($data, 'text') && $data->text) {
                        $validate['status'] = 1;
                        $validate['data'] = $data->text;
                        $validate['cid'] = $data->cid;
                        break;
                    }
            }
        }
        return $validate;
    }

    public function http($url, $method, $postfields = NULL, $headers = array(),$outputHeader=false) {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ci, CURLOPT_TIMEOUT, 60);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        if($outputHeader){
            curl_setopt($ci, CURLOPT_HEADER, true);
        }
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);

                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
            case 'GET':
                curl_setopt($ci, CURLOPT_HTTPGET, true);
        }
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }


    public function ali_pay(){
        $_SESSION['demo'] = $_REQUEST;
        var_dump($_SESSION);
    }

    //获取sign值
    public function new_sign(){
        $stringA = 'appid='.$this->APPID.'&body=test&device_info=WEB&mch_id='.$this->mch_id.'&nonce_str=ibuaiVcKdpRxkhJA';
        $stringSignTemp = $stringA."&key=".$this->key;
        $sign = strtoupper(MD5($stringSignTemp));
        $sign = hash_hmac("sha256",$stringSignTemp,$this->key);
    }

    public function get_code(){
        $redirect_uri = urlencode("http://wx.66173.cn/demo.php");
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->APPID.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        header('location:'.$url);
    }

    public function get_access_token(){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->APPID."&secret=8bae09f8cf665a6b1267c0ccbd77cd61&code=".$this->code."&grant_type=authorization_code";
        header('location:'.$url);
//        var_dump($data);
//        header('location:'.$url);
    }

    public function get_refresh_token(){
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->APPID."&grant_type=refresh_token&refresh_token=".$this->refresh_token;
        header('location:'.$url);
    }

    public function get_userInfo(){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$this->access_toke."&openid=".$this->openid."&lang=zh_CN";
        $data = $this->request($url);
        var_dump($data);
//        header('location:'.$url);
    }

    public function verify_token(){
        $url = "https://api.weixin.qq.com/sns/auth?access_token=".$this->access_toke."&openid=".$this->openid;
        $data = $this->request($url);
        var_dump($data);
    }

    public function place_order(){
        $wx_data = $this->make_wx_data("http://ins.66173.cn/wx_secure_notify.php");
        $xml_data = $this->array_to_xml($wx_data);
        var_dump($xml_data);
        $request = $this->request('https://api.mch.weixin.qq.com/pay/unifiedorder',$xml_data);
        var_dump($request);
    }

    public function make_wx_data($notify_url){
        $data = array(
            'appid' => $this->APPID,
            'mch_id' => $this->mch_id,
            'nonce_str' => 'CS'.time(),
            'body' => '12344',
            'out_trade_no' => time(),
            'total_fee' => 1*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'JSAPI',
            'openid' => $this->openid
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".$this->key;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}