<?php
COMMON('baseCore');
DAO('index_dao');
class index_admin extends baseCore {
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new index_dao();
    }


    function index_view($type) {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $new_game = $this->DAO->get_new_game();
        $banner_list = $this->DAO->get_banner_list();
        $wx = $this->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("banner_list", $banner_list);
        $this->assign("new_game",$new_game);
        if($type == 1){
            $this->display("index.html");
        }elseif($this->isMobile()){
            $this->redirect("enter");
        }else{
            $this->display("index.html");
        }
    }

    public function wx_share(){
        $ret = $this->DAO->get_wx_access_token();
        if(!$ret){
            COMMON('weixin.class');
            $ret = wxcommon::getToken();
            $this->DAO->set_wx_access_token($ret);
        }
        $ACCESS_TOKEN = $ret['access_token'];
        $jsapi_data = $this->DAO->get_wx_access_jsapi_data($ACCESS_TOKEN);
        if(!$jsapi_data){
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$ACCESS_TOKEN.'&type=jsapi';
            $content = file_get_contents($url);
            $jsapi_data = json_decode($content, true);
            $this->DAO->set_wx_access_jsapi_data($ACCESS_TOKEN,$jsapi_data);
        }
        $guid = $this->create_guids();
        $time = time();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $share_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $sign = "jsapi_ticket=".$jsapi_data['ticket']."&noncestr=".$guid."&timestamp=".$time."&url=".$share_url;
        $signature = sha1($sign);
        $result = array("noncestr"=>$guid,"timestamp"=>$time,"signature"=>$signature);
        return $result;
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }
}