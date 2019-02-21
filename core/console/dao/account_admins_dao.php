<?php
COMMON('dao');
class account_admins_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function get_admin_list($params,$page){
        $this->limit_sql = "select * from admins where 1=1" ;
        if($params['account']){
            $this->limit_sql=$this->limit_sql." and account like'%".$params['account']."%'";
        }
        if($params['real_name']){
            $this->limit_sql=$this->limit_sql." and real_name like'%".$params['real_name']."%'";
        }
        if($params['usr_name']){
            $this->limit_sql=$this->limit_sql." and usr_name like'%".$params['usr_name']."%'";
        }
        if($params['qq']){
            $this->limit_sql=$this->limit_sql." and qq like'%".$params['qq']."%'";
        }
        if($params['group']){
            $this->limit_sql=$this->limit_sql." and `group` ='".$params['group']."'";
        }
        $this->limit_sql=$this->limit_sql." order by id asc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public  function get_admin_by_account($account){
        $this->sql = "select * from admins where account=?" ;
        $this->params=array($account);
        $this->doResult();
        return $this->result;
    }

    public function get_group_list(){
        $this->sql = "select * from admin_groups where Status='1' order by ID";
        $this->doResultList();
        return $this->result;
    }

    public function insert_data($admin){
        $this->sql = "insert into admins(account,usr_name, usr_pwd, real_name,`group`,qq, last_login) values(?,?,?,?,?,?,?)";
        $this->params = array($admin['account'],$admin['usr_name'], md5(strtolower($admin['usr_pwd'])), $admin['real_name'], $admin['group'],$admin['qq'], '946656000');
        $this->doExecute();
    }

    public function get_info($id){
        $this->sql = "select * from admins where id=".$id;
        $this->doResult();
        return $this->result;
    }

    public function update_data($admin){
        if(!$admin['usr_pwd']){
            $this->sql = "update admins set usr_name=?, real_name=?,account=?, is_del=?, `group`=?,qq=? where id=?";
            $this->params = array($admin['usr_name'], $admin['real_name'],$admin['account'], $admin['is_del'],
                $admin['group'],$admin['qq'], $admin['id']);
        }else{
            $admin['usr_pwd'] = md5(strtolower($admin['usr_pwd']));
            $this->sql = "update admins set usr_name=?, usr_pwd=?, real_name=?,account=?,is_del=?, `group`=?,qq=? where id=?";
            $this->params = array($admin['usr_name'], $admin['usr_pwd'], $admin['real_name'], $admin['account'], $admin['is_del'],
                $admin['group'], $admin['qq'],$admin['id']);

        }
        $this->doExecute();

    }

}