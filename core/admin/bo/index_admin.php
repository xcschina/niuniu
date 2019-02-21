<?php
COMMON('adminBaseCore');
BO("login_admin");
DAO('login_dao','system_setting_dao');

class index_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new login_dao();
    }
    
    public function index_view(){
        $user_info = $this->DAO->get_user_by_id($_SESSION['usr_id']);
        if(empty($user_info) || $_COOKIE['token'] != $user_info['token']){
            header("location:login.php?act=login");
        }
        $_SESSION['is_online'] = $this->DAO->get_online_info('is_online');
        $this->display("index.html");
    }
}