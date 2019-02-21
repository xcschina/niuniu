<?php
COMMON('baseCore','alipay','alipay/alipay_submit.class','alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
class game_super_web extends baseCore{

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
        $this->DAO = new game_super_dao();
        $this->qa_user_id = array('71');
        $this->super_usr_header();
    }

    public function token($data){
        $this->params_check($data);
        if($data['platform']=='huawei'){
            $result = array("result" => 1, "desc" => "验证通过");
            die(json_encode($result));
        }else{
            $result = array("result" => 1, "desc" => "验证通过");
//            $result = array("result" => 0, "desc" => "渠道信息异常");
            die(json_encode($result));
        }

    }

    public function add_role(){
//        var_dump($this->usr_params);
        $app_id = $this->usr_params['appId'];
        $user_id = $this->usr_params['userId'];
        $server_id = $this->usr_params['serverId'];
        $role_id = $this->usr_params['roleId'];
        if(!$app_id || !$user_id ||!$server_id||!$role_id){
            $this->err_log(var_export($this->usr_params,1),'super_h5_role_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        $user_info = $this->DAO->get_user_app_info($app_id,$user_id,$server_id,$role_id);
        $ip = $this->client_ip();
        $this->usr_params['token'] = $this->create_guid();
        if(empty($user_info)){
            $this->DAO->add_user_app_info($this->usr_params,$ip);
        }else{
            $this->DAO->update_user_app_info($this->usr_params,$ip,$user_info['ID']);
        }
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function add_device(){
//        var_dump($this->usr_params);
        $app_id = $this->usr_params['appId'];
        $user_id = $this->usr_params['userId'];
        $ip = $this->client_ip();
        $SID = $this->usr_params['sid'];
        if(!$app_id){
            $this->err_log(var_export($this->usr_params,1),'super_h5_device_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        if(!empty($SID)){
           $device_info = $this->DAO->get_device_info($SID,$this->usr_params['platform'],$app_id);
           if($device_info['id']){
               if(empty($user_id) && $device_info['user_id']){
                   $this->usr_params['userId'] = $device_info['user_id'];
               }
               $this->DAO->update_device_info($this->usr_params,$ip,$this->create_guid(),$device_info['id']);
           }else{
               $this->DAO->add_device_log($this->usr_params,$ip,$this->create_guid());
           }
//        }else{
//            $this->DAO->add_device_log($this->usr_params,$ip,$this->create_guid());
        }
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function add_login(){
//        var_dump($this->usr_params);
        $app_id = $this->usr_params['appId'];
        $user_id = $this->usr_params['userId'];
        $ip = $this->client_ip();
        if(!$app_id || !$user_id){
            $this->err_log(var_export($this->usr_params,1),'super_h5_login_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        $this->usr_params['token'] = $this->create_guid();
        $this->DAO->add_super_user_log($this->usr_params,$ip,'渠道账号登录');
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function params_check($data){
        if(!is_array($data)){
            $this->result_error('缺少必要参数！');
        }
        $data = array();
        foreach($data as $key=>$item){
            switch ($key){
                case "app_id":
                    if(empty($item)){
                        $this->result_error('缺少游戏ID！');
                    }
                    break;
                case "user_id":
                    if(empty($item)){
                        $this->result_error('缺少用户ID！');
                    }
                    break;
                case "platform":
                    if(empty($item)){
                        $this->result_error('platform不能为空！');
                    }
                    break;
                case "token":
                    if(empty($item)){
                        $this->result_error('token不能为空！');
                    }
                    break;
                case "sign":
                    if(empty($item)){
                        $this->result_error('sign不能为空！');
                    }
                    break;
            }
        }
        return true;
    }

    public function xsb_login(){
        $params = $_POST;
        if(!$params['app_id']){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少游戏ID"))));
        }
        if(!$params['game_id']){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少渠道参数"))));
        }
        if(!$params['sessionid']){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"token不能为空"))));
        }

        $super_info = $this->DAO->get_super_info($params['app_id'],$params['game_id']);
        if(empty($super_info)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"渠道信息获取失败"))));
        }
        $url = 'http://api.fb.xueshanbao.com/sdk.php';
        $post_data = array(
            'ac'=>'checklogin',
            'appid'=>$super_info['app_key'],
            'sdkversion'=>'1.4',
            'sessionid'=>$params['sessionid'],
            'time'=>time()

        );
        $arg = '';
        foreach($post_data as $key=>$item){
            if(!empty($item)){
//                if($key == 'sessionid'){
//                    $domain = strpos($item, '%');
//                    var_dump($domain);
//                    if($domain){
//                        $item = urldecode($item);
//                    }
//                }
                $arg = $arg.$key.'='.$item.'&';
            }
        }
        $arg = substr($arg,0,strlen($arg)-1);
        $sign = md5($arg.$super_info['param1']);
        $post_data['sign'] = $sign;
        $this->err_log(var_export($sign,1),'xsb_log');
        $this->err_log(var_export($post_data,1),'xsb_log');
        $result = $this->request($url,$post_data);
        $result = json_decode($result);
        $this->err_log(var_export($result,1),'xsb_log');
        if($result->status == 'success'){
            die('0'.base64_encode(json_encode(array("result"=>1, "desc"=>'成功',
                    'uid'=>$result->userInfo->uid, 'username'=>$result->userInfo->username))));
        }else{
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>$result->msg))));
        }

    }

    public function result_error($msg){
        $result = array("result" => 0, "desc" => "网络异常");
        if($msg){
            $result['desc'] = $msg;
        }
        die(json_encode($result));
    }

//    public function log_out(){
//        $_SESSION['h5_user_info']['app_id'] = $_SESSION['h5_user_info']['appid'] ;
//        $user_info = $_SESSION['h5_user_info'];
//        unset($_SESSION['h5_user_info']);
//        $ip = $this->client_ip();
//        $this->DAO->add_user_log($user_info,$ip,'登出');
//        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"注销成功。"))));
//    }

    public function super_usr_header(){
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            $params = array();
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                $params[$param[0]] = $param[1];
            }
            $this->usr_params = $params;
        }
    }

}
?>
