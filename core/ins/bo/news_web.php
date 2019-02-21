<?php
COMMON('sdkCore','uploadHelper','alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
DAO('android_pay_dao');

class news_web extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new news_dao();
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
    }

    public function news_status($app_id,$user_id,$channel){
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'num'=> 0,
            'timestamp'=> 0,
            'is_show'=> 0,
            'msg_id'=> 0,
        );
        $time = time();
        if(!$app_id ||!$user_id ||!$channel){
            $result['desc']='参数异常。';
            die("0".base64_encode(json_encode($result)));
        }
        $timestamp = 0;
        if($_GET['time']){
            $timestamp = $_GET['time'];
        }
        //推送用户的消息
        $user_count = $this->DAO->get_user_news_count($app_id,$user_id,$channel,$timestamp);
        //所有推送游戏or指定渠道游戏
        $new_num = $this->DAO->get_game_news_count($app_id,$channel,$timestamp);
        $new_count = $this->DAO->game_news_is_read_count($app_id,$user_id,$channel,$timestamp);
        $user_msg = $this->get_user_message($user_id,$app_id,$channel,1);
        $kefu_num = $this->DAO->get_service($app_id,$user_id);
        if($user_msg[0]['id']){
            $result['is_show'] = 1;
            $result['msg_id'] = $user_msg[0]['id'];
        }
        if(is_numeric($user_count) && is_numeric($new_num) && is_numeric($new_count)){
            $result['kefu_num'] = $kefu_num['num'];
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['num'] = $user_count+$new_num-$new_count;
            $result['timestamp'] = $time;
        }

        die("0".base64_encode(json_encode($result)));
    }

    public function news_list($app_id,$user_id,$channel){
        if(!$app_id || !$user_id || !$channel){
            die("请先登录游戏");
        }
        $this->page = 1;
        $user_message = $this->get_user_message($user_id,$app_id,$channel,$this->page);
        $this->assign('info',$user_message);
        $this->V->display('news_list.html');
    }

    public function get_user_message($user_id,$app_id,$channel,$page='1'){
//        $data = $this->DAO->get_mmc_data('user_msg'.$user_id."_".$app_id."_".$channel);
        if(!$data){
            $user_meassge = $this->DAO->get_user_message_list($user_id, $app_id, $channel);
            $app_meassge = $this->DAO->get_app_message_list( $app_id, $channel);
            if ($app_meassge) {
                foreach ($app_meassge as $key => $data) {
                    $msg_log = $this->DAO->get_user_msg_log($data['id'], $user_id);
                    if ($msg_log) {
                        $app_meassge[$key]['is_read'] = $msg_log['is_read'];
                    } else {
                        $app_meassge[$key]['is_read'] = 0;
                    }
                }
            }
            $user_msg = array_merge($user_meassge,$app_meassge);
            $data = $this->array_sort($user_msg,'push_time','desc');
            $this->DAO->set_mmc_data('user_msg'.$user_id."_".$app_id."_".$channel,$data);
        }
        $new_data = array_chunk($data,10);
        return $new_data[$page-1];
    }

    //多维数组通过关键字排序
    public function array_sort($array,$keys,$type='asc'){
        $keysvalue = $new_array = array();
        foreach ($array as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc') {
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $array[$k];
        }
        return $new_array;
    }

    public function news_info($appid,$user_id,$channel,$id){
        $details = $this->DAO->get_message_details($id,$appid);
        //更新已读状态
        $message_log = $this->DAO->get_message_log($id,$user_id);
        if(empty($message_log)){
            $this->DAO->add_message_log($id,$user_id);
        }elseif($message_log && $message_log['is_read']=='0'){
            $this->DAO->update_message_log($message_log['id']);
        }
        $this->assign('details',$details);
        $this->V->display('news_details.html');
    }

    public function news_more($appid,$user_id,$channel){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'info'=> '',
            'num'=> 0
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id'] || !$USR_HEADER['channel']){
            $result['desc']='参数异常。';
            die(json_encode($result));
        }
        //推送用户的消息
        $user_message = $this->get_user_message($USR_HEADER['user_id'],$USR_HEADER['appid'],$USR_HEADER['channel'],$this->page);
        if($user_message){
            $result['result'] = 1;
            $result['desc'] = '查询成功。';
            $result['info'] = $user_message;
            $result['num'] = count($user_message);
        }else{
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
            $result['num'] = 0;
        }
        die(json_encode($result));
    }


    public function update_order_group(){
        $this->open_debug();
        $orders = $this->DAO->get_order_list();
        if(!$orders){
            die("执行完毕");
        }
        echo '开始时间：'.time();
        foreach($orders as $k=>$order){
           if(!$order['channel']){
               $this->DAO->update_order_info($order,4); //无公会
           }else{
               //公会账号
               $ch_info = $this->DAO->get_channel_info($order['channel'],10);
               if($ch_info){
                   $this->DAO->update_order_info($order,1);//公会
               }else{
                   //正则匹配渠道
                   $information_ch = preg_replace('/(.*[a-zA-Z]+)\d*$/','$1',$order['channel']);
                   $information = $this->DAO->get_channel_info($information_ch,12);
                   if($information){
                       $this->DAO->update_order_info($order,2);//信息流
                   }else{
                       $this->DAO->update_order_info($order,5); //其他
                   }
               }
           }
        }
        echo '结束时间：'.time();
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

}
