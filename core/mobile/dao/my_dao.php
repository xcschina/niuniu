<?php
COMMON('dao','randomUtils');
class my_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }

    public function  get_my_orders($params,$user_id,$page){
        $this->limit_sql="select o.* from orders as o inner join products as p on o.product_id=p.id where  buyer_id=? ";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql= $this->limit_sql." and o.game_id=".$params['game_id'];
        }
        if($params['game_id'] && $params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql= $this->limit_sql." and o.serv_id=".$params['serv_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            $this->limit_sql=$this->limit_sql." and o.status=".$params['status'];
        }
        $this->limit_sql=$this->limit_sql." order by o.id desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_qb_orders($params,$user_id,$page){
        $this->limit_sql="select * from qb_order where buyer_id=? ";
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            $this->limit_sql=$this->limit_sql." and status=".$params['status'];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function  get_order_detail($id,$user_id){
        $this->sql = "select g.game_name,o.*,p.title as title_desc,p.intro,p.type,a.qq as service_qq from orders as o inner join game as g on o.game_id=g.id
                      inner join products as p on o.product_id=p.id left join admins as a on o.service_id=a.id 
                      where o.id=? and o.buyer_id=?";
        //$this->sql="select g.game_name,t.* from game as g inner join (select p.intro,p.type,o.* from orders as o
        // inner join products as p on o.product_id=p.id where o.id =? and o.buyer_id=?)t on g.id=t.game_id";
        $this->params=array($id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_qb_order_detail($id,$user_id){
        $this->sql = "select qb.*,a.qq as service_qq from qb_order as qb left join admins as a on qb.service_id=a.id where qb.id=? and qb.buyer_id=?";
        $this->params=array($id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_qb_cancel($id,$user_id){
        $this->sql = "update qb_order set status=9 where id=? and buyer_id=?";
        $this->params=array($id,$user_id);
        $this->doExecute();
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
        }
        $this->limit_sql= $this->limit_sql." order by id desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_msgs_detail($id,$user_id){
       $this->sql="select * from messages where id=? and receiver_id=?";
        $this->params=array($id,$user_id);
        $this->doResult();
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

    public function get_gifts_name($id){
        $this->sql="select title from game_gift_info where id =?";
        $this->params=array($id);
        $this->doResult();
        return $this->result['title'];
    }

    public function  get_my_gifts($user_id,$page,$params){
        $this->limit_sql="select gm.game_name,cd.* from game_gifts cd inner join game gm on cd.game_id=gm.id where  cd.buyer_id=? and cd.is_use=1";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql= $this->limit_sql." and cd.game_id=".$params['game_id'];
        }
        $this->limit_sql = $this->limit_sql." order by cd.buy_time desc";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }
}
?>