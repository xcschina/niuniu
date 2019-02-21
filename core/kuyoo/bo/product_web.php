<?php
COMMON('baseCore', 'pageCore');
COMMON('alipay/alipay_notify.class');
COMMON('wxPay');
DAO('product_dao','game_dao','common_dao');
VALIDATOR('product_validator');
BEAN('product_bean');
BO('pay_web');
BO('baseKuyoo');

class product_web extends baseKuyoo {

    public $DAO;
    public $GAMEDAO;
    public $COMDAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new product_dao();
        $this->GAMEDAO = new game_dao();
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包",
            8=>"苹果代充"
            );
        $this->user_id=$_SESSION['user_id'];
        $this->COMDAO=new common_dao();
        $notices=$this->COMDAO->get_mod_articles(21);
        $links=$this->COMDAO->get_friendly_links();
        $games = $this->COMDAO->get_hot_games();
        $this->assign("games", $games);
        $this->assign("notices", $notices);
        $this->assign("links", $links);
        $this->assign('IMAGES_PATH','http://static.kuyoo.com/images/');
        $this->assign('IMG_PATH','http://static.kuyoo.com/kuyoo/wwwv2/css/img/');
        $this->assign('JS_PATH','http://static.kuyoo.com/kuyoo/wwwv2/js/');
        $this->assign('CSS_PATH','http://static.kuyoo.com/kuyoo/wwwv2/css/');
    }


    // --------------------------
    // v2 <zbc>
    // --------------------------

    public function buy_view($game_id, $type = 0, $pro_id=0, $ch_id=0){
        ini_set("display_errors","off");
        $gameinfo = $this->GAMEDAO->get_game($game_id);
        if(!$gameinfo){
            die("鱼不可脱于渊，国之利器不可以示人");
        }
        $this->set_product_tag($gameinfo['tags']);
        $imgs = $this->DAO->get_game_intro_imgs($game_id);
        $downs = $this->GAMEDAO->get_game_ch_download($game_id);
        $game_ch_downs = array();
        foreach ($downs as $key => $val) {
            switch ($val['platform']) {
                case '1': $game_ch_downs['android'][] = $downs[$key]; break;
                case '2': $game_ch_downs['ios'][]     = $downs[$key]; break;
                default : break;
            }
        }
        $this->assign('game_ch_downs',$game_ch_downs);
        $article_list  =  $this->DAO->get_articles_list($game_id);
        $this->assign("article_list", $article_list);
        $gname = $gameinfo['game_name'];
        switch ($type) {
            case '1':   $wkey = $gname.'首充号'; $ttype=1; break;
            case '2': 
            case '201': $wkey = $gname.'首充号续充'; $ttype=2; break;
            case '3':   $wkey = $gname.'代充'; $ttype=3; break;
            case '4': 
            case '401': $wkey = $gname.'账号'; $ttype=4; break;
            case '5': 
            case '501': $wkey = $gname.'游戏币'; $ttype=5; break;
            case '8':   $wkey = $gname.'苹果代充'; $ttype=8; break;
            default:
                $wkey  = $gname.'首充号，';
                $wkey .= $gname.'首充号续充，';
                $wkey .= $gname.'代充，';
                $wkey .= $gname.'账号，';
                $wkey .= $gname.'游戏币，';
                $wkey .= $gname.'苹果代充'; 
            break;
        }
        $this->assign("web_key", $gname."，".$wkey."，".$gname."游戏充值，酷游手游交易平台");
        $this->assign("web_des", "酷游提供".$wkey."，玩".$gname."就上酷游，(shouyou.kuyoo.com)是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。");
        $this->page_hash();
        $this->gameinfo = $gameinfo;
        $this->assign('gameinfo',$gameinfo);
        $this->assign('imgs',$imgs);
        $this->assign("page_title", $gname);
        if($type == 0){
            $this->display("product_buy.html"); die;
        }else{
            $this->assign("page_title", $gameinfo['game_name'].$this->type[$type]);
        }
        $recommends = $this->DAO->get_recommends($game_id,$ttype);
        $this->assign('recommends',$recommends);
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        if(in_array($type, array(1,3,8))){ // 首充/代充/苹果代充
            $products = $this->DAO->get_products_by_gameid($game_id,$type);
            $this->assign('products',$products);
        }elseif($type == 2){
            $user_id = $_SESSION['user_id'];
            $characters = $this->DAO->get_characters_by_userid($user_id,$game_id);
            if($characters){
                 $products = $this->DAO->get_products_by_gameid($game_id,$type);
                $this->assign('products',$products);
            }
            $this->assign('firstpays',$characters);
        }elseif($type == 201){
            // 续充商品列表
            $products = $this->DAO->get_products_by_gameid($game_id,2);
            $this->assign('products',$products);
        }elseif($type == 5){
            $datas = $this->buy_gamecoin_list($game_id, $type);
        }elseif($type == 501){
            $this->buy_gamecoin_detail($game_id, $type, $pro_id, $ch_id);
        }elseif($type == 4){
            $this->buy_account_list($game_id);
        }elseif($type == 401){
            $this->buy_account_detail($gameinfo, $pro_id, $ch_id);
        }
        $this->assign('type',$type);
        $this->display("product_buy.html");
        die;
    }

    /**
     * 游戏币 - 商品列表
     */
    private function buy_gamecoin_list($game_id, $type){
        // 游戏币无分类列表
        $products = $this->DAO->get_coin_products_by_gameid($game_id,$type,PERPAGE,$this->page);

        // 所有可用渠道折扣
        $channels = $this->DAO->get_channels();
        $i = 0;
        foreach ($products as $key => $product) {
            $this->set_discount($product, $channels);
            foreach ($channels as $k => $channel) {
                $i++;
                $res[$i]['channel'] = $channel;
                $res[$i]['product'] = $product;
                $res[$i]['disprice'] = round($product['price']*$channel['discount']/10); 
            }
        }
        $this->assign('products',$res);

        // 记录总数非从数据库取得，在变动，目前有误。
        $par['act'] = 'buy';
        $par['ch']  = '5';
        $par['id']  = $game_id;
        $par['ch_type'] = intval($_GET['ch_type']);
        $par['ch_id']   = intval($_GET['ch_id']);
        $par['serv_id'] = intval($_GET['serv_id']);
        $par['price_order'] = $_GET['price_order'];
        $page = $this->pageshow($this->page, 'product.php?'.http_build_query($par).'&');
        $this->assign("page_show", $page->show(9));

        // 商品细分
        $games = $this->GAMEDAO->get_game($game_id);
        $units = explode("|", $games['game_units']);
        $this->assign('units',$units);
    }

    /**
     * 游戏币 - 商品详情
     */
    private function buy_gamecoin_detail($game_id, $type, $pro_id, $ch_id){
        if($pro_id){
            $product = $this->DAO->get_product_info($pro_id);
            $data['product'] = $product;
            if(!intval($product['serv_id'])){
                $servs = $this->GAMEDAO->get_game_servs($game_id);
                $this->assign('servs',$servs);
            }
            $channels = $this->DAO->get_channels();
            $data['price'] = $product['price']; 
            $this->set_discount($product, $channels);     
            foreach ($channels as $key => $val) {
                if($val['id'] == $ch_id){
                    $channel = $channels[$key];
                }
            }
            $data['channels'] = $channel;
            $data['discount'] = $channels['discount'];
            $data['disprice'] = round($product['price']*$channel['discount']/10); 
        }
        $this->assign('type',$type);
        $this->assign('info',$data);
        $this->assign('web_title',$this->gameinfo['game_name'].$data['product']['title']);
        $this->display("product_buy_gamecoin2.html");
        die;
    }

    /**
     * 账号 - 商品列表
     */
    private function buy_account_list($game_id){
        $data['products'] = $this->DAO->get_count_products($game_id,$this->page,PERPAGE);
        $data['servs'] = $this->GAMEDAO->get_game_servs($game_id);
        $data['channels'] = $this->DAO->get_channels();
        $this->assign('info',$data);
        $par['act'] = 'buy';
        $par['ch']  = '4';
        $par['id']  = $game_id;
        $par['ch_type'] = intval($_GET['ch_type']);
        $par['ch_id']   = intval($_GET['ch_id']);
        $par['serv_id'] = intval($_GET['serv_id']);
        $par['price_order'] = $_GET['price_order'];
        $page = $this->pageshow($this->page, 'product.php?'.http_build_query($par).'&');
        $this->assign("page_show", $page->show(9));
    }

    /**
     * 账号 - 商品详情
     */
    private function buy_account_detail($gameinfo, $pro_id, $ch_id){
        if($pro_id){
            $params['pro_id'] = $pro_id;
            $params['ch_id']  = $ch_id;
            $temp = $this->DAO->get_count_products($gameinfo['id'],1,1,$params);
            $data['product'] = $temp[0];
        }
        $this->assign('info',$data);
        $this->assign('web_title',$gameinfo['game_name']." 账号购买");
        $this->display("product_buy_account2.html");
        die;
    }


    /**
     * 对该指定渠道计算折扣
     * 续充 - 指定渠道
     * 代充- 不指定渠道
     */
    public function get_product_valid_discount($product_id,$channel_id=0){
        $product = $this->DAO->get_product_info($product_id);
        $channels = $this->DAO->get_channels();
        if(!$channel_id){
            $data['product'] = $product;
        }else{
            $temp[0] = $channels[intval($channel_id)-1]; 
            unset($channels);
            $channels = $temp;
        }
        $this->set_discount($product, $channels);
        $data['channels'] = $channels;

        if($channel_id){
            $data['price'] = $product['price']; 
            $data['disprice'] = round($product['price']*$channels[0]['discount']/10); 
        }
        die(json_encode($data));
    }

    /**
     * 购买页 - 首充
     * 指定游戏指定渠道的服务器列表
     */
    public function get_product_channel_servs($game_id, $ch_id){
        $servers = $this->DAO->get_game_ch_servs($game_id, $ch_id);
        die(json_encode($servers));
    }

    /**
     * 购买页 - 续充[首充号验证]
     */
    public function check_game_user($game_id,$game_user){
        if(!empty($_COOKIE['cfpt'])){
            $times = intval($_COOKIE['cfpt']);
            if($times>30){
                $bak['ret'] = 'often';
                die(json_encode($bak));
            }else{
                $times++;
            }
        }else{
            $times = 1;
        }
        setCookie('cfpt',$times,time()+60); 
        $res = $this->DAO->check_game_user($game_id,$game_user);
        if($res['id']){
            $bak['ret'] = 'right';
            $bak['firstpay'] = $res;
        }else{
            $bak['ret'] = 'wrong';
        }
        die(json_encode($bak));
    }

    /**
     * 购买页 - 游戏币
     */
    public function get_coin_products($id,$type,$game_id){
        $res = array();
        if($type == 'pt_id'){
            // 取指定游戏+指定平台 的可用渠道列表
            $res = $this->DAO->get_channels_by_gameid($game_id,5,$id);
        }elseif($type == 'ch_id'){
            // 取渠道可用的区服列表
            $servs = $this->DAO->get_game_ch_servs($game_id,$id);
            foreach ($servs as $key => $serv) {
                $res[$key]['serv_id']   = $serv['serv_id'];
                $res[$key]['serv_name'] = $serv['serv_name'];
            }
        }elseif($type == 'do'){
            $products = $this->DAO->get_coin_products(5,PERPAGE,$this->page);
            $page = $this->pageshow($this->page, '');
            $page->open_ajax('f_page_fn');
            $res['show'] = $page->show(9);
            $ch_id = intval($_POST['ch_id']);
            $channels = $this->DAO->get_channels();
            if($ch_id>0){
                $channel[0] = $channels[$ch_id-1]; 
            }else{
                $channel = $channels; 
            }
            foreach ($products as $key => $product) {
                $this->set_discount($product, $channel);
                $res['pro'][$key]['product_id'] = $product['id'];
                $res['pro'][$key]['title'] = $product['title'];
                $res['pro'][$key]['serv_name'] = $product['serv_name'];
                $res['pro'][$key]['channel_id'] = $product['channel_id'];
                $res['pro'][$key]['price'] = $product['price']; 
                $res['pro'][$key]['disprice'] = round($product['price']*$channel[0]['discount']/10); 
            }
        }
        die(json_encode($res));
    }

    /**
     * 购买页 - 账号
     */
    public function get_count_products($game_id){
        $res = array();
        $products = $this->DAO->get_count_products($game_id,$this->page,PERPAGE,$_POST);
        $res['pro'] = $products;
        $page = $this->pageshow($this->page, '');
        $page->open_ajax('f_page_fn');
        $res['show'] = $page->show(9);
        die(json_encode($res));
    }

    /**
     * 生成购买订单并核对
     */
    public function order_view($type){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $this->check_usr_login();
        $Validator = new productValidator($_POST);
        if($type == 1){
            $Validator->v2_check_firstpay();
        }elseif($type == 2){
            $Validator->v2_check_recharge();
        }elseif(in_array($type, array(8,3))){
            $Validator->v2_check_helppay();
        }elseif($type == 5){
            $Validator->v2_check_gamecoin();
        }elseif($type == 4){
            $Validator->v2_check_account();
        }
        $product_id = $_POST['product_id'];
        $product = $this->DAO->get_product_info($product_id);
        $this->set_order_session($product_id, $_POST, $product);
        $banks = $this->DAO->get_banks();
        if($type == 4){
            $discount = 10;
        }else{
            $discount = $product['ch_'.$_POST['channel_id']];
            $ch_discount = $product['chd_'.$_POST['channel_id']];
            if($discount==0){
                $discount = $ch_discount;
            }
            if($discount==0){
                $discount = 10;
            }
        }
        $this->assign("discount", $discount);
        $this->assign("info", $product);
        $this->assign("banks", $banks);
        $this->display("product_order.html");
    }

    protected function set_order_session($id, $order, $product){
        $serv_info = $this->DAO->get_serv_info($order['serv_id']);
        $order['serv_name'] = $serv_info['serv_name'];
        $order['title'] = $product['title'];
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


    /**
     * 支付
     */
    public function do_pay($product_id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $this->check_usr_login();
        $Validator = new productValidator($_POST);
        $Validator->v2_do_pay_check($product_id);
        $bank        = $_POST['bank'];
        $pay_channel = $_POST['pay-channel'];
        $order_id = $this->orderid($_SESSION['order']['game_id']);
        $product  = $this->insert_order($product_id, $order_id, $pay_channel);
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

    /**
     * 订单入库
     */
    protected function insert_order($id, $order_id, $pay_channel){
        $validator = new productValidator(array());
        $validator->v2_check_session_order();
        $channel_id = $_SESSION['order']['channel_id'];
        $product = $this->DAO->get_product_info($id);
        $price = $product['price'];
        $type  = $product['type'];
        $discount = 10;
        if($product['ch_'.$channel_id]!=0){
            $discount = $product['ch_'.$channel_id];
        }else{
            if($type>0 && $type<4){
                $discount = $product['chd_'.$channel_id];
                if($product['chd_'.$channel_id]!=0){
                    $discount = $product['chd_'.$channel_id];
                }else{
                    $discount = 10; // 0折即原价
                }
            }
        }
        if($type == 4){
            $discount = 10; // 账号交易不打折
        }
        //商人交易
        if($product['type']<4){
            if($_SESSION['is_agent']>0){
                $user_agent_game = $this->DAO->get_user_agent_game($_SESSION['user_id'], $product['game_id']);
                if($user_agent_game['discount_'.$product['type']]>0){
                    $discount = $user_agent_game['discount_'.$product['type']];
                }
            }
        }
        //客服环节加入
        $service = $this->DAO->get_service();
        if(empty($service)){
            $service['id']='114';
        }
        $bean = new productBean();
        $bean->order_id = $order_id;
        $bean->title = $product['title'];
        $bean->buyer_id = $this->user_id;
        $bean->product_id = $id;
        $bean->amount = $_SESSION['order']['num'];
        $bean->money = $price;
        $bean->unit_price = $price;
        $bean->pay_money = round(($price*$discount)/10)*$bean->amount;
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
        $bean->role_back_name = $_SESSION['order']['role_back_name'];
        $bean->service_id = $service['id'];
        $bean->game_user = $_SESSION['order']['game_user'];
        $bean->game_pwd = $_SESSION['order']['game_pwd'];
        $bean->attr = json_encode($_SESSION['order']['attr'],JSON_UNESCAPED_UNICODE);
        $bean->is_agent = $_SESSION['is_agent']?:0;
        $bean->reduce_product = 0;
        if($product['type']>3){
            $bean->reduce_product = 1;
        }
        unset($bean->id);
        $this->DAO->insert_order((array)$bean);
        return $bean;
    }

    public function ali_pay_return(){
        $alipay_config['partner']       = ALI_partner;
        $alipay_config['key']           = ALI_key;
        $alipay_config['sign_type']     = ALI_sign_type;
        $alipay_config['input_charset'] = ALI_input_charset;
        $alipay_config['cacert']        = ALI_cacert;
        $alipay_config['transport']     = ALI_transport;
        $alipay = new AlipayNotify($alipay_config);
        $verify_result = $alipay->verifyReturn();
        if($_GET['trade_status'] == 'TRADE_SUCCESS'){
            $result = 'success';
        }else{
            $result = 'fail';
        }
        if ($verify_result) {
            $order_id = $_GET ['out_trade_no']; // 订单号
            $url = 'product.php?act=pay_result&id=1&r='.$result.'&o='.$order_id;
        }else{
            $url = 'product.php?act=pay_result&id=1&r='.$result;
        }
        $this->redirect($url);
    }

    public function ali_pay_return_view($order_id='',$result='fail'){
        if($result == 'success'){
            $order_info = $this->DAO->get_order_info($order_id);
            if(!$order_info){ 
                $result = 'no_order';
            }else{
                $order_relation = $this->DAO->get_order_relation_info($order_info['product_id']);
                $this->assign("order_relation", $order_relation);
                $this->change_user_group($order_info['buyer_id'], 11);
            }
            $this->assign("info", $order_info);
        }
        $this->assign('result',$result);
        $this->display("pay_result.html");
    }

    public function wx_pay_return(){
        $pay = new wxPay();
        $res = $pay->pay_return();
        if($res['status']){
            $order_status = $this->DAO->get_order_status($res['msg']['order_id']);
            if($order_status['status']!=0){
                $pay->pay_return_bak($res);
            }
            $this->DAO->update_order_after_wx_pay($res['msg']);
            $this->change_user_group($order_status['buyer_id'], 11);
            if($this->client_ip() == '127.0.0.1'){
                $email = '278917472@qq.com;';
            }else{
                $email = '2874759177@qq.com;278917472@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com;';
            }
            $this->send_mail($email,'单号'.$res['msg']['order_id']."付款",'网站有人付款，单号'.$res['msg']['order_id']."，微信支付");
        }
        $pay->pay_return_bak($res);
    }

    public function wx_order_query($order_id=''){
        if($this->user_id != 71){
            die('您无权限!');
        }
        if($order_id){
            $pay = new wxPay();
            $res = $pay->query(array('order_id'=>$order_id));
            die($res['msg']);
        }else{
            die('输入订单号为空或无效');
        }
    }

    public function change_user_group($user_id, $user_group=11){
        $user_group = $this->DAO->get_user_group($user_id);
        if($user_group == 10){
            $this->DAO->set_user_group($order_info['buyer_id'],$user_group);
        }
        return true;
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
        $this->assign("tags", $atts);
    }

    protected function set_discount($product, &$channels){
        foreach($channels as $k=>$v){
            $discount = $product['ch_'.$v['id']];
            $g_discount = $product['chd_'.$v['id']];
            $discount = ($discount!=$g_discount && $discount>0)?$discount:$g_discount;

            if($discount==0){
                unset($channels[$k]);
                continue;
            }
            $channels[$k]['discount'] = $discount;
        }
        usort($channels, function($a, $b) {
            $al = $a['discount'];
            $bl = $b['discount'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1;
        });
    }

}