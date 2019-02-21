<?php
COMMON('baseCore', 'paramUtils', 'pageCore');
DAO('weekactivity_dao','product_dao');

class weekactivity_mobile extends baseCore {

    public function __construct() {
        parent::__construct();
        $this->DAO = new weekactivity_dao();
    }

    public function activity_view(){
        $this->display("weekactivity.html");
    }

    public function m_web($app_id,$apple_id){
//        $this->open_debug();
        if(!$app_id){
            die("缺少必要参数");
        }
        if(!$apple_id){
            die("页面丢失");
        }
        $info = $this->DAO->get_app_info_by_appleid($app_id,$apple_id);
        if(empty($info)){
            $info = $this->DAO->get_app_info($app_id);
        }
        $this->assign('info',$info);
        $this->display("m_web_view.html");
    }
}