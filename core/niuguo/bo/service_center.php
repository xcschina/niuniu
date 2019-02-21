<?php
COMMON('baseCore');
BO('index_admin');
class service_center extends baseCore {
    public  $bo;

    public function __construct(){
        parent::__construct();
        $this->bo = new index_admin();
    }

    function view() {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $wx = $this->bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->display("service_center.html");
    }
}