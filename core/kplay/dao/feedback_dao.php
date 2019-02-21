<?php
COMMON('niuniuDao','randomUtils');
class feedback_dao extends niuniuDao{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_app_name(){
        $data = $this->mmc->get("app_name");
        if(!$data){
            $this->sql = "select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id where a.is_del  = 0 and a.app_id <>'1000' and a.app_id <>'1001'  order by a.id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("app_name",$data,1,120);
        }
        return $data;
    }

    public function insert_feedback($params){
        $this->sql = "insert into feedback_tb (`type`,app_id,login_name,service_name,role_name,mode,`desc`,add_time,user_id) values (?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['type'],$params['app_id'],$params['login_name'],$params['service_name'],$params['role_name'],$params['mode'],$params['desc'],time(),$params['user_id']);
        $this->doExecute();
    }

}