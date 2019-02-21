<?php
COMMON('dao');
class friendly_link_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_link_list($params,$page){
        $this->limit_sql="select * from friendly_links where 1=1";
        if($params['title']){
            $this->limit_sql=$this->limit_sql." and title like '%".$params['channel_name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function add_link($params){
        $this->sql="insert into friendly_links(title,icon,go_url) values(?,?,?)";
        $this->params=array($params['title'],$params['icon'],$params['go_url']);
        $this->doInsert();
        memcache_delete($this->mmc, 'friendly_links');
    }

    public function get_link_info($id){
        $this->sql="select * from friendly_links where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_link_info($params){
        $this->sql="update friendly_links set title=?,icon=?,go_url=? where id=?";
        $this->params=array($params['title'],$params['icon'],$params['go_url'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc, 'friendly_links');
    }

    public function del_link($id){
        $this->sql="delete from friendly_links where id=?";
        $this->params=array($id);
        $this->doExecute();
        memcache_delete($this->mmc, 'friendly_links');
    }
}