<?php
COMMON('dao');
class apk_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_top_banner(){
        $data = memcache_get($this->mmc, "66apk_top_banner");
        if(!$data){
            $this->sql="select title,img as picture,url,`type`,game_id,theme_id from 66app_banner_tb where is_del=0 AND is_disc=0 order by id desc limit 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_top_banner",$data, 1, 3600);
        }
        return $data;
    }

    public function get_fine_game(){
        $data = memcache_get($this->mmc, "66apk_fine_game");
        if(!$data){
            $this->sql="select id,game_banner,game_icon,down_url,game_name,is_gift,apk_name,game_title from 66app_game_tb where is_del=0 and type = 1 order by id desc limit 6";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_fine_game",$data, 1, 3600);
        }
        return $data;
    }

    public function get_fine_game_list($page){
        $data = memcache_get($this->mmc, "66apk_fine_game_list_".$page);
        if(!$data){
            $this->limit_sql="select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and type = 1 ";
            $this->limit_sql.=" order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_fine_game_list_".$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_game_count($type){
        $this->sql="select count(1) as num from 66app_game_tb where is_del=0 and type = ".$type;
        $this->doResult();
        return $this->result;
    }

    public function get_tx_game(){
        $data = memcache_get($this->mmc, "66apk_tx_game");
        if(!$data){
            $this->sql="select id,game_icon,game_banner,down_url,game_name,is_gift,apk_name,game_title from 66app_game_tb where is_del=0 and type = 2 order by id desc limit 6";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_tx_game",$data, 1, 3600);
        }
        return $data;
    }

    public function get_tx_game_list($page){
        $data = memcache_get($this->mmc, "66apk_tx_game_list_".$page);
        if(!$data){
            $this->limit_sql="select id,game_name,game_icon,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and type = 2 ";
            $this->limit_sql.=" order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_tx_game_list_".$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_game_list($page){
        $data = memcache_get($this->mmc, "66apk_game_list_".$page);
        if(!$data){
            $this->limit_sql="select id,game_name,game_icon,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 ";
            $this->limit_sql.=" order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_game_list_".$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_all_game_count(){
        $this->sql="select count(1) as num from 66app_game_tb where is_del=0  ";
        $this->doResult();
        return $this->result;
    }

    public function get_new_game(){
        $data = memcache_get($this->mmc, "66apk_new_game");
        if(!$data){
            $this->sql="select id,game_icon,game_banner,down_url,game_name,is_gift,apk_name,game_title from 66app_game_tb where is_del=0 and is_new_game = 1 order by id desc limit 6";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_new_game",$data, 1, 3600);
        }
        return $data;
    }

    public function get_game_detail($game_id){
        $data = memcache_get($this->mmc, "66apk_game_detail_".$game_id);
        if(!$data){
            $this->sql="select * from 66app_game_tb where is_del=0 and id= ? ";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_game_detail_".$game_id,$data, 1, 3600);
        }
        return $data;
    }

    public function get_game_gifts($game_id){
        $data = memcache_get($this->mmc, "66apk_game_gifts_game_id_".$game_id);
        if(!$data){
            $this->sql="select id,title,content,end_time,get_way,num,remain,`type` from 66app_gift_tb where is_del=0 and game_id=? limit 10";
            $this->params=array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_game_gifts_game_id_".$game_id,$data, 1, 3600);
        }
        return $data;
    }

    public function get_activity_center(){
        $data = memcache_get($this->mmc, "66apk_activity_center");
        if(!$data){
            $this->sql="select * from 66app_activity_tb where is_del=0 order by id desc limit 10";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_activity_center",$data, 1, 3600);
        }
        return $data;
    }

    public function get_activity_center_count(){
        $data = memcache_get($this->mmc, "66apk_activity_center_count");
        if(!$data){
            $this->sql="select count(*) as num from 66app_activity_tb where is_del=0 ";
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_activity_center_count",$data, 1, 3600);
        }
        return $data;
    }

    public function get_user_nnb_num($user_id){
        $this->sql="select nnb from user_info where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_gifts($user_id,$batch_id){
            $this->sql = "select b.code,c.title,b.batch_id from 66app_gifts as b 
                      INNER join 66app_gift_tb as c on b.batch_id=c.id where b.buyer_id=? and b.batch_id=? order by b.id desc";
            $this->params = array($user_id,$batch_id);
            $this->doResultList();
        return $this->result;
    }

    public function update_code_status($gift, $user_id, $batch_id){
        $this->sql = "update 66app_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array($user_id, strtotime("now"), $gift['id']);
        $this->doExecute();

        $this->sql = "update 66app_gift_tb set remain=remain-1 where id=?";
        $this->params = array($batch_id);
        $this->doExecute();

        memcache_delete($this->mmc, '66apk_usr_gifts'.$user_id);
    }

    public function get_gift_info($id){
        $this->sql = "select a.*,b.game_name,b.game_icon from 66app_gift_tb as a INNER JOIN 66app_game_tb as b on a.game_id=b.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift($batch_id){
        $this->sql = "select * from 66app_gifts where is_use=0 and batch_id=?";
        $this->params = array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_gift_batch($user_id,$batch_id){
        $this->sql = "select batch_id from 66app_gifts where buyer_id=? and batch_id=? group by batch_id";
        $this->params = array($user_id,$batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_my_gifts($user_id,$page){
        $this->limit_sql = "select c.id as gift_id,a.game_name,a.game_icon,b.code,c.title,c.end_time,c.content,c.get_way,b.batch_id from 66app_game_tb as a INNER join 66app_gifts as b on a.id=b.game_id
                      INNER join 66app_gift_tb as c on b.batch_id=c.id where b.buyer_id=? order by a.id desc";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_gifts_count($user_id){
        $this->sql = "select count(*) as num from 66app_game_tb as a INNER join 66app_gifts as b on a.id=b.game_id
                      INNER join 66app_gift_tb as c on b.batch_id=c.id where b.buyer_id=? order by a.id desc";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_qb_orders($user_id,$page){
        $this->limit_sql = "select * from qb_order where buyer_id=?";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_qq_rate($id) {
        $this->sql = "select * from setting where id = ".$id;
        $this->doResult();
        return $this->result['qq_discount'];
    }

    public function get_pop_list(){
        $this->sql = "select id,img as picture,`type`,game_id,theme_id,url,start_time,end_time from 66app_activity_pop where is_del = 0 and `status` = 0 and end_time >= ? and start_time <= ?";
        $this->params = array(time(),time());
        $this->sql .= " order by add_time desc limit 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_hot_search(){
        $this->sql = "select id as game_id,game_icon,game_name from 66app_game_tb where is_hot_search = 1 order by add_time desc limit 6";
        $this->doResultList();
        return $this->result;
    }

    public function get_search_count($keyword){
        $this->sql = "select count(1) as count from 66app_game_tb where game_name like ? and is_del=0";
        $this->params = array('%'.$keyword.'%');
        $this->doResult();
        return $this->result;
    }

    public function get_search_list($keyword,$page){
        $this->limit_sql = "select id,game_icon,down_url,game_name,is_gift ,apk_name ,game_desc,
                            down_num ,version ,apk_size,game_title  from 66app_game_tb where game_name like ? and is_del=0 ";
        $this->limit_sql .= " order by is_hot_search desc";
        $this->params = array('%'.$keyword.'%');
        $this->doLimitResultList($page);
        return $this->result;
    }
}