<?php
COMMON('niuniuDao');
class service_admin_dao extends niuniuDao{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function get_service_list($page,$params){
        $this->limit_sql = "select b.*,a.app_name from business_services b LEFT JOIN business_apps a ON b.app_id=a.app_id where b.is_del=0 ";
        if ($params['app_id']){
            $this->limit_sql .= " AND b.app_id=".$params['app_id'];
        }
        if ($params['service_id']){
            $this->limit_sql .= " AND b.service_id=".$params['service_id'];
        }
        if ($params['service_name']){
            $this->limit_sql .= " AND b.service_name like'%".$params['service_name']."%'";
        }
        $this->limit_sql = $this->limit_sql. " order by b.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_check_service_id($app_id,$service_id,$service_name){
        $this->sql = "SELECT * FROM business_services WHERE app_id=? AND (service_id=? OR service_name=?) ORDER BY id DESC ";
        $this->params = array($app_id,$service_id,$service_name);
        $this->doResult();
        return $this->result;
    }

    public function insert_service($params){
        $this->sql = "INSERT INTO business_services(app_id,service_id,service_name,add_time,modify_time,service_type)VALUES(?,?,?,?,?,?)";
        $this->params = array($params['app_id_add'],$params['service_id_add'],$params['service_name_add'],time(),time(),$params['service_type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_service_by_id($id){
        $this->sql = "select b.*,a.app_name from business_services b LEFT JOIN business_apps a ON b.app_id=a.app_id where b.is_del=0 
                    AND b.id=? ORDER BY b.id DESC";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_check_service_edit($app_id,$service_id,$service_name,$id){
        $this->sql = "SELECT * FROM business_services WHERE app_id=? AND (service_id=? OR service_name=?) AND id!=? ORDER BY id DESC ";
        $this->params = array($app_id,$service_id,$service_name,$id);
        $this->doResult();
        return $this->result;
    }

    public function update_service($params){
        $this->sql = "UPDATE business_services SET app_id=?,service_id=?,service_name=?,modify_time=? WHERE id=?";
        $this->params = array($params['app_id_edit'],$params['service_id_edit'],$params['service_name_edit'],time(),$params['id']);
        $this->doExecute();
    }

    public function import_services($import_data){
        $sql = 'INSERT INTO business_services(app_id,service_id,service_name,add_time,modify_time,service_type)VALUES';
        foreach ($import_data as $value){
            $sql .= "(".$value['app_id'].",".$value['service_id'].",'".$value['service_name']."',".time().",".time().",".$value['service_type']."),";
        }
        $this->sql = trim($sql,',');
        $this->params = array();
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_user_app($data,$ActCity,$RegCtiy){
        $this->sql = "insert into stats_user_app_copy(ActIP,ActTime,AppID,AreaServerID,AreaServerName,Channel,GUID,ID,RegIP,RegTime,RoleID,RoleLevel,RoleName,SID,UserID,ActCity,RegCtiy)VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params =array($data['ActIP'],$data['ActTime'],$data['AppID'],$data['AreaServerID'],$data['AreaServerName'],$data['Channel'],$data['GUID'],$data['ID'],$data['RegIP'],$data['RegTime'],$data['RoleID'],$data['RoleLevel'],$data['RoleName'],$data['SID'],$data['UserID'],$ActCity,$RegCtiy);
        $this->doInsert();
    }

    public function get_ip_session($ip){
        return $this->mmc->get($ip);
    }

    public function set_ip_session($ip,$data){
        $this->mmc->set($ip,$data,1,3600);
    }

    public function get_role_info($role){
        $data = $this->mmc->get($role);
        if(!$data){
            $this->sql = "select RoleID from stats_user_app_copy where RoleID=?";
            $this->params = array($role);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set($role,$data,1,3600);
        }
        return $data;
    }

    public function check_services_import($check_data){
        $sql = "SELECT * FROM business_services WHERE ";
        foreach ($check_data as $value){
            $sql .= " (app_id=".$value['app_id']." AND (service_id IN(".$value['service_id_all'].") OR service_name IN(".$value['service_name_all']."))) OR ";
        }
        $this->sql = trim($sql,' OR ');
        $this->doResultList();
        return $this->result;
    }

    public function get_services_by_game_id($app_list,$page,$user_id){
        $this->limit_sql = "SELECT s.*,a.app_name FROM business_services s LEFT JOIN business_apps a ON s.app_id=a.app_id WHERE s.app_id IN(".$app_list.") AND 
                        (s.service_type=1 OR (s.service_type=2 AND (s.is_indservice=0 OR s.is_indservice=".$user_id.")))";
        $this->limit_sql = $this->limit_sql. " order by s.id desc";
        $this->doLimitResultList($page,10);
        return $this->result;
    }

    public function get_services_by_game_id_all($app_list,$user_id){
        $this->sql = "SELECT s.*,a.app_name FROM business_services s LEFT JOIN business_apps a ON s.app_id=a.app_id WHERE s.app_id IN(".$app_list.") AND  
            (s.service_type=1 OR (s.service_type=2 AND (s.is_indservice=0 OR s.is_indservice=".$user_id."))) order by s.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_services_by_services_id_all($service_list){
        $this->sql = "SELECT s.*,a.app_name FROM business_services s LEFT JOIN business_apps a ON s.app_id=a.app_id WHERE s.id IN(".$service_list.") order by s.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_info($app_id){
        $this->sql = "select * from business_apps where app_id = ?";
        $this->params = array($app_id);
        return $this->result;
    }

    public function get_chamber_list(){
        $this->sql = "select * from admins where group_id = 15 and is_del = 0 and (p1='' or p2='')";
        $this->doResultList();
        return $this->result;
    }

    public function insert_stock_info($params,$user_id){
        $this->sql = "insert into business_stock_info(app_id,service_id,user_id,add_time) values(?,?,?,?)";
        $this->params = array($params['app_id'],$params['service_id'],$user_id,time());
        $this->doInsert();
    }
}