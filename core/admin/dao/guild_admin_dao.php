<?php
COMMON('niuniuDao');
class guild_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_user_info($user_id){
        $this->sql="select * from admins where id =?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_guild_list(){
        $this->sql="select * from admins where group_id=10 and is_del ='0'";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_guilds_list(){
        $this->sql="select * from admins where group_id=10 or group_id=1";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function update_pay_pwd($account, $id){
        $this->sql = "update admins set pay_pwd=? where id=?";
        $this->params = array(md5($account['password']), $id);
        $this->doExecute();
    }

    public function get_pay_log($user_id,$params,$page){
        $this->limit_sql="select * from guild_nnb_log where 1=1 ";
        if(!empty($user_id)){
            $this->limit_sql = $this->limit_sql. " and ( user_id =".$user_id." or parent_id=".$user_id." or guild_id=".$user_id.")";
        }
        if(!empty($params['start_time'])){
            $this->limit_sql = $this->limit_sql. " and add_time >=".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->limit_sql = $this->limit_sql. " and add_time <=".strtotime($params['end_time']);
        }
        if(!empty($params['do']) || is_numeric($params['do'])){
            $this->limit_sql = $this->limit_sql. " and do =".$params['do'];
        }
        if($params['guild_id']){
            $this->limit_sql .= " and guild_id = ".$params['guild_id'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and user_id = ".$params['user_id'];
        }
        if($params['parent_id']){
            $this->limit_sql .= " and parent_id = ".$params['parent_id'];
        }
        $this->limit_sql = $this->limit_sql. " ORDER BY id DESC ";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_pay_log_info($id){
        $this->sql="select * from guild_nnb_log where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_export_log($user_id,$params){
        $this->sql="select * from guild_nnb_log where 1=1 ";
        if(!empty($user_id)){
            $this->sql = $this->sql. " and ( user_id =".$user_id." or parent_id=".$user_id." or guild_id=".$user_id.")";
        }
        if(!empty($params['start_time'])){
            $this->sql = $this->sql. " and add_time >=".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->sql = $this->sql. " and add_time <=".strtotime($params['end_time']);
        }
        if(!empty($params['do']) || is_numeric($params['do'])){
            $this->sql = $this->sql. " and do =".$params['do'];
        }
        if($params['guild_id']){
            $this->sql .= " and guild_id = ".$params['guild_id'];
        }
        if($params['user_id']){
            $this->sql .= " and user_id = ".$params['user_id'];
        }
        if($params['parent_id']){
            $this->sql .= " and parent_id = ".$params['parent_id'];
        }
        $this->sql = $this->sql. " ORDER BY id DESC ";
        $this->doResultList();
        return $this->result;
    }

    public function in_this_guild($son_id,$parent_id){
        $this->sql="select * from admins where id =? and (p1=? or p2=?)";
        $this->params = array($son_id,$parent_id,$parent_id);
        $this->doResult();
        return $this->result;
    }

    public function user_in_guild($son_id,$code){
        $this->sql="select * from `66173`.user_info where user_id =".$son_id." and channel in ( ".$code." )";
        $this->doResult();
        return $this->result;
    }

    public function get_account_list($params,$page){
        $this->limit_sql="select a.*,b.ch_name from admins as a inner join admin_groups as b on a.group_id=b.id where a.group_id=10 and a.is_del='0'";
        if($params['guild']) {
            $this->limit_sql = $this->limit_sql . " and a.user_code='" . $params['guild']."'";
        }
        if($params['type']== 1){
            $this->limit_sql = $this->limit_sql . " and a.p1 ='0' and a.p2 ='0'";
        }elseif($params['type']== 2){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 ='0'";
        }elseif($params['type']== 3){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 !='0'";
        }
        $this->limit_sql=$this->limit_sql." order by a.id asc ";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_account_by_group($usr_id,$params,$page){
        $this->limit_sql = "select a.*,b.ch_name from admins as a inner join admin_groups as b on a.group_id=b.id where a.group_id=10 and a.is_del='0'";
        if($params['guild']){
            $this->limit_sql = $this->limit_sql . " and a.user_code='" . $params['guild']."'";
        }
        if($params['type']== 2){
            $this->limit_sql = $this->limit_sql . " and a.p2 ='0' and a.p1 = ".$usr_id;
        }elseif($params['type']== 3){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 = ".$usr_id;
        }else{
            $this->limit_sql = $this->limit_sql. " and a.p1=".$usr_id." or a.p2=".$usr_id ;
        }
        $this->limit_sql = $this->limit_sql. " order by a.id asc ";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_guild_all_son($usr_id){
        $this->sql = "select * from admins where group_id=10 and id=? or p1=? or p2=? order by id asc";
        $this->params = array($usr_id,$usr_id,$usr_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_son_count($user_id){
        $this->sql="select cast(sum(nnb) as DECIMAL(14,2)) AS sum from admins where p1=? and group_id=10 and is_del='0'";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_lower_count($user_id){
        $this->sql="select cast(sum(nnb) as DECIMAL(14,2)) as sum from admins where p2=? and group_id=10 and is_del='0'";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_guild_count($case){
        $this->sql = "select cast(sum(nnb) as DECIMAL(14,2)) as sum from admins where group_id=10 and is_del='0'";
        if($case=="1"){
            $this->sql = $this->sql . " and p1='0' and p2='0' ";
        }elseif($case=="2"){
            $this->sql = $this->sql . " and p1!='0' and p2='0' ";
        }elseif($case=="3"){
            $this->sql = $this->sql . " and p1!='0' and p2!='0' ";
        }
        $this->params = array();
        $this->doResult();
        return $this->result;
    }

    public function get_recharge_list($page,$params,$ids=""){
        $this->limit_sql="select * from recharge_record_tb where 1=1 ";
        if(!empty($ids)){
            $this->limit_sql.=" and user_id in (".$ids.")";
        }
        if(!empty($params['start_time'])){
            $this->limit_sql = $this->limit_sql. " and update_time >=".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->limit_sql = $this->limit_sql. " and update_time <=".strtotime($params['end_time']);
        }
        if(!empty($params['status']) || is_numeric($params['status'])){
            $this->limit_sql = $this->limit_sql. " and status =".$params['status'];
        }
        $this->limit_sql.=" order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_export_recharge_list($params,$ids=""){
        $this->sql="select * from recharge_record_tb where 1=1 ";
        if(!empty($ids)){
            $this->sql.=" and user_id in (".$ids.")";
        }
        if(!empty($params['start_time'])){
            $this->sql = $this->sql. " and update_time >=".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->sql = $this->sql. " and update_time <=".strtotime($params['end_time']);
        }
        if(!empty($params['status']) || is_numeric($params['status'])){
            $this->sql = $this->sql. " and status =".$params['status'];
        }
        $this->sql.=" order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_recharge_record($id){
        $this->sql="select * from recharge_record_tb where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function guild_lock($id,$num){
        $this->sql = "update admins set nnb_lock=?,nnb_last_time=? where id=?";
        $this->params = array($num,time(),$id);
        $this->doExecute();
    }

    public function user_lock($id,$num){
        $this->sql = "update `66173`.user_info set nnb_lock=? where user_id=?";
        $this->params = array($num,$id);
        $this->doExecute();
    }

    public function update_guild_nnb($nnb,$id){
        $this->sql = "update admins set nnb=?,nnb_last_time=? where id=?";
        $this->params = array($nnb,time(),$id);
        $this->doExecute();
    }

    public function update_user_nnb($nnb,$id){
        $this->sql = "update `66173`.user_info set nnb=? where user_id=?";
        $this->params = array($nnb,$id);
        $this->doExecute();
    }

    public function insert_guild_nnb_log($guid,$params,$do){
        $this->sql = 'insert into guild_nnb_log(guid,parent_id,guild_id,do,amount,add_time,remarks) values(?,?,?,?,?,?,?)';
        $this->params = array($guid,$params['parent_id'],$params['son_id'],$do,$params['amount'],time(),$params['remarks']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_guild_nnb_log($revoke_id,$id){
        $this->sql = "update guild_nnb_log set is_revoke=?,revoke_id=? where id=? ";
        $this->params = array(1,$revoke_id,$id);
        $this->doExecute();
    }

    public function insert_user_nnb_log($guid,$params,$do){
        $this->sql = 'insert into guild_nnb_log(guid,parent_id,user_id,do,amount,add_time,remarks) values(?,?,?,?,?,?,?)';
        $this->params = array($guid,$params['parent_id'],$params['son_id'],$do,$params['amount'],time(),$params['remarks']);
        $this->doInsert();
    }

    public function insert_recharge_record($img,$data){
        $this->sql = 'insert into recharge_record_tb(user_id,amount,`img`,`status`,remarks,add_time,update_time) values(?,?,?,?,?,?,?)';
        $this->params = array($data['user_id'],$data['amount'],$img,1,$data['remarks'],time(),time());
        $this->doInsert();
    }

    public function update_recharge_record($data,$operator_id,$id){
        $this->sql = "update recharge_record_tb set status=?,reason=?,operator_id=?,update_time=? where id=?";
        $this->params = array($data['status'],$data['reason'],$operator_id,time(),$id);
        $this->doExecute();
    }

    public function get_user_list($page,$params){
        $count_sql = "SELECT COUNT(*) as count_no FROM `66173`.user_info WHERE 1=1";
        $this->limit_sql = 'select * from `66173`.user_info WHERE 1=1';
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql." and user_id =".$params['user_id'];
            $count_sql = $count_sql." and user_id =".$params['user_id'];
        }
        if($params['guild_code']){
            $this->limit_sql = $this->limit_sql." and channel in (".$params['guild_code'].")";
            $count_sql = $count_sql." and channel in (".$params['guild_code'].")";
        }
        $this->limit_sql = $this->limit_sql." order by nnb desc";
        $this->sql = $count_sql." order by nnb desc";
        $this->doResult();
        $no = $this->result;
        $this->doLimitResultList_OP($page,$no['count_no']);
//        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_data($user_id){
        $this->sql = 'select * from `66173`.user_info where user_id=?';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_son_guild_code($user_id){
        $this->sql = "select * from admins where group_id=10 and is_del='0' and (p1=? or p2=?)";
        $this->params = array($user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }


    public function get_guild_all_code($user_id){
        $this->sql = "select * from admins where group_id=10 and (id=? or p1=? or p2=?)";
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_pack_list($page,$params){
        $data = $this->mmc->get("pack_list".$page);
        if(!$data) {
            $this->limit_sql = "select a.*,b.real_name,g.app_name from guild_app_tb as a left join admins as b on a.guild_code = b.user_code left join apps as g on a.app_id = g.app_id where 1=1";
            if ($params['app_id']) {
                $this->limit_sql = $this->limit_sql . " and a.app_id = " . $params['app_id'];
            }
            if ($params['status'] && is_numeric($params['status']) || $params['status'] === "0") {
                $this->limit_sql = $this->limit_sql . " and a.status = " . $params['status'];
            }
            if ($params['guild_code']) {
                $this->limit_sql = $this->limit_sql . " and a.guild_code = '" . $params['guild_code'] . "'";
            }
            $this->limit_sql = $this->limit_sql . " order by a.status asc";
            $this->doLimitResultList($page);
            $data = $this->result;
            $this->mmc->set("pack_list".$page, $data, 1, 600);
        }
        return $data;
    }

    public function get_app_name(){
        $data = $this->mmc->get("get_app_name");
        if(!$data) {
            $this->sql = "select * from apps where app_id <>'5001'";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_app_name",$data,1,6000);
        }
        return $data;
    }

    public function get_app_name_all(){
        //$data = $this->mmc->get("get_app_name_all");
        if(!$data) {
            $this->sql = "select * from apps ";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_app_name_all",$data,1,6000);
        }
        return $data;
    }

    public function update_pack_status($id){
        $this->sql = "update guild_app_tb set status = '4' where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_guild_app_info($id){
        $this->sql = "select * from guild_app_tb where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_guild_app_type($id){
        $this->sql = "update guild_app_tb set type = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_user_revoke_log($s_time,$e_time,$condition,$info,$page){
        //缓存处理
        $this->limit_sql = "SELECT a.id,a.order_id,a.operation_type,c.account,b.user_id,a.nnb_revoke,a.time_revoke,a.dd_id,a.revoke_reason 
        FROM user_revoke_log a INNER JOIN `66173`.user_info b ON a.user_id_revoke=b.user_id INNER JOIN admins c ON 
        a.arrive_id=c.id WHERE a.time_revoke>=? AND a.time_revoke<=?";
        $this->params = array(strtotime($s_time),strtotime($e_time));
        if ($info && $condition==1){
            $this->limit_sql .= " AND a.order_id=?";
            array_push($this->params,$info);
        }
        if ($info && $condition!=1){
            $this->limit_sql .= " AND a.user_id_revoke=?";
            array_push($this->params,$info);
        }
        $this->limit_sql .= " ORDER BY a.time_revoke DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_revoke_info($user_account){
        $this->sql = "SELECT user_id,nick_name,mobile,nnb,channel FROM `66173`.user_info WHERE user_id=? LIMIT 1";
        $this->params = array($user_account);
        $this->doResult();
        return $this->result;
    }

    public function get_user_super_info($channel){
        $this->sql = "SELECT * FROM admins WHERE user_code=? LIMIT 1";
        $this->params = array($channel);
        $this->doResult();
        return $this->result;
    }

    public function update_user_nnb_lock($nnb_lock,$id){
        $this->sql = "UPDATE `66173`.user_info SET nnb_lock =? WHERE user_id = ?";
        $this->params = array($nnb_lock,$id);
        $this->doExecute();
    }

    public function update_user_nnb_revoke($nnb,$id){
        $this->sql = "UPDATE `66173`.user_info SET nnb = nnb - ? WHERE user_id = ?";
        $this->params = array($nnb,$id);
        $this->doExecute();
    }

    public function update_arrive_nnb_lock($nnb_lock,$id){
        $this->sql = "UPDATE admins SET nnb_lock =? WHERE id = ?";
        $this->params = array($nnb_lock,$id);
        $this->doExecute();
    }

    public function update_arrive_nnb($nnb,$id){
        $this->sql = "UPDATE admins SET nnb = nnb + ? WHERE id = ?";
        $this->params = array($nnb,$id);
        $this->doExecute();
    }

    public function insert_revoke_log($params){
        $this->sql = "INSERT INTO user_revoke_log(order_id,arrive_id,nnb_revoke,user_id_revoke,operation_id,
                    time_revoke,dd_id,revoke_reason,user_money,arrive_money)VALUES(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['arrive_id'],$params['nnb_revoke'],$params['user_id_revoke'],
            $params['operation_id'],$params['time_revoke'],$params['dd_id'],$params['revoke_reason'],$params['user_money'],$params['arrive_money']);
        $this->doInsert();
    }

    public function get_revoke_log_details($id){
        $this->sql = "SELECT * FROM user_revoke_log WHERE id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }


    public function get_sup_pay_money($page,$start_time,$end_time,$id=''){
        if ($id){
            $id_sql = "AND c.id=".$id;
        }else{
            $id_sql = '';
        }
        $time_sql="AND a.pay_time >=".strtotime($start_time)." AND a.pay_time<=".strtotime($end_time);
        $this->limit_sql = "SELECT c.id,c.real_name,d.money,e.count_no FROM admins c LEFT JOIN 
                        (
                        SELECT sum(pay_money) AS money,groupid FROM (
                        select a.pay_money,d.id AS groupid from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid INNER JOIN admins d
                        ON c.channel=d.user_code WHERE a.status=2 AND d.p1=0 AND d.group_id=10 AND b.app_id<>5001 ".$time_sql."
                        UNION ALL 
                        (
                        select a.pay_money,d.p1 AS groupid from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid INNER JOIN admins d
                        ON c.channel=d.user_code WHERE a.status=2 AND d.p1<>0 AND d.p2=0 AND d.group_id=10 AND b.app_id<>5001 ".$time_sql."
                        )
                        UNION ALL(
                        select a.pay_money,d.p2 AS groupid from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid INNER JOIN admins d
                        ON c.channel=d.user_code WHERE a.status=2 AND d.p1<>0 AND d.p2<>0 AND d.group_id=10 AND b.app_id<>5001 ".$time_sql."
                        )
                        ) tb  GROUP BY groupid
                        ) d ON c.id=d.groupid INNER JOIN (
                        SELECT a.id,count(b.id) AS count_no FROM admins a LEFT JOIN admins b ON 
                        a.id=b.p1 WHERE a.group_id=10 AND a.p1=0 GROUP BY a.id
                        ) e ON c.id=e.id 
                        WHERE c.group_id=10 AND c.p1=0 ".$id_sql." ORDER BY d.money DESC,c.id DESC";
        $this->params = array();
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_sub_pay_money($page,$id,$start_time,$end_time,$sub_id=''){
        if ($sub_id){
            $id_sql = "AND c.id=".$sub_id;
        }else{
            $id_sql = '';
        }
        $time_sql="AND a.pay_time >=".strtotime($start_time)." AND a.pay_time<=".strtotime($end_time);
        $this->limit_sql = "SELECT c.id,c.real_name,d.money,e.count_no FROM admins c LEFT JOIN 
                        (
                        SELECT sum(pay_money) AS money,groupid FROM (
                        select a.pay_money,d.id AS groupid from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid INNER JOIN admins d
                        ON c.channel=d.user_code WHERE a.status=2 AND d.p1=? AND d.p2=0 AND d.group_id=10 AND b.app_id<>5001 ".$time_sql."
                        UNION ALL(
                        select a.pay_money,d.p1 AS groupid from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid INNER JOIN admins d
                        ON c.channel=d.user_code WHERE a.status=2 AND d.p2=? AND d.group_id=10 AND b.app_id<>5001 ".$time_sql."
                        )
                        ) tb  GROUP BY groupid
                        ) d ON c.id=d.groupid INNER JOIN (
                        SELECT a.id,a.real_name,count(b.id) AS count_no FROM admins a LEFT JOIN admins b 
                        ON a.id=b.p1 WHERE a.p2=0 AND a.p1=? AND a.group_id=10 GROUP BY a.id
                        ) e ON c.id=e.id 
                        WHERE c.group_id=10 AND c.p1=? ".$id_sql." ORDER BY d.money DESC,c.id DESC";
        $this->params = array($id,$id,$id,$id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_child_pay_money($page,$id,$start_time,$end_time,$child_id=''){
        if ($child_id){
            $id_sql = 'AND a.id='.$child_id;
        }else{
            $id_sql = '';
        }
        $time_sql="AND a.pay_time >=".strtotime($start_time)." AND a.pay_time<=".strtotime($end_time);
        $this->limit_sql = "SELECT  a.real_name,b.money,a.id FROM admins a LEFT JOIN(
                        select sum(a.pay_money) as money,d.real_name,d.id from orders as a 
                        inner join apps as b on a.app_id=b.app_id left join user_apptb as c on a.app_id=c.app_id and 
                        a.buyer_id=c.userid INNER JOIN admins d ON c.channel=d.user_code  WHERE a.status=2 AND d.p1=? 
                        AND d.p2<>0 AND d.group_id=10 AND b.app_id<>5001 ".$time_sql." GROUP BY d.id
                        ) b ON a.id=b.id 
                        WHERE a.p1=? AND a.p2<>0 AND a.group_id=10 ".$id_sql." ORDER BY b.money DESC,a.id DESC";
        $this->params = array($id,$id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_apps_pay_money($page,$start_time,$end_time,$appid_list=''){
        if ($appid_list){
            $appid_sql = "AND c.app_id IN(".$appid_list.")";
        }else{
            $appid_sql = '';
        }
        $time_sql="AND a.pay_time >=".strtotime($start_time)." AND a.pay_time<".strtotime($end_time);
        $this->limit_sql = "SELECT c.app_id,c.app_name,a.channel_no,a.money_all FROM  apps c LEFT JOIN(
                        SELECT tb.app_name,tb.channel_no,tb.money_all,tb.app_id  FROM (
                        SELECT tb1.app_name,count(1) AS channel_no,sum(tb1.money) AS money_all,tb1.app_id FROM 
                        (
                        select sum(a.pay_money) AS money,b.app_id,b.app_name,c.channel from orders as a 
                        inner join apps as b on a.app_id=b.app_id left join user_apptb as c on a.app_id=c.app_id
                        and a.buyer_id=c.userid WHERE a.status=2 AND c.channel IS NOT NULL AND b.app_id<>5001 ".$time_sql." GROUP BY a.app_id,c.channel
                        )tb1  GROUP BY tb1.app_id 
                        ) tb
                        )a ON c.app_id=a.app_id WHERE c.app_id<>5001 ".$appid_sql." ORDER BY a.money_all DESC,c.app_id DESC";
        $this->params = array();
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_channel_pay_money($page,$app_id,$start_time,$end_time){
        $time_sql="AND a.pay_time >=".strtotime($start_time)." AND a.pay_time<".strtotime($end_time);
        $this->limit_sql = "SELECT tb1.money,tb1.app_id,tb1.app_name,tb1.channel  FROM (
                        select sum(a.pay_money) AS money,b.app_id,b.app_name,c.channel from orders as a inner join apps as b on a.app_id=b.app_id
                        left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid
                        WHERE a.status=2 AND c.channel IS NOT NULL AND b.app_id=? ".$time_sql." GROUP BY a.app_id,c.channel 
                        )tb1 ORDER BY money DESC,tb1.channel DESC";
        $this->params = array($app_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_nd_app_user($page,$params){
        $this->limit_sql = "SELECT n.*,a.app_name FROM nd_user_info n 
                          INNER JOIN apps a ON n.app_id=a.app_id INNER JOIN `66173`.user_info u ON n.user_id=u.user_id WHERE 1=1";
        if ($params['app_id'])
            $this->limit_sql.=" AND n.app_id=".$params['app_id'];
        if ($params['user_id'])
            $this->limit_sql.=" AND n.user_id=".$params['user_id'];
        if ($params['guild_channels'])
            $this->limit_sql.=" AND u.channel IN (".$params['guild_channels'].")";
        $this->limit_sql.=" ORDER BY n.update_time DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_nd_user($user_id,$app_id){
        $this->sql = "SELECT * FROM nd_user_info WHERE user_id=? AND app_id=?";
        $this->params = array($user_id,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_nd_user($app_id,$user_id,$nd_num){
        $this->sql = "INSERT INTO nd_user_info(app_id,user_id,nd_num,nd_lock,add_time,update_time)VALUES(?,?,?,0,?,?)";
        $this->params = array($app_id,$user_id,$nd_num,time(),time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_nd_user($nd_num,$app_id,$user_id){
        $this->sql = "UPDATE nd_user_info SET nd_num=?,update_time=".time()." WHERE app_id=? AND user_id=?";
        $this->params = array($nd_num,$app_id,$user_id);
        $this->doExecute();
    }

    public function nd_user_lock($nd_lock,$app_id,$user_id){
        $this->sql = "UPDATE nd_user_info SET nd_lock=? WHERE app_id=? AND user_id=?";
        $this->params = array($nd_lock,$app_id,$user_id);
        $this->doExecute();
    }

    public function insert_nd_user_charge_log($orders,$user_id,$app_id,$nnb_num,$nd_num,$nd_discount,$order_type,$guild_id){
        $this->sql = "INSERT INTO nd_operation_log(orders,user_id,app_id,do_type,nnb_num,nd_num,nd_discount,order_type,
                    guild_id,add_time)VALUES(?,?,?,1,?,?,?,?,?,?)";
        $this->params = array($orders,$user_id,$app_id,$nnb_num,$nd_num,$nd_discount,$order_type,$guild_id,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_nd_user_charge_log($order_type,$id){
        $this->sql = "UPDATE nd_operation_log SET order_type=?,add_time=?  WHERE id=?";
        $this->params = array($order_type,time(),$id);
        $this->doExecute();
    }

    public function update_nd_user_frozen($nd_lock,$app_id,$user_id){
        $this->sql = "UPDATE nd_user_info SET nd_lock=?,update_time=? WHERE app_id=? AND user_id=?";
        $this->params = array($nd_lock,time(),$app_id,$user_id);
        $this->doExecute();
    }

    public function insert_nd_user_frozen_log($orders,$user_id,$app_id,$do_type,$guild_id){
        $this->sql = "INSERT INTO nd_operation_log(orders,user_id,app_id,do_type,order_type,
                    guild_id,add_time)VALUES(?,?,?,?,2,?,?)";
        $this->params = array($orders,$user_id,$app_id,$do_type,$guild_id,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_nd_operation_log($page,$params){
        $this->limit_sql = "SELECT n.*,a.app_name FROM nd_operation_log n 
                          INNER JOIN apps a ON n.app_id=a.app_id INNER JOIN `66173`.user_info b ON n.user_id=b.user_id WHERE 1=1";
        if ($params['app_id'])
            $this->limit_sql.=" AND n.app_id=".$params['app_id'];
        if ($params['user_id'])
            $this->limit_sql.=" AND n.user_id=".$params['user_id'];
        if ($params['guild_channels']){
            $this->limit_sql.=" AND b.channel IN(".$params['guild_channels'].")";
        }
        if($params['orders']){
            $this->limit_sql .= " AND n.orders = '".$params['orders']."'";
        }
        if($params['do_type']){
            $this->limit_sql .= " AND n.do_type = ".$params['do_type'];
        }
        if($params['guild_id']){
            $this->limit_sql .=" AND n.guild_id = ".$params['guild_id'];
        }
        if($params['order_type'] == '-1'){
            $this->limit_sql .= " AND n.order_type NOT in (0,1,2)";
        }else if($params['order_type'] && is_numeric($params['order_type']) || $params['order_type'] === '0'){
            $this->limit_sql .= " AND n.order_type = ".$params['order_type'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->limit_sql .= " AND n.add_time >= ".strtotime($params['start_time'])." AND n.add_time <= ".strtotime($params['end_time']);
        }elseif($params['start_time'] && !$params['end_time']){
            $this->limit_sql .= " AND n.add_time >= ".strtotime($params['start_time']);
        }elseif(!$params['start_time'] && $params['end_time']){
            $this->limit_sql .= " AND n.add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " ORDER BY n.add_time DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_export_nd_operation_log($params){
        $this->sql = "SELECT n.*,a.app_name FROM nd_operation_log n 
                          INNER JOIN apps a ON n.app_id=a.app_id INNER JOIN `66173`.user_info b ON n.user_id=b.user_id WHERE 1=1";
//        $this->sql = "SELECT n.*,a.app_name FROM nd_operation_log n
//                  INNER JOIN apps a ON n.app_id=a.app_id INNER JOIN admins b ON n.guild_id=b.id WHERE 1=1";
        if ($params['app_id'])
            $this->sql.=" AND n.app_id=".$params['app_id'];
        if ($params['user_id'])
            $this->sql.=" AND n.user_id=".$params['user_id'];
        if ($params['guild_channels']){
            $this->sql.=" AND b.user_code IN(".$params['guild_channels'].")";
        }
        if($params['orders']){
            $this->sql .= " AND n.orders = '".$params['orders']."'";
        }
        if($params['do_type']){
            $this->sql .= " AND n.do_type = ".$params['do_type'];
        }
        if($params['order_type'] == '-1'){
            $this->sql .= " AND n.order_type NOT in (0,1,2)";
        }else if($params['order_type'] && is_numeric($params['order_type']) || $params['order_type'] === '0'){
            $this->sql .= " AND n.order_type = ".$params['order_type'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->sql .= " AND n.add_time >= ".strtotime($params['start_time'])." AND n.add_time <= ".strtotime($params['end_time']);
        }elseif($params['start_time'] && !$params['end_time']){
            $this->sql .= " AND n.add_time >= ".strtotime($params['start_time']);
        }elseif(!$params['start_time'] && $params['end_time']){
            $this->sql .= " AND n.add_time <= ".strtotime($params['end_time']);
        }
        $this->doResultList();
        return $this->result;
    }

    public function insert_nd_user_revoke_log($orders,$user_id,$app_id,$nnb_num,$nd_num,$nd_discount,$order_type,$guild_id,$reason){
        $this->sql = "INSERT INTO nd_operation_log(orders,user_id,app_id,do_type,nnb_num,nd_num,nd_discount,order_type,
                    guild_id,add_time,remarks)VALUES(?,?,?,3,?,?,?,?,?,?,?)";
        $this->params = array($orders,$user_id,$app_id,$nnb_num,$nd_num,$nd_discount,$order_type,$guild_id,time(),$reason);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_nd_user_revoke_log($order_type,$id){
        $this->sql = "UPDATE nd_operation_log SET order_type=?,add_time=? WHERE id=?";
        $this->params = array($order_type,time(),$id);
        $this->doExecute();
    }
}