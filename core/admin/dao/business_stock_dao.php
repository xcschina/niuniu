<?php
COMMON('niuniuDao','randomUtils');
class business_stock_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_stock_list($user_id,$page,$params){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name from business_stock_info a left join business_apps b on a.app_id=b.app_id left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id where a.user_id = ?";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_stock_info_list($page,$params){
        $data = memcache_get($this->mmc,'stock_info_list_'.$params['user_id']);
        if(!$data){
            $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name from business_stock_info a left join business_apps b on a.app_id=b.app_id left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id left join admins d on a.user_id=d.id where 1=1";
            if($params['user_list']){
                $this->limit_sql .= " and a.user_id in (".$params['user_list'].")";
            }
            if($params['app_id']){
                $this->limit_sql .= " and a.app_id = ".$params['app_id'];
            }
            if($params['service_id']){
                $this->limit_sql .= " and a.service_id = ".$params['service_id'];
            }
            if($params['channel']){
                $this->limit_sql .= " and b.channel = ".$params['channel'];
            }
            if($params['user_id']){
                $this->limit_sql .= " and a.user_id = ".$params['user_id'];
            }
            $this->limit_sql .= " order by a.add_time desc,a.id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,'stock_info_list',$data,1,600);
        }
        return $data;
    }

    public function get_stock_info($id){
        $this->sql = "select a.*,b.real_name from business_stock_info a left join admins b on a.user_id=b.id where a.id= ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_stock_info($user_id,$info,$data){
        $this->sql = "insert into business_stock_info(app_id,service_id,user_id,add_time) values(?,?,?,?)";
        $this->params = array($info['app_id'],$data['service_id'],$user_id,time());
        $this->doInsert();
    }

    public function insert_stock_record($params,$info,$user_id){
        $this->sql = "insert into business_stock_record (app_id,service_id,stock_num,stock_balance,new_stock_balance,stock_collect,add_time,`desc`,user_id,operator_id,p_id) values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($info['app_id'],$info['service_id'],$params['stock_num'],$info['stock_balance'],$params['new_stock_balance'],$params['stock_collect'],time(),$params['desc'],$info['user_id'],$user_id,$params['id']);
        $this->doInsert();
    }

    public function update_stock_info($params){
        $this->sql = "update business_stock_info set stock_balance=?,stock_collect=?  where id=?";
        $this->params = array($params['new_stock_balance'],$params['new_stock_collect'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'stock_info_list');
    }

    public function get_record_list($id,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name from business_stock_record a left join business_apps b on a.app_id=b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id where a.p_id =? ";
        $this->limit_sql .= " and a.user_id = ".$user_id;
        $this->params = array($id);
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_list(){
        $this->sql = "select * from admins where group_id = 15 and is_del=0 and (p1='' or p2='')";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info_list($user_id=''){
        $this->sql = "select * from admins where group_id = 15 ";
        if($user_id){
            $this->sql .= " and p1 = ".$user_id;
        }else{
            $this->sql .= " and !p1 and !p2";
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_stock_admin_list($page,$params,$users){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name from business_stock_info a left join business_apps b on a.app_id=b.app_id left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id left join admins d on a.user_id = d.id where 1=1";
        if($users){
            $this->limit_sql .= " and a.user_id in (".$users.")";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_stock_group_info($info,$users){
        $this->sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name from business_stock_info a left join business_apps b on a.app_id=b.app_id left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id left join admins d on a.user_id = d.id where a.app_id =? and a.service_id =? ";
        $this->sql .= " and a.user_id in (".$users.")";
        $this->params = array($info['app_id'],$info['service_id']);
        $this->doResultList();
        return $this->result;
    }

    public function get_group_stock_info($info,$user_id){
        $this->sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name from business_stock_info a left join business_apps b on a.app_id=b.app_id left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id left join admins d on a.user_id = d.id where a.app_id =? and a.service_id =? and a.user_id =?";
        $this->params = array($info['app_id'],$info['service_id'],$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_stock_all_list(){
        $this->sql = "select id from business_stock_info limit 10";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_record_list($params,$page,$user_id){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name from business_stock_record a left join business_apps b on a.app_id = b.app_id  left join business_services c on a.service_id = c.service_id and a.app_id=c.app_id where a.user_id = ?";
        $this->limit_sql .= " order by a.add_time desc";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }
}