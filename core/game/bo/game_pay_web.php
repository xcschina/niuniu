<?php
COMMON('baseCore','pageCore');
DAO('game_site_dao');
class game_pay_web extends baseCore{

    public $DAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_site_dao();
        $this->qa_user_id = array();
    }

    public function game_pay(){
        echo "充值系统正在开发,目前暂未开放.";
        exit();
    }
}
?>