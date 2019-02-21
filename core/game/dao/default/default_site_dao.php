<?php
COMMON('dao');
class default_site_dao extends Dao {
	public $mmc;

	public function __construct() {
		parent::__construct();
//		$this->mmc = new Memcache();
//		$this->mmc->connect(MMCHOST, MMCPORT);
	}

    public function get_game_info($site_id){
        $this->sql = "select * from game where id=?";
        $this->params = array($site_id);
        $this->doResult();
        $info = $this->result;
        return $info;
    }

    public function get_article($site_id, $module_id){
        $this->sql = "select * from articles where game_id=? and part_id=? order by id desc";
        $this->params = array($site_id, $module_id);
        $this->doResultList();
        $data = $this->result;
        return $data;
    }

    public function get_app_downs($site_id){
        $this->sql = "select * from game_downloads where game_id=? and is_del=0 order by channel_id";
        $this->params = array($site_id);
        $this->doResultList();
        $data = $this->result;
        return $data;
    }

    public function get_links(){
        $this->sql = "select * from friendly_links ";
        $this->params = array();
        $this->doResultList();
        $data = $this->result;
		return $data;
	}

	public function exchanges_data($app_id){
		$this->sql = "select * from products where game_id=? and `type`=3 and is_pub=1 order by price";
		$this->params = array($app_id);
		$this->doResultList();
		return $this->result;
	}

	public function get_game_dict($game_id){
		//memcache_delete($this->mmc, "gao7gao8webserver" . $game_id);
		return memcache_get($this->mmc, "gao7gao8webserver" . $game_id);
	}

	public function set_game_dict($game_id, $game_server){
		memcache_set($this->mmc, "gao7gao8webserver".$game_id, $game_server, MEMCACHE_COMPRESSED, 10);
	}

	public function get_player_name($serv_id, $usr_name, $game_id){
		memcache_delete($this->mmc, "WEB-PLAYER-" . $game_id ."-s".$serv_id."-p".$usr_name);
		return memcache_get($this->mmc, "WEB-PLAYER-" . $game_id ."-s".$serv_id."-p".$usr_name);
	}

	public function set_player_name($serv_id, $usr_name, $game_id, $player){
		return memcache_set($this->mmc, "WEB-PLAYER-" . $game_id ."-s".$serv_id."-p".$usr_name, $player,1,60);
	}

	public function get_order($order_id){
        COMMON('streamBaseDao');
        $dao = new streamBaseDao();
        $dao->sql = "select * from pay_ordertb where OrderId=?";
        $dao->params = array($order_id);
        $dao->doResult();
		return $dao->result;
	}

	public function get_banks(){
//		$data = memcache_get($this->mmc, "bank_codes");
        $data="";
		if(!$data){
			$this->sql = "select * from bankcodes where seq!=15 order by seq";
			$this->params = array();
			$this->doResultList();
			$data = $this->result;
//			memcache_set($this->mmc, "bank_codes", $data, 1, 600);
		}
		return $data;
	}

	public function get_article_info($id){
        $this->sql = "select * from articles where id=?";
        $this->params = array($id);
        $this->doResult();
        $data = $this->result;
		return $data;
	}

    public function get_banners($game_id,$num){
//        $banners = memcache_get($this->mmc, 'mod_articles7');
        $banners="";
        if(!$banners){
            $this->sql = "select * from articles where is_pub=1 and part_id=7 order by lastupdate desc,id desc limit ".$num;
            $this->doResultList();
            $banners = $this->result;
//            memcache_set($this->mmc, 'mod_articles7', $banners, 1, 600);
        }
        return $banners;
    }

}
?>