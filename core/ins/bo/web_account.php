<?php
COMMON('baseCore', 'pageCore','uploadHelper','alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
BO('web_admin');
DAO('web_account_dao');
class web_account  extends baseCore{
    public $DAO;
    public $bo;
    public $qa_user_id;
    public $USR_HEADER;

    public function __construct(){
        parent::__construct();
        $this->DAO = new web_account_dao();
        $this->bo = new web_admin();
        $this->qa_user_id = array(2233916,1441047,1883479,57);
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                if($param[0] == 'sdkver'){
                    $this->sdkver = $param[1];
                }elseif($param[0] == 'channel'){
                    $this->guild_code = $param[1];
                }elseif($param[0] == 'ver'){
                    $this->gameVer = $param[1];
                }
            }
        }
        $this->USR_HEADER =  $this->get_usr_session('USR_NEWS_HEADER');
    }

    public function account_bill($user_id){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id){
            $status = -1;
            $this->page = 1;
            $order_list = $this->DAO->order_list($user_id, $status,$this->page);
            foreach ($order_list as $key => $order) {
                $app = $this->DAO->get_app_info($order['app_id']);
                $order_list[$key] = array_merge($order, array('AppName' => $app['game_name'], 'en_name' => $app['en_name']));
            }
            $this->V->assign("is_read", $this->bo->is_read());
            $this->V->assign('is_question_read',$this->bo->is_question_read());
            $this->V->assign("status", $status);
            $this->V->assign("order_list", $order_list);
        }
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_bill.html");
    }

    public function bill_more(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'code'=> 0,
            'desc'=> '网络异常。',
            'list'=> ''
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }else{
            $status = -1;
            $order_list = $this->DAO->order_list($USR_HEADER['user_id'], $status, $this->page);
            foreach ($order_list as $key => $order) {
                $app = $this->DAO->get_app_info($order['app_id']);
                $order_list[$key] = array_merge($order, array('AppName' => $app['game_name'], 'en_name' => $app['en_name']));
            }
        }
        if($order_list){
            $result['code'] = 1;
            $result['desc'] = '查询成功。';
            $result['list'] = $order_list;
        }else{
            $result['code'] = 1;
            $result['desc'] = '查询成功';
            $result['list'] = "";
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function account_center($user_id){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id){
            $userDao = new user_dao();
            $user_info = $userDao->get_user_info($user_id);
            $nd_info = $this->DAO->get_nd_info($user_id,$USR_HEADER['appid']);
            $this->V->assign("nd_info", $nd_info);
            $this->V->assign("user_info", $user_info);
        }
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_center.html");
    }

    public function account_change_password(){
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_change_password.html");
    }

    public function account_niubi($user_id){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id) {
            $userDao = new user_dao();
            $user_info = $userDao->get_user_info($user_id);
            $nnb_log = $userDao->get_user_nnb_log($user_id,1);
            $this->V->assign("app_id", $USR_HEADER['appid']);
            $this->V->assign("nnb_log", $nnb_log);
            $this->V->assign("user_info", $user_info);
        }
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_niubi.html");
    }

    public function niubi_more(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'code'=> 0,
            'desc'=> '网络异常。',
            'list'=> ''
        );
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        $userDao = new user_dao();
        $nnb_log = $userDao->get_user_nnb_log($USR_HEADER['user_id'],$this->page);
        if($nnb_log){
            $result['code'] = 1;
            $result['desc'] = '查询成功。';
            $result['list'] = $nnb_log;
        }else{
            $result['code'] = 1;
            $result['desc'] = '查询成功';
            $result['list'] = "";
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function account_niudian($user_id){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        $page = 1;
        $nd_info = $this->DAO->get_nd_info($user_id,$USR_HEADER['appid']);
        if($nd_info['nd_lock'] == '1'){
            $this->bo->show_error("敬请期待");
            exit();
        }
        $nd_log = $this->get_user_nd($user_id,$USR_HEADER['appid'],$page);
        $this->V->assign("nd_info", $nd_info);
        $this->V->assign("nd_log", $nd_log);
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('is_read',$this->bo->is_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_niudian.html");
    }

    public function niudian_more(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'code'=> 0,
            'desc'=> '网络异常。',
            'list'=> ''
        );
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        $nd_log = $this->get_user_nd($USR_HEADER['user_id'],$USR_HEADER['appid'],$this->page);
        if($nd_log){
            $result['code'] = 1;
            $result['desc'] = '查询成功。';
            $result['list'] = $nd_log;
        }else{
            $result['code'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function get_user_nd($user_id,$app_id,$page){
        $three_month = mktime(date('H'),date('i'),date('s'),date('m')-3,date('d'),date('y'));
        $nd_log = $this->DAO->get_nd_log($user_id,$app_id,$three_month);
        $nd_orders = $this->DAO->get_nd_orders($user_id,$app_id,$three_month);
        $user_msg = array_merge($nd_log,$nd_orders);
        $data = $this->bo->array_sort($user_msg,'add_time','desc');
        $new_data = array_chunk($data,10);
        return $new_data[$page-1];
    }

    public function account_phone_bind(){
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_phone_bind.html");
        if($this->USR_HEADER['orientation'] == 'p'){
            $this->display("web_v/account_phone_bind.html");
        }else{
            $this->display("web_h/account_phone_bind.html");
        }
    }

    public function account_phone_bind_change(){
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_phone_bind_change.html");
    }

    public function account_real_verify($user_id){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id) {
            $userDao = new user_dao();
            $user_info = $userDao->get_user_info($user_id);
            $this->V->assign("user_info", $user_info);
        }
        $this->V->assign("is_read", $this->bo->is_read());
        $this->V->assign('is_question_read',$this->bo->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/account_real_verify.html");
    }

    public function verify_submit(){
        $result = array('code'=>'0','desc'=>'网络出错');
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $params = $_POST;
        if(strpos($params['user_name'],'·') || strpos($params['user_name'],'•')){
            $smb = true;
        }else{
            $smb = false;
        }
        $preg = preg_match("/^[\x{4e00}-\x{9fa5}]{1,5}[·•][\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        $preg1 = preg_match("/^[\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        if(mb_strlen($params['user_name']) < 2 || ($smb && !$preg) || (!$smb && !$preg1)){
            $result['desc'] = '真实姓名错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $idc = $this->is_idcard($params['id_number']);
        if(!$idc){
            $result['desc'] = '身份证号码错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $age = date('Y') - mb_substr($params['id_number'],6,4);
        $this->DAO->update_user_info($params,$USR_HEADER['user_id'],$age);
        $result['code'] = 1;
        $result['desc'] = '认证成功';
        die('0'.base64_encode(json_encode($result)));
    }

    function is_idcard($id){
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $id)){
            return FALSE;
        }
        if(15==strlen($id)){     //检查15位
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)){
                return FALSE;
            }else{
                return TRUE;
            }
        }else{     //检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)){//检查生日日期是否正确
                return FALSE;
            }else{
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for( $i = 0; $i < 17; $i++ ){
                    $b = (int) $id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n  = $sign % 11;
                $val_num = $arr_ch[$n];
                if($val_num != substr($id,17, 1)){
                    return FALSE;
                }else{
                    return TRUE;
                }
            }
        }
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    function decrypt($str,$privateKey) {
        $iv     = "1236547887654123";
        $encryptedData = base64_decode($str);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);
        return $decrypted;
    }

    public function sms_code(){
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->select(1);
        $result = array('result' => 0 ,'desc'=>'网络请求出错');
        $this->USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$this->USR_HEADER['user_id']){
            $result['desc'] = '参数异常';
            die('0'.base64_encode(json_encode($result)));
        }
        if($this->USR_HEADER['appid']){
            $app_info = $this->DAO->get_app_info($this->USR_HEADER['appid']);
        }else{
            $result['desc'] = '访问出错啦';
            die('0'.base64_encode(json_encode($result)));
        }
        $phone = $_GET['mobile'];
        $phone = trim($this->decrypt($phone,$app_info['app_key']));
        if(!$phone || !$this->is_mobile($phone)){
            $result['desc'] = "请输入正确的手机号";
            die('0'.base64_encode(json_encode($result)));
        }
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($this->USR_HEADER['user_id']);
        $nowtime = strtotime("now");
        if($phone != $user_info['mobile']){
            $result['desc'] = "您输入的手机号码与原手机号码不一致，请重新输入";
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$this->is_mobile($phone)){
            $result['desc'] = "验证消息发送失败,请重试";
            die('0'.base64_encode(json_encode($result)));
        }
        $last_send_time = $redis->get('last_send_time');
        if(isset($last_send_time)){
            if($nowtime-$last_send_time<120){
                $result['desc'] = "获取验证码太频繁，请稍后再试";
                die('0'.base64_encode(json_encode($result)));
            }else{
                $redis->set('last_send_time',$nowtime);
            }
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_sms($phone,array($code),"181503");
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $redis->hmset(md5($this->USR_HEADER['sid'])."MOBILE",array('mobile'=>$phone,'code'=>$code));
            $redis->set('last_send_time',$nowtime);
            $redis->expire(md5($this->USR_HEADER['sid'])."MOBILE",300);
            $result['result'] = "1";//1成功0失败
            $result['desc'] = "验证消息发送成功！";
//            $result['sms_code'] = $code;
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result['desc'] = "验证消息发送失败,请重试2";
            die('0'.base64_encode(json_encode($result)));
        }
    }

    public function verify_old_idcard(){
        $result = array('result' => 0 ,'desc'=>'网络请求出错');
        $this->USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$this->USR_HEADER['user_id']){
            $result['desc'] = '参数异常';
            die('0'.base64_encode(json_encode($result)));
        }
        if($this->USR_HEADER['appid']){
            $app_info = $this->DAO->get_app_info($this->USR_HEADER['appid']);
        }else{
            $result['desc'] = '访问出错啦';
            die('0'.base64_encode(json_encode($result)));
        }
        $error = $this->DAO->get_session('idcard_error_'.$this->USR_HEADER['user_id']);
        if($error >= 3){
            $idcard_error_time = $this->DAO->get_session('idcard_error_time');
            if(isset($idcard_error_time)){
                if(strtotime("now")-$idcard_error_time<10){
                    $result['desc'] = "请求太频繁，请稍后再试";
                    die('0'.base64_encode(json_encode($result)));
                }else{
                    $this->DAO->set_session('idcard_error_time',array('idcard_error_time'=>strtotime("now")));
                }
            }
            $this->DAO->set_session('idcard_error_time',array('idcard_error_time'=>strtotime("now")));
        }
        $params = $_GET;
        $params['id_number'] = trim($this->decrypt($params['id_number'],$app_info['app_key']));
        if(!$params['id_number']){
            $error = $error + 1;
            $this->DAO->set_session('idcard_error_'.$this->USR_HEADER['user_id'],array('idcard_error_'.$this->USR_HEADER['user_id']=>$error));
            $result['desc'] = '请输入原主人的身份证号码';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['name']){
            $error = $error + 1;
            $this->DAO->set_session('idcard_error_'.$this->USR_HEADER['user_id'],array('idcard_error_'.$this->USR_HEADER['user_id']=>$error));
            $result['desc'] = '请输入原主人的真实姓名';
            die('0'.base64_encode(json_encode($result)));
        }
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($this->USR_HEADER['user_id']);
        if($params['id_number'] != $user_info['id_number'] ){
            $error = $error + 1;
            $this->DAO->set_session('idcard_error_'.$this->USR_HEADER['user_id'],array('idcard_error_'.$this->USR_HEADER['user_id']=>$error));
            $result['desc'] = '您填写的身份证号不正确，请重新填写';
            die('0'.base64_encode(json_encode($result)));
        }
        if(trim($params['name']) != $user_info['user_name']){
            $error = $error + 1;
            $this->DAO->set_session('idcard_error_'.$this->USR_HEADER['user_id'],array('idcard_error_'.$this->USR_HEADER['user_id']=>$error));
            $result['desc'] = '您填写的真实姓名不正确，请重新填写';
            die('0'.base64_encode(json_encode($result)));
        }
        $result['result'] = 1;
        $result['desc'] = '信息校对成功';
        $this->DAO->del_session('idcard_error_'.$this->USR_HEADER['user_id']);
        die('0'.base64_encode(json_encode($result)));
    }

    public function bind_new_idcard(){
        $result = array('result' => 0 ,'desc'=>'网络请求出错');
        $this->USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$this->USR_HEADER['user_id']){
            $result['desc'] = '参数异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $params = $_GET;
        if($this->USR_HEADER['appid']){
            $app_info = $this->DAO->get_app_info($this->USR_HEADER['appid']);
        }else{
            $result['desc'] = '访问出错啦';
            die('0'.base64_encode(json_encode($result)));
        }
        $params['id_number'] = trim($this->decrypt($params['id_number'],$app_info['app_key']));
        $params['user_name'] = $params['name'];
        if(!$params['id_number']){
            $result['desc'] = '请输入您的身份证号码';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['user_name']){
            $result['desc'] = '请输入您的真实姓名';
            die('0'.base64_encode(json_encode($result)));
        }
        if(strpos($params['user_name'],'·') || strpos($params['user_name'],'•')){
            $smb = true;
        }else{
            $smb = false;
        }
        $preg = preg_match("/^[\x{4e00}-\x{9fa5}]{1,5}[·•][\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        $preg1 = preg_match("/^[\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        if(mb_strlen($params['user_name']) < 2 || ($smb && !$preg) || (!$smb && !$preg1)){
            $result['desc'] = '真实姓名错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $idc = $this->is_idcard($params['id_number']);
        if(!$idc){
            $result['desc'] = '身份证号码错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $number_info = $this->DAO->get_user_info($params['id_number'],$this->USR_HEADER['user_id']);
        if($number_info['num'] >= 20){
            $result['desc'] = '该身份证号绑定数量已上限';
            die('0'.base64_encode(json_encode($result)));
        }
        $userDao = new user_dao();
        $age = date('Y') - mb_substr($params['id_number'],6,4);
        $user_info = $userDao->get_user_info($this->USR_HEADER['user_id']);
        $this->DAO->update_user_info($params,$this->USR_HEADER['user_id'],$age);
        $this->DAO->insert_operation_log($user_info['user_id'],"3",1,"真实姓名与身份证号由“".$user_info['user_name'].'_'.$user_info['id_number']."”变更为“".$params['user_name'].'_'.$params['id_number']."”");
        $result['result'] = 1;
        $result['desc'] = '更绑成功';
        die('0'.base64_encode(json_encode($result)));
    }

}