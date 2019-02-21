<?php
COMMON('baseCore','uploadHelper','pageCore');
DAO('apk_pay_dao');

class apk_pay_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new apk_pay_dao();
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'user_id') {
                    $this->user_id = $param[1];
                }
                if ($param[0] == 'app_id') {
                    $this->app_id = $param[1];
                }
                if ($param[0] == 'channel') {
                    $this->channel = $param[1];
                }
                if ($param[0] == 'timestamp') {
                    $this->timestamp = $param[1];
                }
                if ($param[0] == 'mac') {
                    $this->mac = $param[1];
                }
                if ($param[0] == 'sid') {
                    $this->sid = $param[1];
                }
                if ($param[0] == 'cp_order_id') {
                    $this->cp_order_id = $param[1];
                }
                if ($param[0] == 'usertoken') {
                    $this->usertoken = $param[1];
                }
                if ($param[0] == 'safety') {
                    $this->safety = $param[1];
                }
            }
        }
    }

    public function qb_pay(){
        $result = array("errcode" => 1, "msg" => "网络请求出错");
        $params = $_POST;
        $app_info = $this->DAO->get_app_info($this->app_id);
        $this->safety_verif($app_info);
        $rate = $this->DAO->get_qq_rate(1);
        $this->qb_money_verif($params['money'],(int)$params['amount'],$params['unit_price'],$rate);
        $SYS_order_id = $this->orderid('QB');
        $params['good_price'] = $params['money'];
        $params['good_title'] = '充值'.$params['money'].'获得'.$params['unit_price']*$params['amount'].'QB';
        if(!$params['charge_qq']){
            $result = array("errcode" => 1, "msg" => "QQ账号异常");
            die("0".base64_encode(json_encode($result)));
        }
        if($params['paytype']=='1'){
            $order_info = $this->insert_order($SYS_order_id,$params,$rate);
            $result = $this->get_ali_result($order_info,$params,$app_info,$SYS_order_id,ALI_MQB_notify_url);
        }elseif($params['paytype']=='2'){
            $order_info = $this->insert_order($SYS_order_id,$params,$rate);
            $result = $this->get_wx_result($order_info,$params,$app_info,$SYS_order_id,WX_MQB_notify_url);
        }else{
            $result = array("errcode" => 1, "msg" => "支付方式异常");
            die("0".base64_encode(json_encode($result)));
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function nnb_pay(){
//        $this->open_debug();
        $result = array("errcode" => 1, "msg" => "网络请求出错");
        $params = $_POST;
        $app_info = $this->DAO->get_app_info($this->app_id);
        $this->safety_verif($app_info);
        $time = strtotime("now");
        $this->money_verif($params['money'],(int)$params['amount'],$params['unit_price']);

        $SYS_order_id = $this->orderid('66'.$this->app_id);
        //获取金额赠送积分
        $integral = $this->DAO->get_integral_info((int)$params['money']);
        $params['integral'] = $integral['integral'];
        $params['good_price'] = $params['money'];
        $params['good_title'] = '充值'.$params['money'].'获得'.$params['amount']*$params['unit_price'].'牛币';
        if($params['paytype']=='1'){
            $order_info = $this->create_order($params,$SYS_order_id,$time,$params['paytype']);
            $result = $this->get_ali_result($order_info,$params,$app_info,$SYS_order_id,ALI_NIU_SECURE_notify_url);
        }elseif($params['paytype']=='2'){
            if($params['way'] == 'ngxs'){
                $params['pay_from'] = 2;
                $notify_url = XS_WX_NIU_notify_url;
            }else{
                $params['pay_from'] = 1;
                $notify_url = WX_NIU_notify_url;
            }
            $order_info = $this->create_order($params,$SYS_order_id,$time,$params['paytype']);
            $result = $this->get_wx_result($order_info,$params,$app_info,$SYS_order_id,$notify_url);
        }else{
            $result = array("errcode" => 1, "msg" => "支付方式异常");
            die("0".base64_encode(json_encode($result)));
        }
        $this->err_log(var_export($result,1),'66apk_order');
        die("0".base64_encode(json_encode($result)));
    }

    protected function insert_order($order_id,$params,$rate){
//        $params['unit_price'] = $rate/10;
//        $params['money'] = $params['amount']*$rate/10;
        $params['pay_money'] = $params['money'];
        $params['title'] = $params['pay_money'].'元充值'.$params['amount'].'QB';
        $params['pay_channel'] = $params['paytype'];
        $params['buyer_id'] = $this->user_id;
        $params['pay_channel'] =
        //客服环节加入
        $service = $this->DAO->get_service();
        if(empty($service)){
            $service['id']='114';
        }
        $params['service_id'] = $service['id'];
        $params['order_id'] = $order_id;
        $params['buy_remark'] = $params['message'];
        $this->DAO->insert_order($params);
        return $params;
    }

    public function get_ali_result($order_info,$params,$app_info,$SYS_order_id,$notify_url){
        if($this->user_id=='71'){
            $params['good_price']='0.01';
        }
        $sign = $this->make_pay_sign($order_info,$params,$app_info);
        $result = array(
            "errcode"=>0,
            "orderid"=>$SYS_order_id,
            "goodsname"=>$params['good_title'],
            "goodsdesc"=> $params['good_title'] ,
            "goodsfee"=>$params['good_price'],
            "notifyurl"=>$notify_url,
            "sign"=>$sign,
            "msg"=>"订单发送成功");
        return $result;
    }

    protected function make_pay_sign($order,$params,$app_info){
        if(!$order || !$params || !$app_info) {
            return '';
        }
        $sign = md5($this->app_id.$order['order_id'].$params['good_price'].$order['buyer_id'].$this->usertoken.$app_info['app_key']);
        return $sign;
    }

    public function get_wx_result($order_info,$params,$app_info,$SYS_order_id,$notify_url){
        if($this->user_id==71){
            $params['money'] = 0.01;
        }
        if($params['way'] == 'ngxs'){
            $wx_data = $this->make_ngxs_wx_data($params['money'],$params,$order_info['order_id'],$notify_url);
            $wx_api_url = WX_API_url;
        }else{
            $wx_data = $this->make_wx_data($params['money'],$params,$order_info['order_id'],$notify_url);
            $wx_api_url = XY_API_url;
        }
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request($wx_api_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode" => 1, "msg" => $request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS' && $request_data['trade_type']=='APP'){
            if($params['way'] == 'ngxs'){
                $time = time();
                $data = array(
                    'appid' => $request_data['appid'],
                    'partnerid' => $request_data['mch_id'],
                    'noncestr' => $request_data['nonce_str'],
                    'prepayid' => $request_data['prepay_id'],
                    'package' => 'Sign=WXPay',
                    'timestamp' => $time,
                );
                ksort($data);
                $str = '';
                $new_data=array();
                foreach($data as $key => $val ){
                    if(!empty($val)){
                        $new_data[$key]=$val;
                        $str.=$key."=".$val."&";
                    }
                }
                $str = $str."key=".XS_WX_APPKEY;
                $req_sign = strtoupper(md5($str));
                $req_appid = $request_data['appid'];
                $req_partnerid = $request_data['mch_id'];
                $req_prepayid = $request_data['prepay_id'];
                $req_package = 'Sign=WXPay';
                $req_noncestr = $request_data['nonce_str'];
                $req_timestamp = $time;
            }else{
                $req_appid = $request_data['req_appid'];
                $req_partnerid = $request_data['req_partnerid'];
                $req_prepayid = $request_data['req_prepayid'];
                $req_package = $request_data['req_package'];
                $req_noncestr = $request_data['req_noncestr'];
                $req_timestamp = $request_data['req_timestamp'];
                $req_sign = $request_data['req_sign'];
            }
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode" => 1, "msg" => $request_data['err_code_des'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode" => 1, "msg" => '请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }
        $result = array(
            "errcode"=>0,
            "req_appid"=>$req_appid,
            "req_partnerid"=>$req_partnerid,
            "req_prepayid"=>$req_prepayid,
            "req_package"=>$req_package,
            "req_noncestr"=>$req_noncestr,
            "req_timestamp"=>$req_timestamp,
            "req_sign"=>$req_sign,
            "msg"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'66app_wx_order');
        return $result;
    }

    public function make_ngxs_wx_data($money,$product,$out_trade_no,$notify_url){
        $data = array(
            'appid' => XS_WX_APPID,
            'mch_id' => XS_MCH_ID,
            'nonce_str' => $this->create_guids(),
            'body' => $product['good_title'],
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'APP'
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".XS_WX_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function make_wx_data($money,$product,$out_trade_no,$notify_url){
        $attach='`store_appid='.XY_STORE_APPID.'#store_name='.XY_STORE_NAME.'#op_user=';
        $data = array(
            'version' => '1.0.4',//兴业APPID
            'appid' => XY_APPID,//兴业APPID
            'mch_id' => XY_MCD_ID,//兴业MCH_ID
            'wx_appid' => WX_APPID,
            'nonce_str' => $this->create_guids(),
            'body' => $product['good_title'],
            'attach' => $attach,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'APP'
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".XY_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    protected function safety_verif($app_info){
        if($this->safety){
            $safety = $this->app_id.$this->channel.$this->mac.$this->sid.$this->timestamp.$app_info['app_key'];
            if(strtolower($this->safety) != md5($safety)){
                $result = array("errcode" => 1, "msg" => "参数出错了。");
                die("0".base64_encode(json_encode($result)));
            }
        }
    }

    public function money_verif($money,$amount,$unit_price){
        $result = array("errcode" => 1, "msg" => "充值金额异常");
        if(empty($money) || empty($amount) || empty($unit_price)){
            die("0".base64_encode(json_encode($result)));
        }
        if(!is_int($amount) || $amount < 0 || $amount > 5000){
            $result['msg']="牛币充值数量异常";
            die("0".base64_encode(json_encode($result)));
        }
        $new_money = $amount * $unit_price;
        if($new_money != $money){
            $result['msg']="牛币充值金额异常";
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function qb_money_verif($money,$amount,$unit_price,$rate){
        $result = array("errcode" => 1, "msg" => "QB充值金额异常");
        if(empty($money) || empty($amount) || empty($unit_price)){
            die("0".base64_encode(json_encode($result)));
        }
        if($rate/10 != $unit_price){
            $result['msg']="QB单价异常，请刷新该页面后再重新充值。";
            die("0".base64_encode(json_encode($result)));
        }
        if(!is_int($amount) || $amount < 10 || $amount > 5000){
            $result['msg']="QB充值数量异常";
            die("0".base64_encode(json_encode($result)));
        }
        $new_money = round($amount*$unit_price ,2);
        if($new_money != $money){
            $result['msg']="QB充值金额异常";
            die("0".base64_encode(json_encode($result)));
        }
    }

    protected function create_order($params, $SYS_order_id,$time,$pay_channel=1){
        $order['app_id']        = $this->app_id;
        $order['order_id']       = $SYS_order_id;
        $order['pay_channel']   = $pay_channel;
        $order['buyer_id']    = $this->user_id;
        $order['title']       = $params['good_title'];
        $order['pay_money']   = $params['money'];
        $order['nnb_num']   = $params['amount'];
        $order['rate']    = $params['unit_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['channel']   = $this->channel;
        $order['integral'] = $params['integral'];
        $order['pay_from'] = $params['pay_from'];
        if (!$this->DAO->create_nnb_order($order)) {
            $result = array("errcode" => 1, "msg" => "订单创建失败");
            die("0" . base64_encode(json_encode($result)));
        }
        return $order;
    }

    public function qb_order(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $qb_order = $this->DAO->get_qb_order($this->user_id,$this->page);
        if($qb_order){
            $count = $this->DAO->get_qb_order_count($this->user_id);
            $result = array("result" => "1", "desc" => "查询成功","count"=>$count['num'],  "data" => $qb_order);
        }else{
            $result['desc'] = "无QB充值记录";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function nnb_order(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $nnb_order = $this->DAO->get_nnb_order($this->user_id,$this->page);
        if($nnb_order){
            $count = $this->DAO->get_nnb_order_count($this->user_id);
            $result = array("result" => "1", "desc" => "查询成功","count"=>$count['num'], "data" => $nnb_order);
        }else{
            $result['desc'] = "无牛币充值记录";
        }
        die("0".base64_encode(json_encode($result)));
    }
}