<?php
COMMON('dao');
class site_setting_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_site_setting_list($page){
        $this->limit_sql="select * from setting order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_setting_info_byid($id){
        $this->sql="select * from setting where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_setting($params){
        $this->sql="update setting set tel=?,weixin=?,weixin_img=?,weixin_welcome=?,weixin_reply=?,trades=?,qq_discount=? where id=?";
        $this->params=array($params['tel'],
            $params['weixin'],$params['weixin_img'],$params['weixin_welcome'],$params['weixin_reply'],$params['trades'],$params['qq_discount'],$params['id']);
        $this->doExecute();
    }
}