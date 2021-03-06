<?php
COMMON('dao');
class gift_dao extends Dao {

	public function __construct() {
		parent::__construct();
		$this->mmc = new Memcache();
		$this->mmc->connect(MMCHOST, MMCPORT);
	}
	//礼包列表
	public function get_gift_list(){
		$this->sql = "select a.*,b.game_name,b.game_icon from game_gift_info as a INNER JOIN game as b on a.game_id=b.id
                        where a.type!=3 and a.is_del=0 and is_attach=0 and a.remain>0 and a.end_time>unix_timestamp(now()) order by a.is_top desc,a.lastupdate desc limit 50";
		$this->doResultList();
		return $this->result;
	}
	//礼包批次
	public function get_user_gift_batch($user_id){
		$this->sql = "select batch_id from weixin_gifts where user_id=? group by batch_id";
		$this->params = array($user_id);
		$this->doResultList();
		return $this->result;
	}
	//礼包信息
	public function get_gift_info($id){
		$this->sql = "select a.*,b.game_name,b.game_icon from game_gift_info as a INNER JOIN game as b on a.game_id=b.id where a.id=?";
		$this->params = array($id);
		$this->doResult();
		return $this->result;
	}
	//推荐礼包
	public function get_game_gifts($game_id){
//		$data = memcache_get($this->mmc,"game_gifts".$game_id);
		if(!$data){
			$this->sql = "select a.*,b.game_name,b.game_icon from game_gift_info as a INNER JOIN game as b on a.game_id=b.id
                where a.game_id=? and a.is_del=0 and a.remain>0 and a.end_time>unix_timestamp(now()) order by a.id desc limit 10";
			$this->params = array($game_id);
			$this->doResultList();
			$data = $this->result;
			memcache_set($this->mmc, "game_gifts".$game_id, 1, 600);
		}
		return $data;
	}
	//游戏详情
	public function get_game_info($id){
		$this->sql = "select * from game where id=?";
		$this->params = array($id);
		$this->doResult();
		return $this->result;
	}
	//游戏下载地址
	public function get_game_downs($game_id){
		$data = memcache_get($this->mmc,"game_downs".$game_id);
		if(!$data){
			$this->sql = "select a.*,b.icon from game_downloads as a INNER  JOIN channels as b on a.channel_id=b.id
                where a.is_del=0 and a.game_id=? order by b.id=6 desc";
			$this->params = array($game_id);
			$this->doResultList();
			$data = $this->result;
			memcache_set($this->mmc,"game_downs".$game_id, $data,1, 600);
		}
		return $data;
	}
	//用户礼包记录
	public function get_user_gifts($user_id){
		$data = memcache_get($this->mmc, 'usr_gifts'.$user_id);
		if(!$data){
			$this->sql = "select a.*,b.code,c.title,b.batch_id from weixin_gifts as a INNER join game_gifts as b on a.gift_id=b.id
                      INNER join game_gift_info as c on b.batch_id=c.id where a.user_id=? order by a.id desc";
			$this->params = array($user_id);
			$this->doResultList();
			$data = $this->result;
			memcache_set($this->mmc, 'usr_gifts'.$user_id, $data);
		}
		return $data;
	}
	//获取礼包
	public function get_gift($batch_id){
		$this->sql = "select * from game_gifts where is_use=0 and batch_id=?";
		$this->params = array($batch_id);
		$this->doResult();
		return $this->result;
	}

	public function update_code_status($gift, $user_id, $batch_id, $wx_id){
		$this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
		$this->params = array($user_id, strtotime("now"), $gift['id']);
		$this->doExecute();

		$this->sql = "update game_gift_info set remain=remain-1 where id=?";
		$this->params = array($batch_id);
		$this->doExecute();

		$this->sql = "insert into weixin_gifts(openid, gift_id, user_id, game_id, add_time,batch_id)values(?,?,?,?,?,?)";
		$this->params = array($wx_id, $gift['id'], $user_id, $gift['game_id'], strtotime("now"), $gift['batch_id']);
		$this->doExecute();
		memcache_delete($this->mmc, 'usr_gifts'.$user_id);
	}

}