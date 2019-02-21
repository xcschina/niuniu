<?php
COMMON('baseCore');
DAO('app_dao');
class app_admin extends baseCore {
    public $DAO;
    public $R_DAO;
    public $game_type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new app_dao();
        $this->R_DAO = new reserve_dao();
        $this->game_type = array(
            101 => '动作',
            102 => '角色',
            103 => '射击',
            104 => '策略',
            105 => '即时',
            106 => '回合',
            107 => '休闲',
            108 => '冒险',
            109 => '模拟',
            110 => '竞技',
            111 => '卡牌',
            112 => '体育',
            113 => '格斗',
            114 => 'MOBA');
    }

    public function app_share_detail($game_id){
        if(empty($game_id)){
            die("地址出错啦！");
        }
        //兼容腾讯应用宝
        if ($_GET['isTX']==1){
            $apk_name = $_GET['apkname'];
            $result_app_detail = $this->DAO->get_game_info_tx($apk_name);
            if ($result_app_detail['head']['ret']===0 && $result_app_detail['body']['ret']===0){
                if ($result_app_detail['body']['mpRes']){
                    $app_res = $result_app_detail['body']['mpRes'][$apk_name];
                    $apk_size = sprintf("%.1f", ((int)$app_res['fileSize'])/1024/1024);
                    $info = array(
                        "id"=>$app_res['appId'],"game_name"=>$app_res['appName'],"game_icon"=>$app_res['iconUrl'],"game_banner"=>"",
                        "game_desc"=>$app_res['description'], "game_title"=>"","version"=>$app_res['versionName'],"down_url"=>$app_res['apkUrl'],
                        "down_num"=>((string)$app_res['appDownCount']),"apk_size"=>$apk_size."M", "apk_name"=>$app_res['pkgName'],"channel"=>$app_res['channelId'],
                        "language"=>"0","system"=>"","img1"=>$app_res['screenshots'][0]['size550Url'],"img2"=>$app_res['screenshots'][1]['size550Url'],
                        "img3"=>$app_res['screenshots'][2]['size550Url'],"img4"=>$app_res['screenshots'][3]['size550Url'],"is_gift"=>"0","tag"=>$app_res['categoryInfo']['categoryName'],
                        "isTX"=>"1");
                }else{
                    die("未能获取到腾讯游戏详情数据");
                }
            }else{
                die("获取腾讯数据出错");
            }
        }else{
            $info = $this->DAO->get_game_info($game_id);
            if(!$info){
                die("查无此游戏");
            }else{
                $info['tag']='';
                if(!empty($info['tags'])){
                    $info['tag'] = $this->get_tags_str($info['tags']);
                }
            }
        }
        $this->wx_share();
        $this->assign('info',$info);
        $this->display('app_share_detail.html');
    }

    public function get_tags_str($tags){
        $tag_array = explode(',', $tags);
        $new_tags = array();
        foreach($tag_array as $k=>$data){
            array_push($new_tags,$this->game_type[$data]);
        }
        return  implode(',', $new_tags);
    }

    public function wx_share(){
        $ret = $this->R_DAO->get_wx_access_token();
        if(!$ret){
            COMMON('weixin.class');
            $ret = wxcommon::getToken();
            $this->R_DAO->set_wx_access_token($ret);
        }
        $ACCESS_TOKEN = $ret['access_token'];
        $jsapi_data = $this->R_DAO->get_wx_access_jsapi_data($ACCESS_TOKEN);
        if(!$jsapi_data){
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$ACCESS_TOKEN.'&type=jsapi';
            $content = file_get_contents($url);
            $jsapi_data = json_decode($content, true);
            $this->R_DAO->set_wx_access_jsapi_data($ACCESS_TOKEN,$jsapi_data);
        }
        $guid = $this->create_guids();
        $time = time();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $share_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $sign = "jsapi_ticket=".$jsapi_data['ticket']."&noncestr=".$guid."&timestamp=".$time."&url=".$share_url;
        $signature = sha1($sign);
        $this->assign("noncestr", $guid);
        $this->assign("timestamp", $time);
        $this->assign("signature", $signature);
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function send_code($app_id){
        $result = array('code' => 0 ,'msg'=>'网络请求出错');
        $app_info = $this->DAO->get_app_info($app_id);
        $params = $_POST;
        $sign = md5($params['mobile'].'_'.$params['code'].'_'.$app_info['app_key']);
        $nowtime = strtotime("now");
        if(!$params['mobile']){
            $result['msg'] = "请输入您的手机号";
            die(json_encode($result));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['msg'] = "手机号码不正确";
            die(json_encode($result));
        }
        if($params['sign'] != $sign){
            $result['msg'] = "验证信息出错啦";
            die(json_encode($result));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<120){
                $result['msg'] = "获取验证码太频繁，请稍后再试";
                die(json_encode($result));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $send_result = $this->send_sms($params['mobile'],array($params['code'],5),"228851");
        $this->err_log(var_export($send_result,1),'send_code');
        if($send_result=='0'){
            $result['code'] = 1;//1成功0失败
            $result['msg'] = "验证消息发送成功！";
            die(json_encode($result));
        }elseif($send_result=='1'){
            $result['msg'] = "验证消息发送失败,请重试";
            echo json_encode($result);
        }else{
            $result['code'] = $send_result->statusCode;//1成功0失败
            $result['msg'] = $send_result->statusMsg;
            echo json_encode($result);
        }
    }

    public function send_sms($mobile, $data, $template_id) {
        COMMON('CCPRestSDK');
        $rest = new REST();
        $result = $rest->sendTemplateSMS($mobile, $data, $template_id);
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_sms.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result) {
            return 1;
        }
        if($result->statusCode != 0){
            return $result;
        }
        return 0;
    }
}