<?php
COMMON('baseCore', 'paramUtils', 'pageCore');
DAO('game_dao','gift_dao');

class gift_mobile extends baseCore {

    public $DAO;
    public $id;
    protected $url;
    protected $base_url;
    protected $param;
    protected $game_dao;

    public function __construct() {
        parent::__construct();
        ini_set("display_error","on");
        $this->DAO = new gift_dao();
        $this->game_dao = new game_dao();
    }

    public function item_view($id){
        $info = $this->DAO->get_gift_info($id);
        $game_info  = $this->game_dao->get_game($info['game_id']);

        $this->assign("info", $info);
        $this->assign("game", $game_info);
        $this->display("gift.html");
    }
}