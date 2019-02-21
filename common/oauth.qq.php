<?php
class oauth_qq{
    private static $_instance;
    private $config = array();
    public function __construct($config){
        $this->Oauth_qq($config);
    }

    public static function getInstance($config){
        if (!isset(self::$_instance)){
            $c = __CLASS__;
            self::$_instance = new $c($config);
        }
        return self::$_instance;
    }

    private function Oauth_qq($config){
        $this->config = $config;
        $_SESSION["qq_appid"] = $this->config['appid'];
        $_SESSION["qq_appkey"] = $this->config['appkey'];
        $_SESSION["qq_callback"] = $this->config['callback'];
        $_SESSION["qq_scope"] = "get_user_info,add_share,add_t";
//        $_SESSION["scope"] = "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_t";
    }

    function login(){
        $_SESSION['qq_state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
            . $_SESSION["qq_appid"] . "&redirect_uri=" . urlencode($_SESSION["qq_callback"])
            . "&state=" . $_SESSION['qq_state']
            . "&scope=" . $_SESSION["qq_scope"];
        header("Location:$login_url");
    }

    function callback(){
        if ($_REQUEST['state'] == $_SESSION['qq_state']){
            $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                . "client_id=" . $_SESSION["qq_appid"] . "&redirect_uri=" . urlencode($_SESSION["qq_callback"])
                . "&client_secret=" . $_SESSION["qq_appkey"] . "&code=" . $_REQUEST["code"];
            $response = $this->get_url_contents($token_url);
            if (strpos($response, "callback") !== false){
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
                $msg = json_decode($response);
                if (isset($msg->error)){
                    return false;
                }
            }
            $params = array();
            parse_str($response, $params);
            return $_SESSION["qq_access_token"] = $params["access_token"];
        }else{
            //echo("The state does not match. You may be a victim of CSRF.");
            return false;
        }
    }

    function get_openid(){
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=". $_SESSION['qq_access_token'];
        $str = $this->get_url_contents($graph_url);
        if (strpos($str, "callback") !== false){
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
        }
        $user = json_decode($str);
        if (isset($user->error)){
            return false;
        }

        return $_SESSION["qq_openid"] = $user->openid;
    }

    function get_user_info(){
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=" . $_SESSION['qq_access_token']
            . "&oauth_consumer_key=" . $_SESSION["qq_appid"]
            . "&openid=" . $_SESSION["qq_openid"]
            . "&format=json";
        $info = $this->get_url_contents($get_user_info);
        $arr = json_decode($info, true);
        return $arr;
    }

    /*
     * publish Tencent weibo
     */
    function add_t($content, $clientip){
        $add_t = "https://graph.qq.com/t/add_t?"
            . "access_token=" . $_SESSION['qq_access_token']
            . "&oauth_consumer_key=" . $_SESSION["qq_appid"]
            . "&openid=" . $_SESSION["qq_openid"]
            . "&format=json"
            . "&content=$content"
            . "&clientip=$clientip";

        $info = get_url_contents($add_t);
        $arr = json_decode($info, true);
        return $arr;
    }

    /*
     * Share shuoshuo
     */
    function add_share($title, $url, $site, $fromurl){
        $add_share = "https://graph.qq.com/share/add_share?"
            . "access_token=" . $_SESSION['qq_access_token']
            . "&oauth_consumer_key=" . $_SESSION["qq_appid"]
            . "&openid=" . $_SESSION["qq_openid"]
            . "&format=json"
            . "&title=$title"
            . "&url=$url"
            . "&site=$site"
            . "&fromurl=$fromurl";

        $info = get_url_contents($add_share);
        $arr = json_decode($info, true);
        return $arr;
    }

    public function __clone(){
        trigger_error('Clone is not allow', E_USER_ERROR);
    }
    function do_post($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
    function get_url_contents($url){
        if (ini_get("allow_url_fopen") == "1")
            return file_get_contents($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}