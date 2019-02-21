<?php
COMMON('sdkCore','class.phpmailer', 'class.smtp','uploadHelper');
DAO('play_ground_dao');

class play_ground extends sdkCore{
    public $DAO;
    public $app_id;
    public $login_integral;
    public $data;
    public $ES;
    public $ES_PARAMS;
    public $qa_user_id;
    public $money;

    public function __construct(){
        parent::__construct();
        $this->DAO = new play_ground_dao();
        $hosts = [ ES_HOST.":".ES_PORT];
        $this->ES = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->ES_PARAMS = array();
        $this->qa_user_id = array('71','164626');
        $this->app_id = '1100';
        $this->login_integral = '100';
        $this->money = array('10','30','50','100','300','500');
        $this->data = array(array('type'=>1,'title'=>'通天塔掉率翻倍限时开启','app_id'=>'1060'),array('type'=>2,'title'=>'魔域魔石充值抽奖已上线'));
    }

    public function index($app_id){
        $start_time = strtotime(date('Y-m-d', time()));
        $end_time = strtotime(date('Y-m-d 24:00:00', time()));
        $user = $this->get_usr_session('user');
        if(!$app_id){
            $app_id = $this->app_id;
        }
        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info['app_id']){
            die("链接出错啦");
        }
        if(!$app_info['version_url']){
            die("游戏已下架。");
        }
        $user_message = $this->DAO->get_user_message($user['user_id']);
        if($user_message){
            $user_info = $this->DAO->get_user_info($user['user_id']);
        }else{
            $user_info = $this->DAO->get_user($user['user_id']);
            $user_info['integral'] = 0;
            $user_info['exp'] = 0;
        }
        $info = $this->DAO->get_login_reward($user['user_id'],$start_time,$end_time);
        if($info){
            $first_login = 1;
        }else{
            $this->DAO->insert_login_reward($user,$app_id,$this->login_integral);
            $integral = $user_info['integral'] + (int)$this->login_integral;
            if($user_message){
                $this->DAO->update_user_integral($user['user_id'],$integral);
            }else{
                $this->DAO->insert_user_message($user['user_id'],$integral);
            }
            $this->DAO->update_user_integral($user['user_id'],$integral);
            $first_login = 0;
        }
        if($user_info['mobile']){
            $user_info['mobile'] = substr_replace($user_info['mobile'],"******", 3, 6);
        }
        $order_list = $this->get_integral_info(1);
        $this->assign('order_list',$order_list);
        $this->assign('data_list',$this->data);
        $this->assign('user_info',$user_info);
        $this->assign('app_id',$app_id);
        $this->assign('app_info',$app_info);
        $this->assign('if_du',0);//开启debug
        $this->assign("isFirstLogin",$first_login);
        $this->assign("addIntegral",$this->login_integral);
        $this->display("website/ylc.html");
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    public function shop_list(){
        $result = array('result'=>0,'desc'=>'网络出错啦');
        $user = $this->get_usr_session('user');
        if(!$user['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        $user_message = $this->DAO->get_user_message($user['user_id']);
        if($user_message){
            $user_info = $this->DAO->get_user_info($user['user_id']);
        }else{
            $user_info = $this->DAO->get_user($user['user_id']);
            $user_info['integral'] = 0;
            $user_info['exp'] = 0;
        }

        $app_list = $this->DAO->get_app_list();
        $game_info = $this->DAO->get_app_info($user['app_id']);
        $index = 'sdk_log';
        if($game_info['web_serv_url']){
            $servers = $this->get_my_games($user['user_id'],$index,$game_info['app_id']);
            if(!$servers){
                $index = 'ios_log';
                $servers = $this->get_my_games($user['user_id'],$index,$game_info['app_id']);
            }
            $app_id = $game_info['app_id'];
        }else{
            $servers = $this->get_my_games($user['user_id'],$index,$game_info['app_id']);
            if(!$servers){
                $index = 'ios_log';
                $servers = $this->get_my_games($user['user_id'],$index,$game_info['app_id']);
            }
            $app_id = $app_list[0]['app_id'];
        }
        $good_list = $this->DAO->get_good_list($app_id);
        if($good_list){
            foreach($good_list as $key=>$data){
                $good_list[$key]['consume'] = $data['good_price']*$game_info['nnb_scale'];
            }
        }
        $result['result'] = 1;
        $result['desc'] = '请求成功';
        $result['appList'] = $app_list;
        $result['servers'] = $servers;
        $result['good_list'] = $good_list;
        $result['user_info'] = $user_info;
        $result['app_id'] = $app_id;
        die('0'.base64_encode(json_encode($result)));
    }

    public function server_list(){
        $result = array('result'=>0,'desc'=>'网络出错啦');
        $user = $this->get_usr_session('user');
        if(!$user['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$_POST['app_id']){
            $result['desc'] = '请选择游戏';
            die('0'.base64_encode(json_encode($result)));
        }
        $app_info = $this->DAO->get_app_info($_POST['app_id']);
        $index = 'sdk_log';
        $servers = $this->get_my_games($user['user_id'],$index,$_POST['app_id']);
        if(!$servers){
            $index = 'ios_log';
            $servers = $this->get_my_games($user['user_id'],$index,$_POST['app_id']);
        }
        $good_list = $this->DAO->get_good_list($_POST['app_id']);
        if($good_list){
            foreach($good_list as $key=>$data){
                $good_list[$key]['consume'] = $data['good_price']*$app_info['nnb_scale'];
            }
        }
        $result['result'] = 1;
        $result['servers'] = $servers;
        $result['good_list'] = $good_list;
        die('0'.base64_encode(json_encode($result)));
    }

    public function get_my_games($user_id, $index,$app_id){
        $hosts = [ ES_HOST.":".ES_PORT];
        $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $params = array();
        $params['index'] = $index;
        $params['type']  = 'stats_user_app';
        $json = '{"query":{"bool":{"must":[{"term":{"AppID":'.$app_id.'}},{"term":{"UserID":'.$user_id.'}}],"must_not":[{"match":{"AreaServerID":""}}]}},"sort":[{"ActTime":{"order":"asc"}}],
        "aggregations":{"AreaServerID":{"terms":{"field":"AreaServerID"},"aggregations":{"AreaServerName":{"terms":{"field":"AreaServerName"},
        "aggregations":{"RoleID":{"terms":{"field":"RoleID"},"aggregations":{"RoleName":{"terms":{"field":"RoleName"}}}}}}}}}}';
        $params['body'] = $json;
        $results = $client->search($params);
        $es_data = $results['aggregations']['AreaServerID'];
        $data = array();
        foreach ($es_data['buckets'] as $key => $val) {
            $data[$key]["AreaServerID"]=$val['key'];
            $data[$key]["AreaServerName"]=$val['AreaServerName']['buckets'][0]['key'];
            if(!$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['key']){
                $data[$key]["RoleID"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][1]['key'];
                $data[$key]["RoleName"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][1]['RoleName']['buckets'][0]['key'];
            }else{
                $data[$key]["RoleID"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['key'];
                $data[$key]["RoleName"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['RoleName']['buckets'][0]['key'];
            }
        }
        return $data;
    }

    public function exchange(){
        $result = array('result'=>0,'desc'=>'网络错误');
        $user = $this->get_usr_session('user');
        if(!$user['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        $params = $_POST;
        if(!$params['app_id'] || !$params['server_id'] || !$params['role_id']){
            $result['desc'] = '请选择游戏或者区服角色';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['goods_id']){
            $result['desc'] = "请选择兑换面值";
            die('0'.base64_encode(json_encode($result)));
        }
        $goods_info = $this->DAO->get_goods_info($params['goods_id']);
        $app_message = $this->DAO->get_app_message($goods_info['app_id']);
        if($app_message){
            $goods_info['nnb_scale'] = $app_message['nnb_scale'];
        }
        $goods_info['consume'] = $goods_info['good_price']*$goods_info['nnb_scale'];
        $user_info = $this->DAO->get_user_message($user['user_id']);
        if($goods_info['consume']>$user_info['integral']){
            $result['desc'] = "积分不足，无法兑换";
            die('0'.base64_encode(json_encode($result)));
        }
        $YD_order_id = "JFDH".$this->orderid($goods_info['app_id']);
        $game = $this->DAO->get_app_info($goods_info['app_id']);
        $time = strtotime("now");
        //获取厂商订单号
        $AppOrderId = $this->get_game_orderid($params,$user, $goods_info, $YD_order_id, $time, $game);
        if(!$AppOrderId){
            $result['desc'] = "获取厂商订单号错误";
            die('0'.base64_encode(json_encode($result)));
        }
        $order = $this->insert_YD_order($YD_order_id, $user,$AppOrderId, $params, $goods_info, $time);
        if($order){
            $integral = $user_info['integral']-$goods_info['consume'];
            $user_message = $this->DAO->get_user_message($user['user_id']);
            if($user_message){
                $this->DAO->update_user_integral($user['user_id'],$integral);
            }else{
                $this->DAO->insert_user_message($user['user_id'],$integral);
            }
            $params['integral'] = $goods_info['consume'];
            $params['type'] = 5;
            $params['user_id'] = $user['user_id'];
            $this->DAO->insert_integral($params,$user);
            $result['result'] = 1;
            $result['msg_title'] = "恭喜您，中奖了";
            $result['msg_title'] = "您已成功兑换".$goods_info['good_name'];
            $result['integral'] = $integral;
            $result['desc'] = "兑换成功";
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result['desc'] = "兑换失败";
            die('0'.base64_encode(json_encode($result)));
        }
    }

    //获取厂商订单号
    public function get_game_orderid($pay,$user, $money, $order_id, $time, $game){
        if(in_array($user['user_id'], $this->qa_user_id)){
            $this->is_request_debug = true;
        }
        if($user['user_id']==0)$user['user_id']='';
        $sign_str = $game['app_id']. $pay['server_id']. $pay['role_id']. $order_id. $money['good_amount'].$money['good_price']. $time. $game['app_key'];
        $sign = md5($sign_str);

        $post_string = "app_id=".$game['app_id']."&serv_id=".urlencode($pay['server_id'])."&player_id=".urlencode($pay['role_id']).
            "&order_id=".urlencode($order_id)."&coin=".urlencode($money['good_amount'])."&money=".urlencode($money['good_price']).
            "&create_time=".urlencode($time)."&sign=".urlencode($sign)."&good_code=".$money['good_code']."&usr_id=".$user['user_id'];
        $result = $this->request($game['web_order_url'].'?'.$post_string, '', array(), 10);
        $this->err_log($sign_str,'order_test');
        $this->err_log($post_string,'order_test');
        $this->err_log($result,'order_test');
        $result = json_decode($result);
        if(!$result){
            $result['desc'] = "游戏服务出错,请联络客服";
            die('0'.base64_encode(json_encode($result)));
        }

        if(!$result->err_code && $result->desc){
            return $result->desc;
        }else{
//            $_SESSION['error'] = "[订单号：".$order_id."]<br />订单号创建失败。<br /><span class=\"red\">[错误代码：10996]</span>";
            return false;
        }
    }

    //平台订单创建
    public function insert_YD_order($YD_order_id,$user, $app_order_id, $pay, $money, $time, $ch=1){
        $order['app_id']      = $money['app_id'];
        $order['order_id']       = $YD_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $ch;
        if(empty($pay['usr_id'])){
            $pay['usr_id']=57;
        }
        $order['buyer_id']    = $user['user_id'];
        $order['role_id']     = $pay['role_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['good_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $pay['role_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['status']      = 1;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $pay['server_id'];
        $order['channel']   = 'ong';
        $order['payExpandData'] = $pay['payexpanddata']?$pay['payexpanddata']:'';
        $order['pay_from'] = 2;
        $order['ch_type'] = 5;  //积分兑换
        $order['mac'] = $pay['mac']?$pay['mac']:'';
        $order['idfa'] = $pay['idfa']?$pay['idfa']:'';
        $order['idfv'] = $pay['idfv']?$pay['idfv']:'';
        $order['web_channel'] = $pay['web_channel']?$pay['web_channel']:'';
        $order['consume'] = $money['consume'];
        if(!$this->DAO->insert_order($order)){
            $result['desc'] = "订单号创建失败";
            die('0'.base64_encode(json_encode($result)));
        }
        return $order;
    }

    public function draw_log(){
        $result = array('result'=>0,'desc'=>'网络错误');
        $user = $this->get_usr_session('user');
        if(!$user['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 1;
        }
        $order_list = $this->get_integral_info($this->page);
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['page'] = $this->page;
        $result['data'] = $order_list;
        die('0'.base64_encode(json_encode($result)));
    }

    private function get_integral_info($page = 0) {
        $user = $this->get_usr_session('user');
        $items = $this->DAO->get_integral_order($user['user_id']);
        $items = array_chunk($items,10);
        return $items[$page-1];
    }

    public function cp_integral(){
        $params = $_REQUEST;
        header("Location:http://117.27.77.97:5670/play_ground.php?act=cp_integral&user_id=".$params['user_id'].'&type='.$params['type'].'&integral='.$params['integral'].'&app_id='.$params['app_id'].'&sign='.$params['sign']);
        $result = array('result'=>0,'desc'=>'网络错误');
        $user = $this->get_usr_session('user');
        $params = $_POST;
        if(!$params['user_id']){
            $result['desc'] = '请先登录游戏';
            die('0'.base64_encode(json_encode($result)));
        }
        if($params['type'] != 1 && $params['type'] != 2){
            $result['desc'] = '积分类型不正确';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['integral']){
            $result['desc'] = '积分值不能为空';
            die('0'.base64_encode(json_encode($result)));
        }elseif($params['integral'] > 50000){
            $result['desc'] = '参数异常';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['app_id']){
            $result['desc'] = '游戏ID不能为空';
            die('0'.base64_encode(json_encode($result)));
        }
        $app_info = $this->DAO->get_app_info($params['app_id']);
        $sign = md5($params['user_id'].'_'.$app_info['app_id'].'_'.$app_info['app_key']);
        if($params['sign'] != $sign){
            $result['desc'] = '验证信息出错啦';
            die('0'.base64_encode(json_encode($result)));
        }
        $user_message = $this->DAO->get_user_message($params['user_id']);
        if($params['type'] == '1'){
            $integral = $user_message['integral'] + $params['integral'];
            $params['type'] = 4;
        }elseif($params['type'] == '2'){
            if($user_message['integral'] > $params['integral']){
                $integral = $user_message['integral'] - $params['integral'];
            }else{
                $integral = 0;
            }
            $params['type'] = 3;
        }
        if($user_message){
            $this->DAO->update_user_integral($params['user_id'],$integral);
        }else{
            $this->DAO->insert_user_message($params['user_id'],$integral);
        }
        $this->DAO->insert_integral($params,$user);
        $result['result'] = 1;
        $result['desc'] = '更新成功';
        $result['integral'] = $integral;
        die('0'.base64_encode(json_encode($result)));
    }

    public function get_money_integral(){
        $user = $this->get_usr_session('user');
        $user_info = $this->DAO->get_user($user['user_id']);
        $integral_list = $this->DAO->get_money_integral();
        $count = count($integral_list);
        if($count<6){
            $money = array();
            foreach($integral_list as $key=>$data){
                array_push($money,$data['money']);
            }
            foreach($this->money as $k=>$d){
                if(!in_array($d,$money)){
                    $integral_list[$count]['money'] = $d;
                    $integral_list[$count]['integral'] = '0';
                    $count = $count +1;
                }
            }
            foreach ($integral_list as $k => $v) {
                $integral[] = $v['money'];
            }
            array_multisort($integral, SORT_ASC, $integral_list);
        }
        $result = array('result'=>1,'desc'=>'查询成功','data'=>$integral_list,'num'=>$user_info['nnb']);
        die('0'.base64_encode(json_encode($result)));
    }

}