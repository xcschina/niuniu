<?php
COMMON('dao');
class disc_web_dao extends Dao {

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function get_top_banner(){
        $data = memcache_get($this->mmc, "66apk_disc_banner");
        if(!$data){
            $this->sql = "SELECT a.img AS picture,a.type AS type,a.game_id AS game_id,b.game_name AS game_name,a.title AS title,a.theme_id,a.url FROM 
                        66app_banner_tb a LEFT JOIN 66app_game_tb b ON a.game_id=b.id WHERE a.is_del=0 AND a.is_disc =1 ORDER BY a.id 
                        DESC LIMIT 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_banner",$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_find(){
       $data = memcache_get($this->mmc, "66apk_disc_game_find");
        if(!$data){
            $this->sql = "SELECT game_icon,id AS game_id,game_name FROM 66app_game_tb WHERE is_disc_new=1 AND is_disc_rec=1 
                         AND is_del=0 ORDER BY last_update DESC LIMIT 4";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_find",$data, 1, 3600);
        }
        return $data;
    }
    public function get_theme_entry(){
        $data = memcache_get($this->mmc, "66apk_disc_theme_entry");
        if(!$data){
            $this->sql = "SELECT img AS theme_picture,id AS theme_id,m_title AS theme_title,sub_title AS theme_subtitle FROM 66app_disc_theme_tb  
                        WHERE is_hot=1 AND is_del=0 ORDER BY update_time DESC LIMIT 3";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_theme_entry",$data, 1, 3600);
        }
        return $data;
    }
    public function get_theme_count(){
        $data = memcache_get($this->mmc, "66apk_disc_theme_count");
        if(!$data){
            $this->sql="SELECT COUNT(1) AS count FROM 66app_disc_theme_tb WHERE is_del=0 ";
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_theme_count",$data, 1, 3600);
        }
        return $data;
    }
    public function get_theme_list($page){
        $data = memcache_get($this->mmc, "66apk_disc_theme_list_".$page);
        if(!$data){
            $this->limit_sql="SELECT img AS theme_picture,id AS theme_id,m_title AS theme_title,sub_title AS theme_subtitle FROM 66app_disc_theme_tb  
                         WHERE is_del=0 ORDER BY id DESC";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_theme_list_".$page,$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_recommend_entry(){
        $data = memcache_get($this->mmc, "66apk_disc_game_recommend_entry");
        if(!$data){
            $this->sql = "SELECT disc_rec_img AS images,game_icon,id AS game_id,game_name,down_url AS game_url,
                        apk_name AS game_packname,game_title,tags AS game_label FROM 66app_game_tb WHERE is_disc_rec=1 
                        AND is_del=0 ORDER BY last_update DESC LIMIT 3";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_recommend_entry",$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_recommend_count(){
        $data = memcache_get($this->mmc, "66apk_disc_game_recommend_count");
        if(!$data){
            $this->sql="SELECT COUNT(1) AS count FROM 66app_game_tb WHERE is_disc_rec=1 AND is_del=0 ";
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_recommend_count",$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_recommend_list($page){
        $data = memcache_get($this->mmc, "66apk_disc_game_recommend_list".$page);
        if(!$data){
            $this->limit_sql="SELECT disc_rec_img AS images,game_icon,id AS game_id,game_name,down_url AS game_url,
                        apk_name AS game_packname,game_title,tags AS game_label FROM 66app_game_tb WHERE is_disc_rec=1 
                        AND is_del=0 ORDER BY id DESC ";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_recommend_list".$page,$data, 1, 3600);
        }
        return $data;
    }
    public function get_theme($theme_id){
        $data = memcache_get($this->mmc, "66apk_disc_theme".$theme_id);
        if (!$data){
            $this->sql = "SELECT img1 AS theme_picture,m_title AS theme_title,introduce AS theme_desc,re_game FROM 66app_disc_theme_tb 
                    WHERE id=? LIMIT 1";
            $this->params = array($theme_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_theme".$theme_id,$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_theme_count($game_str,$theme_id){
        $data = memcache_get($this->mmc, "66apk_disc_game_theme_count".$theme_id);
        if(!$data){
            $this->sql="SELECT COUNT(1) AS count FROM 66app_game_tb WHERE id in (".$game_str.") AND is_del=0 ";
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_theme_count".$theme_id,$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_theme_list($game_str,$theme_id,$page){
        $data = memcache_get($this->mmc, "66apk_disc_game_theme_list".$theme_id.$page);
        if(!$data){
            $this->limit_sql="SELECT disc_img AS game_banner,game_icon,m_title AS game_title,game_desc AS game_subtitle,id AS game_id FROM 66app_game_tb
                            WHERE id in(".$game_str.")  AND is_del=0 ORDER BY last_update DESC ";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_theme_list".$theme_id.$page,$data, 1, 3600);
        }
        return $data;
    }
    public function get_game_article($game_id){
        $data = memcache_get($this->mmc, "66apk_disc_game_ariticle".$game_id);
        if(!$data){
            $this->sql = "SELECT  down_url AS game_url,game_name,apk_name AS game_packname,game_icon FROM 66app_game_tb 
                      WHERE id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_ariticle".$game_id,$data,1, 3600);
        }
        return $data;
    }

    public function get_game_article_details($game_id){
        $data = memcache_get($this->mmc, "66apk_disc_game_ariticle_details".$game_id);
        if (!$data){
            $this->sql = "SELECT game_name,game_icon,down_num,apk_size,disc_img_text FROM 66app_game_tb 
                      WHERE id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_disc_game_ariticle_details".$game_id,$data,1, 3600);
        }
        return $data;
    }
}
?>