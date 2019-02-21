<?php
COMMON('niuniuDao');

class app_feedback_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_feedback_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name from feedback_tb as a left join apps as b on a.app_id =b.app_id where 1=1 ";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['operator_id']){
            $this->limit_sql .= " and a.operator_id = ".$params['operator_id'];
        }
        if($params['type']){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] ==="0") {
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from apps";
        $this->doResultList();
        return $this->result;
    }

    public function get_feedback_info($id){
        $this->sql = "select a.*,b.app_name,c.real_name from feedback_tb as a left join apps as b on a.app_id = b.app_id left join admins as c on a.operator_id = c.id where a.id = ? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_feedback_tb($params,$operator_id,$id){
        $this->sql = "update feedback_tb set feedback_desc=?,operator_id=?,feedback_time=?,status=?,feedback_img=? where id=?";
        $this->params = array($params['feedback_desc'],$operator_id,time(),'1',$params['feedback_img'],$id);
        $this->doExecute();
    }


}
?>