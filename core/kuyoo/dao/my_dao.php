<?php
COMMON('dao','randomUtils');
class my_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }

    public function get_my_orders($params,$user_id,$page){
        $this->limit_sql="select o.* from orders as o inner join products as p on o.product_id=p.id where  o.buyer_id=? ";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql= $this->limit_sql." and o.game_id=".$params['game_id'];
        }
        if($params['game_id'] && $params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql= $this->limit_sql." and o.serv_id=".$params['serv_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            $this->limit_sql=$this->limit_sql." and o.status=".$params['status'];
        }
        $this->limit_sql.=" order by id desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_my_coupon($user_id,$page,$params){
        $this->limit_sql = "select b.name,b.type,b.discount,b.total_amount,b.discount_amount,a.start_time,a.end_time,a.use_time
                            from coupon_user_log_tb  as a inner join coupon_tb as b on a.coupon_id =b.id
                            where a.user_id = ? and  a.end_time is not NULL ";
        if($params == "1"){
            $this->limit_sql .=" and a.use_time > 0";
        }else if($params == "2"){
            $this->limit_sql .=" and a.end_time <".time()." and a.use_time is null";
        }
        $this->limit_sql .=" order by a.use_time,end_time desc ";
        $this->params=array($user_id);
        $this->doLimitResultList($page,6);
        return $this->result;
    }

    public function get_order_detail($id,$user_id){
        $this->sql = "select g.game_name,o.*,p.intro,p.type,a.qq as service_qq from orders as o inner join game as g on o.game_id=g.id 
                      inner join products as p on o.product_id=p.id inner join admins as a on o.service_id=a.id 
                      where o.id=? and o.buyer_id=?";
        //$this->sql = "select g.game_name,p.intro,p.type,o.* from orders as o inner join products as p on
        // o.product_id=p.id inner join game as g on o.game_id=g.id where o.id=? and o.buyer_id=?";
        $this->params=array($id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_order_cancel($id, $user_id, $order){
        $this->sql = "update orders set status=9 where id=? and buyer_id=?";
        $this->params = array($id,$user_id);
        $this->doExecute();

        if($order['type']>3){
            $this->sql = "update products set is_pub=1,stock=stock+? where id=?";
            $this->params = array($order['amount'], $id);
            $this->doExecute();
        }
    }

    public function get_my_msgs($user_id,$page,$params){
        $this->limit_sql="select * from messages where receiver_id=?";
        if($params['is_read']=="0"){
            $this->limit_sql= $this->limit_sql." and is_read=0";
        }else if($params['is_read']=="1"){
            $this->limit_sql= $this->limit_sql." and is_read=1";
        }
        $this->limit_sql= $this->limit_sql." order by id desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function upd_msgs_read($user_id,$id){
        $this->sql="update messages set is_read=1 where is_read=0 and receiver_id=? and id=?";
        $this->params=array($user_id,$id);
        $this->doExecute();
    }
    public function get_unread_msg_count($user_id){
        $this->sql="select count(1) as undread_num from messages where is_read=0 and receiver_id=?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result['undread_num'];
    }

    public function get_msgs_detail($id,$user_id){
        $this->sql="select * from messages where id=? and receiver_id=?";
        $this->params=array($id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_my_gifts($user_id,$page,$params){
        $this->limit_sql="select gm.game_name,cd.* from game_gifts cd inner join game gm on cd.game_id=gm.id where  cd.buyer_id=? and cd.is_use=1";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql= $this->limit_sql." and cd.game_id=".$params['game_id'];
        }
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }
}
?>