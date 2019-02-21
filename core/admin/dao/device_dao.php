<?php
COMMON('niuniuDao');
class device_dao extends niuniuDao
{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "device_black";
    }

    public function get_device_list($page,$params){
        $this->limit_sql = 'SELECT * FROM device_black WHERE 1=1';
        if ($params['device_no']){
            $this->limit_sql .= ' AND device_no=\''.$params['device_no'].'\'';
        }
        if ($params['device_type']){
            $this->limit_sql .= ' AND device_type='.$params['device_type'];
        }
        $this->limit_sql .= ' AND device_status=1 ORDER BY add_time DESC,id DESC';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_device_black($device_no){
        $this->sql = 'SELECT * FROM device_black WHERE device_no=? ';
        $this->params = array($device_no);
        $this->doResult();
        return $this->result;
    }

    public function insert_device_black($params){
        $this->sql = 'INSERT INTO device_black(device_no,device_type,device_status,add_time)VALUES(?,?,?,?)';
        $this->params = array($params['device_no_add'],$params['device_type_add'],1,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_device_black($params){
        $this->sql = 'UPDATE device_black SET device_status=? ,add_time=? WHERE id=?';
        $this->params = array($params['device_status'],$params['add_time'],$params['id']);
        $this->doExecute();
    }

    public function insert_device_opertion_log($params){
        $this->sql = 'INSERT INTO device_black_log(device_black_id,operation_type,operation_id,operation_time)VALUES(?,?,?,?)';
        $this->params = array($params['device_black_id'],$params['operation_type'],$params['operation_id'],$params['operation_time']);
        $this->doInsert();
    }

}