<?php
COMMON('baseCore', 'pageCore');
DAO('game_dao');
BO('index_admin');
class game_admin extends baseCore {
    public $DAO;
    public $tags;
    public  $bo;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_dao();
        $this->tags = array(
            '101' => "角色",
            '102' => "策略",
            '103' => "卡牌",
            '104' => "其他"
        );
        $this->bo = new index_admin();
    }

    public function list_view() {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $params = $_GET;
        if($params['page']){
            $this->page = $params['page'];
        }
        $game_list = $this->DAO->get_game_list($this->page,$params);
        $game_count = $this->DAO->get_game_count($params);
        $page_num = $game_count['num']%9;
        if($game_count['num']){
            if($page_num){
                $num = intval($game_count['num']/9)+1;
            }else{
                $num = intval($game_count['num']/9);
            }
        }else{
            $num = 1;
        }
        $this->page_hash();
        $wx = $this->bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("num",$num);
        $this->assign("currentTag",$params);
        $this->assign("tags_list",$this->tags);
        $this->assign("game_list",$game_list);
        $this->display("game_center.html");
    }

    public function detail_view($id) {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $game_info = $this->DAO->get_game_info($id);
        $wx = $this->bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("game_info",$game_info);
        $this->display("game_detail.html");
    }

    public function ajax(){
        $result = array("code"=>0,"msg"=>"网络出错");
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = "参数异常!  001";
            die(json_encode($result));
        }
        $game_list = $this->DAO->get_game_list($params['page'],$params);
        $game_count = $this->DAO->get_game_count($params);
        $page_num = $game_count['num']%9;
        if($game_count['num']){
            if($page_num){
                $num = intval($game_count['num']/9)+1;
            }else{
                $num = intval($game_count['num']/9);
            }
        }else{
            $num = 1;
        }
        $result['code'] = 1;
        $result['msg'] = "查询成功";
        $result['data'] = $game_list;
        $result['currentPage'] = $params['page'];
        $result['totalPage'] = $num;
        die(json_encode($result));
    }

    public function zhijian_callback($data){
        if(empty($data)){
            die('缺少参数');
        }
        if($data['ext']){
            $data['ext'] = htmlspecialchars_decode($data['ext']);
        }
        if(!$data['cp_order']){
            die('缺少订单信息');
        }
        $order_info = $this->DAO->get_super_order($data['cp_order']);
        if(!$order_info){
            die('订单信息异常');
        }elseif($order_info['status'] >= 1){
            die('success');
        }
        $zhijian_info = $this->DAO->get_zhijian_info($order_info['app_id'],$order_info['channel'],$data['cpgameid']);
        if(!$zhijian_info){
            die('缺少渠道参数');
        }
        $sign_str = 'cp_order='.$data['cp_order'].'&cpgameid='.$data['cpgameid'].'&fee='.$data['fee'].'&qqes_order='.$data['qqes_order'].'&timestamp='.$data['timestamp'].'&'.$zhijian_info['app_key'];
        if($data['sign'] == md5($sign_str)){
            $this->DAO->update_super_order_info($data['cp_order'],time(),$data['qqes_order']);
            die('success');
        }else{
            die('签名错误');
        }
    }

    public function jbsj_callback($data){
        if(empty($data)){
            die('paynum_exist');
        }
        if(!$data['gameid']){
            //缺少游戏id
            die('缺少游戏id');
        }
        if(!$data['PayNum']){
            //缺少订单号
            die('缺少订单号');
        }
        $channle_app = $this->DAO->get_channel_app_info($data['gameid'],'jbsj');
        if(empty($channle_app)){
            //缺少渠道参数
            die('缺少渠道参数');
        }
        $order_info = $this->DAO->get_super_order($data['PayNum']);
        if(!$order_info){
            //未查询到订单号
            die('未查询到订单号');
        }elseif($order_info['status'] >= 1){
            die('true');
        }
        $sign_str = 'basicword'.$data['PayNum'].$data['gameid'].$data['PayTime'].$data['amount'];
        if($data['ticket']==md5($sign_str)){
            $this->DAO->update_super_order_info($data['PayNum'],time(),$data['ticket']);
            die('true');
        }else{
            //签名错误
            die('签名错误');
        }

    }

    public function hjy_callback($data){
        if(empty($data)){
            die('缺少参数');
        }
        if(!$data['gameId']){
            //缺少游戏id
            die('缺少游戏id');
        }
        if(!$data['cpOrderId']){
            //缺少订单号
            die('缺少订单号');
        }
        $channle_app = $this->DAO->get_channel_app_info($data['gameId'],'hjy');
        if(empty($channle_app)){
            die('缺少渠道参数');
        }
        $order_info = $this->DAO->get_super_order($data['cpOrderId']);
        if($data['status'] !='success'){
            die('订单状态失败');
        }
        if(!$order_info){
            die('未查询到订单号');
        }elseif($order_info['status'] >= 1){
            die('true');
        }
        $sign_str = 'cpOrderId='.$data['cpOrderId'].'&gameId='.$data['gameId'].'&goodsId='.$data['goodsId'].'&goodsName='.$data['goodsName'].'&money='.$data['money'].'&orderId='.$data['orderId'];
        $sign_str = $sign_str.'&role='.$data['role'].'&server='.$data['server'].'&status='.$data['status'].'&time='.$data['time'].'&uid='.$data['uid'];
        $sign_str = $sign_str.'&userName='.$data['userName'].'&key='.$channle_app['param1'];
        if($data['sign'] == md5($sign_str)){
            $this->DAO->update_super_order_info($data['cpOrderId'],time(),$data['orderId']);
            die('true');
        }else{
            //签名错误
            die('签名错误');
        }

    }

    public function tianxing_callback($data){
        $result = array("code" => 0, "msg" => "网络出错");
        if(empty($data)){
            $result['msg'] ='缺少必要参数';
            die(json_encode($result));
        }
        if(!$data['out_trade_no']){
            $result['msg'] ='缺少渠道订单号';
            die(json_encode($result));
        }
        $order_info = $this->DAO->get_super_order($data['trade_no']);
        if(!$order_info){
            $result['msg'] ='未查询到订单号';
            die(json_encode($result));
        }elseif($order_info['status'] >= 1){
            $result = array("status" => "success");
            die(json_encode($result));
        }
        $channle_app = $this->DAO->get_super_app_info($order_info['app_id'],'tianxing');
        if(empty($channle_app)){
            $result['msg'] ='缺少渠道参数';
            die(json_encode($result));
        }

        $sign_str = "amount=".$data['amount']."&channel_source=".$data['channel_source']."&game_appid=".$data['game_appid'];
        $sign_str = $sign_str."&out_trade_no=".$data['out_trade_no']."&payplatform2cp=".$data['payplatform2cp']."&trade_no=".$data['trade_no'].$channle_app['app_key'];
        if($data['sign']==md5($sign_str)){
            $this->DAO->update_super_order_info($data['trade_no'],time(),$data['out_trade_no']);
            $result = array("status" => "success");
            die(json_encode($result));
        }else{
            //签名错误
            $result['msg'] ='签名错误';
            die(json_encode($result));
        }

    }

    public function xunrui_callback($data){
//        $result = array("code" => 0, "msg" => "网络出错");
        if(empty($data)){
            $result = '缺少必要参数';
            die(json_encode($result));
        }
        if(!$data['invoice']){
            $result = '缺少平台订单号';
            die(json_encode($result));
        }
        $order_info = $this->DAO->get_super_order($data['orderid']);
        if(!$order_info){
            $result = '未查询到订单号';
            die(json_encode($result));
        }elseif($order_info['status'] >= 1){
            $result = 'OK';
            die(json_encode($result));
        }
        $channle_app = $this->DAO->get_super_app_info($order_info['app_id'],'xunrui');
        if(empty($channle_app)){
            $result = '缺少渠道参数';
            die(json_encode($result));
        }
        unset($data['ext']);
        $sign_str = $this->get_sign($data).$channle_app['app_key'];
        if($data['sign']==md5($sign_str) && $data['status'] == 99){
            $this->DAO->update_super_order_info($data['orderid'],time(),$data['invoice']);
            $result = 'OK';
            die(json_encode($result));
        }else{
            //签名错误
            $result = '签名错误';
            die(json_encode($result));
        }
    }

    public function get_sign($data){
        unset($data['sign']);
        ksort($data);
        foreach($data as $k => $v){
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp);
        return $str;
    }

    public function lanmo_callback($data){
        if(empty($data)){
            $result = '缺少必要参数';
            die($result);
        }
        if(!$data['orderid']){
            $result = '缺少商户订单号';
            die($result);
        }
        $order_info = $this->DAO->get_super_order($data['orderid']);
        if(!$order_info){
            $result = '未查询到订单号';
            die($result);
        }elseif($order_info['status'] >= 1){
            $result = 'success';
            die($result);
        }
        $channel_app = $this->DAO->get_super_app_info($order_info['app_id'],'lanmo');
        if(empty($channel_app)){
            $result = '缺少渠道参数';
            die($result);
        }
        $data['appkey'] = $channel_app['app_key'];
        $sign_str = $this->get_sign($data);
        if($data['sign']==md5($sign_str) && $data['status'] == 10000){
            //由于蓝魔未传平台订单号，因此用平台订单号字段记录sign
            $this->DAO->update_super_order_info($data['orderid'],time(),$data['sign']);
            $result = 'success';
            die($result);
        }else{
            //签名错误
            $result = '签名错误';
            die($result);
        }
    }

    public function iqiyi_callback($data){
        $result = array('success'=>'false','data'=>-5,'message'=>'网络出错');
        if(empty($data)){
            $result['data'] = -2;
            $result['message'] = '缺少必要参数';
            die(json_encode($result));
        }
        if(!$data['order_id']){
            $result['data'] = -2;
            $result['message'] = '缺少平台订单号';
            die(json_encode($result));
        }
        $order_info = $this->DAO->get_super_order($data['userData']);
        if(!$order_info){
            $result['data'] = -2;
            $result['message'] = '未查询到订单号';
            die(json_encode($result));
        }elseif($order_info['status'] >= 1){
            $result['success'] = 'true';
            $result['data'] = $order_info['order_id'];
            $result['message'] = '成功';
            die(json_encode($result));
        }
        $channel_app = $this->DAO->get_super_app_info($order_info['app_id'],'iqiyi');
        if(empty($channel_app)){
            $result['data'] = -2;
            $result['message'] = '缺少渠道参数';
            die(json_encode($result));
        }
        $sign_str = "user_id=".$data['user_id']."&order_id=".$data['order_id']."&money=".$data['money']."&server_id=".$data['server_id']."&key=".$channel_app['app_key'];
        if($data['sign']==md5($sign_str)){
            $this->DAO->update_super_order_info($data['userData'],time(),$data['order_id']);
            $result['success'] = 'true';
            $result['data'] = $data['order_id'];
            $result['message'] = '成功';
            die(json_encode($result));
        }else{
            //签名错误
            $result['data'] = -1;
            $result['message'] = '签名错误';
            die(json_encode($result));
        }
    }

    public function xiaomi_callback($data){
        $result = array('code'=>400,'msg'=>'网络出错');
        if(empty($data)){
            $result['msg'] = '缺少必要参数';
            die(json_encode($result));
        }
        if(!$data['orderId']){
            $result['msg'] = '缺少平台订单号';
            die(json_encode($result));
        }
        $order_info = $this->DAO->get_super_order($data['extend']);
        if(!$order_info){
            $result['msg'] = '未查询到订单号';
            die(json_encode($result));
        }elseif($order_info['status'] >= 1){
            $result['code'] = 200;
            $result['msg'] = '成功';
            die(json_encode($result));
        }
        $channel_app = $this->DAO->get_super_app_info($order_info['app_id'],'xiaomi_h5');
        if(empty($channel_app)){
            $result['msg'] = '缺少渠道参数';
            die(json_encode($result));
        }
        $sign_str = $this->get_sign($data).'&'.$channel_app['app_key'];
        if($data['sign']==md5($sign_str)){
            if($data['status'] == 'TRADE_SUCCESS'){
                $this->DAO->update_super_order_info($data['extend'],substr($data['timestamp'],0,10),$data['orderId']);
            }
            $result['code'] = 200;
            $result['msg'] = '成功';
            die(json_encode($result));
        }else{
            //签名错误
            $result['msg'] = '签名错误';
            die(json_encode($result));
        }
    }

    public function oppo_callback($data){
        $result = 'FAIL';
        $msg = '网络出错';
        if(empty($data)){
            $msg = '缺少必要参数';
            die('result='.$result.'&resultMsg='.$msg);
        }
        if(!$data['notifyId']){
            $msg = '缺少平台订单号';
            die('result='.$result.'&resultMsg='.$msg);
        }
        $order_info = $this->DAO->get_super_order($data['partnerOrder']);
        if(!$order_info){
            $msg = '未查询到订单号';
            die('result='.$result.'&resultMsg='.$msg);
        }elseif($order_info['status'] >= 1){
            $result = 'OK';
            $msg = '成功';
            die('result='.$result.'&resultMsg='.$msg);
        }
        $channel_app = $this->DAO->get_super_app_info($order_info['app_id'],'oppo_h5');
        if(empty($channel_app)){
            $msg = '缺少渠道参数';
            die('result='.$result.'&resultMsg='.$msg);
        }

        $sign_str = $this->rsa_decrypt_params($data); //rsa加密
        if($sign_str == 1){
            $this->DAO->update_super_order_info($data['partnerOrder'],time(),$data['notifyId']);
            $result = 'OK';
            $msg = '成功';
            die('result='.$result.'&resultMsg='.$msg);
        }else{
            //签名错误
            $msg = '签名错误';
            die('result='.$result.'&resultMsg='.$msg);
        }
    }

    public function rsa_decrypt_params($data){
        $data_str = "notifyId=".$data['notifyId']."&partnerOrder=".$data['partnerOrder']."&productName=".$data['productName'].
            "&productDesc=".$data['productDesc']."&price=".$data['price']."&count=".$data['count']."&attach=".$data['attach'];
        $publickey = file_get_contents(PREFIX.'/common/super/keys/oppo_pay_rsa_public_key.pem');
        $public_key_id = openssl_pkey_get_public($publickey);
        $signature = base64_decode($data['sign']);
        return openssl_verify($data_str, $signature, $public_key_id );//成功返回1,0失败，-1错误,其他看手册;
    }

}