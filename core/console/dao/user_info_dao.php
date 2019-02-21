<?php
COMMON('dao');
class user_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_user_info_list($params,$page,$code=''){
        $count_sql = "select count(*) as count_no from user_info where 1=1";
        $this->limit_sql="select * from user_info where 1=1";
        if(!empty($code)){
            $this->limit_sql=$this->limit_sql." and code ='".$code."'";
            $count_sql .= "and code ='".$code."'";
        }
        if($params['user_id'] && is_numeric($params['user_id'] )){
            $this->limit_sql=$this->limit_sql." and user_id =".$params['user_id'];
            $count_sql .= " and user_id =".$params['user_id'];
        }
        if($params['nick_name']){
            $this->limit_sql=$this->limit_sql." and nick_name like '%".$params['nick_name']."%'";
            $count_sql .= " and nick_name like '%".$params['nick_name']."%'";
        }
        if($params['user_name']){
            $this->limit_sql=$this->limit_sql." and user_name like '%".$params['user_name']."%'";
            $count_sql .= " and user_name like '%".$params['user_name']."%'";
        }
        if($params['id_number']){
            $this->limit_sql=$this->limit_sql." and id_number ='".$params['id_number']."'";
            $count_sql .= " and id_number ='".$params['id_number']."'";
        }
        if($params['mobile']){
            $this->limit_sql=$this->limit_sql." and mobile= '".$params['mobile']."'";
            $count_sql .= " and mobile= '".$params['mobile']."'";
        }
        if($params['email']){
            $this->limit_sql=$this->limit_sql." and email = '".$params['email']."'";
            $count_sql .= " and email = '".$params['email']."'";
        }
        if($params['sex'] && is_numeric($params['sex'])){
            $this->limit_sql=$this->limit_sql." and sex = ".$params['sex'];
            $count_sql .= " and sex = ".$params['sex'];
        }
        if($params['reg_from'] && is_numeric($params['reg_from'])){
            $this->limit_sql=$this->limit_sql." and reg_from = ".$params['reg_from'];
            $count_sql .= " and reg_from = ".$params['reg_from'];
        }
        if($params['group_id'] && is_numeric($params['group_id'])){
            $this->limit_sql .= " and user_group = ".$params['group_id'];
            $count_sql .= " and user_group = ".$params['group_id'];
        }
        if($params['reg_time'] && $params['reg_time2']){
            $this->limit_sql .= " and reg_time>=".$params['reg_time']." and reg_time<=".$params['reg_time2'];
            $count_sql .= " and reg_time>=".$params['reg_time']." and reg_time<=".$params['reg_time2'];
        }elseif($params['reg_time'] && !$params['reg_time2']) {
            $this->limit_sql .= " and reg_time>=".$params['reg_time'];
            $count_sql .= " and reg_time>=".$params['reg_time'];
        }elseif(!$params['reg_time'] && $params['reg_time2']) {
            $this->limit_sql .= " and reg_time<=".$params['reg_time2'];
            $count_sql .= " and reg_time<=".$params['reg_time2'];
        }
        if((int)$params['has_id_number']==2){
            $this->limit_sql .= " and id_number<>''";
            $count_sql .= " and id_number<>''";
        }elseif((int)$params['has_id_number']==1){
            $this->limit_sql .= " and id_number=''";
            $count_sql .= " and id_number=''";
        }

