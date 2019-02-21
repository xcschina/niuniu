<?php
COMMON('dao');
class orders_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //游戏列表
    public function get_game_list(){
        $this->sql="select * from game where is_del=0 and status=1";
        $this->doResultList();
        return $this->result;
    }
    //区服列表
    public function get_game_servs_list(){
        $this->sql="select * from game_servs ";
        $this->doResultList();
        return $this->result;
    }
    //获取渠道列表
    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }

    //商品信息
    public function get_product_info($id){
        $this->sql="select * from products where id =?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_shop_list(){
        $this->sql = 'select * from shop';
        $this->doResultList();
        return $this->result;
    }

    public function get_orders_list($params,$page){
        $this->limit_sql="select ord.*,pro.type,b.game_name,a.real_name from orders ord inner join products pro on ord.product_id=pro.id
                        inner join game as b on ord.game_id=b.id
                        left join admins as a on ord.service_id=a.id where 1=1 ";
        if($params['shop_id']){
            $this->limit_sql=$this->limit_sql." and ord.shop_id = '".$params['shop_id']."'";
        }
        if($params['order_id']){
            $this->limit_sql=$this->limit_sql." and ord.order_id = '".$params['order_id']."'";
        }
        if($params['user_id']){
            $this->limit_sql=$this->limit_sql." and ord.buyer_id = '".$params['user_id']."'";
        }
        if($params['game_user']){
            $this->limit_sql=$this->limit_sql." and ord.game_user = '".$params['game_user']."'";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and ord.game_id =".$params['game_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql=$this->limit_sql." and ord.serv_id =".$params['serv_id'];
        }
        if($params['game_channel'] && is_numeric($params['game_channel'])){
            $this->limit_sql=$this->limit_sql." and ord.game_channel =".$params['game_channel'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            if($params['status']=="-1"){
                $this->limit_sql=$this->limit_sql." and ord.is_del =2";
            }else{
                $this->limit_sql=$this->limit_sql." and ord.is_del = 0 and ord.status =".$params['status'];
            }
        }
        if($params['buy_time'] && $params['buy_time2']){
            $this->limit_sql=$this->limit_sql .=  " and ord.buy_time>=".$params['buy_time']." and ord.buy_time<=".$params['buy_time2'];
        }else if($params['time'] && !$params['time2']) {
            $this->limit_sql=$this->limit_sql.=  " and ord.buy_time>=".$params['buy_time'];
        } else if(!$params['time'] && $params['time2']) {
            $this->limit_sql=$this->limit_sql.=  " and ord.buy_time<=".$params['buy_time2'];
        }
        $this->limit_sql=$this->limit_sql." order by ord.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_order_detail($id){
        $this->sql="select a.*,b.game_name,c.title,c.type,gs.serv_name,ch.channel_name from orders as a left join game as b on a.game_id=b.id
            left join products as c on a.product_id=c.id left join game_servs as gs on a.serv_id=gs.id left join channels as ch
            on a.game_channel=ch.id where a.id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_tags($id){
        $this->sql="select tags from game where id= ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function isexist_game_user($game_user){
        $this->sql="select * from orders where game_user=?";
        $this->params=array($game_user);
        $this->doResult();
        return $this->result;
    }

    public function do_order_edit($params){
        $this->sql="update orders set game_user=?,game_pwd=?,status=2,pay_img=? where id=?";
        $this->params=array($params['game_user'],$params['game_pwd'],$params['pay_img'],$params['id']);
        $this->doExecute();
    }

    public function get_active_info($params){
        $this->sql="select * from weekactivity where id= ?";
        $this->params=array($params['activity_id']);
        $this->doResult();
        return $this->result;
    }

    public function upd_active_info($params){
        $this->sql="update weekactivity set repertory=repertory-1 where id=? ";
        $this->params=array($params['activity_id']);
        $this->doExecute();
    }

    public function do_order_finish($params){
        $this->sql="update orders set status=2,pay_img=?,ship_time=? where id=?";
        $this->params=array($params['pay_img'],strtotime("now"),$params['id']);
        $this->doExecute();
    }

    public function get_order_relation($order_id){
        $this->sql = "select * from orders_relation_tb where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function update_order_time($order_id){
        $this->sql = "update orders_relation_tb set finish_time=? where order_id=?";
        $this->params = array(time(),time(),$order_id);
        $this->doExecute();
    }

    public function do_order_refund($params){
        $this->sql="update orders set is_del=2,refund_img=?,status=? where id=?";
        $this->params=array($params['refund_img'],4,$params['id']);
        $this->doExecute();
    }

    public function do_gift_code_save($params){
        $this->sql="update orders set gift_code=? where id=?";
        $this->params=array($params['gift_code'],$params['id']);
        $this->doExecute();
    }

    public function get_artificial_orders_list($params,$page){
        $this->limit_sql="select * from artificial_orders where 1=1 ";
        if($params['order_id']){
            $this->limit_sql=$this->limit_sql." and order_id = '".$params['order_id']."'";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and game_id =".$params['game_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql=$this->limit_sql." and serv_id =".$params['serv_id'];
        }
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and channel_id =".$params['channel_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            $this->limit_sql=$this->limit_sql." and status =".$params['status'];
        }
        if($_SESSION["usr_id"] && $_SESSION["group"]&& $_SESSION["group"]!="admin"){
            $this->limit_sql=$this->limit_sql." and receive_id =".$_SESSION["usr_id"];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_artificia_orders_info($id){
        $this->sql="select * from artificial_orders where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function upd_artificia_orders_info($params){
        $this->sql="update artificial_orders set status=?,remark=?,op_time=? where id=?";
        $this->params=array($params['status'],$params['remark'],strtotime("now"),$params['id']);
        $this->doExecute();
    }

    public function add_order_logs($params){
        $this->sql="insert into order_logs(admin_id,add_time,do) values(?,?,?)";
        $this->params=array($params['admin_id'],strtotime("now"),$params['do']);
        $this->doInsert();
    }

    public function get_order_imgs($order_id,$type){
        $this->sql="select * from order_imgs where order_id=? and is_del=0 and `type`=?";
        $this->params=array($order_id,$type);
        $this->doResultList();
        return $this->result;
    }

    public function add_order_img($params){
        $this->sql="insert into order_imgs(order_id,img,admin_id)values(?,?,?)";
        $this->params=array($params['order_id'],$params['img'],$params['admin_id']);
        $this->doInsert();
    }

    public function del_order_img($id){
        $this->sql="update order_imgs set is_del=1 where id=?";
        $this->params=array($id);
        $this->doExecute();
    }

    public function upd_order_info($params){
        if($params['type']==1){
            $this->sql="update orders set service_id=?,serv_id=?,qq=?,tel=?,remarks=?,role_name=?,role_back_name=?,attr=? where id=?";
            $this->params=array($params['service_id'],$params['serv_id'],$params['qq'],$params['tel'],$params['remarks'],$params['role_name'],$params['role_back_name'],$params['attr'],$params['id']);
        }elseif($params['type']==2 || $params['type']==3){
            $this->sql="update orders set service_id=?,serv_id=?,qq=?,tel=?,remarks=?,game_user=?,game_pwd=?,role_name=? where id=?";
            $this->params=array($params['service_id'],$params['serv_id'],$params['qq'],$params['tel'],$params['remarks'],$params['game_user'],$params['game_pwd'],$params['role_name'],$params['id']);
        }else{
            $this->sql="update orders set service_id=?,serv_id=?,qq=?,tel=?,remarks=? where id=?";
            $this->params=array($params['service_id'],$params['serv_id'],$params['qq'],$params['tel'],$params['remarks'],$params['id']);
        }
        $this->doExecute();
    }

    public function all_orders_list($params){
        $this->sql="select ord.*,pro.title,pro.type,b.game_name,gs.serv_name from orders ord inner join products pro on ord.product_id=pro.id inner join game as b on ord.game_id=b.id left join game_servs as gs on gs.id=ord.serv_id where 1=1  ";
        if($params['shop_id']){
            $this->sql=$this->sql." and ord.shop_id = '".$params['shop_id']."'";
        }
        if($params['order_id']){
            $this->sql=$this->sql." and ord.order_id = '".$params['order_id']."'";
        }
        if($params['user_id']){
            $this->sql=$this->sql." and ord.buyer_id = '".$params['user_id']."'";
        }
        if($params['game_user']){
            $this->sql=$this->sql." and ord.game_user = '".$params['game_user']."'";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->sql=$this->sql." and ord.game_id =".$params['game_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->sql=$this->sql." and ord.serv_id =".$params['serv_id'];
        }
        if($params['game_channel'] && is_numeric($params['game_channel'])){
            $this->sql=$this->sql." and ord.game_channel =".$params['game_channel'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            if($params['status']=="-1"){
                $this->sql=$this->sql." and ord.is_del =2";
            }else{
                $this->sql=$this->sql." and ord.status =".$params['status'];
            }
        }
        if($params['buy_time'] && $params['buy_time2']){
            $this->sql=$this->sql .=  " and ord.buy_time>=".$params['buy_time']." and ord.buy_time<=".$params['buy_time2'];
        }else if($params['time'] && !$params['time2']) {
            $this->sql=$this->sql.=  " and ord.buy_time>=".$params['buy_time'];
        } else if(!$params['time'] && $params['time2']) {
            $this->sql=$this->sql.=  " and ord.buy_time<=".$params['buy_time2'];
        }

        $this->sql=$this->sql." order by ord.id desc";
        $this->doResultList();
        return $this->result;
    }
    
    public function get_game_ch_servs($game_id, $ch_id){
        $data = memcache_get($this->mmc,'game_servs'.$game_id."_".$ch_id);
        if(!$data){
            $this->sql = "SELECT * FROM game_servs WHERE game_id=? and ch_".$ch_id."=1 order by id desc";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_servs'.$game_id."_".$ch_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_services(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 order by last_service_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_orders_status($params){
        $this->sql = "update orders set status=? where id = ?";
        $this->params = array($params['status'],$params['id']);
        $this->doExecute();
    }
    //获取
    public function get_order_user_id($id){
        $this->sql = "select pro.user_id,ord.pay_money from orders ord inner join products pro on ord.product_id=pro.id where ord.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function add_user_balance_detail($params){
        $this->sql="insert into user_balance_detail(user_id,`type`,money,add_time)values(?,?,?,?)";
        $this->params=array($params['user_id'],2,$params['money'],time());
        $this->doInsert();
    }
    public function get_user_balance($user_id){
        $this->sql = "SELECT balance FROM user_info WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_user_balance($balance,$user_id){
        $this->sql = "update user_info set balance=? where user_id = ?";
        $this->params = array($balance,$user_id);
        $this->doExecute();
    }
}
