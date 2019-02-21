<?php
COMMON('baseCore', 'pageCore');
DAO('common_dao','game_dao');
BEAN('product_bean');
class weekactivity_web extends baseCore{
    public $DAO;
    public $COMDAO;
    public function __construct(){
        parent::__construct();
        $this->COMDAO=new common_dao();
        $this->GAMEDAO=new game_dao();
        $this->DAO = new weekactivity_dao();
        $this->assign('JS_PATH','http://cdn.66173.cn/wwwv2/js/');
        $this->assign('IMAGES_PATH','http://cdn.66173.cn/images/');
        $this->assign('IMG_PATH','http://cdn.66173.cn/wwwv2/css/img/');
    }

    public function activity_view(){
        $_SESSION['login_back_url'] = 'weekactivity.php?act=index';
        $banks = $this->DAO->get_banks();
        $batch = $this->DAO->get_game_batch();
        $list = $this->DAO->get_game_list($batch['id']);
        foreach($list as $k=>$v){
            $reper = ($v['buy_number']/$v['repertory'])*100;
            $reper = 100 - $reper;
            if(empty($v['false_reper'])){
                $list[$k]['reper'] = $reper;
            }else if($reper < 20){
                $list[$k]['reper'] = $reper;
            }else{
                $list[$k]['reper'] = $v['false_reper'];
            }
            $order = $this->DAO->get_order_status($v['id'],$_SESSION['user_id']);
            if(!empty($order)){
                if($order['status'] == 1 || $order['status'] == 2){
                    $list[$k]['order_status'] = 1;
                    $list[$k]['order_id'] = $order['id'];
                }
            }
        }
        $this->page_hash();
        $this->assign("batch",$batch);
        $this->assign("data",$list);
        $this->assign("banks", $banks);
        $this->display('weekactivity.html');
    }

    public function ajax_down_list(){
        $downs = $this->DAO->get_game_ch_download($_POST['game_id']);
        $game = $this->DAO->get_game($_POST['game_id']);
        $down['game'] = $game;
        foreach($downs as $k=>$v){
            if($v['platform'] == 1){
                $down['android'][$k] = $v;
            }else{
                $down['ios'][$k] = $v;
            }
        }
        echo  json_encode($down);
    }

    protected function set_product_tag($tags){
        $atts = array();
        if(!$tags){
            $this->assign("tags",array());
            return false;
        }
        $tags = explode("\n", $tags);
        if(!is_array($tags))return false;
        foreach($tags as $k=>$v){
            if($v){
                $tag = explode("：",$v);
                $atts[$tag[0]] = explode("|",$tag[1]);
            }
        }
        return $atts;
    }

    public function ajax_buy_list(){
        $data=array(
            'msg'=>0,
            'desc'=>'网络请求出错,请刷新后重试.');
        $params = $_POST;
        if(!$_POST['id']){
            die(json_encode($data));
        }
        $order = $this->DAO->get_order_status($_POST['id'],$_SESSION['user_id']);
        if($order && $order['status']!=0){
            $data['desc']='您已购买过该商品.';
            die(json_encode($data));
        }
        $game = $this->DAO->get_game($params['game_id']);
        $product = $this->DAO->get_game_last_product($params['game_id'], 1);
        $activity = $this->DAO->get_weekactivity_tb($params['id']);
        $channels = $this->DAO->get_channels();
        $data['attr'] = $this->set_product_tag($game['tags']);
        $this->set_discount($product, $channels, 1);
        $game_servs = $this->DAO->get_game_serv($params['game_id']);
        foreach($channels as $k => $ch){
            if($ch['platform']==1){
                $android_chs[] = $ch;
            }
            if($ch['platform']==2){
                $ios_chs[] = $ch;
            }
        }
        $data['msg'] = 1;
        $data['game_servs'] = $game_servs;
        $data['product'] = $activity;
        $data['android_chs'] = $android_chs;
        $data['ios_chs'] = $ios_chs;
        die(json_encode($data));
    }

