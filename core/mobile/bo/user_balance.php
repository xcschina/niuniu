<?php
COMMON('baseCore', 'pageCore');
DAO('user_balance_dao');

class user_balance extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_balance_dao();
    }

    //订单修改
    public function user_order(){
        $orders = $this->DAO->get_order();
        if(!$orders){
            die('执行完毕');
        }
        foreach($orders as $key => $order){
            $product_info = $this->DAO->get_product($order['product_id']);
            //买家明细
            $buy_user_id  = $order['buyer_id'];
            $balance['order_id'] = $order['order_id'];
            $balance['pay_mode'] = 1;
            $balance['user_id']  = $buy_user_id;
            $balance['type']     = 1;
            $balance['status']   = 3;
            $balance['money']    = $order['money'];
            $this->DAO->add_user_balance_detail($balance);

            if($product_info['user_id'] > 1){
                $pay_lock = $this->DAO->get_user_lock($product_info['user_id'])['pay_lock'];
                if($pay_lock == 1){
                    $balance = $this->DAO->get_balance($product_info['user_id'])['balance'];
                    $word    = '订单号' . $order['order_id'] . '余额' . $balance;
                    $this->err_log($word, 'user_balance');
                }
                $user_balance_info = $this->DAO->get_user_balance_by_order_id($order['order_id']);
                if($user_balance_info && $pay_lock==0){
                    $this->DAO->update_user_lock(1, $product_info['user_id']);
                    $this->DAO->update_user_balance($order['pay_money'], $product_info['user_id']);
                    //添加卖家明细
                    $sell_balance['order_id'] = $order['order_id'];
                    $sell_balance['pay_mode'] = 1;
                    $sell_balance['user_id']  = $product_info['user_id']['user_id'];
                    $sell_balance['type']     = 2;
                    $sell_balance['status']   = 3;
                    $sell_balance['money']    = $order['money'];
                    $this->DAO->add_user_balance_detail($sell_balance);
                    $this->DAO->update_user_lock(0, $product_info['user_id']);
                    $balance      = $this->DAO->get_balance($product_info['user_id'])['balance'];
                    $success_word = '成功订单' . $order['order_id'] . '金额' . $balance;
                    $this->err_log($success_word, 'user_balance');
                } else{
                    $this->err_log($order['order_id'] . '查询明细出错', 'user_balance');
                }

            }
        }
    }

    //用户充值
    public function user_recharge(){
        $user_balance_list = $this->DAO->get_user_balance_by_type();
        if(!$user_balance_list){
            die('执行完毕');
        }
        foreach($user_balance_list as $key => $user_balance){
            $pay_lock = $this->DAO->get_user_lock($user_balance['user_id'])['pay_lock'];
            if($pay_lock==0){
                $this->DAO->update_user_lock(1, $user_balance['user_id']);
                $this->DAO->update_user_balance($user_balance['money'], $user_balance['user_id']);
                //更新明细状态
                $this->DAO->update_user_balance_status(3, $user_balance['order_id']);
                $this->DAO->update_user_lock(0, $user_balance['user_id']);
            }

        }
    }

}