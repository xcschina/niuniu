<?php
COMMON('adminBaseCore','luoke_qb/config','luoke_qb/tools');
class services extends adminBaseCore {

    //商品查询接口
    /*
     * http://chong.zukeb.com/merchantgame/queryItems?merchant_id=100000&
     * sign=eba67335b8317ef8e1681f29ebbe306b&sign_type=md5&timestamp=20180321122323&
     * game_name=Q%e5%b8%81&format=json&version=1.0
     */
    public function queryItems($game_name){
        $timestamp = date('YmdHis',time());
        $sign_params = array(
            'merchant_id'=>config::MERCHANTID,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'game_name'=>$game_name
        );
        $sign = tools::set_sign($sign_params);
        $url_params = array(
            'merchant_id'=>config::MERCHANTID,
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'game_name'=>$game_name,
            'format'=>'Json',
            'version'=>'1.0'
        );
        $url = config::ENVIRONMENT.'/merchantgame/queryItems?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

    //正式充值接口
    /*
     *  http://chong.zukeb.com/merchantgame/chargeQbi?merchant_id=100000&
     *  sign=44793e7abbbb58ebd843165e386a27ac&sign_type=md5&timestamp=20180321122323&
     *  format=json&version=1.0&item_id=20000&merchant_order_no=111&buy_num=1&game_account=375151631&
     *  client_ip=129.169.23.221&notify_url=xxxxx&game_name=Q%e5%b8%81
     */
    public function chargeQbi($order_id,$item_id,$game_name,$account,$buy_no){
        $timestamp = date('YmdHis',time());
        $sign_params = array(
            'merchant_id'=>config::MERCHANTID,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'item_id'=>$item_id,
            'merchant_order_no'=>$order_id,
            'buy_num'=>$buy_no,
            'game_account'=>$account,
            'game_name'=>$game_name,
            "client_ip"=>$this->client_ip(),
            'notify_url'=>config::NOTIFYURL
        );
        $sign = tools::set_sign($sign_params);
        $url_params = array(
            'merchant_id'=>config::MERCHANTID,
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'item_id'=>$item_id,
            'merchant_order_no'=>$order_id,
            'buy_num'=>$buy_no,
            'game_account'=>$account,
            "client_ip"=>$this->client_ip(),
            'notify_url'=>config::NOTIFYURL,
            'game_name'=>$game_name
        );
        $url = config::ENVIRONMENT.'/merchantgame/chargeQbi?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

    //订单查询接口
    /*
     *   http://chong.zukeb.com/merchantgame/queryOrder?merchant_id=100000&sign=00c2ac1b51f37ec9995bf7621827205f&
     * sign_type=md5&timestamp=20180321122323&format=json&version=1.0&merchant_order_no=111
     */

    public function queryOrder($order_id){
        $timestamp = date('YmdHis',time());
        $sign_params = array(
            'merchant_id'=>config::MERCHANTID,
            'format'=>'Json',
            'timestamp'=>$timestamp,
            'version'=>'1.0',
            'merchant_order_no'=>$order_id
        );
        $sign = tools::set_sign($sign_params);
        $url_params = array(
            'merchant_id'=>config::MERCHANTID,
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'merchant_order_no'=>$order_id
        );
        $url = config::ENVIRONMENT.'/merchantgame/queryOrder?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

    //余额查询接口
    /*
     *http://chong.zukeb.com/merchantgame/queryBalance?merchant_id=100000&sign=00c2ac1b51f37ec9995bf7621827205f&
     * sign_type=md5&timestamp=20180321122323&format=json&version=1.0
     */
    public function queryBalance(){
        $timestamp = date('YmdHis',time());
        $sign_params = array(
            'merchant_id'=>config::MERCHANTID,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0'
        );
        $sign = tools::set_sign($sign_params);
        $url_params = array(
            'merchant_id'=>config::MERCHANTID,
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0'
        );
        $url = config::ENVIRONMENT.'/merchantgame/queryBalance?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

    //结算金额统计信息
    /*
     *http://chong.zukeb.com/merchantgame/queryDailyTrade?merchant_id=100000&sign=0121b0d63148397e1faa1b850f2c8325&
     * sign_type=md5&timestamp=20180321122323&format=json&version=1.0&stat_time=2018-4-23
     */
    public function queryDailyTrade($trade_date){
        $timestamp = date('YmdHis',time());
        $sign_params = array(
            'merchant_id'=>config::MERCHANTID,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'stat_time'=>$trade_date
        );
        $sign = tools::set_sign($sign_params);
        $url_params = array(
            'merchant_id'=>config::MERCHANTID,
            'sign'=>$sign,
            'timestamp'=>$timestamp,
            'format'=>'Json',
            'version'=>'1.0',
            'stat_time'=>$trade_date
        );
        $url = config::ENVIRONMENT.'/merchantgame/queryDailyTrade?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

    //查询充值记录
    /*
     * http://chong.zukeb.com/merchantgame/queryRechargeList?merchant_id=100000&sign=37972bd371b38af04683db292cd22c18&
     * sign_type=md5&timestamp=20180321122323&format=json&version=1.0&start_time=2018-4-01
     */
    public function queryRechargeList($start_time,$end_time=''){
        $timestamp = date('YmdHis',time());
        if (empty($end_time)){
            $sign_params = array(
                'merchant_id'=>config::MERCHANTID,
                'timestamp'=>$timestamp,
                'format'=>'Json',
                'version'=>'1.0',
                'start_time'=>$start_time
            );
            $sign = tools::set_sign($sign_params);
            $url_params = array(
                'merchant_id'=>config::MERCHANTID,
                'sign'=>$sign,
                'timestamp'=>$timestamp,
                'format'=>'Json',
                'version'=>'1.0',
                'start_time'=>$start_time
            );
        }else{
            $sign_params = array(
                'merchant_id'=>config::MERCHANTID,
                'timestamp'=>$timestamp,
                'format'=>'Json',
                'version'=>'1.0',
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            $sign = tools::set_sign($sign_params);
            $url_params = array(
                'merchant_id'=>config::MERCHANTID,
                'sign'=>$sign,
                'timestamp'=>$timestamp,
                'format'=>'Json',
                'version'=>'1.0',
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
        }
        $url = config::ENVIRONMENT.'/merchantgame/queryRechargeList?'.http_build_query($url_params);
        return json_decode($this->request($url),true);
    }

}