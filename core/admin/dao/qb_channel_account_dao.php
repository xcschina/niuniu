<?php
COMMON('niuniuDao');
class qb_channel_account_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_channel_account";
    }

    public function get_list($page, $params){
        $this->limit_sql="select a.*,b.channel_name from qb_channel_account a left join qb_channel b on b.id = a.channel_id  where a.is_del=0";
        if($params['channel_name']){
            $this->limit_sql .= " and b.channel_name like '%" . $params['channel_name'] . "%'";
        }
        if($params['user_name']){
            $this->limit_sql .= " and a.user_name like '%" . $params['user_name'] . "%'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }
    public function get_qb_channel_list(){
        $this->sql = "select * from qb_channel";
        $this->doResultList();
        return $this->result;
    }




    public function insert_qb_channel_account($params){
        $this->sql = "insert into qb_channel_account(add_time,user_id,credit_money,mode,pay_mode,user_name,channel_id,payment_account,`type`) values(?,?,?,?,?,?,?,?,?)";
        $this->params = array(time(),$_SESSION['usr_id'],$params['credit_money'],$params['mode'],$params['pay_mode'],$params['user_name'],$params['channel_id'],$params['payment_account'],$params['type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }


    public function get_qb_channel_account_info($app_id){
        $this->sql = "select * from qb_channel_account where id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }
    public function get_qb_channel_account_info_by_account($payment_account){
        $this->sql = "select payment_account from qb_channel_account where payment_account = ? and is_del=0";
        $this->params = array($payment_account);
        $this->doResult();
        return $this->result;
    }

    public function update_qb_channel_account($params,$id){
        $this->sql = "update qb_channel_account set pay_mode=?,credit_money=?,mode=?,channel_id=?,`type`=?,user_name=?,payment_account=? where id = ?";
        $this->params = array($params['pay_mode'],$params['credit_money'],$params['mode'],$params['channel_id'],$params['type'],$params['user_name'],$params['payment_account'],$id);
        $this->doExecute();
    }
    public function update_qb_channel_account_delete($params){
        $this->sql = "update qb_channel_account set is_del = 1 where id =?";
        $this->params = array($params['id']);
        $this->doExecute();
    }


}