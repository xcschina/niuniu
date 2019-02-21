<?php
COMMON('dao');
class game_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
          $this->mmc = new Memcache();
         $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //获取渠道列表
    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }

    //获取游戏列表
    public function get_game_info_list($params,$page){
        $this->limit_sql="select * from game where is_del=0";
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and channel_id =".$params['channel_id'];
        }
        if($params['game_name']){
            $this->limit_sql=$this->limit_sql." and game_name like '%".$params['game_name']."%'";
        }
        if($params['first_letter']){
            $this->limit_sql=$this->limit_sql." and first_letter = '".$params['first_letter']."'";
        }
        if($params['is_hot'] && is_numeric($params['is_hot']) || $params['is_hot']=="0"){
            $this->limit_sql=$this->limit_sql." and is_hot =".$params['is_hot'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=="0"){
            $this->limit_sql=$this->limit_sql." and status =".$params['status'];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    //获取游戏列表
    public function get_all_game_info_list($params){
        $this->sql="select * from game left join channel_discount as ch on game.id=ch.game_id where is_del=0";
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->sql=$this->limit_sql." and channel_id =".$params['channel_id'];
        }
        if($params['game_name']){
            $this->sql=$this->sql." and game_name like '%".$params['game_name']."%'";
        }
        if($params['first_letter']){
            $this->sql=$this->sql." and first_letter = '".$params['first_letter']."'";
        }
        if($params['is_hot'] && is_numeric($params['is_hot']) || $params['is_hot']=="0"){
            $this->sql=$this->sql." and is_hot =".$params['is_hot'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=="0"){
            $this->sql=$this->sql." and status =".$params['status'];
        }
        $this->sql=$this->sql." order by id desc";
        $this->doResultList();
        return $this->result;
    }

    //获取游戏信息
    public function get_game_info($params){
        $this->sql="select * from game where (game_name=? or en_name=?) and `channel_id`=? and is_del=0";
        $this->params=array($params['game_name'],$params['en_name'],$params['channel_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_game_info_byid($id){
        $this->sql="select * from game where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_game_introduce_byid($id){
        $this->sql="select * from game_introduce_tb where game_id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_game_introduce($data,$id){
        $this->sql="update game_introduce_tb set publisher=?,other_name=?,`status`=?,s_time=?,game_type=?,platform=?,game_screen=? ,game_theme=?,notice=?,com_desc=?,`server`=? where id=?";
        $this->params=array($data['publisher'],$data['other_name'],$data['status'],strtotime($data['s_time']),$data['game_type'],$data['platform'],$data['game_screen'],$data['game_theme'],
            $data['notice'],$data['com_desc'],$data['server'],$id);
        $this->doExecute();
    }

    public function add_game_introduce($data){
        $this->sql="insert into game_introduce_tb(game_id,publisher,other_name,`status`,s_time,game_type,platform,game_screen,game_theme,notice,com_desc,`server`,add_time) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($data['id'],$data['publisher'],$data['other_name'],$data['status'],strtotime($data['s_time']),$data['game_type'],$data['platform'],$data['game_screen'],$data['game_theme'],
            $data['notice'],$data['com_desc'],$data['server'],time());
        $this->doInsert();
    }

    public function del_game($id){
        $this->sql="update game set is_del=1 where id=?";
        $this->params=array($id);
        $this->doExecute();
    }

    //添加游戏信息
    public function add_game($params){
        $this->sql="insert into game(game_name,en_name,subtitle,channel_id,first_letter,is_hot,discount,status,tags,product_img,
                  create_time,gift_guide,stars,img1,img2,img3,img4,game_tags,game_size,game_intr,apply) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['game_name'],$params['en_name'],$params['subtitle'],$params['channel_id'],$params['first_letter'],
            $params['is_hot'],$params['discount'],"1",$params['tags'],$params['product_img'],strtotime("now"),$params['gift_guide'],
            $params['stars'], $params['img1'],$params['img2'],$params['img3'],$params['img4'],implode(',',$params['game_tags']),$params['game_size'],$params['game_intr'],$params['apply']);
        $this->doInsert();

        $game_id = $this->LAST_INSERT_ID;

        $this->sql = "insert into channel_discount(game_id)values(?)";
        $this->params = array($game_id);
        $this->doExecute();

        $this->sql="update game set app_key=? where id=?";
        $this->params=array(md5("66173!@#.cn".$game_id),$game_id);
        $this->doExecute();
        return $game_id;
    }

    public function update_game_icon($game_icon, $game_id){
        $this->sql = "update game set game_icon=? where id=?";
        $this->params = array($game_icon, $game_id);
        $this->doExecute();
    }

    //修改游戏信息
    public function update_game($params){
        $this->sql="update game set game_name=?,en_name=?,subtitle=?,game_icon=?,channel_id=?,first_letter=?,is_hot=?,discount=?,status=?,tags=?,
                product_img=?,is_del=?,gift_guide=?,down_url=?,stars=?,img1=?,img2=?,img3=?,img4=?,keyword=?,game_units=?,game_storages=?,game_tags=?,game_size=?,game_intr=?,apply=? where id=?";
        $this->params=array($params['game_name'],$params['en_name'],$params['subtitle'],$params['game_icon'],$params['channel_id'],$params['first_letter'],
            $params['is_hot'],$params['discount'],$params['status'],$params['tags'],$params['product_img'],$params['is_del'],$params['gift_guide'],
            $params['down_url'],$params['stars'],$params['img1'],$params['img2'],$params['img3'],$params['img4'],$params['keyword'],$params['game_units'],
            $params['game_storages'],implode(",",$params['game_tags']),$params['game_size'],$params['game_intr'],$params['apply'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'game_info'.$params['id']);
    }

    public function get_game_channels($game_id){
        $this->sql = "select * from channel_discount where game_id=?";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function up_game_char_rate($game_id,$min){
        $this->sql = "update game set char_min_rate=? where id=?";
        $this->params = array($min,$game_id);
        $this->doExecute();
    }

    public function up_game_refill_rate($game_id,$min){
        $this->sql = "update game set refill_min_rate=? where id=?";
        $this->params = array($min,$game_id);
        $this->doExecute();
    }

    public function get_channel_info($ch_id){
        $this->sql = "select * from channels where id=?";
        $this->params = array($ch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_game_ch($game_id, $chs){
        $this->sql = "update channel_discount set game_id=?";
        $val = array();
        foreach($chs as $k=>$v){
            $this->sql.=",".$k."=?";
            $val[] = $v;
        }
        $this->sql.= " where game_id=?";
        $val[] = $game_id;
        array_unshift($val, $game_id);
        $this->params = $val;
        $this->doExecute();
    }

    public function update_product_discount($game_id, $type, $ch_id, $discount){
        $this->sql = "update product_discounts as a inner join products as b on a.product_id=b.id ";
        $this->sql .= "set a.chd_".$ch_id."=? where b.game_id=? and b.type=? and a.chd_".$ch_id."<>?";
        $this->params = array($discount, $game_id, $type, $discount);
        $this->doExecute();
    }

    public function hot_game_list($type){
        $this->sql="select game_id from hot_games where is_del=0 and `type`=? order by id ASC";
        $this->params=array($type);
        $this->doResultList();
        return $this->result;
    }

    public function del_hot_games($type){
        $this->sql="delete from hot_games where `type`=$type";
        $this->doExecute();
        memcache_delete($this->mmc, 'h5_hot_games');
    }

    public function add_hot_games($type,$game_ids){
        if($game_ids){
            $row=1;
            $sqlStr="insert into hot_games(type,game_id)values(?,?)";
            $params=array();
            foreach ($game_ids as $game_id){
                array_push($params,$type,$game_id);
                if($row>1){
                    $sqlStr.=",(?,?)";
                }
                $row.=$row+1;
            }
            $this->sql=$sqlStr;
            $this->params=$params;
            $this->doInsert();
        }
        memcache_delete($this->mmc, 'h5_hot_games');
    }

    public function upd_game_config($param){
        $this->sql="update game set api_serv_url=?,api_auth_url=?,api_put_order_url=?,api_2c_order_url=?,api_2b_order_url=? where id=?";
        $this->params=array($param['api_serv_url'],$param['api_auth_url'],$param['api_put_order_url'],$param['api_2c_order_url'],$param['api_2b_order_url'],$param['id']);
        $this->doExecute();
    }


    //获取游戏列表
    public function game_download_list($params,$page){
        $this->limit_sql="select t.*,c.channel_name from channels c inner join  (select d.*,g.game_name from game_downloads d inner join game g on d.game_id=g.id where 1=1)t on c.id=t.channel_id ";
        if($params['game_name']){
            $this->limit_sql=$this->limit_sql." and t.game_name like '%".$params['game_name']."%'";
        }
        if($params['channel_name']){
            $this->limit_sql=$this->limit_sql." and c.channel_name like '%".$params['channel_name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by t.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function  game_download_isexist($params){
        $this->sql="select * from game_downloads where game_id=? and channel_id=? and is_del=0";
        $this->params=array($params['game_id'],$params['channel_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_game_download_info($id){
        $this->sql="select t.*,c.channel_name from channels c inner join (select d.*,g.game_name from game_downloads d inner join game g on d.game_id=g.id where d.id=?)t on c.id=t.channel_id ";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function  do_game_download_add($params){
        $this->sql="insert into game_downloads (title,game_id,channel_id,url) values(?,?,?,?)";
        $this->params=array($params['title'],$params['game_id'],$params['channel_id'],$params['url']);
        $this->doInsert();
    }

    public function  do_game_download_edit($params){
        $this->sql="update game_downloads set title=?,url=?,is_del=? where id=?";
        $this->params=array($params['title'],$params['url'],$params['is_del'],$params['id']);
        $this->doExecute();
    }

    public function get_app_list(){
        $this->sql = "select * from niuniu.apps where status = 1 and web_serv_url != '' order by add_time desc";
        $this->doResultList();
        return $this->result;
    }
}