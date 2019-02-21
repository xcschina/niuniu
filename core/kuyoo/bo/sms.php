<?php
COMMON('baseCore');
BO('baseKuyoo');

class sms extends baseKuyoo{

    public function __construct(){
        parent::__construct();

        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    private $special_tel = array(
        '18559180171', '15059480647', '13635272973'
    );

    public function send(){
        $result = array('result' => 0, 'desc' => '短消息发送失败');

        $tel = $_REQUEST['tel'];
        $sms_template_id = intval($_REQUEST['template_id']);
        if(!$sms_template_id){
            $sms_template_id = 0;
        }

        if (!$tel) {
            $result['desc'] = '电话号码不能为空!';
            $this->out_put(json_encode($result));
        }

        if(!$this->check_phone_number($tel)){
            $result['desc'] = '手机号码不正确!';
            $this->out_put(json_encode($result));
        }

        $msg = $_REQUEST['msg'];

        if (!$msg) {
            $result['desc'] = '短消息不能为空!';
            $this->out_put(json_encode($result));
        }

        $msg = urldecode($msg);

        if($sms_template_id > 0){
            $msg = json_decode($msg, true);
        }else{
            $msg = array($msg);
        }
        $telphones = memcache_get($this->mmc, 'sended_sms_telphone');
        $now = $_SERVER['REQUEST_TIME'];

        $n_telphones = array();

        if ($telphones) {
            foreach ($telphones as $key => $t_tel) {
                if ($now - $t_tel['first_time'] < 600) { //十分钟有效
                    $n_telphones[$key] = $t_tel;
                }
            }
        }

        $info = array_key_exists($tel, $n_telphones) ? $n_telphones[$tel] : array('first_time' => $now, 'freq' => 0);

        if ($info['freq'] >= 3 && !in_array($tel , $this->special_tel)) {
            $result['desc'] = '十分钟内已发送三次短消息,请稍后再试.';
            $this->out_put(json_encode($result));
        }

        if($this->send_sms($tel, $msg, $sms_template_id)){
            $info['freq'] += 1;
            $n_telphones[$tel] = $info;
            $result['desc'] = '短消息正在发送,请耐心等待!';
            $result['result'] = 1;
        }

        memcache_delete($this->mmc, 'sended_sms_telphone');
        memcache_set($this->mmc, 'sended_sms_telphone', $n_telphones, 1, 0);
        $this->out_put(json_encode($result));
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    function logResult($word='') {
        $fp = fopen(PREFIX."/htdocs/log_sms.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}