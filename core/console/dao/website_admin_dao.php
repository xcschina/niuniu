<?php
COMMON('dao');
class website_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_promotion($page){
        $this->limit_sql = "select l.*,p.game_name,p.acronym from promotion_link_tb as l left join promotion_game_tb as p on l.game_id=p.id where l.game_id != 0 order by l.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_game_info($page){
        $this->limit_sql = "select l.*,b.game_name from promotion_link_tb as l left join game_template as p on p.id=l.template_id left join game as b on p.game_id=b.id where p.is_del=0 order by l.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_promotion_game(){
        $this->sql = "select * from promotion_game_tb ";
        $this->doResultList();
        return $this->result;
    }

    public function add_promotion($params){
        $this->sql = "insert into promotion_link_tb(ch_name,game_id,qr_url,down_url,add_time)values(?,?,?,?,?)";
        $this->params = array($params['ch_name'],$params['game_id'],$params['qr_url'],$params['down_url'], time());
        $this->doExecute();
    }

    public function insert_promoter_info($params,$url){
        $this->sql = "insert into promoter_tb(`name`,phone_num,code,url,add_time) values(?,?,?,?,?)";
        $this->params = array($params['name'],$params['phone_num'],$params['code'],$url,time());
        $this->doExecute();
    }

    public function get_promoter_list($params,$page){
        $this->limit_sql = "select * from promoter_tb where 1=1 ";
        if($params['id']){
            $this->limit_sql = $this->limit_sql." and id =".$params['id'];
        }
        if($params['nick_name']){
            $this->limit_sql = $this->limit_sql." and `name` like '%".$params['nick_name']."%'";
        }
        $this->limit_sql = $this->limit_sql.=" ORDER BY id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_list($code){
        $this->sql = "select user_id from user_info where code = ?";
        $this->params = array($code);
        $this->doResultList();
        return $this->result;
    }

    public function get_promoter_code($code){
        $this->sql = "select * from promoter_tb where code = ?";
        $this->params = array($code);
        $this->doResult();
        return $this->result;
    }

    public function get_game_page(){
        $this->sql = "select * from game_template where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function add_promotion_info($params){
        $this->sql = "insert into promotion_link_tb(ch_name,template_id,qr_url,down_url,add_time)values(?,?,?,?,?)";
        $this->params = array($params['ch_name'],$params['page_id'],$params['qr_url'],$params['down_url'], time());
        $this->doExecute();
    }

}