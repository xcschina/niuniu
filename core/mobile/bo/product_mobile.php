<?php
COMMON('baseCore', 'pageCore','alipay','alipay/alipay_submit.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
COMMON('yeepay/yeepayCommon');
DAO('product_dao');
VALIDATOR('product_validator');
BEAN('product_bean');

class product_mobile extends baseCore {

    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new product_dao();
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包"
        );
        $this->user_id=$_SESSION['user_id'];
        if(!$_SESSION['user_id']){
            $this->assign("is_login",'nologin');
        }
    }

    //商品信息
    public function product_info($product_id){
        $_SESSION['login_back_url'] = 'shop'.$product_id;
        $product = $this->DAO->get_product_info($product_id);
        if(!$product){
            die("商品已下架");
        }
        if($product['type']==1){
            $this->set_product_tag($product['tags']);
        }elseif($product['type']==2){
            $game_user = $this->DAO->get_game_user($product['game_id'], $this->user_id);
            $this->V->assign("game_users", $game_user);
        }
        $channels = $this->DAO->get_channels();
        $this->set_discount($product, $channels);

        $servs = $this->DAO->get_game_servs($product['game_id']);
        //$services = $this->DAO->get_services();
        $product['type_name'] = $this->type[$product['type']];
        $this->page_hash();

        $this->assign("info", $product);
        $this->assign("servs", $servs);
        //$this->assign("services", $services);
        $this->assign("channels", $channels);
        $this->display("product.html");
        $_SESSION['pay_error']='';
    }

    //下单
    public function product_order($product_id){
        $_SESSION['login_back_url'] = 'shop'.$product_id;
        $this->check_usr_login();
        $Validator = new productValidator($_POST);
        if($_POST['do'] == 'character'){
            $Validator->check_character($product_id);
        }
        if($_POST['do'] == 'recharge'){
            $Validator->check_recharge($product_id, $this->DAO);
        }
        if($_POST['do']=='topup'){
            $Validator->check_topup($product_id);
        }
        if($_POST['do']=='iap'){
            $Validator->check_iap($product_id);
        }
        $product = $this->DAO->get_product_info($product_id);
        $this->set_order_session($product_id, $_POST, $product);
        if (!empty($_POST['user_id'])) {
            $this->get_coupon_info($_POST,$product_id);
        }
        $this->assign("info", $product);
        $this->display("product-order.html");
    }

    private function get_coupon_info($order,$product_id){
        //获取优惠券的规则 根据游戏,渠道,首充号等等
        $user_all_coupon = $this->DAO->get_user_all_coupon($order['stprice'], $order['user_id']);
        $user_coupon = array();
        foreach ($user_all_coupon as $key => $coupon) {
            switch ($coupon['apply_type']) {
                case '1':
                    if($coupon['discount_type'] == 2){
                        if($order['price'] >= $coupon['total_amount']){
                            $coupon_amoun = $this->get_coupon_amount($coupon['type'], $order['price'], $coupon);
                            array_push($user_coupon, $coupon_amoun);
                        }
                    }else{
                        $coupon_amoun = $this->get_coupon_amount($coupon['type'], $order['stprice'], $coupon);
                        array_push($user_coupon, $coupon_amoun);
                    }
                    break;
                case '2':
                case '3':
                    $coupon = $this->coupon_verify($coupon, $order['buy_type'], $product_id, $coupon['apply_type']);
                    if (!empty($coupon)) {
                        if($coupon['discount_type'] == 2){
                            if($order['price'] >= $coupon['total_amount']){
                                $coupon_amoun = $this->get_coupon_amount($coupon['type'], $order['price'], $coupon);
                                array_push($user_coupon, $coupon_amoun);
                            }
                        }else{
                            $coupon_amoun = $this->get_coupon_amount($coupon['type'], $order['stprice'], $coupon);
                            array_push($user_coupon, $coupon_amoun);
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $this->assign("user_coupon", $user_coupon);
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

    public function order_pay($id){
        if($this->client_ip()=='220.160.77.201'){
            $this->open_debug();
        }
        $this->check_usr_login();
        if(!$_POST['pay-channel'] || !is_numeric($_POST['pay-channel'])){
            $this->redirect("item".$id);
            exit;
        }
        if(!isset($_SESSION['order']) || empty($_SESSION['order'])){
            $this->redirect("item".$id);
            exit;
        }
        $order_id = $this->orderid($_SESSION['order']['game_id']);
        //优惠券
        if(!empty($_POST['pay_coupon_id'])){
            $coupon_id = $_POST['pay_coupon_id'];
            $payment = $_POST['payment_amount'];
        }
        $product = $this->insert_order($id,$order_id,$_POST['pay-channel'],$coupon_id,$payment);
        //$this->ali_pay();
        if($_POST['pay-channel']==2){
            $this->yeepay($product);
        }elseif($_POST['pay-channel']==1){
            $this->ali_pay($product);
        }
        unset($_SESSION['order']);
    }

    public function yeepay($order){
        header("Content-Type: text/html; charset=GBK");
        $yeepay = new yeepay();

        $p2_Order= $order->order_id;
        $p3_Amt	= $order->pay_money;
        $p4_Cur	= "CNY";
        $p5_Pid	= $_SESSION['order']['title'];
        $p6_Pcat= "手机游戏";
        $p7_Pdesc= 'dNF';
        $p8_Url	= "http://charge.66173.cn/yeepay.php";
        $pa_MP	= $order->order_id;
        #支付通道编码
        $pd_FrpId= "";
        $pr_NeedResponse= 1;

        $form['p2_Order'] = $order->order_id;
        $form['p3_Amt'] = $p3_Amt;
        $form['p4_Cur'] = $p4_Cur;
        $form['p5_Pid'] = $p5_Pid;
        $form['p6_Pcat'] = $p6_Pcat;
        $form['p7_Pdesc'] = $p7_Pdesc;
        $form['p8_Url'] = $p8_Url;
        $form['pa_MP'] = $pa_MP;
        $form['pd_FrpId'] = $pd_FrpId;
        $form['pr_NeedResponse'] = 1;

        #调用签名函数生成签名串
        $hmac = $yeepay->getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
        $form['hmac'] = $hmac;
        $yeepay->redirect_yeepay($form);
    }

    protected function ali_pay($product){
        if($this->user_id==57 || $this->user_id==71){
            $product->pay_money = 0.01;
        }
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $_SESSION['order']['title'] .
                ']</subject><out_trade_no>' . $product->order_id .
                '</out_trade_no><total_fee>' . $product->pay_money .
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>" . ALI_MOBILE_notify_url .
                "</notify_url><out_user></out_user><merchant_url>" . ALI_MOBILE_merchant_url.
                "</merchant_url>" . "<call_back_url>" . ALI_MOBILE_call_back_url.
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $product->order_id,
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );

        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => ALI_MOBILE_partner,
            "call_back_url" => ALI_MOBILE_call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
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

    protected function set_order_session($id, $order, $product){
        if($order['serv_id']==1){
            $order['other_ser'] = $order['serv_name'] = $order['other_ser'];
        }else{
            if ($order['buy_type'] == 8) {
                $serv_info = $this->DAO->get_iap_serv_info($order['serv_id']);
            } else {
                $serv_info = $this->DAO->get_serv_info($order['serv_id']);
            }
            $order['serv_name'] = $serv_info['serv_name'];
        }
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

    protected function insert_order($id, $order_id, $pay_channel,$coupon_id='',$payment=''){
        if($this->client_ip()=='27.155.145.206'){
            $this->open_debug();
        }
        $validator = new productValidator(array());
        $validator->check_session_order();
        $channel_id = $_SESSION['order']['channel_id'];
        $product = $this->DAO->get_product_info($id);

        $price = $product['price'];
        $type = $product['type'];
        $discount = 10;
        if($product['type']<4){
            if($product['ch_'.$channel_id]!=0){
                $discount = $product['ch_'.$channel_id];
            }else{
                if($type>0 && $type<4){
                    $discount = $product['chd_'.$channel_id];
                }
            }
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
        $bean->pay_money = round(($price*$discount)/10) * $_SESSION['order']['num'];
        $bean->game_id = $product['game_id'];
        $bean->serv_id = $_SESSION['order']['serv_id'];
        $bean->game_channel = $_SESSION['order']['channel_id'];
        $bean->seller_id = $product['user_id'];
        $bean->status = 0;
        $bean->buy_time = strtotime("now");
        $bean->pay_channel = $pay_channel;
        $bean->qq = $_SESSION['order']['qq'];
        $bean->tel = $_SESSION['order']['tel'];
        $bean->discount = $discount;
        $bean->discount_in = 0;
        $bean->role_name = $_SESSION['order']['role_name'];
        $bean->role_back_name = $_SESSION['order']['role_back_name'];
        $bean->service_id = $service['id'];
        $bean->game_user = $_SESSION['order']['game_user'];
        $bean->game_pwd = $_SESSION['order']['game_pwd'];
        $bean->platform=1;
        $bean->is_rand_user = $_SESSION['order']['is_rand_user'];
        $bean->attr = json_encode($_SESSION['order']['attr'],JSON_UNESCAPED_UNICODE);
        $bean->is_agent = $_SESSION['order']['is_agent'];
        $bean->role_level = $_SESSION['order']['role_level'];
        $bean->reduce_product = 0;
        $bean->coupon_id = 0;
        $bean->other_ser = $_SESSION['order']['other_ser'];
        //使用优惠券
        if($coupon_id){
            $bean->coupon_id = $coupon_id;
            $bean->pay_money = $payment;
        }
        if($product['type']>3){
            $bean->reduce_product = 1;
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(strpos($user_agent, 'MicroMessenger') === false){
            // 非微信浏览器禁止浏览
            $bean->platform=1;
        }else{
            // 微信浏览器，允许访问
            $bean->platform=4;
        }

        unset($bean->id);
        $this->err_log(var_export($bean,1),'orderm');
        $this->err_log(var_export((array)$bean,1),'orderm');
        $this->DAO->insert_order((array)$bean);
        return $bean;
    }

    public function ali_pay_return(){
         //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);

        if ($verify_result) {
            $order_id       = $_GET ['out_trade_no']; // 订单号
            $order_result   = $_GET ['result']; // 订单状态，是否成功
            $ali_order_id   = $_GET ['trade_no']; // 交易号
            $order_info = $this->DAO->get_order_info_by_order_id($order_id);
            $this->assign("info", $order_info);
            if(!$order_info){
                die("没有查到该订单");
            }

            $this->display("pay_result.html");
        } else {
            $this->display("pay_result.html");
        }
    }

    public function ali_qb_return(){
        //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);

        if ($verify_result) {
            $order_id       = $_GET ['out_trade_no']; // 订单号
            $order_result   = $_GET ['result']; // 订单状态，是否成功
            $ali_order_id   = $_GET ['trade_no']; // 交易号
            $order_info = $this->DAO->get_qb_order_info_by_order_id($order_id);
            $this->assign("info", $order_info);
            if(!$order_info){
                die("没有查到该订单");
            }
            $this->display("qb_pay_result.html");
        } else {
            $this->display("qb_pay_result.html");
        }
    }
}