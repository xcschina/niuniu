<?php
COMMON('dao');
class market_dao extends Dao {
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function get_banner($page){
        $this->limit_sql = "select a.*,g.game_name,t.m_title from 66app_banner_tb as a left join 66app_game_tb as g on a.game_id=g.id left join 66app_disc_theme_tb as t on a.theme_id=t.id where a.is_del=0 order by a.id desc";
        $this->params = array();
        $this->doLimitResultList($page);
        return $this->result;
    }
    //删除图片
    public function del_banner($id){
        $this->sql = "update 66app_banner_tb set is_del =1 where id=?";
        $this->params = array($id);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_disc_banner');
        memcache_delete($this->mmc,'66apk_top_banner');
    }
    public function get_name_list(){
        $this->sql = "select * from 66app_game_tb where is_del=0";
        $this->doResultList();
        return $this->result;
    }

    public function get_theme_list(){
        $this->sql = "select * from 66app_disc_theme_tb where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_rec_list(){
        $this->sql = "select * from 66app_game_tb where is_del = 0 and is_disc_rec = 1";
        $this->doResultList();
        return $this->result;
    }

    public function insert_banner($params){
        $this->sql = "insert into 66app_banner_tb(title,url,add_time,is_del,img,`type`,game_id,is_disc,theme_id)
                      VALUES(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['url'],time(),'0',$params['img'],$params['type'],$params['game_id'],$params['is_disc'],$params['theme_id']);
        $this->doInsert();
        if($params['is_disc'] == 1){
            memcache_delete($this->mmc,'66apk_disc_banner');
        }else{
            memcache_delete($this->mmc,'66apk_top_banner');
        }
        return $this->LAST_INSERT_ID;
    }
    public function game_name(){
        $this->sql = "select id,game_name from 66app_game_tb where is_del=0 ";
        $this->doResultList();
        return $this->result;
    }
    public function game_list($params,$page){
        $this->limit_sql = "select * from 66app_game_tb where is_del=0 ";
        if($params['game_name']){
            $this->limit_sql = $this->limit_sql." and game_name like '%".$params['game_name']."%'";
        }
        if($params['channel']){
            $this->limit_sql = $this->limit_sql." and channel = ".$params['channel'];
        }
        if($params['type']){
            $this->limit_sql = $this->limit_sql." and type = ".$params['type'];
        }
        if($params['is_top'] && is_numeric($params['is_top']) || $params['is_top']=="0"){
            $this->limit_sql = $this->limit_sql." and is_top = ".$params['is_top'];
        }
        if($params['is_disc_rec'] && is_numeric($params['is_disc_rec']) || $params['is_disc_rec']=="0"){
            $this->limit_sql = $this->limit_sql." and is_disc_rec = ".$params['is_disc_rec'];
        }
        if($params['is_new_game'] && is_numeric($params['is_new_game']) || $params['is_new_game']=="0"){
            $this->limit_sql = $this->limit_sql." and is_new_game = ".$params['is_new_game'];
        }
        if($params['is_hot_search'] && is_numeric($params['is_hot_search']) || $params['is_hot_search']=="0"){
            $this->limit_sql = $this->limit_sql." and is_hot_search = ".$params['is_hot_search'];
        }
        if($params['app_type'] && is_numeric($params['app_type']) || $params['app_type']=="0"){
            $this->limit_sql = $this->limit_sql." and app_type = ".$params['app_type'];
        }
        if($params['app_recommend'] && is_numeric($params['app_recommend']) || $params['app_recommend']=="0"){
            $this->limit_sql = $this->limit_sql." and app_recommend = ".$params['app_recommend'];
        }
        if($params['is_chosen'] && is_numeric($params['is_chosen']) || $params['is_chosen']=="0"){
            $this->limit_sql = $this->limit_sql." and is_chosen = ".$params['is_chosen'];
        }
        $this->limit_sql = $this->limit_sql." order by is_top desc,id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
    //获取游戏信息
    public function get_game_info($params){
        $this->sql = "select * from 66app_game_tb where game_name=? and is_del=0";
        $this->params = array($params['game_name']);
        $this->doResult();
        return $this->result;
    }
    //添加游戏信息
    public function add_game($params){
        $this->sql = "insert into 66app_game_tb(game_name,game_banner,game_icon,game_desc,game_title,down_url,tags,version,channel,down_num,apk_size,apk_name,`language`,is_del,add_time,system,img1,img2,img3,img4,is_gift,update_time,`type`,is_top,last_update,disc_img_text,is_disc_new,disc_img,m_title,is_disc_rec,
disc_rec_img,is_new_game,is_hot_search,app_type,app_recommend,is_chosen) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['game_name'],$params['game_banner'],$params['game_icon'],$params['game_desc'],$params['game_title'],$params['down_url'],implode(',',$params['tags']),$params['version'],$params['channel'],$params['down_num'],$params['apk_size'],$params['apk_name'],$params['language'],'0',
            time(),$params['system'], $params['img1'],$params['img2'],$params['img3'],$params['img4'],$params['is_gift'],$params['update_time'],$params['type'], $params['is_top'],time(),$params['disc_img_text'],$params['is_disc_new'],$params['disc_img'],$params['m_title'],$params['is_disc_rec'],$params['disc_rec_img'],$params['is_new_game'],$params['is_hot_search'],$params['app_type'],$params['app_recommend'],$params['is_chosen']);
        $this->doInsert();
        $game_id = $this->LAST_INSERT_ID;
        if($params['is_disc_rec'] == 1){
            memcache_delete($this->mmc,'66apk_disc_game_find');
        }
        memcache_delete($this->mmc,'66apk_disc_game_recommend_entry');
        memcache_delete($this->mmc,'66apk_disc_game_recommend_list1');
        memcache_delete($this->mmc,'66apk_chosen_game');
        memcache_delete($this->mmc,'66apk_chosen_game_list_1_1');
        memcache_delete($this->mmc,'66apk_chosen_game_list_2_1');
        return $game_id;
    }
    //修改游戏信息
    public function update_game($params){
        $this->sql = "update 66app_game_tb set game_name=?,game_banner=?,game_icon=?,game_desc=?,game_title=?,down_url=?,tags=?,version=?,channel=?,down_num=?,apk_size=?,`language`=?,system=?,img1=?,img2=?,img3=?,img4=?,is_gift=?,update_time=?,`type`=?,is_top=?,last_update=?,
disc_img_text=?,is_disc_new=?,disc_img=?,m_title=?,is_disc_rec=?,disc_rec_img=?,is_new_game=?,is_hot_search=?,app_type=?,app_recommend=?,is_chosen=? where id=?";
        $this->params = array($params['game_name'],$params['game_banner'],$params['game_icon'],$params['game_desc'],$params['game_title'],$params['down_url'],implode(',',$params['tags']),$params['version'],$params['channel'],$params['down_num'],$params['apk_size'],$params['language'],
            $params['system'], $params['img1'],$params['img2'],$params['img3'],$params['img4'],$params['is_gift'],$params['update_time'],$params['type'],$params['is_top'],time(),$params['disc_img_text'],$params['is_disc_new'],$params['disc_img'],$params['m_title'],$params['is_disc_rec'],
            $params['disc_rec_img'],$params['is_new_game'],$params['is_hot_search'],$params['app_type'],$params['app_recommend'],$params['is_chosen'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_game_detail_'.$params['id']);
        memcache_delete($this->mmc,'66apk_disc_game_recommend_entry');
        memcache_delete($this->mmc,'66apk_chosen_game');
        memcache_delete($this->mmc,'66apk_chosen_game_list_1_1');
        memcache_delete($this->mmc,'66apk_chosen_game_list_2_1');
        memcache_delete($this->mmc,'66apk_disc_game_ariticle_details'.$params['id']);
    }
    //判断首页推荐位
    public function get_recommend_list($id=""){
        if ($id==""){
            $this->sql = "SELECT COUNT(1) AS recommend_no FROM 66app_game_tb WHERE app_recommend=1";
        }else{
            $this->sql = "SELECT COUNT(1) AS recommend_no FROM 66app_game_tb WHERE app_recommend=1 AND id!=".$id;
        }
        $this->doResult();
        return $this->result;
    }
    public function get_game($id){
        $this->sql = "select * from 66app_game_tb where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    //删除游戏
    public function del_game($id){
        $this->sql = "update 66app_game_tb set is_del =1 where id=?";
        $this->params = array($id);
        $this->doExecute();
        memcache_delete($this->mmc,'66apk_disc_game_recommend_entry');
    }
    public function get_info($id){
        $this->sql = "select * from 66app_game_tb where is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }


}