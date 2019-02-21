<?php 
COMMON('baseCore');

class weixinCore extends baseCore{

    public $oauth_url;

    public function __construct(){
        parent::__construct();
        $this->oauth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".WX_APPID
            ."&redirect_uri=".urlencode("http://wx.66173.cn/my.php")."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        $this->assign("oauth_url",$this->oauth_url);
    }

    protected function get_auth_usr_token(){
        if (!isset($_SESSION['weixin_code'])) {
            die("发生错误，请重新进入。ERR001");
        }
        $usr_token = $this->request("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . WX_APPID . "&secret=" . WX_APPSECRET . "&code=" . $_SESSION['weixin_code'] . "&grant_type=authorization_code");
        $usr_token = json_decode($usr_token, true);
        if (isset($usr_token['openid']) && isset($usr_token['access_token'])) {
            $_SESSION['wx_openid'] = $usr_token['openid'];
            $_SESSION['wx_token'] = $usr_token['access_token'];
            $_SESSION['wx_token_time'] = strtotime("now");
            $_SESSION['wx_token_expires_in'] = $usr_token['expires_in'];
        } else {
            die("发生错误，请重新进入。ERR002");
        }
    }

    protected function build_wx_user_info(){
        if(!$_SESSION['wx_usr_info']){
            $wx_usr_info = $this->request("https://api.weixin.qq.com/sns/userinfo?access_token=".$_SESSION['wx_token']."&openid=".$_SESSION['wx_openid']."&lang=zh_CN");
            $wx_usr_info = json_decode($wx_usr_info,true);
            $_SESSION['wx_usr_info'] = $wx_usr_info;
        }
    }

    protected function save_avatar($user_info){
        if($user_info['PhotoUrl']){
            return;
        }
        if(!$_SESSION['wx_usr_info']['headimgurl']){
            return;
        }
        $data = file_get_contents($_SESSION['wx_usr_info']['headimgurl']);
        $filepath = '/data/webroot/66173.com/htdocs/static/avatar/';
        $filename = $user_info['ID'].'.png'; //生成文件名，
        $fp = @fopen($filepath.$filename,"w"); //以写方式打开文件
        @fwrite($fp,$data); //
        fclose($fp);

        $this->DAO->update_avatar($user_info['ID']);
        $post_string = array("username"=>CDN_USR,"password"=>CDN_PWD,
            "type"=>1,
            "url"=>"http://cdndl.go.cc/avatar/".$user_info['ID'].".png");
        $this->request(CDN_HOST,$post_string,array());
    }
}
