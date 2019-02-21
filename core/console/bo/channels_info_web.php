<?php
COMMON('baseCore','uploadHelper');
DAO('channels_info_dao');

class channels_info_web extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new channels_info_dao();
    }

    public function get_channels_list(){
        $params=$_POST;
        $dataList=$this->DAO->get_channels_info_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("channels_info_list.html");
    }

    public function channel_add_view(){
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->display("channels_info_add.html");
    }

    public function  add_channel(){
        $params=$_POST;
        if(!$params['channel_name']){
            echo json_encode($this->error_msg("请输入渠道名"));
            exit;
        }

        if(!$_FILES['channel_icon']['tmp_name']){
            echo json_encode($this->error_msg("请上传ICON图片"));
            exit;
        }

        $info=$this->DAO->get_channel_info($params);
        if($info){
            if($info['channel_name']==$params['channel_name']){
                echo json_encode($this->error_msg("渠道名已存在"));
                exit;
            }
        }
        $params['icon']=$this->up_img('channel_icon',CHANNEL_ICON);
        $this->DAO->add_channel($params);
        echo json_encode($this->succeed_msg("渠道添加成功","channels_list"));
    }

    public function channel_edit_view($id){
        $channel_info=$this->DAO->get_channel_info_byid($id);
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->assign("channel_info",$channel_info);
        $this->display("channels_info_edit.html");
    }

    public function edit_channel(){
        $params=$_POST;
        if(!$params['channel_name']){
            echo json_encode($this->error_msg("请输入渠道名"));
            exit;
        }
        $info=$this->DAO->get_channel_info($params);
        if($info){
            if($info['channel_name']==$params['channel_name'] && $info['id']!=$params['id']){
                echo json_encode($this->error_msg("渠道名已存在"));
                exit;
            }
        }
        if(!$_FILES['channel_icon']['tmp_name']){
            $params['icon']=$params['old_channel_icon'];
        }else{
            $params['icon']=$this->up_img('channel_icon',CHANNEL_ICON);
        }

        $this->DAO->update_channel($params);
        echo json_encode($this->succeed_msg("渠道编辑成功","channels_list"));
    }
}
?>