<?php
COMMON('baseCore', 'pageCore');
DAO('moyu_index_dao');

class moyu_index_mobile extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new moyu_index_dao();
    }

    //首页
    public function index_view(){
//        $this->open_debug();
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $banners = $this->DAO->get_banners();
        $notices = $this->DAO->get_moyu_articles_list();
        $links = $this->DAO->get_friendly_links();
        $games = $this->DAO->get_game_list();
        $this->wx_share();
        $this->assign("ip", $this->client_ip());
        $this->assign("games", $games);
        $this->assign("banners", $banners);
        $this->assign("notices", $notices);
        $this->assign("links", $links);
        $this->assign("games_json", json_encode($games));
        $this->display('moyu/demo3.html');
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
        $this->assign("noncestr", $guid);
        $this->assign("timestamp", $time);
        $this->assign("signature", $signature);
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }
}