<?php
COMMON('baseCore','uploadHelper');
DAO('site_setting_dao');

class site_setting_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new site_setting_dao();
    }

    public function get_site_setting_list(){
        $dataList=$this->DAO->get_site_setting_list($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->display("site_setting_list.html");
    }

    public function setting_edit_view($id){
        $setting_info=$this->DAO->get_setting_info_byid($id);
        $this->assign("info",$setting_info);
        $this->display("site_setting_edit.html");
    }

    public function do_setting_edit(){
        $params=$_POST;
        if($_FILES['weixin_img']['tmp_name']){
            $params['weixin_img']=$this->up_img('weixin_img',INTRO_IMG);
        }else{
            $params['weixin_img']=$params['old_weixin_img'];
        }
        if($params['qq_discount']<0 || $params['qq_discount']>10){
            die(json_encode($this->error_msg("请输入范围之内的值","setting_edit_view")));
        }
        $this->DAO->update_setting($params);
        echo json_encode($this->succeed_msg("站点设置编辑成功","site_setting_list"));
    }

}