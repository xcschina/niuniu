<?php
COMMON('dao');
class articles_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //游戏列表
    public function get_game_list(){
        $this->sql="select * from game where is_del=0 and status=1";
        $this->doResultList();
        return $this->result;
    }
    //区服列表
    public function get_game_servs_list(){
        $this->sql="select * from game_servs ";
        $this->doResultList();
        return $this->result;
    }
    //获取渠道列表
    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }

    //商品信息
    public function get_article_info($id){
        $this->sql="select * from articles where id =?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    //获取模块列表
    public function get_parts_list($params,$page){
        $this->limit_sql="select * from parts  where 1=1";
        if($params['name']){
            $this->limit_sql=$this->limit_sql." and `name` like '%".$params['name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function  get_part_by_name($name){
        $this->sql="select * from parts where name=?";
        $this->params=array($name);
        $this->doResult();
        return $this->result;
    }

    public function  get_part_by_id($id){
        $this->sql="select * from parts where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function add_part($name){
        $this->sql="insert into parts(name)values(?)";
        $this->params=array($name);
        $this->doInsert();
    }

    public function upd_part($params){
        $this->sql="update parts set name=? where id=?";
        $this->params=array($params['name'],$params['id']);
        $this->doExecute();
    }

    public function parts_list(){
        $this->sql="select * from parts  ";
        $this->doResultList();
        return $this->result;
    }

    public function get_articles_list($params,$page){
        $this->limit_sql="select p.name,a.*,b.real_name from articles as a inner join parts as p on p.id=a.part_id 
                          inner join admins as b on a.create_admin_id=b.id where 1=1";
        if($params['part_id'] && is_numeric($params['part_id'])){
            $this->limit_sql=$this->limit_sql." and a.part_id =".$params['part_id'];
        }
        if($params['title']){
            $this->limit_sql=$this->limit_sql." and a.title like '%".$params['title']."%'";
        }
        if($params['intro']){
            $this->limit_sql=$this->limit_sql." and a.intro like '%".$params['intro']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function add_article($params){
        $this->sql="insert into articles(part_id,title,intro,img,go_url,summary,source,tags,game_id,add_time,is_pub,lastupdate,create_admin_id,update_admin_id)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['part_id'],$params['title'],$params['intro'],$params['img'],$params['go_url'],$params['summary'],$params['source'],$params['tags']
                            ,$params['game_id'],strtotime("now"),$params['is_pub'],strtotime("now"), $_SESSION['usr_id'], $_SESSION['usr_id']);
        $this->doInsert();
        $data=$this->LAST_INSERT_ID;
        memcache_delete($this->mmc, 'mod_articles'.$params['part_id']);
        return $data;
    }

    public function edit_article($params){
        $this->sql="update articles set part_id=?,title=?,img=?,go_url=?,summary=?,source=?,tags=?,game_id=?,intro=?,is_pub=?,lastupdate=?, update_admin_id=? where id=?";
        $this->params=array($params['part_id'],$params['title'],$params['img'],$params['go_url'],$params['summary'],$params['source'],$params['tags'],
                            $params['game_id'],$params['intro'],$params['is_pub'],strtotime("now"), $_SESSION['usr_id'], $params['id']);
        $this->doExecute();
        memcache_delete($this->mmc, 'mod_articles'.$params['part_id']);
    }
}