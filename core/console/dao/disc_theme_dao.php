<?php
COMMON('dao');
class disc_theme_dao extends Dao
{
    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_theme_list($params,$page){
        $this->limit_sql = "select * from 66app_disc_theme_tb where is_del = 0 ";
        if($params['m_title']){
            $this->limit_sql .= " and m_title like '%".$params['m_title']."%'";
        }
        if($params['is_hot'] && is_numeric($params['is_hot']) || $params['is_hot'] ==='0'){
            $this->limit_sql .= " and is_hot = ".$params['is_hot'];
        }
        $this->limit_sql .= " order by update_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_game_list(){
        $this->sql = "select * from 66app_game_tb where is_del=0 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function insert_theme($params){
        $this->sql = "insert into 66app_disc_theme_tb (m_title,sub_title,img,img1,introduce,add_time,update_time,re_game,is_hot) values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['m_title'],$params['sub_title'],$params['img'],$params['img1'],$params['introduce'],time(),time(),$params['re_game'],$params['is_hot']);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_disc_theme_entry');
    }

    public function get_theme_info($id){
        $this->sql = "select * from 66app_disc_theme_tb where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_theme($params){
        $this->sql = "update 66app_disc_theme_tb set m_title=?,sub_title=?,img=?,img1=?,introduce=?,update_time=?,re_game=?,is_hot=? where id = ?";
        $this->params =  array($params['m_title'],$params['sub_title'],$params['img'],$params['img1'],$params['introduce'],time(),$params['re_game'],$params['is_hot'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_disc_game_theme_list'.$params['id']);
        memcache_delete($this->mmc,'66apk_disc_theme_count');
    }

    public function del_theme($id){
        $this->sql = "update 66app_disc_theme_tb set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_disc_theme_entry');
    }
}