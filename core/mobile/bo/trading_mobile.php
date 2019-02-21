<?php
COMMON('baseCore', 'pageCore', 'imageCore', 'uploadHelper', 'class.phpmailer', 'oauth.qq');
COMMON('alipay', 'alipay/alipay_submit.class', 'alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service', "alipay.mobile.config", 'alipay_mobile/alipay_notify');
COMMON('paramUtils', 'RNCryptor/RNEncryptor');
DAO('trading_dao', 'my_dao', 'account_dao', 'moyu_product_dao');

class trading_mobile extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new trading_dao();
    }

    public function account_center(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $user_info                  = $this->DAO->get_user_info($_SESSION['user_id']);
        if(!$user_info){
            $this->redirect("/Login");
        }
        $deliver_goods      = $this->DAO->get_order($user_info['user_id'], 1, 1);
        $collect_goods      = $this->DAO->get_order($user_info['user_id'], 2, 1);
        $sell_goods         = $this->DAO->get_sell_order($user_info['user_id'], 1);
        $sell_collect_goods = $this->DAO->get_sell_order($user_info['user_id'], 2);
        $pusblish_product   = $this->DAO->get_sell_product($user_info['user_id'], 5);
        $on_product         = $this->DAO->get_sell_product($user_info['user_id'], 1);
        $message            = $this->DAO->get_unread_messages($user_info['user_id']);
        $pending            = $this->DAO->get_order_list($_SESSION['user_id'], '0');
        //判断未付款订单是否过20分钟
        foreach($pending as $key => $payment){
            if((time() - intval($payment['buy_time'])) > 1200){
                $this->DAO->update_order_status($payment['id'], 5);
                $this->DAO->update_order_type($payment['id'], 0);
            }
        }
        $user_certification = $this->DAO->get_user_certification_count($user_info['user_id'])['num'];//实名认证
        $account_info       = $this->DAO->get_account_info($user_info['user_id']);
        $pending_payment    = $this->DAO->get_order($user_info['user_id'], 0, 1);
        $this->assign('user_id', $user_info['user_id']);
        $this->assign('info', $user_info);
        $this->assign('account_info', $account_info);
        $this->assign('message', $message);
        $this->assign('pusblish_product', $pusblish_product);
        $this->assign('on_product', $on_product);
        $this->assign('user_certification', $user_certification);
        $this->assign('pending_payment', $pending_payment);
        $this->assign('deliver_goods', $deliver_goods);
        $this->assign('sell_collect_goods', $sell_collect_goods);
        $this->assign('sell_goods', $sell_goods);
        $this->assign('collect_goods', $collect_goods);
        $this->display('moyu/userCenter.html');
    }

    public function order_list($status){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $order_list = $this->DAO->get_order_list($_SESSION['user_id'], $status);
        foreach($order_list as $key => $data){
            if(!$data['serv_name']){
                $goods_info                    = $this->DAO->get_goods_info($data['product_id']);
                $order_list[$key]['serv_name'] = $goods_info['serv_name'];
            }
        }
        $pending_payment = $this->DAO->get_order_list($_SESSION['user_id'], '0');
        //判断未付款订单是否过20分钟
        foreach($pending_payment as $key => $payment){
            if((time() - intval($payment['buy_time'])) > 1200){
                $this->DAO->update_order_status($payment['id'], 5);
                $this->DAO->update_order_type($payment['id'], 0);
            }
        }
        $this->assign("order_list", $order_list);
        $this->assign("status", $status);
        $this->display('moyu/buy_list.html');
    }

    public function get_order($status){
        $result = array('code' => 0, 'msg' => '网络出错！');
        if(!$_POST['user_id']){
            $result['msg'] = '用户ID不能为空';
            die('0' . base64_encode(json_encode($result)));
        }
        $order_list     = $this->DAO->get_order_list($_POST['user_id'], $status);
        $result['code'] = 1;
        $result['msg']  = '查询成功';
        $result['list'] = $order_list;
        die('0' . base64_encode(json_encode($result)));
    }

    public function collect_list(){
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        //收藏列表
        $collect_list = $this->DAO->get_collect_list($_SESSION['user_id']);
        $collect_list = $this->get_info($collect_list);

        //失效列表
        $invalid_list = $this->DAO->get_collect_list($_SESSION['user_id'], 1);
        $invalid_list = $this->get_info($invalid_list);
        $this->page_hash();
        $this->assign('user_id', $_SESSION['user_id']);
        $this->assign('collect_list', $collect_list);
        $this->assign('invalid_list', $invalid_list);
        $this->display('moyu/my_collection.html');
    }

    public function get_info($data_list){
        foreach($data_list as $key => $data){
            $game                            = $this->DAO->get_game_info($data['game_id']);
            $service                         = $this->DAO->get_service_info($data['serv_id']);
            $channel                         = $this->DAO->get_channel_info($data['channel_id']);
            $data_list[$key]['game_name']    = $game['game_name'];
            $data_list[$key]['serv_name']    = $service['serv_name'];
            $data_list[$key]['channel_name'] = $channel['channel_name'];
        }
        return $data_list;
    }

    public function clear_invalid(){
        $result = array('code' => 0, 'msg' => '网络出错啦！');
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        $invalid_list = $this->DAO->get_collect_list($_POST['user_id'], 1);
        foreach($invalid_list as $key => $data){
            $this->DAO->update_collect($data['id'], $_POST['user_id']);
        }
        $this->DAO->del_session('my_collect_', $_POST['user_id'], 1);
        $result['code'] = 1;
        $result['msg']  = '已清空失效商品';
        die('0' . base64_encode(json_encode($result)));
    }

    public function del_collect(){
        $result = array('code' => 0, 'msg' => '网络出错啦！');
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_POST['goods_list']){
            $result['msg'] = '未选择删除收藏商品';
            die('0' . base64_encode(json_encode($result)));
        }
        $_POST['goods_list'] = explode(',', $_POST['goods_list']);
        foreach($_POST['goods_list'] as $data){
            $this->DAO->update_collect($data, $_POST['user_id']);
        }
        $this->DAO->del_session('my_collect_', $_POST['user_id']);
        $result['code'] = 1;
        $result['msg']  = '移除收藏商品成功';
        die('0' . base64_encode(json_encode($result)));
    }

    //消费记录
    public function consume_record(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $record_list = $this->DAO->get_order_list($_SESSION['user_id'], 3);
        $this->assign('record_list', $record_list);
        $this->display('moyu/pay_records.html');
    }

    public function real_name_verify(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
            exit;
        }
        $info = $this->DAO->get_user_info($_SESSION['user_id']);
        if($info['id_number']){
            $this->redirect('/accountCenter');
        }
        $this->page_hash();
        $this->assign('info', $info);
        $this->display('trading/check_identity.html');
    }

    public function do_real_verify(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(strpos($params['user_name'], '·') || strpos($params['user_name'], '•')){
            $smb = true;
        } else{
            $smb = false;
        }
        $preg  = preg_match("/^[\x{4e00}-\x{9fa5}]{1,5}[·•][\x{4e00}-\x{9fa5}]{2,15}$/u", $params['user_name']);
        $preg1 = preg_match("/^[\x{4e00}-\x{9fa5}]{2,15}$/u", $params['user_name']);
        if(mb_strlen($params['user_name']) < 2 || ($smb && !$preg) || (!$smb && !$preg1)){
            $result['msg'] = '真实姓名错误';
            die('0' . base64_encode(json_encode($result)));
        }
        $idc = $this->is_idcard($params['id_number']);
        if(!$idc){
            $result['msg'] = '身份证号码错误';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_info($params, $_SESSION['user_id']);
        $result['code'] = 1;
        $result['msg']  = '认证成功';
        die('0' . base64_encode(json_encode($result)));
    }

    public function phone_bind(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
            exit;
        }
        $info = $this->DAO->get_user_info($_SESSION['user_id']);
        if($info['mobile']){
            $this->redirect('trading_center.php?act=account_center');
        }
        $this->page_hash();
        $this->assign('info', $info);
        $this->display('trading/phone_check.html');
    }

    public function do_phone_bind(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$this->is_mobile($_POST['mobile'])){
            $result['msg'] = '手机格式错误。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $_POST['mobile'] . "_" . $_POST['sms_code'] ||
            $_POST['sms_code'] == ""){
            $result['msg'] = '验证码错误';
            die('0' . base64_encode(json_encode($result)));
        }
        if(time() - $_SESSION['last_send_time'] > 300){
            $result['msg'] = '验证码超时';
            die('0' . base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_by_mobile($_POST['mobile']);
        if($user_info){
            $result['msg'] = "该手机号码已被绑定，请换个手机号";
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_mobile($_SESSION['user_id'], $_POST['mobile']);
        $result['code'] = 1;
        $result['msg']  = '手机绑定成功';
        die('0' . base64_encode(json_encode($result)));
    }

    public function sms_code(){
        $result  = array('code' => 0, 'msg' => '网络错误');
        $phone   = $_POST['mobile'];
        $nowtime = strtotime("now");
        $today   = date('Y-m-d', time());
        $hash    = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$phone){
            $msg['msg'] = "请输入您的手机号";
            die('0' . base64_encode(json_encode($msg)));
        }
        if(!$this->is_mobile($phone)){
            $msg['msg'] = "验证消息发送失败,请重试";
            die('0' . base64_encode(json_encode($msg)));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime - $_SESSION['last_send_time'] < 180){
                $msg['msg'] = "获取验证码太频繁，请稍后再试";
                die('0' . base64_encode(json_encode($msg)));
            } else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        } else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $this->send_verify($this->client_ip(), $phone, $today);
        $code = str_split($_SERVER["REQUEST_TIME"] . rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->send_sms($phone,array($code),"23267");
        $send_result = $this->bm_send_sms($phone, array($code));
        $this->err_log(var_export($send_result, 1), 'trading_center');
        if($send_result){
            $_SESSION['reg_core'] = $phone . "_" . $code;
            $msg['code']          = "1";//1成功0失败
            $msg['msg']           = "验证消息发送成功！";
            $msg['ver_code']      = $code;
            die('0' . base64_encode(json_encode($msg)));
        } else{
            $msg['msg'] = "验证消息发送失败,请重试2";
            die('0' . base64_encode(json_encode($msg)));
        }
    }

    public function send_verify($ip, $phone, $today){
        $account_dao = new account_dao();
        $ip_count    = $account_dao->get_ip_count($ip, $today);
        if($ip_count['num'] >= 5){
            $result['code'] = "0";//1成功0失败
            $result['msg']  = "本日该手机号接受短信已上限!!!";
            die('0' . base64_encode(json_encode($result)));
        }
        $phone_count = $account_dao->get_phone_count($phone, $today);
        if($phone_count['num'] >= 5){
            $result['code'] = "0";//1成功0失败
            $result['msg']  = "本日该手机号接受短信已上限";
            die('0' . base64_encode(json_encode($result)));
        }
    }

    public function message_list(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
        }
        $msg_list = $this->DAO->get_my_messages($_SESSION['user_id']);
        $this->assign("msg_list", $msg_list);
        $this->display("trading/news_center.html");
    }

    public function message_detail($id){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
        }
        if(!$id){
            die('此消息ID错误');
        }
        $info = $this->DAO->get_message_detail($id);
        if(!$info){
            die('查无此消息');
        }
        $this->assign('info', $info);
        $this->display('trading/news_detail.html');
    }

    public function confirm_view($goods_id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
        }
        if(!$goods_id){
            die('商品ID出错啦');
        }
        $goods_info   = $this->DAO->get_goods_info($goods_id);
        $service_list = $this->DAO->get_service_list($goods_info);
        $this->page_hash();
        $this->assign('service_list', $service_list);
        $this->assign('info', $goods_info);
        $this->display('moyu/confirm_paylist.html');
    }

    public function agreement_view(){
        $this->display('trading/agree.html');
    }

    public function close_trade(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_POST['id']){
            $result['msg'] = '订单ID出错啦';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_POST['type']){
            $result['msg'] = '关闭交易类型不能为空';
            die('0' . base64_encode(json_encode($result)));
        }
        $info = $this->DAO->get_order_info($_POST['id']);
        if(!$info){
            $result['msg'] = '查无此订单';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_order_status($_POST['id'],5);
        $this->DAO->update_order_type($_POST['id'], 0);
        $result['code'] = 1;
        $result['msg']  = '已关闭交易';
        die('0' . base64_encode(json_encode($result)));
    }

    public function confirm_trade(){
        $id     = $_POST['id'];
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$id){
            $result['msg'] = '订单ID出错啦';
            die('0' . base64_encode(json_encode($result)));
        }
        $info = $this->DAO->get_order_info($id);
        if(!$info){
            $result['msg'] = '查无此订单';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_order_status($id,3);
        $this->DAO->update_order_time($id);
        $result['code'] = 1;
        $result['msg']  = '已确认收货';
        die('0' . base64_encode(json_encode($result)));
    }

    public function continue_pay($id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
        }
        if(!$id){
            die('订单ID出错啦');
        }
        $info                      = $this->DAO->get_order_info($id);
        $product                   = $this->DAO->get_goods_info($info['product_id']);
        $serv_id                   = $info['serv_id'] ? $info['serv_id'] : $product['serv_id'];
        $service                   = $this->DAO->get_service_info($serv_id);
        $channel                   = $this->DAO->get_channel_info($product['channel_id']);
        $info['type']              = $product['type'];
        $info['server_id']         = $product['serv_id'];
        $info['product_serv_name'] = $service['serv_name'] ? $service['serv_name'] : "全区全服";
        $info['channel_name']      = $channel['channel_name'];
        $info['platform']          = $channel['platform'];
        if(!$info){
            die('查无此订单');
        }
        $this->page_hash();
        $this->assign('info', $info);
        $this->display('moyu/continue_pay.html');
    }

    public function do_continue_pay(){
        $result = array('code' => 0, 'msg' => '网络出错');
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$params['order_id']){
            $result['msg'] = '订单id出错啦';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$params['pay_mode']){
            $result['msg'] = '请选择支付方式';
            die('0' . base64_encode(json_encode($result)));
        }
        $info = $this->DAO->get_order_info($params['order_id']);
        if((time() - intval($info['buy_time'])) > 1200){
            $this->DAO->update_order_status($info['id'], 5);
            $this->DAO->update_order_type($info['id'], 0);
            $info['status'] = 5;
        }
        if($info['status'] == '5'){
            $result['msg'] = '订单已关闭，请重新下单';
            die('0' . base64_encode(json_encode($result)));
        } elseif($info['status'] != '0'){
            $result['msg'] = '订单已支付，无需继续支付';
            die('0' . base64_encode(json_encode($result)));
        }
        $goods_info = $this->DAO->get_goods_info($info['product_id']);
        if(!$goods_info){
            $result['msg'] = '订单商品错误，请联系客服';
            die('0' . base64_encode(json_encode($result)));
        } elseif($goods_info['type'] == '5' || $goods_info['type'] == '6'){
            if($goods_info['stock'] < $info['amount']){
                $result['msg'] = '当前库存为' . $goods_info['stock'] . '件，您购买的数量不能高于商品库存';
                die('0' . base64_encode(json_encode($result)));
            }
        }
        if($goods_info['end_time'] < time()){
            $product_dao = new moyu_product_dao();
            $product_dao->update_product($info['product_id']);
            $goods_info['is_pub'] = 0;
        }
        if($goods_info['is_pub'] != 1){
            $result['msg'] = '商品已被抢购一空啦，请选择其他商品';
            die('0' . base64_encode(json_encode($result)));
        }
        $info['pay_mode'] = $params['pay_mode'];
        if($params['pay_mode'] == '1'){
            $url            = $this->ali_pay($info, $goods_info);
            $result['code'] = 1;
            $result['url']  = $url;
            $result['msg']  = '调起支付';
            die('0' . base64_encode(json_encode($result)));
        } elseif($params['pay_mode'] == '5'){
            $this->wx_pay($info, $goods_info);
        } elseif($params['pay_mode'] == '6'){
            $banlance_money = $this->DAO->get_user_balance($_SESSION['user_id'])['balance'];
            if($banlance_money < $info['pay_money']){
                $result['code'] = 0;
                $result['msg']  = '余额不足';
                die('0' . base64_encode(json_encode($result)));
            }
            $this->balance_pay($info['pay_money'], $info['id']);
        } else{
            $result['msg'] = '请选择正确的支付方式';
            die('0' . base64_encode(json_encode($result)));
        }
    }

    public function order_detail($id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("/Login");
        }
        if(!$id){
            die('订单ID出错啦');
        }
        $info    = $this->DAO->get_order_info($id);
        $product = $this->DAO->get_goods_info($info['product_id']);
        $imgs    = $this->DAO->get_product_imgs($info['product_id']);
        if(!$info){
            die('查无此订单');
        }
        $this->assign('imgs', $imgs);
        $this->assign('product', $product);
        $this->assign('info', $info);
        $this->display('moyu/paylist_detail.html');
    }

    public function trading_guide(){
        $top_list = $this->DAO->get_articles_list(26, 4);
        $buy_help = $this->DAO->get_articles_list(27);
        $this->assign('top_list', $top_list);
        $this->assign('buy_help', $buy_help);
        $this->display('trading/pay_guide.html');
    }

    public function guide_detail($id){
        if(!$id){
            die('交易指南ID出错啦');
        }
        $info = $this->DAO->get_articles_info($id);
        if(!$info){
            die('查无此交易指南');
        }
        $this->assign('info', $info);
        $this->display('trading/pay_guide_detail.html');
    }

    public function confirm_pay(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$params['type']){
            $result['msg'] = '商品类型错误，请重新下单';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$params['agreement']){
            $result['msg'] = '未同意66173商品购买协议';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['msg'] = "手机号码格式错误,请重试";
            die('0' . base64_encode(json_encode($result)));
        }
        if(!preg_match("/^[1-9][0-9]{4,12}$/", $params['qq'])){
            $result['msg'] = "QQ号格式错误,请重试";
            die('0' . base64_encode(json_encode($result)));
        }
        $goods_info = $this->DAO->get_goods_info($params['id']);
        if($params['type'] == '5' || $params['type'] == '6'){
            if(!$params['role_name']){
                $result['msg'] = "角色名不能为空";
                die('0' . base64_encode(json_encode($result)));
            }
            if(!$params['job']){
                $result['msg'] = "职业不能为空";
                die('0' . base64_encode(json_encode($result)));
            }
            if($params['type'] == '5'){
                if(!$params['sex']){
                    $result['msg'] = "性别不能为空";
                    die('0' . base64_encode(json_encode($result)));
                }
                if($goods_info['serv_id'] == 0){
                    if(!$params['service_id']){
                        $result['msg'] = "服务器不能为空";
                        die('0' . base64_encode(json_encode($result)));
                    }
                }
            }
        }
        if($params['num'] < 1){
            $result['msg'] = "购买数量不能小于1";
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$params['pay_mode']){
            $result['msg'] = '请选择支付方式';
            die('0' . base64_encode(json_encode($result)));
        }

        if($params['type'] == '5' || $params['type'] == '6'){
            if($goods_info['stock'] < $params['num']){
                $result['msg'] = '当前库存为' . $goods_info['stock'] . '件，您购买的数量不能高于商品库存';
                die('0' . base64_encode(json_encode($result)));
            }
        }
        if($goods_info['end_time'] < time()){
            $product_dao = new moyu_product_dao();
            $product_dao->update_product($params['id']);
            $goods_info['is_pub'] = 0;
        }
        if($goods_info['is_pub'] != 1){
            $result['msg'] = '商品已被抢购一空啦，请选择其他商品';
            die('0' . base64_encode(json_encode($result)));
        }
        $params['order_id'] = $this->orderid($goods_info['game_id']);
        $params['money']    = $params['pay_money'] = $params['num'] * $params['price'];
        $this->DAO->insert_order($params, $goods_info, $_SESSION['user_id']);
        if($params['pay_mode'] == '1'){
            $url            = $this->ali_pay($params, $goods_info);
            $result['code'] = 1;
            $result['url']  = $url;
            $result['msg']  = '调起支付';
            die('0' . base64_encode(json_encode($result)));
        } elseif($params['pay_mode'] == '5'){
            $this->go_wx_pay($params, $goods_info);
        } elseif($params['pay_mode'] == '6'){
            $order_info     = $this->DAO->get_order_id($params['order_id']);
            $banlance_money = $this->DAO->get_user_balance($_SESSION['user_id'])['balance'];
            if($banlance_money < $params['pay_money']){
//                $result['code'] = 0;
                $result['msg']  = '余额不足';
                die('0' . base64_encode(json_encode($result)));
            }
            $this->balance_pay($params['pay_money'], $order_info['id']);

        } else{
            $result['msg'] = '请选择支付方式';
            die('0' . base64_encode(json_encode($result)));
        }
    }

    public function ali_pay($params, $info){
        $user_id = $_SESSION['user_id'];
        $pms1    = array(
            "req_data" => '<direct_trade_create_req><subject>' . $info['title'] .
                '</subject><out_trade_no>' . $params['order_id'] .
                '</out_trade_no><total_fee>' . $params['pay_money'] .
                '</total_fee><user_id>'.$user_id.
                '</user_id><seller_account_name>' . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/trade_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL .
                "</merchant_url>" . "<call_back_url>" . SITE_URL . "trading_ali_return.php" .
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $params['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms1['app_pay'] = 'Y';
        }
        $call_back_url = SITE_URL . "trading_ali_return.php";
        // 构造请求函数
        $alipay = new alipay_service();
        $token  = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id);
        $pms2   = array(
            "req_data" => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service" => ALI_MOBILE_Service_authAndExecute,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "call_back_url" => $call_back_url,
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay'] = 'Y';
        }
        return $alipay->alipay_Wap_Auth_AuthAndExecute2($pms2, ALI_MOBILE_key);
    }

    public function go_wx_pay($order, $info){
        $data = array(
            'funcode' => 'WP001',//定值
            'version' => '1.0.0',//定值
            'appId' => XZ_WX_WAP_APPID,
            'mhtOrderNo' => $order['order_id'],
            'mhtOrderName' => $info['title'],
            'mhtOrderType' => '01',//定值（普通消费）
            'mhtCurrencyType' => '156',//定值 156人民币
//            'mhtOrderAmt' => '1',//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderAmt' => $order['pay_money'] * 100,//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderDetail' => $info['game_name'] . '-' . $order['title'],
            'mhtOrderStartTime' => date("YmdHis", time()),
            'notifyUrl' => XZ_WX_WAP_notify_url,
            'frontNotifyUrl' => XZ_WX_WAP_front_notify_url,
            'mhtCharset' => 'UTF-8',
            'deviceType' => '0601',
            'payChannelType' => '13',
            'mhtLimitPay' => '0',
            'outputType' => '2',
            'mhtSignType' => 'MD5'
        );
        ksort($data);
        $data_str = "";
        foreach($data as $key => $param){
            if($param != ''){
                $data_str = $data_str . "&" . $key . "=" . $param;
            }
        }
        $data_str             = substr($data_str, 1);
        $sgin                 = md5($data_str . "&" . md5(XZ_WX_WAP_KEY));
        $data['mhtSignature'] = $sgin;
        $result               = $this->request('https://pay.ipaynow.cn', $data);
        //参数验证
        $this->xz_wx_result_verify($result, XZ_WX_WAP_KEY);
    }

    public function xz_wx_result_verify($result, $wx_wap_key){
        $msg      = '';
        $pop_type = 1;
        if(!$result){
            $msg = '网络异常，请重新支付.';
        }
        $result_array = explode("&", $result);
        $param        = array();
        foreach($result_array as $key => $item){
            $item_array = array();
            $item_array = explode("=", $item);
            if(!empty($item_array)){
                $param[$item_array[0]] = urldecode($item_array[1]);
            }
        }
        if($param['responseCode'] == 'A001'){
            //验证sign
            $old_sign = $param['signature'];
            unset($param['signature']);
            ksort($param);
            $data_str = "";
            foreach($param as $key => $data){
                $data_str = $data_str . "&" . $key . "=" . $data;
            }
            $data_str = substr($data_str, 1);
            $sgin     = md5($data_str . "&" . md5($wx_wap_key));
            if($sgin != $old_sign){
                $msg = '加密出错';
                $this->err_log(var_export($result, 1), 'zx_wap_pre_payment');
                $this->err_log(var_export($param, 1), 'zx_wap_pre_payment');
            } else{
                $pop_type = 2;
//                $this->V->assign('pop_type',$pop_type);
//                $this->V->assign('wx_token',$param['tn']);
//                $this->V->display("weixin_pay_view.html");
            }
        } else{
            $msg = '错误代码' . $param['responseCode'];
        }
//        $this->V->assign('pop_type',$pop_type);
//        $this->V->assign('msg',$msg);
//        $this->V->display("weixin_pay_view.html");
    }

    public function wx_pay($params, $info){
        $openid       = $this->get_user_openid($_SESSION['user_id'], WX_APPID);
        $wx_data      = $this->make_wx_data($params, $info, WX_SECURE_notify_url, $openid);
        $xml_data     = $this->array_to_xml($wx_data);
        $request      = $this->request(WX_API_url, $xml_data);
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code'] == 'FAIL'){
            $result['msg'] = $request_data['return_msg'];
            die('0' . base64_encode(json_encode($result)));
        } elseif($request_data['return_code'] == 'SUCCESS' && $request_data['result_code'] == 'SUCCESS'){
            $prepay_id = $request_data['prepay_id'];
        } elseif($request_data['return_code'] == 'SUCCESS' && $request_data['result_code'] == 'FAIL'){
            $result['msg'] = $request_data['err_code_des'] . $request_data['err_code'];
            die('0' . base64_encode(json_encode($result)));
        } else{
            $result['msg'] = '请求订单异常.' . $request_data;
            die('0' . base64_encode(json_encode($result)));
        }
        $time     = time();
        $nonceStr = $this->create_guids();
        $package  = 'prepay_id=' . $prepay_id;
        $pay_sign = "appId=" . WX_APPID . "&nonceStr=" . $nonceStr . "&package=" . $package . "&signType=MD5&timeStamp=" . $time . "&key=" . WX_APP_KEY;
        $pay_sign = strtoupper(md5($pay_sign));
        $result   = array(
            "timeStamp" => $time,
            "nonceStr" => $nonceStr,
            "package" => $package,
            "paySign" => $pay_sign,
            "msg" => "预支付订单发送成功");
        $this->err_log(var_export($request_data, 1), 'tread_wx_pay_order');
        return $result;
    }

    public function pay_success(){
        $this->display('trading/pay_success.html');
    }

    public function create_guids(){
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid   = substr($charid, 0, 32);
        return $uuid;
    }

    public function array_to_xml($arr = array()){
        $xml = '<xml>';
        foreach($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<" . $key . ">" . $this->array_to_xml($val) . "</" . $key . ">";
            } else{
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function make_wx_data($params, $info, $notify_url, $openid){
        $data = array(
            'appid' => WX_APPID,
            'mch_id' => MCH_ID,
            'nonce_str' => $this->create_guids(),
            'body' => $info['title'],
            'out_trade_no' => $params['order_id'],
            'total_fee' => $params['pay_money'] * 100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'JSAPI',
            'openid' => $openid
        );
        ksort($data);
        $str      = '';
        $new_data = array();
        foreach($data as $key => $val){
            if(!empty($val)){
                $new_data[$key] = $val;
                $str            .= $key . "=" . $val . "&";
            }
        }
        $str              = $str . "key=" . WX_APP_KEY;
        $new_data['sign'] = strtoupper(md5($str));
        return $new_data;
    }

    public function get_user_openid($user_id, $wx_app_id){
        $user_info = $this->DAO->get_user_info($user_id);
        if(empty($user_info['unionid'])){
            $result = array('msg' => '用户信息异常');
            die('0' . base64_encode(json_encode($result)));
        }
        $wx_user_info = $this->DAO->get_wx_user_openid($user_info['unionid'], $wx_app_id);
        if(empty($wx_user_info['open_id'])){
            $result = array('msg' => '用户未关注，无法支付');
            die('0' . base64_encode(json_encode($result)));
        }
        return $wx_user_info['open_id'];
    }

    public function ali_pay_return(){
        //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();
        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);
        $info = $this->DAO->get_order_id($_GET['out_trade_no']);
        if($verify_result){
            $_SESSION['msg'] = '支付成功';
            $this->display('moyu/pay_success.html');
        } else{
            $_SESSION['msg'] = '支付失败';
            $this->redirect("/continuePay/" . $info['id']);
        }

    }

    //余额支付
    public function balance_pay($pay_money, $ord_id){
        //使用余额支付
        //添加余额明细
        $balance['order_id']  = $this->orderid('moyu');
        $balance['money']    = $pay_money;
        $balance['pay_mode'] = 1;
        $balance['user_id']  = $_SESSION['user_id'];
        $balance['type']     = 1;
        $balance['status']     = 4;
        $this->DAO->add_user_balance_detail($balance);

        $product_id = $this->DAO->get_order_info($ord_id)['product_id'];
        $product_info = $this->DAO->get_product_info($product_id);
        $user_id = $product_info['user_id'];
        if($user_id > 1){
            $sell['order_id']  = $this->orderid('moyu');
            $sell['money']    = $pay_money;
            $sell['pay_mode'] = 1;
            $sell['user_id']  = $user_id;
            $sell['type']     = 2;
            $sell['status']     = 4;
            $this->DAO->add_user_balance_detail($sell);//售出记录
            $sell_pay_lock =  $this->DAO->get_user_lock($user_id)['pay_lock'];
            if($sell_pay_lock){
                die('用户正在操作，请稍后重试');
            }
        }
        $buy_pay_lock =  $this->DAO->get_user_lock($_SESSION['user_id'])['pay_lock'];
        if($buy_pay_lock){
            die('用户正在操作，请稍后重试');
        }
        $this->DAO->update_user_lock(1,$user_id);
        $this->DAO->update_user_lock(1,$_SESSION['user_id']);
        $this->DAO->update_seller_balance($pay_money,$user_id);
        $this->DAO->update_user_balance($pay_money,$_SESSION['user_id']);
        $this->DAO->update_user_lock(0,$user_id);
        $this->DAO->update_user_lock(0,$_SESSION['user_id']);

        //更新订单状态
        $this->DAO->update_order_status($ord_id,1);
    }

    //卖家订单
    public function get_sell_order($status){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $order_list = $this->DAO->get_sell_order_list($_SESSION['user_id'], $status);
        foreach($order_list as $key => $data){
            if(!$data['serv_name']){
                $goods_info                    = $this->DAO->get_goods_info($data['product_id']);
                $order_list[$key]['serv_name'] = $goods_info['serv_name'];
            }
        }
        $this->assign("order_list", $order_list);
        $this->assign("status", $status);
        $this->display('moyu/my_order_list.html');
    }

    //卖家发货
    public function seller_delivery(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params     = $_POST;
        if(!$params['pagehash'] || $params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = '参数异常!  001';
            die('0' . base64_encode(json_encode($result)));
        }
        $order_info = $this->DAO->get_order_info($params['id']);
        if(!$params['id']){
            $result['msg'] = '参数异常';
            die('0' . base64_encode(json_encode($result)));
        }
        if($order_info['status'] == 10){
            $result['msg'] = '该订单已发货，无需重新发货';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_FILES['myImage1']['tmp_name'] || !$_FILES['myImage2']['tmp_name'] || !$_FILES['myImage3']['tmp_name']){
            $result['msg'] = "请完整的上传三张图片";
            die('0' . base64_encode(json_encode($result)));
        }
        if($_FILES['myImage1']['name'] == $_FILES['myImage2']['name'] || $_FILES['myImage1']['name'] == $_FILES['myImage3']['name'] || $_FILES['myImage2']['name'] == $_FILES['myImage3']['name']){
            $result['msg'] = '请上传三张不同的图片';
            die('0' . base64_encode(json_encode($result)));
        }
        $my_image1 = $this->up_img('myImage1', ORDER_IMG);
        $my_image2 = $this->up_img('myImage2', ORDER_IMG);
        $my_image3 = $this->up_img('myImage3', ORDER_IMG);

        $imgs       = $my_image1.','.$my_image2.','.$my_image3;
        $this->DAO->add_order_imgs($params['id'], $imgs, $_SESSION['user_id']);
        $this->DAO->update_order_status($params['id'],10);
        $result['code'] = 1;
        $result['msg']  = '卖家已发货';
        $result['url'] = '/trading_center.php?act=get_sell_order&status=1';
        die('0' . base64_encode(json_encode($result)));
    }

    //余额充值
    public function user_recharge(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params              = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = 'hash错误，请刷新后重新登录。';
            die('0' . base64_encode(json_encode($result)));
        }
        $params['order_id']  = $this->orderid('moyu');
        $params['type'] = 4;
        $params['pay_mode'] = $_POST['pay_mode'];
        $params['money'] = $_POST['money'];
        $params['status'] = 1;
        $goods_info['title'] = '用户充值';
        $this->DAO->add_user_balance_detail($params);
        if($params['pay_mode'] == '1'){
            $url            = $this->ali_pay_recharge($params, $goods_info);
            $result['code'] = 1;
            $result['url']  = $url;
            $result['msg']  = '调起支付';
            die('0' . base64_encode(json_encode($result)));
        }else{
            $result['msg'] = '请选择支付方式';
            die('0' . base64_encode(json_encode($result)));
        }
    }

    //已实名认证
    public function already_certification($user_id){
        $account_info = $this->DAO->get_account_info($user_id);
        $this->assign('account_info', $account_info);
        $this->display('moyu/already_certification.html');
    }

    //实名认证
    public function user_certification(){
        $this->display('moyu/user_certification.html');
    }

    //已设置安全手机
    public function already_security_mobile($user_id){
        $account_info = $this->DAO->get_account_info($user_id);
        $this->assign('account_info', $account_info);
        $this->display('moyu/already_security_mobile.html');
    }

    //安全手机
    public function security_mobile(){
        $this->display('moyu/user_certification.html');
    }

    //支付密码
    public function pay_passward(){
        $this->page_hash();
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $this->assign('account_info', $account_info);
        $this->display('moyu/set_pay_passward.html');
    }

    public function re_pay_passward(){
        $this->page_hash();
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $this->assign('account_info', $account_info);
        $this->display('moyu/re_pay_passward.html');

    }

    //支付账号
    public function pay_account(){
        $this->page_hash();
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $this->assign('account_info', $account_info);
        $this->display('moyu/set_pay_account.html');
    }

    //已设置支付账号
    public function already_pay_account(){
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $this->assign('account_info', $account_info);
        $this->display('moyu/already_pay_account.html');
    }
    //卖家商品页面
    public function get_sell_product($status){
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $product_list = $this->DAO->get_sell_product_list($_SESSION['user_id'], $status);
        foreach($product_list as $key => $data){
            $product_list[$key]['collect_num'] = $this->DAO->get_product_collect_num($data['id'])['num'];
        }
        $this->assign("product_list", $product_list);
        $this->assign("is_pub", $status);
        $this->display('moyu/sell_product_list.html');
    }

    //获取游戏商品具体信息
    public function sell_product_detail($product_id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $product_info               = $this->DAO->get_product_info($product_id);
        //处理卖家信息
        $user_id = $product_info['user_id'];//发布者
        if($user_id > 1){
            $user_certification_info = $this->DAO->get_account_info($user_id);
        }
        $certification_flag = '';//1-系统 2-用户已认证 3-用户未认证
        $sell_amout         = $this->DAO->get_sell_count_by_user_id($user_id)['num'];//销售数量
        if($user_id == 1){
            $certification_flag = 1;
        } elseif($user_id > 1){
            $num = $this->DAO->get_user_certification($user_id)['num'];
            if($num == 1){
                $certification_flag = 2;
            } else{
                $certification_flag = 3;
            }
        }
        $imgs                 = $this->DAO->get_product_imgs($product_info['id']);
        $params['user_id']    = $_SESSION['user_id'];
        $params['product_id'] = $product_id;

        if(!empty($product_list[0]['game_name'])){
            $game_name = $product_list[0]['game_name'];
        } else{
            $game_name = '暂无数据';
        }
        $this->assign("user_id", $user_id);
        $this->assign("user_certification_info", $user_certification_info);
        $this->assign("certification_flag", $certification_flag);//用户认证
        $this->assign("sell_amout", $sell_amout);//销售数量
        $this->assign("game_name", $game_name);//系统
        $this->assign("product_info", $product_info);
        $this->assign("imgs", $imgs);
        $this->display("moyu/sell_product_detail.html");
    }

    //买家订单接口·
    public function get_my_order_list(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $status     = $_POST['status'];
        $order_list = $this->DAO->get_order_list($_SESSION['user_id'], $status);
        foreach($order_list as $key => $data){
            if(!$data['serv_name']){
                $goods_info                    = $this->DAO->get_goods_info($data['product_id']);
                $order_list[$key]['serv_name'] = $goods_info['serv_name'];
            }
        }
        $pending_payment = $this->DAO->get_order_list($_SESSION['user_id'], '0');
        //判断未付款订单是否过20分钟
        foreach($pending_payment as $key => $payment){
            if((time() - intval($payment['buy_time'])) > 1200){
                $this->DAO->update_order_status($payment['id'], 5);
                $this->DAO->update_order_type($payment['id'], 0);
            }
        }
        if($order_list){
            $result['code'] = 1;
            $result['msg']  = '查询成功';
            $result['data'] = $order_list;
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 0;
        $result['msg']  = '暂无数据';
        die('0' . base64_encode(json_encode($result)));
    }

    //卖家订单
    public function get_sell_order_data(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $status     = $_POST['status'];
        $order_list = $this->DAO->get_sell_order_list($_SESSION['user_id'], $status);
        foreach($order_list as $key => $data){
            if(!$data['serv_name']){
                $goods_info                    = $this->DAO->get_goods_info($data['product_id']);
                $order_list[$key]['serv_name'] = $goods_info['serv_name'];
            }
        }
        if($order_list){
            $result['code'] = 1;
            $result['msg']  = '查询成功';
            $result['data'] = $order_list;
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 0;
        $result['msg']  = '暂无数据';
        die('0' . base64_encode(json_encode($result)));
    }

    //余额充值
    public function ali_pay_recharge($params,$info){
        $user_id = $_SESSION['user_id'];
        $pms1    = array(
            "req_data" => '<direct_trade_create_req><subject>' . $info['title'] .
                '</subject><out_trade_no>' . $params['order_id'] .
                '</out_trade_no><total_fee>' . $params['money'] .
                "</total_fee><user_id>" .$user_id.
                "</user_id><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/recharge_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL .
                "</merchant_url>" . "<call_back_url>" . SITE_URL . "recharge_ali_return.php" .
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $params['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms1['app_pay'] = 'Y';
        }
        $call_back_url = SITE_URL . "recharge_ali_return.php";
        // 构造请求函数
        $alipay = new alipay_service();
        $token  = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id);
        $pms2   = array(
            "req_data" => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service" => ALI_MOBILE_Service_authAndExecute,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "call_back_url" => $call_back_url,
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay'] = 'Y';
        }
        return $alipay->alipay_Wap_Auth_AuthAndExecute2($pms2, ALI_MOBILE_key);
    }

    //充值回调
    public function recharge_ali_pay_return(){
        //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();
        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);
        $money = $_GET['total_fee'];
        if($verify_result){
            $this->assign('money', $money);
            $this->display('moyu/recharge_success.html');
        } else{
            $_SESSION['msg'] = '支付失败';
            $this->redirect("/accountCenter");
        }

    }

    public function recharge(){
        $this->page_hash();
        $this->display('moyu/recharge.html');
    }

    public function recharge_detail(){
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $user_balance_detail = $this->DAO->get_user_balance_detail($_SESSION['user_id'],4);
        $this->assign('user_balance_detail',$user_balance_detail);
        $this->display('moyu/recharge_detail.html');
    }
    //卖家商品接口
    public function get_sell_product_data(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $status     = $_POST['status'];
        $product_list = $this->DAO->get_sell_product_list($_SESSION['user_id'], $status);
        foreach($product_list as $key => $data){
            $product_list[$key]['collect_num'] = $this->DAO->get_product_collect_num($data['id'])['num'];
            $product_list[$key]['add_time'] = date("Y-m-d",$data['add_time']) ;
            $product_list[$key]['end_time'] = date("Y-m-d",$data['end_time']) ;
            $product_list[$key]['audit_time'] = date("Y-m-d",$data['audit_time']) ;
        }
        if($product_list){
            $result['code'] = 1;
            $result['msg']  = '查询成功';
            $result['data'] = $product_list;
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 0;
        $result['msg']  = '暂无数据';
        die('0' . base64_encode(json_encode($result)));
    }

}