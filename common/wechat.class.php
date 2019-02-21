<?php
/**
 * 微信公众平台操作
 * 基本于PHP-CURL
 *
 * @author : bqq<506615054@qq.com>
 * @date   : 2013-09-05
 *
 */
class Weixin
{

    /**
     * PHP curl头部分
     * @var array
     */
    private $_header;

    /**
     * 通讯cookie
     * @var string
     */
    private $_cookie;

    /**
     * 令牌
     * @var string
     */
    private $_token;
    /**
     * 网址url绑定的令牌
     * @var string
     */
    public $token = '';
    /**
     * 是否debug的状态标示，方便我们在调试的时候记录一些中间数据
     * @var boolean
     */
    public $debug =  false;

    public $setFlag = false;
    /**
     *('text','image','location')
     * @var string
     */
    public $msgtype = 'text';
    /**
     *消息信息
     * @var array
     */
    public $msg = array();
    /**
     * 初始化，设置header
     */
    public function __construct($token, $debug = false)
    {
        $this->_header = array();
        $this->_header[] = "Host:mp.weixin.qq.com";
        $this->_header[] = "Referer:https://mp.weixin.qq.com/cgi-bin/getmessage";
        $this->token = $token;//公众号绑定的url的token
        $this->debug = $debug;
        if(!$this->checkSignature()){
            die(".....");
        }
    }

    /**
     * 用户登录
     * 结构 $param = array('username'=>'', 'pwd'=>'');
     *
     * @param array $param
     * @return boolean
     */
    public function login($param)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN';
        $post = 'username='.urlencode($param['username']).'&pwd='.md5($param['pwd']).'&imgcode=&f=json';
        $stream = $this->_html($url, $post);
        // 判断是不是登录成功
        $html = preg_replace("/^.*\{/is", "{", $stream);
        $json = json_decode($html, true);
        //获取 token
        preg_match("/lang=zh_CN&token=(\d+)/is", $json['ErrMsg'], $match);
        $this->_token = $match[1];

