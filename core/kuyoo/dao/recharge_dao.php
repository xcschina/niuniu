<?php
COMMON('dao');

class recharge_dao extends Dao {

    public function __construct() {
        parent::__construct();
       // $this->mmc = new Memcache();
        //$this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_recharge_games(){
      //  $games = memcache_get($this->mmc, 'character_games');
       // if(!$games){
            $this->sql = "select * from game where status=1 and is_del=0 and is_recharge=1";
            $this->doResultList();
            $games = $this->result;
           // memcache_set($this->mmc, 'character_games', $games, 1, 600);
        //}
        return $games;
    }

    public function get_banner(){
        $this->sql="select * from articles where is_pub=1 and img <>'' and go_url <>'' order by add_time desc  ";
        $this->doResult();
        return $this->result;
    }
}
?>