<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 15/5/30
 * Time: 下午7:36
 */

class baidu {
    protected $server_url = "http://data.zz.baidu.com/urls?site=www.66173.cn&token=GyQX30pRYT4CBvWG";
    protected $ping_url;

    public function ping_topic($url){
        $this->ping_url = $url;
        $this->request();
    }

    protected function request(){
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $this->server_url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $this->ping_url,
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $this->err_log(var_export($result,1));
    }

    protected function err_log($word){
        file_put_contents(PREFIX.DS.'logs/baidu_'.date('Ymd').'.log', strftime("%Y-%m-%d %H:%M:%S",time())."\r\n".$word."\r\n",FILE_APPEND);
    }
}