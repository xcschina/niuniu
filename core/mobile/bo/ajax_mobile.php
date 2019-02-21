<?php
COMMON('baseCore', 'paramUtils', 'pageCore');
DAO('game_dao');

class ajax_mobile extends baseCore {

    public $DAO;
    public $id;
    protected $url;
    protected $base_url;
    protected $param;

    public function __construct() {
        parent::__construct();
        $this->DAO = new game_dao();
        $this->user_id=$_SESSION['user_id'];
        if(!$_SESSION['user_id']){
            $this->assign("is_login",'nologin');
        }
    }

    public function ajax_servs($game_id, $ch_id, $buy_type=0){
        $servs = $this->DAO->get_game_ch_servs($game_id, $ch_id);
        $servs = array_chunk($servs,50);
        rsort($servs);
        $end = end($servs);
        array_pop($servs);
        foreach($servs as $k => $v){
            $all_servs[$k+1] = $v;
        }
        $all_servs[0] = $end;
        $this->assign("servs", $all_servs);
        $this->assign("buy_type", $buy_type);
        $this->display("ajax/ajax_servs.html");
    }

    public function iap_servs($game_id, $group_id){
        $servs = $this->DAO->get_game_iap_servs($game_id, $group_id);
        $servs = array_chunk($servs, 50);
        $this->assign("servs", $servs);
        $this->assign("buy_type", 8);
        $this->display("ajax/ajax_servs.html");
    }

    public function ajax_products($game_id, $buy_type){
        $products = $this->DAO->get_all_products($game_id, $buy_type);

        $this->assign("products", $products);
        if(isset($_GET['game_user_id'])){
            $this->assign("game_user_id", $_GET['game_user_id']);
        }
        $this->display("ajax/ajax_products.html");
    }

    public function ajax_channels(){
        $chs = $this->DAO->get_channels();
        $this->assign("chs", $chs);
        $this->display("ajax/ajax_channels.html");
    }

    public function ajax_login(){
        $this->open_debug();
        $this->page_hash();
        $_SESSION['m_login_error'] = '';
        $this->display("ajax/ajax_login.html");
    }
}