<?php
COMMON('niuniuDao');
class qb_channel_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_channel";
    }

    public function get_list($page, $params){
        $this->limit_sql="select * from qb_channel where 1=1 ";
        if($params['channel_name']){
            $this->limit_sql .= " and channel_name like '%" . $params['channel_name'] . "%'";
        }
//        if($params['user_name']){
//            $this->limit_sql .= " and user_name like '%" . $params['user_name'] . "%'";
//        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }


    public function insert_channel($params){
        $this->sql = "insert into qb_channel(channel_name,`type`) values(?,?)";
        $this->params = array($params['channel_name'],$params['type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }


    public function get_qb_channel_info($app_id){
        $this->sql = "select * from qb_channel where id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function update_qb_channel($params,$id){
        $this->sql = "update qb_channel set channel_name=?,`type`=? where id = ?";
        $this->params = array($params['channel_name'],$params['type'],$id);
        $this->doExecute();
    }

}