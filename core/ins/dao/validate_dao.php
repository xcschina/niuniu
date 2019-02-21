<?php
COMMON('dao','niuniuDao');
class validate_dao extends niuniuDao {

	public function __construct() {
		parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
	}

    public function get_app_info($app_id){
//        $data = $this->mmc->get("app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function add_problems_log($data,$params){
        $this->sql = "insert into user_verify_log(appid,user_id,serv_id,role_id,`level`,question,answer,status,`time`)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($data['app_id'],$data['user_id'],$data['area_server_id'],$data['role_id'],$data['role_level'],$params['question'],$params['answer'],1,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_problems_log($id){
        $this->sql = "select * from user_verify_log where id=?";
        $this->params = array($id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_sdk_rules($id){
        $this->sql = "select content from sdk_verify_rules where id in (".$id.")";
        $this->doResultList();
        return $this->result;
    }
}