        // 获取cookie
        $this->_cookie($stream);
        return (boolean)$json['Ret'] == '302';
    }

    /**
     * 获取最新5天关注用户发过来的消息，消息id，用户fakeid，昵称，消息内容
     *
     * 返回结构:id:msgId; fakeId; nickName; content;
     *
     * @return array
     */
    public function newmesg()
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&token='.$this->_token.'&lang=zh_CN&count=50&rad='.rand(10000, 99999);
        $stream = $this->_html($url);

        preg_match('/< type="json" id="json-msgList">(.*?)<\/>/is', $stream, $match);
        $json = json_decode($match[1], true);
        $returns = array();
        foreach ( $json as $val){
            if ( $val['starred'] == '0') {
                $returns[] = $val;
            }
        }
        return $returns;
    }

    /**
     * 设置标记
     *
     * @param integer $msgId 消息标记
     * @return boolean
     */
    public function start($msgId)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/setstarmessage?t=ajax-setstarmessage&rad='.rand(10000, 99999);
        $post = 'msgid='.$msgId.'&value=1&token='.$this->_token.'&ajax=1';
        $stream = $this->_html($url, $post);

        // 是不是设置成功
        $html = preg_replace("/^.*\{/is", "{", $stream);
        $json = json_decode($html, true);

        return (boolean)$json['msg'] == 'sys ok';
    }

    /**
     * 发送消息
     *
     * 结构 $param = array(fakeId, content, msgId);
     * @param array $param
     * @return boolean
     */
    public function sendmesg($param)
    {
        $url  = 'https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response';
        $post = 'error=false&tofakeid='.$param['fakeId'].'&type=1&content='.$param['content'].'&quickreplyid='.$param['msgId'].'&token='.$this->_token.'&ajax=1';

        $stream = $this->_html($url, $post);
        $this->start($param['msgId']);

        // 是不是设置成功
        $html = preg_replace("/^.*\{/is", "{", $stream);
        $json = json_decode($html, true);
        return (boolean)$json['msg'] == 'ok';
    }

    /**
     * 主动发消息结构
     *  $param = array(fakeId, content);
     *  @param array $param
     *  @return [type] [description]
     */
    public function send($param)
    {
        $url  = 'https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response&lang=zh_CN';
        //$post = 'ajax=1&appmsgid='.$param['msgid'].'&error=false&fid='.$param['msgid'].'&tofakeid='.$param['fakeId'].'&token='.$this->_token.'&type=10';
        $post = 'ajax=1&content='.$param['content'].'&error=false&tofakeid='.$param['fakeId'].'&token='.$this->_token.'&type=1';
        $stream = $this->_html($url, $post);
        // 是不是设置成功
        $html = preg_replace("/^.*\{/is", "{", $stream);
        $json = json_decode($html, true);
        return (boolean)$json['msg'] == 'ok';
    }
    /**
     * 批量发送(可能需要设置超时)
     * $param = array(fakeIds, content);
     * @param array $param
     * @return [type] [description]
     */
    public function batSend($param)
    {   $url  = 'https://mp.weixin.qq.com/cgi-bin/masssend?t=ajax-response';
        $post = 'ajax=1&city=&content='.$param['content'].'&country=&error=false&groupid='.$param['groupid'].'&needcomment=0&province=&sex=0&token='.$this->_token.'&type=1';
        $stream = $this->_html($url, $post);
        // 是不是设置成功
        $html = preg_replace("/^.*\{/is", "{", $stream);
        $json = json_decode($html, true);
        return (boolean)$json['msg'] == 'ok';
    }
    /*
     * 新建图文消息
     */
    public function setNews($param, $post_data)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/sysnotify?lang=zh_CN&f=json&begin=0&count=5';
        $post = 'ajax=1&token='.$this->_token.'';
        $stream = $this->_html($url, $post);
        //上传图片
        $url = 'https://mp.weixin.qq.com/cgi-bin/uploadmaterial?cgi=uploadmaterial&type='.$param['type'].'&token='.$this->_token.'&t=iframe-uploadfile&lang=zh_CN&formId=1';
        $stream = $this->_uploadFile($url, $post_data);
        echo '</pre>';
        print_r($stream);
        echo '</pre>';
        exit;
    }

    /**
     * 获得用户发过来的消息（消息内容和消息类型）
     */
    public function getMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if ($this->debug) {
            $this->write_log($postStr);
        }
        if (!empty($postStr)) {
            $this->msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->msgtype = strtolower($this->msg['MsgType']);//获取用户信息的类型
            $this->eventkey = strtolower($this->msg['EventKey']);//获取key值
        }
    }
    /**
     * 回复文本消息
     * @param string $text
     * @return string
     */
    public function makeText($text='')
    {
        $createtime = time();
        $funcflag = $this->setFlag ? 1 : 0;
        $textTpl = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>%s</FuncFlag>
            </xml>";
        return sprintf($textTpl,$text,$funcflag);
    }
    /**
     * 回复图文消息
     * @param array $newsData
     * @return string
     */
    public function makeNews($newsData=array())
    {
        $createtime = time();
        $funcflag = $this->setFlag ? 1 : 0;
        $newTplHeader = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <ArticleCount>%s</ArticleCount><Articles>";
        $newTplItem = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $newTplFoot = "</Articles>
            <FuncFlag>%s</FuncFlag>
            </xml>";
        $content = '';
        $itemsCount = count($newsData['items']);
        $itemsCount = $itemsCount < 10 ? $itemsCount : 10;//微信公众平台图文回复的消息一次最多10条
        if ($itemsCount) {
            foreach ($newsData['items'] as $key => $item) {
                $content .= sprintf($newTplItem,$item['title'],$item['description'],$item['picUrl'],$item['url']);//微信的信息数据

            }
        }
        $header = sprintf($newTplHeader,$newsData['content'],$itemsCount);
        $footer = sprintf($newTplFoot,$funcflag);
        return $header . $content . $footer;
    }
    /**
     * 回复音乐消息
     * @param array $newsData
     * @return string
     */
    public function makeMusic($newsData=array())
    {
        $createtime = time();
        $funcflag = $this->setFlag ? 1 : 0;
        $textTpl = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
			<Title><![CDATA[{$newsData['title']}]]></Title>
            <Description><![CDATA[{$newsData['description']}]]></Description>
            <MusicUrl><![CDATA[{$newsData['MusicUrl']}]]></MusicUrl>
            <HQMusicUrl><![CDATA[{$newsData['HQMusicUrl']}]]></HQMusicUrl>
			</Music>
			<FuncFlag>%s</FuncFlag>
			</xml>";
        return sprintf($textTpl,'',$funcflag);
    }
    /**
     * 得到制定分组的用户列表
     * @param number $groupid
    @param number $pagesize，每页人数
    @param number $pageidx，起始位置
     * @return Ambigous <boolean, string, mixed>
     */
    public function getfriendlist($groupid=0,$pagesize=500,$pageidx=0)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/contactmanagepage?token='.$this->_token.'&t=wxm-friend&lang=zh_CN&pagesize='.$pagesize.'&pageidx='.$pageidx.'&groupid='.$groupid;
        $referer = "https://mp.weixin.qq.com/";
        $response = $this->_html($url, $referer);
        if (preg_match('%< id="json-friendList" type="json/text">([\s\S]*?)</>%', $response, $match))         {
            $tmp = json_decode($match[1], true);
        }
        return $tmp;
    }

    /**
     * 返回给用户信息
     *
     */
    public function reply($data)
    {
        if ($this->debug) {
            $this->write_log($data);
        }
        echo $data;
    }
    /**
     *
     * @param type $log
     */
    private function write_log($log)
    {
        //这里是你记录调试信息的地方请自行完善以便中间调试

        error_log($log."\r\n", 3, "log.txt");
    }

    /**
     * 从Stream中提取cookie
     *
     * @param string $stream
     */
    private function _cookie($stream)
    {
        //echo $stream;

        preg_match_all("/Set-Cookie: (.*?);/is", $stream, $matches);
        $this->_cookie = @implode(";", $matches[1]);

    }
    /**
     * 获取Stream
     *
     * @param string $url
     * @param string $post
     * @return mixed
     */
    public function _html($url, $post = FALSE)
    {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if ($post){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_COOKIE, $this->_cookie);
        curl_exec($ch);
        curl_close($ch);
        $_str = ob_get_contents();
        $_str = str_replace("script", "", $_str);
        ob_end_clean();
        return $_str;
    }
    private function _uploadFile($url, $post = array())
    {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if (is_array($post) && count($post)>0){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_COOKIE, $this->_cookie);
        curl_exec($ch);
        curl_close($ch);
        $_str = ob_get_contents();
        $_str = str_replace("script", "", $_str);
        ob_end_clean();
        return $_str;
    }


    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function http_post($url,$param){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    public function get_token(){
        $token_time = file_get_contents(PREFIX."/cache/token_time.txt");
        $token_time = json_decode($token_time);
        if(!isset($token_time) || (strtotime("now") - $token_time->time)>7200){
            $wx_remote_server = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".WX_APPID."&secret=".WX_APPSECRET;
            $token_str = $this->http_get($wx_remote_server);
            $token = json_decode($token_str);
            if(isset($token->access_token)){
                $fp = fopen(PREFIX."/cache/token_time.txt","w");
                $token->time = strtotime("now");
                fwrite($fp,json_encode($token));
                return $token->access_token;
            }else{
                return false;
            }
        }else{
            return $token_time->access_token;
        }
    }

    public function get_usr_info($openid){
        $token = $this->get_token();
        $usr_info = $this->http_get("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$openid."&lang=zh_CN");
        return $usr_info;
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = WX_TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}