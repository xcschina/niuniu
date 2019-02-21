<?php
COMMON('sdkCore','alipay_secure/alipay_config','alipay_secure/alipay_function','RNCryptor/RNEncryptor');
DAO('android_pay_dao','user_dao');

class app_pay extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new android_pay_dao();
        $this->qa_user_id = array(71);
    }

    public function qb_ali_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS'){
                $userDao = new user_dao();
                $order = $userDao->get_qb_order($order_id);
                if(!$order){
                    $this->err_log('失败','qb_alisdk_notify');
                    echo "fail";
                }else{
                    if($order['status']!=2){
                        //更新订单状态
                        $userDao->up_qb_order($ali_order_id, 1, $buyer_email,$order_id);

                        //更新用户VIP状态
                        $userDao->up_ser_qq_info($order);
                        $email = new baseCore();
                        $email->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$order_id."付款",'有人购买QB ，单号'.$order_id."，通道支付宝");
                    }
                    echo "success";
                }
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "qb_alisdk_notify");
        }
    }

    public function qb_wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $userDao = new user_dao();
                $order = $userDao->get_qb_order($data['out_trade_no']);
                if(!$order){
                    $this->err_log('失败','qb_wxsdk_notify');
                    echo "fail";
                }else{
                    if($order['status']!=2){
                        //更新订单状态
                        $userDao->up_qb_order($data['transaction_id'], 1, $data['openid'],$data['out_trade_no']);

                        //更新用户VIP状态
                        $userDao->up_ser_qq_info($order);
                        $email = new baseCore();
                        $email->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$order_id."付款",'有人购买QB ，单号'.$order_id."，通道支付宝");
                    }
                    echo "success";
                }
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "qb_wxsdk_notify");
        }
    }



}