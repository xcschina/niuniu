<?php
COMMON('weixinCore', 'pageCore');
BO("account_wx");
DAO('games_dao','weixin_dao');
class h5_games_wx extends weixinCore{

    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new games_dao();
    }

    public function game_login(){
        $game_info = $this->DAO->get_user_pid($_GET['game_id']);
        if(empty($game_info)){
            die("无效的pid");
        }
        $app_info = $this->DAO->get_game_info($game_info['app_id']);
        if($app_info['payee_ch']=='3' || $game_info['app_id']=='1001'){
            $wx_appid = 'wx145b6dcbc2f651b5';//北京
            $wx_appsecret = 'c9a57dc61750b745859ca4bd6393b306';
        }elseif($app_info['payee_ch']=='1'){
            $wx_appid = 'wxbaed68c7f2f3a62c';//福建
            $wx_appsecret = '8bae09f8cf665a6b1267c0ccbd77cd61';
        }elseif($app_info['payee_ch']=='2'){
            $wx_appid = 'wxf965c4a0c7bf9851';//海南
            $wx_appsecret = '370761a5cdf594f23f69def4d9fdc3cf';
        }else{
            die('未知的类型');
        }
        $this->get_open_info($wx_appid,$wx_appsecret,$_GET['code']);
        $game_id =  $_GET['game_id'];
        $ch = $_GET['ch'];
        $family = $_GET['family'];
        $cpext = $_GET['cpext'];
        if(strpos($_SERVER['HTTP_HOST'],'66173.cn')){
            $url ='http://ins.66173.cn';
        }elseif (strpos($_SERVER['HTTP_HOST'],'66173yx.com')){
            $url ='http://ins.66173yx.com';
        }elseif (strpos($_SERVER['HTTP_HOST'],'yun273.com')){
            $url ='http://ins.yun273.com';
        }
        if(empty($url)){
            die('异常的游戏地址');
        }
        $url = $url."/game.php?game_id=".$game_id;
        if($ch){
            $url = $url."&ch=".$ch;
        }
        if($family){
            $url = $url."&family=".$family;
        }
        if($cpext){
            $url = $url."&cpext=".$cpext;
        }
        header("Location: $url");
    }

    public function get_open_info($wx_appid,$wx_appsecret,$code){
        if(empty($code)){
            die('缺少游戏参数');
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $wx_appid . "&secret=" . $wx_appsecret . "&code=" . $code . "&grant_type=authorization_code";
        $usr_token = $this->request($url);
        $usr_token = json_decode($usr_token, true);
        if (isset($usr_token['openid']) && isset($usr_token['access_token'])) {
            $_SESSION['weixin_code'] = $code;
            $_SESSION['wx_openid'] = $usr_token['openid'];
            $_SESSION['wx_token'] = $usr_token['access_token'];
            $_SESSION['wx_token_time'] = strtotime("now");
            $_SESSION['wx_token_expires_in'] = $usr_token['expires_in'];
        } else {
            die("微信授权失败,请重新打开链接");
        }
    }

}