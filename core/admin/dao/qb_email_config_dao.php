<?php
COMMON('niuniuDao');
class qb_email_config_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_email_config";
    }

    public function get_list($page,$params){
        $this->limit_sql = "select * from qb_email_config  where is_del=2 ";
        if($params['type']){
            $this->limit_sql = $this->limit_sql." and type = ".$params['type'];
        }
        if($params['email']){
            $this->limit_sql .= " and email like '%" . $params['email'] . "%'";
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }


    public function insert_email($params){
        $this->sql = "insert into qb_email_config(financial_type,`type`,email) values(?,?,?)";
        $this->params = array($params['financial_type'],$params['type'],$params['email']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_message_log($parent_id,$user_id){
        $this->sql = "select * from sdk_message_log where parent_id = ? and user_id = ?";
        $this->params = array($parent_id,$user_id);
        $this->doResult();
        return $this->result;
    }


    public function get_email_info($id){
        $this->sql = "select * from qb_email_config  where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_email_info($params){
        $this->sql = "update qb_email_config set financial_type=?,email=?,`type`=? where id = ?";
        $this->params = array($params['financial_type'],$params['email'],$params['type'],$params['id']);
        $this->doExecute();
    }

    public function update_email_info_delete($params){
        $this->sql = "update qb_email_config set is_del = 1 where id =?";
        $this->params = array($params['id']);
        $this->doExecute();
    }

}