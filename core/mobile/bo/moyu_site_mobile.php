<?php
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer','oauth.qq');
DAO('site_dao');
class moyu_site_mobile  extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new site_dao();
    }

    public function service_view(){
        $servicies = $this->DAO->get_service();
        $set = $this->DAO->get_setting();

        $this->assign("servicies", $servicies);
        $this->assign("set", $set);
        $this->display("moyu_service.html");
    }
}