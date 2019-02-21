<?php
// -------------------------------------------
// 店铺系统 - 订单 <zbc> <2016-04-26>
// -------------------------------------------
COMMON('baseCore','alipay','alipay/alipay_submit.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
// COMMON('yeepay/yeepayCommon');
BO('m'.DS.'common_shop');
BO('m'.DS.'pay_shop');
DAO('m'.DS.'order_shop_dao');
VALIDATOR('product_form_validator');
BEAN('product_bean');

class order_shop extends common_shop {

    protected $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new order_shop_dao();
    }

    /**
     * 购买订单提交
     * 表单校验+创建SESSION订单+显示确认页面
     */
    public function shop_order_review_do($params=array()){
        $this->check_usr_login(); 
        // 刷新页面 或者 同内容订单重复提交 则使用session缓存订单，-> login_back_url -> 店铺列表页 
        $hash_id = md5(http_build_query($params));
        if(empty($params)){
            // 刷新页面
            if(isset($_SESSION['shop_order'])){
                $this->display(self::TPL.'/shop_order_review.html');
                die;
            }elseif(isset($_SESSION['login_back_url'])){
                $this->redirect($_SESSION['login_back_url']);
            }else{
                $this->redirect(); // 店铺列表页 
            }
        }elseif($hash_id == $_SESSION['shop_order']['hash_id']){
            // 同内容表单再次提交，直接取session订单
            $this->display(self::TPL.'/shop_order_review.html');
            die;
        }else{
            unset($_SESSION['shop_order']);
            $params['hash_id'] = $hash_id;
        }

        // 验证购买表单信息
        $validator = new productFormValidator($params);
        $validator->shop_order_form_validator();

        // 合法的表单信息重构成订单信息
        $params =  $this->shop_order_final_audit($params);

        // 订单写入session.shop_order
        $this->shop_order_insert_session($params);

        // 显示订单确认页
        $this->display(self::TPL.'/shop_order_review.html');
    }


    /**
     * 购买订单确认
     * session订单入库
     */
    public function shop_order_create_do($params=array()){
        if($_SESSION['shop_order']['hash_id'] == $params['hash_id']){
            // session订单存在 + 入库 + 入库成功后 删除session订单，防止多次入库
            $this->shop_order_insert_db($params['hash_id']);
        }else{
            // session订单不存在，非法访问，则返回购买页
            $this->redirect_error($_SESSION['login_back_url'],'订单已失效，请重新下单！');
        }
    }


    /**
     * 订单支付页面
     */
    public function shop_order_pay_view($params=array()){
        // 订单信息
        $order_info = $this->DAO->shop_order_info($params, 1);
        if(!$order_info['id']){
            $this->redirect_error('','订单不存在');
        }
        $order_is_del = $order_info['is_del'];
        if($order_is_del == '0'){
            $order_status = $order_info['status'];
            if($order_status == '0'){
                $this->assign('order',$order_info);
                $this->display(self::TPL.'/shop_order_pay.html');
            }elseif($order_status == '1' || $order_status == '2'){
                $this->redirect_error('','下手晚啦，订单已付款~');
            }elseif($order_status == '9'){
                $this->redirect_error('','很遗憾，订单已取消~');
            }else{
                $this->redirect_error('','订单有点诡异哦~~');
            }
        }elseif($order_is_del == '1'){
            $this->redirect_error('','订单已删除啦~~');
        }elseif($order_is_del == '2'){
            $this->redirect_error('','订单已退款啦~~');
        }
    }

    public function shop_order_pay_do($params=array()){
        $order_info = $this->DAO->shop_order_info($params);
        if(!$order_info['id']){
            $this->redirect_error('','订单不存在');
        }

        // 强制要求只有来自支付页面的支付按钮点击才有效[表单请求有效，刷新无效]
        // 防止dopay链接的刷新请求，只认支付页面表单提交。
        if(!intval($params['pay_channel'])){
            $url = 'http://'.SITEURL.'/'.$_SESSION['pay_back_url'];
            $this->redirect_error($url,'支付渠道有误，请重新支付！');
        }

        $pay_order = $order_info; 
        $pay = new pay_shop($pay_order);
        switch ((int)$params['pay_channel']) {
            case 1:
                // 支付宝
                $pay->ali_pay();
                break;
            case 2:
                // 易宝
                // $pay->yee_pay();
                break;
            case 3:
                // 手机充值卡
                break;
            case 4:
                // 银联
                break;
            case 5:
                // 微信
                die('正在跳转微信支付接口');
                break;
            default:
                // 付款渠道错误
                $url = 'http://'.SITEURL.'/'.$_SESSION['pay_back_url'];
                $this->redirect_error($url,'未知支付渠道！');
                break;
        }
    
    }


    // 订单写入session.order
    protected function shop_order_insert_session($params=array()){
        // 重置订单信息，然后ru session，然后订单提交直接使用session信息即可。
        $order['order_id']       = $params['order_id'];
        $order['title']          = $params['product_name'];
        $order['buyer_id']       = $_SESSION['user_id'];
        $order['product_id']     = $params['id'];
        $order['product_type']   = $params['product_type'];

        $order['unit_price']     = $params['money']; // 单价
        $order['amount']         = $params['num'];   // 数量
        $order['money']          = $params['money']*$params['num']; // 总价
        $order['pay_money']      = round($params['money']*$params['discount']/10)*$params['num']; // 应付总价
        
        $order['game_id']        = $params['game_id'];
        $order['serv_id']        = $params['serv_id'];
        $order['game_channel']   = $params['channel_id'];
        $order['seller_id']      = $params['seller_id']; // 商品发布者

        $order['qq']             = $params['qq'];
        $order['tel']            = $params['tel'];
        $order['discount']       = $params['discount']; // 最终生效折扣

        $order['role_name']      = $params['role_name'];
        $order['role_back_name'] = $params['role_back_name'];
        $order['role_level']     = $params['role_level'];

        $order['is_rand_user']   = $params['is_rand_user'];
        $order['game_user']      = $params['game_user'];
        $order['game_pwd']       = $params['game_pwd'];
        $order['attr']           = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);

        // $order['reduce_product'] = 0; // m当前首充和续冲，为1，则库存降低数量
        $order['from_client']    = 3; // H5-店铺订单 [2为PC-店铺订单]
        $order['is_agent']       = $params['is_agent']; // 是否代理购买
        $order['shop_id']        = $params['shop_id'];
        $order['remarks']        = $params['remarks'];

        $order['hash_id']        = $params['hash_id'];
        $order['pagehash']       = $params['pagehash'];

        // 非订单必须数据, review页面使用
        $order['serv_name']       = $params['serv_name'];
        $order['game_name']       = $params['game_name'];
        $order['shop_name']       = $params['shop_name'];
        $order['channel_name']    = $params['channel_name'];

        $_SESSION['shop_order']   = $order;
        return true;
    }


    // session订单最终审核计算并整理后入库
    protected function shop_order_insert_db($hash_id=''){
        if(!$hash_id){
            $this->redirect_error($_SESSION['login_back_url'],'订单页面疑遭篡改，为了安全，请您重新下单！');
        }
        // session订单入库
        $id = $this->shop_order_final_insert_db();
        if($id){
            // 入库成功，删除session订单，跳转订单支付页
            $order_id = $_SESSION['shop_order']['order_id'];
            unset($_SESSION['shop_order']);
            $this->redirect("pay-66173{$id}-{$order_id}.html");
        }else{
            // 入库失败，返回订单确认页面
            $this->redirect($_SESSION['login_back_url'],'生成订单失败，请您再次确认订单，或重新购买！');
        }
    }

    // 订单入库前最终审核数据[ 价格，折扣必须重新取，不能直接用session内容 ]
    protected function shop_order_final_audit($params=array()){
        
        // ------重置订单关键数据------
        // 获取店铺商品最终折扣
        $shop_id    = $params['shop_id'];
        $game_id    = $params['game_id'];
        $product_id = $params['id'];
        $channel_id = $params['channel_id'];
        $chs = $this->shop_product_final_ch_discounts($shop_id, $game_id, $product_id);
        foreach ($chs as $v1) {
            if(is_array($v1)){
                foreach ($v1 as $v2) {
                    if($v2['id'] == $channel_id){
                        $ch_info = $v2;
                        break;
                    }
                }
            }else{
                if($v1['id'] == $channel_id){
                    $ch_info = $v1;
                }
            }
        }
        if($ch_info){
            $params['discount']     = $ch_info['priority_discount'];
            $params['channel_name'] = $ch_info['channel_name'];
        }elseif($params['do'] == 'recharge'){
            // 续充，无可用渠道折扣，则使用原价
            $params['discount']     = 10;
            $params['channel_name'] = $params['channel_name'];
        }else{
            // 首充号，66后台无此渠道商品，店铺上架则购买报错 或者 10折处理
        }
        // 获取66商品信息：原价，标题
        $product_shop_dao = new product_shop_dao();
        $master_product_info    = $product_shop_dao->get_master_product($product_id);
        $params['money']        = $master_product_info['price'];
        $params['seller_id']    = $master_product_info['user_id'];
        $params['title']        = $master_product_info['title'];
        $params['product_type'] = $master_product_info['type'];

        // 店铺信息
        $index_shop_dao = new index_shop_dao();
        $shop = $index_shop_dao->get_shop_info($shop_id); 
        $params['shop_name'] = $shop['s_name'];

        // 游戏信息
        $game_shop_dao = new game_shop_dao();
        $shop_game = $game_shop_dao->get_shop_game_info($params, 1);
        $params['game_name'] = $shop_game['game_name'];

        // 区服信息
        $serv_info = $game_shop_dao->get_serv_info($params['serv_id']);
        $params['serv_name'] = $serv_info['serv_name'];

        // ------更多订单必须信息------
       
        // 生成订单号
        $params['order_id'] = $this->orderid($game_id);

        // 支付渠道空着

        return $params;
    }

    // session订单入库
    protected function shop_order_final_insert_db(){
        $service = $this->DAO->get_service();//客服
        if(empty($service)){
            $service['id']='114';
        }
        $order = $_SESSION['shop_order'];
        $bean  = new productBean();
        $bean->order_id        = $order['order_id'];
        $bean->title           = $order['title'];
        $bean->buyer_id        = $order['buyer_id'];
        $bean->product_id      = $order['product_id'];
        $bean->product_type    = $order['product_type'];
        $bean->amount          = $order['amount'];
        $bean->money           = $order['money'];
        $bean->unit_price      = $order['unit_price'];
        $bean->pay_money       = $order['pay_money'];
        $bean->game_id         = $order['game_id'];
        $bean->serv_id         = $order['serv_id'];
        $bean->game_channel    = $order['game_channel'];
        $bean->seller_id       = $order['seller_id'];
        $bean->qq              = $order['qq'];
        $bean->tel             = $order['tel'];
        $bean->discount        = $order['discount'];
        $bean->role_name       = $order['role_name'];
        $bean->role_back_name  = $order['role_back_name'];
        $bean->service_id      = $service['id'];
        $bean->role_level      = $order['role_level'];
        $bean->is_rand_user    = $order['is_rand_user'];
        $bean->game_user       = $order['game_user'];
        $bean->game_pwd        = $order['game_pwd'];
        $bean->attr            = $order['attr'];
        $bean->platform        = $order['from_client'];
        $bean->is_agent        = $order['is_agent'];
        $bean->shop_id         = $order['shop_id'];
        $bean->remarks         = $order['remarks'];
        $bean->buy_time        = time();
        $bean->status          = 0;
        $bean->discount_in     = 0;
        $bean->reduce_product  = 0;
        $bean->pay_channel     = 0;
        unset($bean->id);
        $id = $this->DAO->shop_insert_order((array)$bean);
        return $id;
    }

    /**
     * 支付宝充值回调
     */
    public function shop_ali_pay_return($params=array()){
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        $verify_result = $alipay->return_verify();
        if ($verify_result) {
            $order_id       = $_GET['out_trade_no']; // 订单号
            $order_result   = $_GET['result'];       // 订单状态，是否成功
            $ali_order_id   = $_GET['trade_no'];     // 交易号
            $order_info = $this->DAO->shop_order_info(array('order_id'=>$order_id));
            $this->assign("info", $order_info);
            if(!$order_info['id']){
                $this->redirect_error('http://shop.66173.cn','没有查到该订单');
            }
        }else{
            $this->redirect_error('http://shop.66173.cn','支付有误');
        }
        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result",   $_GET['result']);
        $this->display(self::TPL."/shop_order_pay_result.html");
    }


    /**
     * 订单状态页/详情页 [ 用户中心/店铺订单 中使用 ]
     */
    public function shop_order_detail_view($params=array()){
        $this->check_usr_login();
        $order_info = $this->DAO->shop_order_info($params, 1);
        if(!$order_info['id']){
            $this->redirect_error('','订单不存在');
        }
        // 当前登录者判断
        if($_SESSION['user_id'] != $order_info['buyer_id']){
            $this->redirect_error('','订单处理中~~');
        }else{
            // 订单提交者
            $this->assign("order",$order_info);
            $this->display(self::TPL."/shop_order_detail.html");
        }
    }


}

