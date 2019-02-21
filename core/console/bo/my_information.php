<?php
COMMON('baseCore');
DAO('my_information_dao');

class my_information extends baseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO=new my_information_dao();
    }
    //用户信息显示
    public function info_view(){
        $usrInfo = $this->DAO->get_user_info();
        $this->assign("usrInfo",$usrInfo);
        $this->display('my_information.html');
    }
}