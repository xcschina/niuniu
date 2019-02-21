<?php
COMMON('dao');
class common_dao extends Dao {

    public function __construct() {
        parent::__construct();
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

    public function get_user_info($user_id){
        $this->sql = "select * from user_info where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
}