<?php
COMMON('baseCore', 'pageCore');
// COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
COMMON('alipay/alipay_notify.class');
COMMON('wxPay');
DAO('product_dao','game_dao','common_dao');
VALIDATOR('product_validator');
BEAN('product_bean');
BO('pay_web');

class product_web extends baseCore {

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
        $notices=$this->COMDAO->get_mod_articles(14);
        $links=$this->COMDAO->get_friendly_links();
        $games = $this->COMDAO->get_hot_games();
        $this->assign("games", $games);
        $this->assign("notices", $notices);
        $this->assign("links", $links);
        $this->assign('IMAGES_PATH','http://cdn.66173.cn/images/');
        $this->assign('IMG_PATH','http://cdn.66173.cn/wwwv2/css/img/');
        $this->assign('JS_PATH','http://cdn.66173.cn/wwwv2/js/');
        $this->assign('CSS_PATH','http://cdn.66173.cn/wwwv2/css/');
    }


    // --------------------------
    // v2 <zbc>
    // --------------------------

    public function buy_view($game_id, $type = 0, $pro_id=0, $ch_id=0){
        $game_info = $this->DAO->get_game_tb($game_id);
        if($game_info['apply'] == 1){
            header("Location:http://m.66173.cn/moyu");
        }
        if($this->is_mobile_client()){
            switch ($type) {
                case 1:
                    header("Location:http://m.66173.cn/game".$game_id."/character");
                    break;
                case 2:
                    header("Location:http://m.66173.cn/game".$game_id."/renew");
                    break;
                case 3:
                    header("Location:http://m.66173.cn/game".$game_id."/topup");
                    break;
                case 4:
                    header("Location:http://m.66173.cn/game".$game_id."/account");
                    break;
                case 5:
                    header("Location:http://m.66173.cn/game".$game_id."/coin");
                    break;
                default:
                    header("Location:http://m.66173.cn/game".$game_id);
                    break;
            }
        }
        ini_set("display_errors","off");
        $gameinfo = $this->GAMEDAO->get_game($game_id);
        if(!$gameinfo){
            die("鱼不可脱于渊，国之利器不可以示人");
        }
        $this->set_product_tag($gameinfo['tags']);
        $imgs = $this->DAO->get_game_intro_imgs($game_id);
        $downs = $this->GAMEDAO->get_game_ch_download($game_id);
        $article_list  =  $this->DAO->get_articles_list($game_id);
        $game_ch_downs = array();
        foreach ($downs as $key => $val) {
            switch ($val['platform']) {
                case '1': $game_ch_downs['android'][] = $downs[$key]; break;
                case '2': $game_ch_downs['ios'][]     = $downs[$key]; break;
                default : break;
            }
        }
        $gname = $gameinfo['game_name'];
        switch ($type) {
            case '1':   $wkey = $gname.'首充号'; break;
            case '2':
            case '201': $wkey = $gname.'首充号续充'; break;
            case '3':   $wkey = $gname.'代充'; break;
            case '4':
            case '401': $wkey = $gname.'账号';  break;
            case '5':
            case '501': $wkey = $gname.'游戏币'; break;
            case '8':   $wkey = $gname.'苹果代充'; break;
            default:
                $wkey  = $gname.'首充号，';
                $wkey .= $gname.'首充号续充，';
                $wkey .= $gname.'代充，';
                $wkey .= $gname.'账号，';
                $wkey .= $gname.'游戏币，';
                $wkey .= $gname.'苹果代充';
                break;
        }
        $this->assign("web_key", $gname."折扣充值，".$gname."充值平台，".$gname."首充号");
        $this->assign("web_des", "66173手游交易平台是".$gname."充值平台，特价优惠的".$gname."首充号最低4折起，购买首充号请到66173官方网站。");
        $this->page_hash();
        $this->gameinfo = $gameinfo;
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign('gameinfo',$gameinfo);
        $this->assign('imgs',$imgs);
        $this->assign("article_list", $article_list);
        $this->assign('game_ch_downs',$game_ch_downs);
        $this->assign("page_title", $gname);

        if($type == 0){
            $this->display("product_buy.html"); die;
        }else{
            $this->assign("page_title", $gameinfo['game_name'].$this->type[$type]);
        }
        $recommends = $this->DAO->get_recommends($game_id,$type);
        $this->assign('recommends',$recommends);
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        if($type == 1){
            $products = $this->DAO->get_products_by_gameid($game_id,$type);
            $product = $this->DAO->get_game_last_product($game_id, 1);
            $this->page_hash();
            $product = $this->DAO->get_product_info($product['id']);
            $channels = $this->DAO->get_channels();
            $this->set_discount($product, $channels);
            foreach($channels as $k => $ch){
                if($ch['platform']==1){
                    $android_chs[] = $ch;
                }
                if($ch['platform']==2){
                    $ios_chs[] = $ch;
                }
            }
            $articles_info = $this->DAO->get_articles_info($game_id,13);
            $game_introduce = $this->DAO->get_game_introduce_byid($game_id);
            $r_num = rand(1, 4);
            $pic = $gameinfo['img' . $r_num];
            $this->game_type();
            $this->assign("pic", $pic);
            $this->assign("page_title", $gname."折扣充值_".$gname."充值平台_".$gname."首充号4折起");
            $this->assign("articles_info", $articles_info);
            $this->assign("game_introduce", $game_introduce);
            $this->assign("android_chs", $android_chs);
            $this->assign("ios_chs", $ios_chs);
            $this->assign('product',$product);
            $this->assign('products',$products);
            $this->assign('type',$type);
            $this->display("product_character.html");
            die();
        }elseif(in_array($type, array(1,3,8))){ // 首充/代充/苹果代充
            $products = $this->DAO->get_products_by_gameid($game_id,$type);
            $this->assign('products',$products);
        }elseif($type == 2){
            $user_id = $_SESSION['user_id'];
            $characters = $this->DAO->get_characters_by_userid($user_id,$game_id);
            if($characters){
                $products = $this->DAO->get_products_by_gameid($game_id,$type);
                $this->assign('products',$products); // 续充商品列表
            }
            $this->assign('firstpays',$characters);
        }elseif($type == 201){
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

    public function game_type(){
        $game_type = array(
            1 => '休闲',
            2 => '模拟',
            3 => '竞技',
            4 => '动作',
            5 => '卡牌',
            6 => '角色',
            7 => '策略',
            8 => '射击',
            9 => '体育',
            10 => '回合',
            11 => '格斗',
            12 => '即时');
        $this->assign("game_type", $game_type);
    }

    /**
     * 游戏币 - 商品列表
     */
    private function buy_gamecoin_list($game_id, $type){
        // 游戏币无分类列表
        $products = $this->DAO->get_coin_products_by_gameid($game_id,$type,PERPAGE,$this->page);

        // 可用渠道折扣
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
            // 渠道折扣
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
        if ($data['products']) {
            foreach ($data['products'] as $key => $val) {
                $imgs = $this->DAO->get_product_imgs($val['id']);
                if ($imgs) {
                    $data['products'][$key]['img'] = $imgs['img_url'];
                }
            }
        }
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
     * 针对该指定渠道计算折扣
     * 续充 - 指定渠道
     * 代充 - 不指定渠道
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
            // 取指定游戏指定平台的可用渠道列表
            $res = $this->DAO->get_channels_by_gameid($game_id,5,$id);
        }elseif($type == 'ch_id'){
            // 取渠道可用的区服列表
            $servs = $this->DAO->get_game_ch_servs($game_id,$id);
            foreach ($servs as $key => $serv) {
                $res[$key]['serv_id']   = $serv['serv_id'];
                $res[$key]['serv_name'] = $serv['serv_name'];
            }
        }elseif($type == 'do'){
            // 游戏币商品列表
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
        if ($products) {
            foreach ($products as $key => $val) {
                $imgs = $this->DAO->get_product_imgs($val['id']);
                if ($imgs) {
                    $products[$key]['img'] = $imgs['img_url'];
                }
            }
        }
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

        if($product['stock']<1){
            die("库存没有拉!");
        }
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
        if (!empty($_SESSION['user_id'])) {
            $price = round($_SESSION['order']['price'] * $discount / 10);
            //获取优惠券的规则 根据游戏,渠道,首充号等等
            $user_all_coupon = $this->DAO->get_user_all_coupon($price, $_SESSION['user_id']);
            $user_coupon = array();
            foreach ($user_all_coupon as $key => $coupon) {
                switch ($coupon['apply_type']) {
                    case '1':
                        if($coupon['discount_type']== 2){
                            if($price >= $coupon['total_amount']){
                                $coupon_amoun = $this->get_coupon_amount($coupon['type'], $price, $coupon);
                                array_push($user_coupon, $coupon_amoun);
                            }
                        }else{
                            $coupon_amoun = $this->get_coupon_amount($coupon['type'], $_SESSION['order']['price'], $coupon);
                        }
                        array_push($user_coupon, $coupon_amoun);
                        break;
                    case '2':
                    case '3':
                        $coupon = $this->coupon_verify($coupon, $type, $product_id, $coupon['apply_type']);
                        if (!empty($coupon)) {
                            if($coupon['discount_type']== 2){
                                if($price >= $coupon['total_amount']){
                                    $coupon_amoun = $this->get_coupon_amount($coupon['type'], $price, $coupon);
                                    array_push($user_coupon, $coupon_amoun);
                                }
                            }else{
                                $coupon_amoun = $this->get_coupon_amount($coupon['type'], $_SESSION['order']['price'], $coupon);
                            }
                            array_push($user_coupon, $coupon_amoun);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        $this->assign("user_coupon", $user_coupon);

        $this->assign("ip", $this->client_ip());
        $this->assign("discount", $discount);
        $this->assign("info", $product);
        $this->assign("banks", $banks);
        $this->display("product_order.html");
    }
    /*计算优惠券抵扣额度和支付额度*/
    protected function get_coupon_amount($type,$price,$data){
        if($type == '2') {
            $payment = $price - $data['discount_amount'];
            $discount_amount = $data['discount_amount'];
        }elseif($type == '1') {
            $payment = sprintf("%.1f", $price * $data['discount'] / 10);
            $discount_amount = $price - $payment;
        }else{
            return $data;
        }
        $data['payment'] = $payment;
        $data['discount_amount'] = $discount_amount;

//        if ($discount_amount > 0) {
//            $data['discount_amount'] = $discount_amount;
//        } else {
//            $data['discount_amount'] = 0.01;
//        }
        return $data;
    }

    /*优惠券验证*/
    protected function coupon_verify($coupon, $type, $product_id, $apply_type){
        if (!empty($coupon['channel_id']) && $coupon['channel_id'] != 'null' && !in_array($_POST['channel_id'], json_decode($coupon['channel_id']))) {
            return "";
        }
        if (!empty($coupon['game_id']) && $coupon['game_id'] != $_POST['game_id'] && $apply_type == '2') {
            return "";
        }
        if (!empty($coupon['game_id']) && $coupon['game_id'] != 'null' && !in_array($_POST['game_id'], json_decode($coupon['game_id'])) && $apply_type == '3') {
            return "";
        }
        if (!empty($coupon['pay_type']) && $coupon['pay_type'] != $type) {
            return "";
        }
        if (!empty($coupon['goods_id']) && $coupon['goods_id'] != $product_id && $apply_type == '2') {
            return "";
        }

        return $coupon;
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
        //优惠券
        if(!empty($_POST['pay_coupon_id'])){
            $coupon_id = $_POST['pay_coupon_id'];
            $payment = $_POST['payment_amount'];
        }
        $product  = $this->insert_order($product_id, $order_id, $pay_channel,$coupon_id,$payment);
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
    protected function insert_order($id, $order_id, $pay_channel,$coupon_id='',$payment=''){
        $validator = new productValidator(array());
        $validator->v2_check_session_order();
        $channel_id = $_SESSION['order']['channel_id'];
        $product = $this->DAO->get_product_info($id);
        $price = $product['price'];
        $type  = $product['type'];
        $discount = 10;
        if($product['stock']<1){
            die("库存没有拉!");
        }
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
        $bean->coupon_id = 0;
        //使用优惠券
        if($coupon_id){
            $bean->coupon_id = $coupon_id;
            $bean->pay_money = $payment;
        }
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
            $order_id = $_GET ['out_trade_no'];
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
                if($order_info['product_id'] != 33875){
                    $order_relation = $this->DAO->get_order_relation_info($order_info['product_id']);
                }else{
                    $order_relation = $this->DAO->get_weekactivity_tb($order_info['activity_id']);
                    $game = $this->DAO->get_game_tb($order_relation['game_id']);
                    $order_relation['title'] = $order_relation['game_name'];
                    $order_relation['game_name'] = $game['game_name'];
                }
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
            if(in_array($order_status['status'],array(1,2))){
                $pay->pay_return_bak($res); // 已付款|已完成
            }
            $this->DAO->update_order_after_wx_pay($res['msg']);
            $this->change_user_group($order_status['buyer_id'], 11);
            if($this->client_ip() == '127.0.0.1'){
                $this->send_mail('278917472@qq.com;','单号'.$res['msg']['order_id']."付款",'网站有人付款，单号'.$res['msg']['order_id']."，微信支付");
            }else{
                $this->send_mail('2874759177@qq.com;278917472@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com;','单号'.$res['msg']['order_id']."付款",'网站有人付款，单号'.$res['msg']['order_id']."，微信支付");
            }
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
            $this->DAO->set_user_group($user_id,$user_group);
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