    protected function set_discount($product, &$channels, $product_type=1){
        $agent_discount = 0;
        if($_SESSION['is_agent']>0){
            $user_agent_game = $this->DAO->get_user_agent_game($_SESSION['user_id'], $product['game_id']);
            if($user_agent_game['discount_'.$product_type]>0){
                $agent_discount = $user_agent_game['discount_'.$product_type];
            }
        }
        foreach($channels as $k=>$v){
            $discount = $product['ch_'.$v['id']];
            $g_discount = $product['chd_'.$v['id']];
            $discount = ($discount!=$g_discount && $discount>0)?$discount:$g_discount;

            if($discount==0){
                unset($channels[$k]);
                continue;
            }
            $channels[$k]['discount'] = $discount;
            //代理折扣
            if($agent_discount>0){
                $channels[$k]['discount'] = $agent_discount;
            }
        }
        usort($channels, function($a, $b) {
            $al = $a['discount'];
            $bl = $b['discount'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1;
        });
    }


    public function do_pay($product_id){
        if($_POST['set_order'] == 1){
            $product = $this->DAO->get_weekactivity_tb($product_id);
            $this->set_order_session($product_id, $_POST, $product);
        }
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $this->check_usr_login();
        $bank        = $_POST['bank'];
        $pay_channel = $_POST['pay-channel'];
        $order_id = $this->orderid($_SESSION['order']['game_id']);
        $product  = $this->insert_order($product_id, $order_id, $pay_channel);
        $activity = $this->DAO->get_weekactivity_tb($product_id);
        if(empty($activity['buy_number'])){
            $num = 0;
        }else{
            $num = $activity['buy_number'] + 1;
        }
        $buy_num = $num;
        $this->DAO->updata_weekactivity_buynum($buy_num,$product_id);
        $pay = new pay_web();
        if($pay_channel == 2){
            $pay->yeepay($bank, $product);
        }elseif($pay_channel == 1){
            $pay->ali_pay($product);
        }elseif($pay_channel == 5){
            $pay->wx_pay($product);
        }
        unset($_SESSION['order']);
    }


    protected function set_order_session($id, $order, $product){
        $serv_info = $this->DAO->get_serv_info($order['serv_id']);
        $order['serv_name'] = $serv_info['serv_name'];
        $order['title'] = $product['game_name'];
        $_SESSION['order'] = $order;
        if(!isset($_SESSION['order']['game_user']) || empty($_SESSION['order']['game_user'])){
            $_SESSION['order']['game_user']='';
        }
        if(!isset($_SESSION['order']['game_pwd']) || empty($_SESSION['order']['game_pwd'])){
            $_SESSION['order']['game_pwd']='';
        }
        if(!$_SESSION['order']['num']){
            $_SESSION['order']['num'] = 1;
        }
        $this->assign('order', $order);
    }

    protected function insert_order($id, $order_id, $pay_channel,$coupon_id='',$payment=''){
        $channel_id = $_SESSION['order']['channel_id'];
        $product = $this->DAO->get_weekactivity_tb($id);
        $discount = 10;
        //客服环节加入
        $service = $this->DAO->get_service();
        if(empty($service)){
            $service['id']='114';
        }
        $bean = new productBean();
        $bean->order_id = $order_id;
        $bean->title = $product['game_name'];
        $bean->buyer_id = $_SESSION['user_id'];
        $bean->product_id = 33875;
        $bean->amount = $_SESSION['order']['num'];
        $bean->money = $product['old_price'];
        $bean->unit_price = $product['old_price'];
        $bean->pay_money = $product['new_price'];
        $bean->game_id = $product['game_id'];
        $bean->serv_id = $_SESSION['order']['serv_id'];
        $bean->game_channel = $_SESSION['order']['channel_id'];
        $bean->seller_id = 0;
        $bean->status = 0;
        $bean->buy_time = strtotime("now");
        $bean->pay_channel = $pay_channel;
        $bean->qq = $_SESSION['order']['qq'];
        $bean->tel = $_SESSION['order']['tel'];
        $bean->discount = $discount;
        $bean->discount_in = 0;
        $bean->is_rand_user = $_SESSION['order']['is_random_nickname']?1:0;
        if($bean->is_rand_user==1){
            $bean->role_name = '随机角色';
        }else{
            $bean->role_name = $_SESSION['order']['role_name'];
        }
        if(empty($bean->role_name)){
            $bean->role_name = '随机角色';
            $bean->is_rand_user==1;
        }
        $bean->role_back_name = $_SESSION['order']['role_back_name'];
        $bean->service_id = $service['id'];
        $bean->game_user = $_SESSION['order']['game_user'];
        $bean->game_pwd = $_SESSION['order']['game_pwd'];
        $bean->attr = json_encode($_SESSION['order']['attr'],JSON_UNESCAPED_UNICODE);
        $bean->is_agent = $_SESSION['is_agent']?:0;
        $bean->reduce_product = 0;
        $bean->coupon_id = 0;
        //使用优惠券
        if($coupon_id){
            $bean->coupon_id = $coupon_id;
            $bean->pay_money = $payment;
        }
        if($product['type']>3){
            $bean->reduce_product = 1;
        }
        $bean->activity_id = $id;
        unset($bean->id);
        $this->DAO->insert_order((array)$bean);
        return $bean;
    }

    public function ajax_order_info(){
        $id = $_POST['order_id'];
        $detail=$this->DAO->get_order_detail($id,$_SESSION['user_id']);
        $game_info=$this->COMDAO->get_game_info($detail['game_id']);
        $detail['game_name']=$game_info['game_name'];
        $channel_info=$this->COMDAO->get_channel_info($detail['game_channel']);
        $detail['channel_name']=$channel_info['channel_name'];
        $serv_info = $this->COMDAO->get_serv_info_by_id($detail['serv_id']);
        $detail['serv_name']=$serv_info['serv_name'];
        echo json_encode($detail);
    }


}