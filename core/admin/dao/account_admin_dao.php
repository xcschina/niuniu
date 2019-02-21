<?php
COMMON('niuniuDao');
class account_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admins";
    }

    public function get_account_list($page,$params){

        $this->limit_sql = "select a.*,b.ch_name from admins as a left join admin_groups as b on a.group_id=b.id where a.is_del='0'";
        if($params['type']== 1){
            $this->limit_sql = $this->limit_sql . " and a.p1 ='0' and a.p2 ='0' and group_id ='10'";
        }elseif($params['type']== 2){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 ='0' and group_id ='10'";
        }elseif($params['type']== 3){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 !='0' and group_id ='10'";
        }elseif($params['type']== 4){
            $this->limit_sql = $this->limit_sql . " and group_id !='10'";
        }
        if($params['channel']){
            $this->limit_sql = $this->limit_sql . " and a.user_code ='".$params['channel']."'";
        }
        if ($params['user_id']){
            $this->limit_sql = $this->limit_sql . " and a.id =".$params['user_id'];
        }
        if($params['group_id']){
            $this->limit_sql .= " and a.group_id =".$params['group_id'];
        }
        if($params['account']){
            $this->limit_sql .= " and a.account = '".trim($params['account'])."'";
        }
        if($params['real_name']){
            $this->limit_sql .= " and a.real_name = '".trim($params['real_name'])."'";
        }
        $this->limit_sql=$this->limit_sql." order by a.last_login desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_list($params){
        $this->sql = "select * from admins where is_del = 0";
        if($params['type']== 1){
            $this->sql = $this->sql . " and p1 ='0' and p2 ='0' and group_id ='10'";
        }elseif($params['type']== 2){
            $this->sql = $this->sql . " and p1 !='0' and p2 ='0' and group_id ='10'";
        }elseif($params['type']== 3){
            $this->sql = $this->sql . " and p1 !='0' and p2 !='0' and group_id ='10'";
        }elseif($params['type']== 4){
            $this->sql = $this->sql . " and group_id !='10'";
        }
        if($params['group_id']){
          $this->sql = $this->sql . " and group_id = ".$params['group_id'];
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_code($user_id){
        $this->sql = "select * from admins where group_id=10 and (id =? or p1=? or p2=?) and is_del=0 ";
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_account_by_group($usr_id,$page,$params){
        $this->limit_sql = "select a.*,b.ch_name from admins as a left join admin_groups as b on a.group_id=b.id where a.is_del='0'";
        if($params['type']== 2){
            $this->limit_sql = $this->limit_sql . " and a.p2 ='0' and a.p1 = ".$usr_id;
        }elseif($params['type']== 3){
            $this->limit_sql = $this->limit_sql . " and a.p1 !='0' and a.p2 = ".$usr_id;
        }else{
            $this->limit_sql = $this->limit_sql. " and ( a.id =".$usr_id." or a.p1=".$usr_id." or a.p2=".$usr_id.")";
        }
        if($params['channel']){
            $this->limit_sql = $this->limit_sql . " and a.user_code ='".$params['channel']."'";
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql . " and a.id =".$params['user_id'];
        }
        if($params['group_id']){
            $this->limit_sql .= " and a.group_id =".$params['group_id'];
        }
        if($params['account']){
            $this->limit_sql .= " and a.account = '".trim($params['account'])."'";
        }
        if($params['real_name']){
            $this->limit_sql .= " and a.real_name = '".trim($params['real_name'])."'";
        }
        $this->limit_sql = $this->limit_sql. " order by a.last_login desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_info($user_id){
        $data = $this->mmc->get("user_info_".$user_id);
        if(!$data) {
            $this->sql="select a.*,b.type,b.id as re_id,b.payee_ch from admins a left join admins_relation_tb b on a.id=b.user_id where a.id =?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("user_info_".$user_id,$data,1,300);
        }
        return $data;
    }

    public function get_merchant_info($params,$page){
        $this->limit_sql = "select a.*,b.ch_name,c.mobile,c.type from admins as a left join admin_groups as b on a.group_id=b.id left join admins_relation_tb c on a.id=c.user_id where a.is_del='0' and a.group_id ='14' or a.group_id = '15'";
        if($params['account']){
            $this->limit_sql = $this->limit_sql . " and a.account ='".$params['account']."'";
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql . " and a.id =".$params['user_id'];
        }
        $this->limit_sql = $this->limit_sql. " order by a.last_login desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function merchant_list(){
        $this->sql = "select * from admins where (group_id=14 or group_id=15) and is_del=0 ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function update_account($account, $id){
        $this->sql = "update admins set email=?,account=?, real_name=?, ch_id=?, qq=?, user_code=?, group_id=?,group_id2=? where id=?";
        $this->params = array($account['email'],$account['account'], $account['real_name'], $account['ch_id'], $account['qq'], $account['user_code'], $account['group_id'],$account['group_id2'], $id);
        $this->doExecute();
    }

    public function update_guild($id,$p1, $p2=''){
        if(empty($p2)){
            $this->sql = "update admins set p1=? where id=?";
            $this->params = array($p1,$id);
            $this->doExecute();
        }else{
            $this->sql = "update admins set p1=?, p2=? where id=?";
            $this->params = array($p1,$p2,$id);
            $this->doExecute();
        }
    }

    public function insert_account($account){
        $this->sql = "insert into admins(email,group_id2,account, real_name, qq, user_code,ch_id, group_id, last_login, usr_pwd)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($account['email'],$account['group_id2'],$account['account'],$account['real_name'],$account['qq'],$account['user_code'],$account['ch_id'],$account['group_id'], strtotime("now"), $account['usr_pwd']);
        $this->doInsert();
        $user_id = $this->LAST_INSERT_ID;

        $group = $this->get_group($account['group_id']);
        $this->sql = "insert into admin_permissions(usr_id,module,permissions)values(?,?,?)";
        $this->params = array($user_id, $group['module'], $group['module']);
        $this->doExecute();
        return $user_id;
    }

    public function insert_relation_tb($params,$user_id){
        $this->sql = "insert into admins_relation_tb(user_id,mobile,add_time)values(?,?,?)";
        $this->params = array($user_id,$params['mobile'],time());
        $this->doInsert();
    }

    public function get_all_guild_list(){
        $this->sql = "select * from admins where group_id=10 AND is_del=0 ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_list(){
        $this->sql="select * from channel_tb order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_groups(){
        $this->sql = "select * from admin_groups order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_groups_name($ch_name){
        $this->sql = "select * from admin_groups where ch_name=? and status=1";
        $this->params = array($ch_name);
        $this->doResult();
        return $this->result;
    }

    public function get_groups_ch_name($group_name){
        $this->sql = "select * from admin_groups where group_name=? and status=1";
        $this->params = array($group_name);
        $this->doResult();
        return $this->result;
    }

    public function insert_admin_groups($params){
        $this->sql = "insert into admin_groups(group_name,ch_name,status)values(?,?,?)";
        $this->params = array($params['group_name'],$params['ch_name'],$params['status']);
        $this->doInsert();
    }

    public function get_group($id){
        $this->sql = "select * from admin_groups where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function check_account($account){
        $this->sql = "select * from admins where account=? or user_code=?";
        $this->params = array($account['account'], $account['user_code']);
        $this->doResult();
        return $this->result;
    }

    public function verify_account($account){
        $this->sql = "select * from admins where account=?";
        $this->params = array($account['account']);
        $this->doResult();
        return $this->result;
    }

    public function update_account_pwd($account, $id){
        $this->sql = "update admins set usr_pwd=? where id=?";
        $this->params = array(md5($account['password']), $id);
        $this->doExecute();
    }

    public function get_user_modules($user_id){
        $this->sql = "select * from admin_permissions where usr_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_permision($user_id, $menus){
        $this->sql = "insert into admin_permissions(usr_id, module, permissions)values(?,?,?)";
        $this->params = array($user_id, $menus, $menus);
        $this->doExecute();
        $this->update_user_token($user_id);
    }

    public function update_user_permision($user_id, $menus){
        $this->sql = "update admin_permissions set module=?,permissions=? where usr_id=?";
        $this->params = array($menus, $menus, $user_id);
        $this->doExecute();
        $this->update_user_token($user_id);
    }

    public function update_group_permision($user_id, $menus){
        $this->sql = "update admin_groups set module=?,permissions=? where id=?";
        $this->params = array($menus, $menus, $user_id);
        $this->doExecute();
    }

    public function update_user_token($user_id){
        $token = $this->create_guid();
        $this->sql = "update admins set token=? where id=?";
        $this->params = array($token, $user_id);
        $this->doExecute();
    }

    protected function create_guid() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }

    public function get_menu($id){
        $this->sql = "select * from admin_menus where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_apps($user_id){
        $this->sql = "select * from admins where id=? ";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_user_apps($user_id, $apps){
        $token = $this->create_guid();
        $this->sql = "update admins set apps=?,token=? where id=?";
        $this->params = array($apps, $token, $user_id);
        $this->doExecute();
    }

    public function update_user_rh_apps($user_id, $apps){
        $token = $this->create_guid();
        $this->sql = "update admins set rh_apps=?,token=? where id=?";
        $this->params = array($apps, $token, $user_id);
        $this->doExecute();
    }

    public function get_guild_list($p1){
        $this->sql="select * from admins where group_id=10 and is_del='0' and p2='0' and p1=? ";
        $this->params = array($p1);
        $this->doResultList();
        return $this->result;
    }
    public function get_chamber_guild_list($p1){
        $this->sql="select * from admins where group_id=15 and is_del='0' and p2='0' and p1=? ";
        $this->params = array($p1);
        $this->doResultList();
        return $this->result;
    }

    public function get_admin_guild_list(){
        $this->sql="select * from admins where group_id=10 and is_del='0' and p2='0' and p1='0' ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_apps($id,$apps){
        $this->sql = "select * from guild_app_tb where guild_id=? and app_id=?";
        $this->params = array($id,$apps);
        $this->doResult();
        return $this->result;
    }

    public function add_guild_apps($id,$apps){
        $this->sql = "insert into guild_app_tb(guild_id,app_id,`time`)values(?,?,?)";
        $this->params = array($id,$apps,time());
        $this->doExecute();
    }

    public function del_guild_apps($id,$apps){
        $this->sql = "update guild_app_tb set is_del='1' where guild_id=? and app_id=? ";
        $this->params = array($id,$apps);
        $this->doExecute();
    }

    public function update_guild_apps($id,$apps){
        $this->sql = "update guild_app_tb set is_del='0' where guild_id=? and app_id=? ";
        $this->params = array($id,$apps);
        $this->doExecute();
    }

    public function del_user($user_id){
        $this->sql = "update admins set is_del='1' where id=?";
        $this->params = array($user_id);
        $this->doExecute();
    }

    public function update_account_img($id,$post){
        $this->sql = "update admins set img = ?,guild_name = ? where id =?";
        $this->params = array($post['img'],$post['guild_name'],$id);
        $this->doExecute();
    }

    public function get_guild(){
        $this->sql = "select a.*,p.module,p.permissions from admins as a left join admin_permissions as p on p.usr_id = a.id where a.is_del = 0 and a.group_id = 10";
        $this->doResultList();
        return $this->result;
    }

    public function update_permision($params){
        $this->sql = "update admin_permissions set `module`=? where 1 = 1 ";
        if($params['usr_id']){
            $this->sql = $this->sql." and usr_id = ".$params['usr_id'];
        }else{
            $this->sql = $this->sql." and usr_id = ".$params['id'];
        }
        $this->params = array($params['module']);
        $this->doExecute();
    }

    public function insert_recharge_log($img,$data){
        $this->sql = 'insert into business_record_tb(user_id,amount,`img`,`status`,remarks,add_time,update_time) values(?,?,?,?,?,?,?)';
        $this->params = array($data['user_id'],$data['amount'],$img,1,$data['remarks'],time(),time());
        $this->doInsert();
    }

    public function insert_business_game($user_id){
        $this->sql = 'INSERT INTO business_game_dis(user_id,add_time,modify_time)VALUES(?,?,?)';
        $this->params = array($user_id,time(),time());
        $this->doInsert();
    }

    public function get_business_game($user_id){
        $this->sql = "SELECT user_id,game_list,service_list FROM business_game_dis WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_business_game($game_list,$service_list,$user_id,$update_data){
        $this->doAffairsBegin();
        $this->sql = 'UPDATE business_game_dis SET game_list=?,service_list=?,modify_time=? WHERE user_id=? ';
        $this->params = array($game_list,$service_list,time(),$user_id);
        $this->doExecute();
        $this->sql = "UPDATE business_services SET is_indservice = CASE ";
        $sql = "";
        $id_list = array();
        foreach ($update_data as $value){
            if ($value['service_type']==2){
                if ($value['is_check']==1){
                    $sql .= " WHEN id=".$value['flag']." THEN ".$user_id;
                }else{
                    $sql .= " WHEN id=".$value['flag']." THEN 0";
                }
                array_push($id_list,$value['flag']);
            }
        }
        if ($sql){
            $this->sql .= $sql." END WHERE id IN (".implode(",",$id_list).")";
            $this->params = array();
            $this->doExecute();
        }
        $this->doAffairsCommit();
    }

    public function get_relation_tb($user_id){
        $this->sql = "select * from admins_relation_tb where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_relation_tb($params){
        $this->sql = "update admins_relation_tb set payment_method=? where user_id=?";
        $this->params = array($params['payment_method'],$params['user_id']);
        $this->doExecute();
    }

    public function insert_relation($params){
        $this->sql = "insert into admins_relation_tb(user_id,payment_method,add_time) values (?,?,?)";
        $this->params = array($params['user_id'],$params['payment_method'],time());
        $this->doInsert();
    }

    public function get_chamber_list($user_id=''){
        $this->sql = "select * from admins where group_id = 15 and p1='' and p2=''";
        if($user_id){
            $this->sql .= " and id = ".$user_id;
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_group_leader(){
        $this->sql = "select * from admins where group_id = 15 and p2=''";
        $this->doResultList();
        return $this->result;
    }

    public function get_chamber_guild_code($user_id,$group_id){
        $this->sql = "select * from admins where (id =? or p1=? or p2=?) and is_del=0 ";
        $this->sql .= " and group_id=".$group_id;
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_chamber_info($usr_id,$page,$params,$group_id){
        $this->limit_sql = "select a.*,b.ch_name,c.mobile,c.type from admins as a left join admin_groups as b on a.group_id=b.id left join admins_relation_tb c on a.id=c.user_id where a.is_del='0'";
        $this->limit_sql = $this->limit_sql. "and a.group_id = ".$group_id ." and ( a.id =".$usr_id." or a.p1=".$usr_id." or a.p2=".$usr_id.")";
        if($params['account']){
            $this->limit_sql = $this->limit_sql . " and a.account ='".$params['account']."'";
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql . " and a.id =".$params['user_id'];
        }
        $this->limit_sql = $this->limit_sql. " order by a.last_login desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_list($id){
        $this->sql = "select * from admins where id=? or p1=? or p2=?";
        $this->params = array($id,$id,$id);
        $this->doResultList();
        return $this->result;
    }

    public function update_admin_relation($params){
        $this->sql = "update admins_relation_tb set `type`=? where user_id = ?";
        $this->params = array($params['type'],$params['user_id']);
        $this->doExecute();
        $this->mmc->delete('user_info_'.$params['user_id']);
    }

    public function get_group_list(){
        $this->sql = "select id,ch_name from admin_groups where status = 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_admin_info($id){
        $this->sql = "select * from admins where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_admin_financial_mode($params,$id){
        $this->sql = "update admins set financial_mode=? where id = ?";
        $this->params = array($params['financial_mode'],$id);
        $this->doExecute();
    }
}