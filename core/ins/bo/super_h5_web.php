<?php
COMMON('baseCore');
class super_h5_web extends baseCore{

    public $DAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;
    public $usr_params;

    protected $exchanges;
    protected $api_serv_url;
    protected $api_user_url;
    protected $api_order_url;

    public function __construct(){
        parent::__construct();
        $this->DAO = new super_pay_dao();
        $this->qa_user_id = array('71');
    }

    public function tianxing_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if (empty($channel_info)) {
            $result['desc'] = '游戏信息异常';
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['appId']);
        unset($get_data['sign']);

        $sign_str = $this->tianxingSignData($get_data,$channel_info['app_key']);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function tianxing_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['appId']);

        $sign_str = $this->tianxingSignData($get_data,$channel_info['app_key']);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function tianxingSignData($data,$game_key){
        ksort($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=' . $v; }
        $str = implode('&', $tmp) . $game_key;

        return $str;
    }

    public function xunrui_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        if(!$get_data['phone'] || $get_data['phone'] == 'null'){
            unset($get_data['phone']);
        }
        unset($get_data['platform']);
        unset($get_data['appId']);
        unset($get_data['sign']);
        $sign_str = $this->tianxingSignData($get_data,$channel_info['app_key']);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function xunrui_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['appId']);
        $sign_str = $this->tianxingSignData($get_data,$channel_info['app_key']);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function lanmo_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['appId']);
        $get_data['appkey'] = $channel_info['app_key'];
        $sign_str = $this->SignData($get_data);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function lanmo_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['appId']);
        $get_data['appkey'] = $channel_info['app_key'];
        $sign_str = $this->SignData($get_data);
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }

    public function SignData($data){
        ksort($data);
        foreach($data as $k => $v){
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp) ;
        return $str;
    }

    public function iqiyi_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $sign_str = strtolower(md5("user_id=".$params['user_id']."&agent=".$params['agent']."&time=".$params['time']."&key=".$channel_info['param1']));
        $result = array("result" => 1, "desc" => "请求成功", "sign" => $sign_str);
        die(json_encode($result));
    }

    public function iqiyi_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $result = array("result" => 1, "desc" => "请求成功");
        die(json_encode($result));
    }

    public function xiaomi_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['app_id'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['app_id']);
        $get_data['appId'] = $channel_info['app_id'];
        $get_data['gameId'] = $channel_info['param1'];
        $sign_str = $this->SignData($get_data).'&'.$channel_info['app_key'];
        $get_data['sign'] = md5($sign_str);
        $result = array("result" => 1, "desc" => "请求成功", "info" => $get_data);
        die(json_encode($result));
    }

    public function xiaomi_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['app_id'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
            die(json_encode($result));
        }
        $get_data = $params;
        unset($get_data['platform']);
        unset($get_data['app_id']);
        $sign_str = $this->SignData($get_data).'&'.$channel_info['app_key'];
        $result = array("result" => 1, "desc" => "请求成功", "sign" => md5($sign_str));
        die(json_encode($result));
    }
}
