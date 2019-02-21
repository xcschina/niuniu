<?php
COMMON('baseCore', 'pageCore');
DAO('game_dao');
BO('index_admin');
class mobile_service_admin extends baseCore {
    public $DAO;
    public $tags;
    public  $bo;

    public function __construct(){
        parent::__construct();
    }

    public function list_view() {
        $this->display("h5/contact.html");
    }


}