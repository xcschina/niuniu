<?php
COMMON('adminBaseCore','pageCore');
DAO('report_data_admin_dao');

class report_data extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new report_data_admin_dao();
    }

    public function data_info_view(){
        if($_POST){
            $_SESSION['report_data_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['report_data_list']);
            $params = array();
        }else{
            $params = $_SESSION['report_data_list'];
        }
        $list = $this->DAO->get_report_data($this->page,$params);
        $page = $this->pageshow($this->page, "report_data.php?act=data_info&");
        $this->assign("datalist", $list);
        $this->assign("params", $params);
        $this->assign("page_bar", $page->show());
        $this->display("report_data_list.html");
    }
}