<?php
COMMON('baseCore','uploadHelper');
DAO('friendly_link_dao');

class friendly_link_web extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new friendly_link_dao();
    }

    public function get_link_list(){
        $params=$_POST;
        $dataList=$this->DAO->get_link_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("friendly_link_list.html");
    }

    public function link_add_view(){
        $this->display("friendly_link_add.html");
    }

    public function  do_link_add(){
        $params=$_POST;
        $params['icon']="";
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入标题"));
            exit;
        }

        if($_FILES['link_icon']['tmp_name']){
           // echo json_encode($this->error_msg("请上传ICON图片"));
            //exit;
            $params['icon']=$this->up_img('link_icon',LINK_ICON);
        }

        if(!$params['go_url']){
            echo json_encode($this->error_msg("请输入跳转地址"));
            exit;
        }


        $this->DAO->add_link($params);
        echo json_encode($this->succeed_msg("友情链接添加成功","get_link_list"));
    }


    public function link_edit_view($id){
        $link_info=$this->DAO->get_link_info($id);
        $this->assign("link_info",$link_info);
        $this->display("friendly_link_edit.html");
    }

    public function do_link_edit(){
        $params=$_POST;
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入标题"));
            exit;
        }

        if(!$_FILES['link_icon']['tmp_name']){
            $params['icon']=$params['old_link_icon'];
        }else{
            $params['icon']=$this->up_img('link_icon',LINK_ICON);
        }

        if(!$params['go_url']){
            echo json_encode($this->error_msg("请输入跳转地址"));
            exit;
        }

        $this->DAO->update_link_info($params);
        echo json_encode($this->succeed_msg("友情链接编辑成功","get_link_list"));
    }

    public function del_link($id){
        $this->DAO->del_link($id);
        echo json_encode($this->unclose_succeed_msg("删除成功","get_link_list"));
    }
}
?>