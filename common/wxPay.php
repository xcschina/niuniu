<?php 
// -------------------------------------
// 微信支付 <zhaobencheng> <2016-7-15>
// -------------------------------------

class wxPay{

    private $pay_url     = 'https://api.cib.dcorepay.com/pay/unifiedorder';
    private $query_url   = 'https://api.cib.dcorepay.com/pay/orderquery';
    private $refund_url  = 'https://api.cib.dcorepay.com/pay/refund';
    private $wx_appid    = 'wxbaed68c7f2f3a62c'; // 商户微信公众账号ID
    private $appid       = 'a20160617000000383'; // APPID
    private $mch_id      = 'm20160617000000383'; // 商户号 
    private $key         = '6b0f67a36e457c014f6c29968698a08d';
    private $store_appid = 's20160804000000540';
    private $store_name  = '66173';
    private $op_user     = '66';
    private $order       = array();

    public function set_order($order=array()){
        $this->order = $order;
    }

    public function pay(){
        $par['appid']            = $this->appid;
        $par['mch_id']           = $this->mch_id; 
        $par['wx_appid']         = $this->wx_appid;
        $par['device_info']      = 'WEB'; // 网页/公众号
        $par['nonce_str']        = md5(rand(0,99999).time());
        $par['body']             = $this->order['title']; // 商品描述
        $par['attach']           = '`store_appid='.$this->store_appid.'#store_name='.$this->store_name.'#op_user='.$this->op_user;
        $par['out_trade_no']     = $this->order['order_id']; //商户订单号
        $par['fee_type']         = 'CNY'; 
        $par['total_fee']        = $this->order['pay_money']*100; //分
        $par['spbill_create_ip'] = $this->client_ip();
        $par['goods_tag']        = 1;
        $par['notify_url']       = 'http://www.66173.cn/wx_pc_pay.php';
        $par['trade_type']       = 'NATIVE';
        $par['product_id']       = $this->order['product_id'];
        $par['sign']             = $this->create_sign($par);

        $xml_data = $this->array_to_xml($par);
        $ret_xml  = $this->post_data($this->pay_url, $xml_data);
        $ret      = $this->xml_to_array($ret_xml);
        $check    = $this->check_sign($ret);

        $res['status'] = 0;
        if($check){
            if($ret['return_code'] == 'SUCCESS'){
                if($ret['result_code'] == 'SUCCESS'){
                    $res['status'] = 1;
                    $res['msg'] = $ret['code_url']; 
                }elseif($ret['result_code'] == 'FAIL'){
                    $res['msg'] = '预支付失败,错误代码：'.$ret['err_code'].',错误信息:'.$ret['err_code_des'];
                }else{
                    $res['msg'] = '预支付通讯成功，交易状态未知';
                }
            }elseif($ret['return_code'] == 'FAIL'){
                $res['msg'] = '预支付通讯失败，原因：'.$ret['return_msg'];
            }
        }else{
            $res['msg'] = '预支付返回数据sign验证失败';
        }
        return $res;
    }

    public function pay_return(){
        $post  = $GLOBALS["HTTP_RAW_POST_DATA"];
        $ret   = $this->xml_to_array($post);
        $check = $this->check_sign($ret);
        $res['status'] = 0;
        if($check){
            if($ret['return_code'] == 'SUCCESS'){
                if($ret['result_code'] == 'SUCCESS'){
                    $par['channel_order_id'] = $ret['transaction_id']; // 微信单号
                    $par['bank_type']   = $ret['bank_type'];
                    $par['order_id']    = $ret['out_trade_no'];
                    $par['openid']      = $ret['openid'];
                    $par['pay_time']    = date('Y-m-d H:i:s',strtotime($ret['time_end']));
                    $par['more']   = $ret;
                    $res['msg']    = $par;
                    $res['status'] = 1;
                }elseif($ret['return_code'] == 'FAIL'){
                    $res['msg'] = '支付失败，错误代码：'.$ret['err_code'].'，错误描述：'.$ret[' err_code_des'];
                }else{
                    $res['msg'] = '支付通讯成功，交易状态未知';
                }
            }else{
                $res['msg'] = '支付通信失败，原因：'.$ret['return_msg'];
            }
        }else{
            $res['msg'] = '支付返回数据sign验证失败';
        }
        return $res;
    }

    public function pay_return_bak($res=array()){
        if($res['status']){
            $bak['return_code'] = 'SUCCESS';
            $bak['return_msg']  = '';
        }else{
            $bak['return_code'] = 'FAIL';
            $bak['return_msg']  = $res['msg'];
        }
        $bak_xml = $this->array_to_xml($bak);
        echo $bak_xml;
        die;
    }

    private $query_trade_state = array(
        'SUCCESS'    => '支付成功',
        'REFUND'     => '转入退款',
        'NOTPAY'     => '未支付',
        'CLOSED'     => '已关闭',
        'REVOKED'    => '已撤销（ 刷卡支付）',
        'USERPAYING' => '用户支付中',
        'NOPAY'      => '未支付(确认支付超时)',
        'PAYERROR'   => '支付失败(其他原因， 如银行返回失败)'
        );

    public function query($params=array()){
        $par['appid']         = $this->appid;
        $par['mch_id']        = $this->mch_id; 
        $par['nonce_str']     = md5(rand(0,99999).time());
        $par['out_trade_no']  = $params['order_id']; 
        $par['sign']          = $this->create_sign($par);
        $xml_data = $this->array_to_xml($par);
        $ret_xml  = $this->post_data($this->query_url, $xml_data);
        $ret      = $this->xml_to_array($ret_xml);
        $check    = $this->check_sign($ret);
        $res['status'] = 0;
        if($check){
            if($ret['return_code']=='SUCCESS' && $ret['result_code']=='SUCCESS'){
                $res['status'] = 1;
                $res['msg'] = '查询成功:'.$this->query_trade_state[$ret['trade_state']];
            }else{
                $res['msg'] = '查询失败,错误代码'.$ret['err_code'].','.$ret['err_code_des'];
            }
        }else{
            $res['msg'] = '查询验证失败';
        }
        return $res;
    }

    public function refund(){
        
    }

    private function post_data($url='', $post=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);
        if(curl_errno($ch)) $res = 0;
        curl_close($ch);
        return $res;
    }

    private function array_to_xml($arr=array()){
        $xml = '<?xml version="1.0" encoding="UTF-8"?><xml>'; 
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

    private function xml_to_array($xml=''){
        return (array)simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA); 
    }

    private function create_sign($par=array()){
        ksort($par);
        $par['key'] = $this->key;
        $str = '';
        foreach ($par as $key => $value) {
            if(trim($value) != ''){
                $str .= $key.'='.$value.'&';
            }
        }
        $str = rtrim($str,"&");
        $sign = strtoupper(md5($str));
        return $sign; 
    }

    private function check_sign($par=array()){
        $right_sign = $par['sign'];
        unset($par['sign']);
        $create_sign = $this->create_sign($par);
        return ($right_sign == $create_sign) ? true : false;
    }

    private function client_ip(){
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

}