<?php
COMMON('niuniuDao','randomUtils','dao');
class user_info_dao extends Dao{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admin_menus";
    }

    public function get_user_groups(){
        $this->sql = 'select * from admin_groups where GroupName like \'%用户\'order by ID desc';
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info_list($params,$page){
        $count_sql = "SELECT COUNT(*) as count_no FROM user_info WHERE 1=1";
        $this->limit_sql = "select user_id,nick_name,user_name,id_number,mobile,email,sex,user_group,reg_from,reg_time,user_type,reg_ip from user_info where 1=1";
        if($params['user_id'] && is_numeric($params['user_id'] )){
            $this->limit_sql .= " and user_id =".$params['user_id'];
            $count_sql .= " and user_id =".$params['user_id'];
        }
        if($params['nick_name']){
            $this->limit_sql .= " and nick_name like '%".$params['nick_name']."%'";
            $count_sql .= " and nick_name like '%".$params['nick_name']."%'";
        }
        if($params['user_name']){
            $this->limit_sql .= " and user_name like '%".$params['user_name']."%'";
            $count_sql .= " and user_name like '%".$params['user_name']."%'";
        }
        if($params['id_number']){
            $this->limit_sql .= " and id_number ='".$params['id_number']."'";
            $count_sql .= " and id_number ='".$params['id_number']."'";
        }
        if($params['mobile']){
            $this->limit_sql .= " and mobile= '".$params['mobile']."'";
            $count_sql .= " and mobile= '".$params['mobile']."'";
        }
        if($params['email']){
            $this->limit_sql .= " and email = '".$params['email']."'";
            $count_sql .= " and email = '".$params['email']."'";
        }
        if($params['sex'] && is_numeric($params['sex']) || $params['sex'] === '0'){
            $this->limit_sql .= " and sex = ".$params['sex'];
            $count_sql .= " and sex = ".$params['sex'];
        }
        if($params['reg_from'] && is_numeric($params['reg_from'])){
            $this->limit_sql .= " and reg_from = ".$params['reg_from'];
            $count_sql .= " and reg_from = ".$params['reg_from'];
        }
        if($params['group_id'] && is_numeric($params['group_id'])){
            $this->limit_sql .= " and user_group = ".$params['group_id'];
            $count_sql .= " and user_group = ".$params['group_id'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->limit_sql .= " and reg_time>=". strtotime($params['start_time'])." and reg_time<=". strtotime($params['end_time']);
            $count_sql .= " and reg_time>=". strtotime($params['start_time'])." and reg_time<=". strtotime($params['end_time']);
        }elseif($params['start_time'] && !$params['end_time']) {
            $this->limit_sql .= " and reg_time>=".strtotime($params['start_time']);
            $count_sql .= " and reg_time>=".strtotime($params['start_time']);
        }elseif(!$params['start_time'] && $params['end_time']) {
            $this->limit_sql .= " and reg_time<=".strtotime($params['end_time']);
            $count_sql .= " and reg_time<=".strtotime($params['end_time']);
        }
        if((int)$params['has_id_number']==2){
            $this->limit_sql .= " and id_number<>''";
            $count_sql .= " and id_number<>''";
        }elseif((int)$params['has_id_number']==1){
            $this->limit_sql .= " and id_number=''";
            $count_sql .= " and id_number=''";
        }
        $this->limit_sql .= " order by user_id desc";
        $this->sql = $count_sql." order by user_id desc";
        $this->doResult();
        $no = $this->result;
        $this->doLimitResultList_OP($page,$no['count_no']);
//        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_info($id=0){
        $this->sql = 'select * from user_info where user_id=?';
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_games($user_id){
        $this->sql = "select b.app_name from niuniu.user_apptb a left join niuniu.apps b on a.app_id = b.app_id where a.userid = ? order by a.app_id desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function set_user_group($user_id=0, $group_id=0){
        $this->sql = 'update user_info set user_group=? where user_id=?';
        $this->params = array($group_id, $user_id);
        $this->doExecute();
    }
}