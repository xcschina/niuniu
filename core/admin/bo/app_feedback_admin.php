<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('app_feedback_dao');

class app_feedback_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new app_feedback_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->get_feedback_list($this->page,$params);
        $app_list = $this->DAO->get_app_list();
        $page = $this->pageshow($this->page,"app_feedback.php?act=list&");
        $this->assign("app_list",$app_list);
        $this->assign("params",$params);
        $this->assign("page_bar",$page->show());
        $this->assign("list",$list);
        $this->display("app_feedback_list.html");
    }

    public function edit_view($id){
        $info = $this->DAO->get_feedback_info($id);
        $feedback_img= explode(",",$info['feedback_img']);
        $this->page_hash();
        $this->assign("feedback_img",$feedback_img);
        $this->assign("info",$info);
        $this->display("app_feedback_edit.html");
    }

    public function do_edit($id){
        $params = $_POST;
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$params['feedback_desc']){
            $this->error_msg("描述信息不能为空");
        }
        $params['feedback_img'] = $this->up_imgs("feedback_img");
        if(!$params['feedback_img']){
            $this->error_msg("请上传反馈回复截图");
        }
        $this->DAO->update_feedback_tb($params,$_SESSION['usr_id'],$id);
        $this->succeed_msg();
    }

    protected function up_imgs($pic){
        $img_path = "";
        if($_FILES[$pic]['tmp_name'] && $_FILES[$pic]['tmp_name'][0]){
            $imgs = $this->batch_up_img($pic, PRODUCT_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path .=$img .",";
                }
            }
            $img_path = rtrim($img_path,",");
        }
        return $img_path;
    }

}
