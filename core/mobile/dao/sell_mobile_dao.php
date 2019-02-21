<?php
DAO('common_dao');
class sell_mobile_dao extends common_dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_info($game_id){
        $data = memcache_get($this->mmc, "game_info".$game_id);
        if(!$data){
            $this->sql = "select * from game where status=1 and is_del=0 and id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            if($data)memcache_set($this->mmc, "game_info".$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_channels(){
        $data = $this->mmc->get("channels");
        if(!$data){
            $this->sql = "select * from channels";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channels", $data, 1 ,600);
        }
        return $data;
    }

    public function insert_sell($sell_info, $sell, $type){
        $time = strtotime("now");
        $this->sql = "insert into products(type,title,game_id,channel_id,serv_id,stock,price,intro,game_storage,unit,
                  user_id,add_time,is_pub,end_time,overdue,pernum)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($type, $sell_info['title'], $sell_info['game_id'], $sell_info['ch'], $sell['serv_id'],
                            $sell_info['stock'], $sell_info['price'], $sell_info['desc'], $sell_info['game_storage'],
                            $sell_info['unit'], $_SESSION['user_id'], $time,2,$time+$sell['overdue']*24*3600, $sell['overdue'], $sell_info['pernum']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_sell_extra($sell, $sell_id){
        $this->sql = "insert into sell_extra(game_user, game_pwd, role_name, role_level, game_user_lock, tel, qq, product_id)
                    values(?,?,?,?,?,?,?,?)";
        $this->params = array($sell['game_user'], $sell['game_pwd'], $sell['role_name'], $sell['role_level'], $sell['game_user_lock'],
                                $sell['tel'], $sell['qq'], $sell_id);
        $this->doExecute();
    }

    public function insert_sell_imgs($img, $sell_id){
        $this->sql = "insert into product_imgs(product_id, img_url)values(?,?)";
        $this->params = array($sell_id, $img);
        $this->doExecute();
    }

    public function get_product($id){
        $this->sql = "select a.*,b.serv_name as s_name,c.channel_name,c.icon,d.game_name from products as a left join
                      game_servs as b on a.serv_id=b.id left join channels as c on a.channel_id=c.id left join game as d
                      on a.game_id=d.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
}