        $this->limit_sql=$this->limit_sql." order by user_id desc";
        $this->sql = $count_sql." order by user_id desc";
//        $this->doLimitResultList($page);
        $this->doResult();
        $no = $this->result;
        $this->doLimitResultList_OP($page,$no['count_no']);
        return $this->result;
    }

    public function get_channel_list(){
        $this->sql="select id,channel_name from channels";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_list(){
        $this->sql = "select * from user_info where id_number != ''";
        $this->doResultList();
        return $this->result;
    }

//    public function update_user_age($age,$user_id){
//        $this->sql = "update user_info set age = ? where user_id = ?";
//        $this->params = array($age,$user_id);
//        $this->doExecute();
//    }

    public function get_pay_user_list($params,$page){
        $this->limit_sql="SELECT b.*,count(0) as count,sum(a.pay_money) as pay FROM `orders` as a inner join user_info as b on a.buyer_id=b.user_id where status=2";
        if($params['user_id'] && is_numeric($params['user_id'] )){
            $this->limit_sql=$this->limit_sql." and b.user_id =".$params['user_id'];
        }
        if($params['nick_name']){
            $this->limit_sql=$this->limit_sql." and b.nick_name like '%".$params['nick_name']."%'";
        }
        if($params['user_name']){
            $this->limit_sql=$this->limit_sql." and b.user_name like '%".$params['user_name']."%'";
        }
        if($params['vip']){
            $this->limit_sql=$this->limit_sql." and b.vip_level ='".$params['vip']."'";
        }
        if($params['id_number']){
            $this->limit_sql=$this->limit_sql." and b.id_number ='".$params['id_number']."'";
        }
        if($params['mobile']){
            $this->limit_sql=$this->limit_sql." and b.mobile= '".$params['mobile']."'";
        }
        if($params['area']){
            $this->limit_sql=$this->limit_sql." and b.area like '%".$params['area']."%'";
        }
        if($params['age']){
            $this->limit_sql=$this->limit_sql." and b.age = '".$params['age']."'";
        }
        if($params['email']){
            $this->limit_sql=$this->limit_sql." and b.email = '".$params['email']."'";
        }
        if($params['sex'] && is_numeric($params['sex'])){
            $this->limit_sql=$this->limit_sql." and b.sex = ".$params['sex'];
        }
        if((int)$params['has_id_number']==2){
            $this->limit_sql .= " and b.id_number<>''";
        }elseif((int)$params['has_id_number']==1){
            $this->limit_sql .= " and b.id_number=''";
        }
        $this->limit_sql=$this->limit_sql." group by a.buyer_id HAVING 1=1";

        if($params['count_pay1'] && is_numeric($params['count_pay1'])){
            $this->limit_sql=$this->limit_sql." and pay >=".$params['count_pay1'];
        }
        if($params['count_pay2'] && is_numeric($params['count_pay2'])){
            $this->limit_sql=$this->limit_sql." and pay <=".$params['count_pay2'];
        }
        if($params['count1'] && is_numeric($params['count1'])){
            $this->limit_sql=$this->limit_sql." and count >=".$params['count1'];
        }
        if($params['count2'] && is_numeric($params['count2'])){
            $this->limit_sql=$this->limit_sql." and count <=".$params['count2'];
        }
        if($params['unit_price1'] && is_numeric($params['unit_price1'])){
            $this->limit_sql=$this->limit_sql." and (pay/count) >=".$params['unit_price1'];
        }
        if($params['unit_price2'] && is_numeric($params['unit_price2'])){
            $this->limit_sql=$this->limit_sql." and (pay/count) <=".$params['unit_price2'];
        }
        $this->limit_sql=$this->limit_sql." order by pay desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_pay_list($page){
        $this->limit_sql="SELECT a.buyer_id,b.area,b.age,b.vip_level,b.reg_ip,b.id_number,count(0) as count,sum(a.pay_money) as pay FROM `orders` as a inner join user_info as b on a.buyer_id=b.user_id
        where status=2 and b.vip_level=0 and b.area is NULL and b.age=0  group by a.buyer_id order by pay desc";
        $this->doLimitResultList($page,20);
        return $this->result;
    }

    public function get_all_pay_count(){
        $this->sql="SELECT COUNT(0) as sum from (select sum(pay_money) AS pay from orders WHERE `status` = 2 GROUP BY buyer_id ORDER BY pay DESC)tb";
        $this->doResult();
        return $this->result;
    }

    public function get_login_log_list($params,$page){
        $this->limit_sql="select * from user_login_log where 1=1";
        if($params['login_time'] && $params['login_time2']){
            $this->limit_sql .= " and create_time>=".$params['login_time']." and create_time<=".$params['login_time2'];
        }elseif($params['login_time'] && !$params['login_time2']) {
            $this->limit_sql .= " and create_time>=".$params['login_time'];
        }elseif(!$params['login_time'] && $params['login_time2']) {
            $this->limit_sql .= " and create_time<=".$params['login_time2'];
        }
        if($params['status'] && is_numeric($params['status'])|| $params['status'] =='0'){
            $this->limit_sql=$this->limit_sql." and status =".$params['status'];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_operation_log_list($params,$page){
        $this->limit_sql="select * from user_operation_log where 1=1";
        if($params['user_id'] && is_numeric($params['user_id'] )){
            $this->limit_sql=$this->limit_sql." and user_id =".$params['user_id'];
        }
        if($params['op_type'] && is_numeric($params['op_type']) || $params['op_type'] =='0'){
            $this->limit_sql=$this->limit_sql." and op_type =".$params['op_type'];
        }
        if($params['op_results'] && is_numeric($params['op_results']) || $params['op_results'] =='0'){
            $this->limit_sql=$this->limit_sql." and op_results =".$params['op_results'];
        }

        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function updata_my_channel($params){
        $this->sql="updata * from user_operation_log where 1=1";
        $this->doLimitResultList();
        return $this->result;
    }

    // -----------------------
    // zbc
    // -----------------------

    public function get_user_groups(){
        $this->sql = 'select * from admin_groups where GroupName like \'%用户\'order by ID desc';
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info($id=0){
        $this->sql = 'select * from user_info where 1=1 and user_id=?';
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function set_user_group($user_id=0, $group_id=0){
        $this->sql = 'update user_info set user_group=? where user_id=?';
        $this->params = array($group_id, $user_id);
        $this->doExecute();
    }

//    public function update_pay_user_info($params){
//        $this->sql = 'update user_info set vip_level=?,age=?,area=? where user_id=?';
//        $this->params = array($params['vip_level'], $params['age'],$params['area'],$params['buyer_id']);
//        $this->doExecute();
//    }

    public function update_my_channel($params){
        $this->sql = 'update orders set game_channel=?,is_update=1 where order_id=?';
        $this->params = array($params['id'], $params['order_id']);
        $this->doExecute();
    }

    public function get_user_last_login_time($user_id=0){
        $this->sql = 'select create_time from user_login_log where 1=1 and status=1 and user_id=?';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result['create_time'];
    }

    public function count_user_total_pay($user_id=0){
        $this->sql = 'select sum(pay_money) as allmoney from orders where 1=1 and status=2 and is_del=0 and buyer_id=?';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result['allmoney'];
    }

    public function get_user_last_pay_order($user_id=0){
        $this->sql = 'select pay_money,pay_time from orders where 1=1 and status=2 and is_del=0 and buyer_id=? order by id desc limit 1';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_pay_games($user_id=0){
        $this->sql = 'select distinct g.game_name from orders as o left join game as g on o.game_id=g.id where o.status=2 and o.is_del=0 and o.buyer_id=?';
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_ch_pay($params,$user_id){
        $this->sql = 'SELECT SUM(pay_money) as sum FROM orders WHERE game_id=? and game_channel=? and game_user=? and service_id=? and buyer_id=?';
        $this->params = array($params['game_id'],$params['game_channel'],$params['game_user'],$params['service_id'],$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_attr_list($user_id=0,$page){
        $this->limit_sql = 'SELECT DISTINCT o.game_user,o.service_id,g.game_name,o.game_id,o.game_channel,c.channel_name,o.attr from orders as o left join game as g on o.game_id=g.id left join channels as c on o.game_channel=c.id
                      where o.status=2 and o.is_del=0 and  o.buyer_id ='.$user_id.' and o.attr<>"null" ORDER BY o.game_id desc';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_pay_games_info($user_id=0){
        $this->sql = 'select distinct g.game_name,o.game_channel,c.channel_name from orders as o left join game as g on o.game_id=g.id LEFT join channels as c on o.game_channel=c.id where o.status=2 and o.is_del=0 and o.buyer_id=?';
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }


    public function get_user_pay_list($params,$page){
       $sql = "select p.type,a.real_name,g.game_name,o.status,o.order_id,o.is_update,o.pay_money,o.pay_time,o.game_user,c.channel_name,c.id
               from orders as o left join game as g on o.game_id=g.id LEFT join channels as c on o.game_channel=c.id left join products as p on p.id=o.product_id left join admins as a on a.id=o.service_id where 1=1 and o.buyer_id=? and o.status=2";
        if($params['type']){
           $sql .= " and p.type=".$params['type'];
        }
        if($params['channel']){
            $sql .= " and o.game_channel=".$params['channel'];
        }
        if(trim($params['game'])){
           $sql .= " and g.game_name='".$params['game']."'";
        }
        if($params['pay_time'] && $params['pay_time2']){
           $sql .= " and pay_time>=".$params['pay_time']." and pay_time<=".$params['pay_time2'];
        }elseif($params['pay_time'] && !$params['pay_time2']) {
           $sql .= " and pay_time>=".$params['pay_time'];
        }elseif(!$params['pay_time'] && $params['pay_time2']) {
           $sql .= " and pay_time<=".$params['pay_time2'];
        }
        $sql .= " order by o.id desc";
        $this->limit_sql = $sql;
        $this->params = array($params['user_id']);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_login_list($params, $page){
        $sql = "select * from user_login_log where 1=1";
        if($params['user_id'] && is_numeric($params['user_id'])){
            $sql .= " and user_id =".$params['user_id'];
        }
        if($params['login_time'] && $params['login_time2']){
           $sql .= " and create_time>=".$params['login_time']." and create_time<=".$params['login_time2'];
        }elseif($params['login_time'] && !$params['login_time2']) {
           $sql .= " and create_time>=".$params['login_time'];
        }elseif(!$params['login_time'] && $params['login_time2']) {
           $sql .= " and create_time<=".$params['login_time2'];
        }
        if($params['status'] && is_numeric($params['status'])|| $params['status'] =='0'){
            $sql .= " and status =".$params['status'];
        }
        $sql .= " order by id desc";
        $this->limit_sql = $sql;
        $this->doLimitResultList($page);
        return $this->result;
    }

}

