<?php
COMMON('baseCore','uploadHelper');
DAO('app_gifts_dao','common_dao');
class app_gifts_web extends baseCore{

    public $DAO;
    public $COMMON;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new app_gifts_dao();
        $this->COMMON=new common_dao();
    }

    public function gift_list(){
        $params=$_POST;
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $dataList=$this->DAO->gift_info_lis($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("app_gifts_list.html");
    }

    public function gift_view(){
        $id=$_GET['id'];
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $data=$this->DAO->gift_info($id,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->assign("data", $data);
        $this->display("app_gift_edit.html");
    }

    public function gifts_list(){
        $params=$_POST;
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $dataList=$this->DAO->get_gifts_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("app_gifts_info_list.html");
    }

    public function add_view(){
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("app_gifts_add.html");
    }

    public function do_save(){
        ini_set('display_errors', 'On');
        $gift_info = $_POST;
        if(!$gift_info['title']){
            die(json_encode($this->error_msg("请输入礼包名")));
        }
        if(!$gift_info['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$gift_info['price']){
            $gift_info['price'] =0;
        }
        if($gift_info['integral']< 0 || !is_numeric($gift_info['integral'])){
            die(json_encode($this->error_msg("积分必须为正整数")));
        }
        if($gift_info['num']<=0 || !is_numeric($gift_info['num'])){
            die(json_encode($this->error_msg("请输入礼包数")));
        }
        if(!$gift_info['content']){
            die(json_encode($this->error_msg("请输入礼包内容")));
        }
        if(!$gift_info['get_way']){
            die(json_encode($this->error_msg("请输入领取方式")));
        }
        if(!$gift_info['start_time']){
            die(json_encode($this->error_msg("请输入开始时间")));
        }
        if(!$gift_info['end_time']){
            die(json_encode($this->error_msg("请输入结束时间")));
        }
        $fileType = $_FILES['codes']['type'];
        if($fileType!=="text/plain"){
            die(json_encode($this->error_msg("文件格式不正确")));
        }
        $gift_info['remain'] = $gift_info['num'];
        $gift_info['start_time']=strtotime($gift_info['start_time']);
        $gift_info['end_time']=strtotime($gift_info['end_time']);
        if($gift_info['effective_time']){
            $gift_info['effective_time']=strtotime($gift_info['effective_time']);
        }
        if($gift_info['expired_time']){
            $gift_info['expired_time']=strtotime($gift_info['expired_time']);
        }
        $id = $this->DAO->add_gift_info($gift_info);
        if(!$id) {
            die(json_encode($this->error_msg("礼包信息录入失败")));
        }

        $gift_info['batch_id']=$id;
        $gift_info['price']=floatval($gift_info['price']);
        $file_name = $_FILES['codes']["tmp_name"];
        $file = fopen($file_name, 'r');
        $i=0;

        $code_arr=array();
        $fail_code=array();
        while (!feof($file)) {
            $code = fgets($file);
            if(strlen(trim($code)) >= 1){
                //$gift_info['code']=trim($code);
                //$code_info=$this->DAO->gift_code_isexist($gift_info);
               /* if($code_info){
                    $fail_code[$i]=$code;
                    $i++;
                }else{
                    $code_arr[]=$code;
                   // $this->DAO->add_gift($gift_info);
                }*/

                $code_arr[]=trim($code);
            }
        }
        $this->DAO->batch_add_gift($code_arr,$gift_info);
        $fail_code=implode(",",$fail_code);
        fclose($file);
        unlink($file_name);
        if($fail_code){
            echo json_encode($this->succeed_msg("礼包录入成功(系统已自动过滤重复礼包码)","gift_list"));
        }else{
            echo json_encode($this->succeed_msg("礼包录入成功","gift_list"));
        }
    }


    public function do_edit(){
        $gift_info = $_POST;
        if(!$gift_info['title']){
            die(json_encode($this->error_msg("请输入礼包名")));
        }
        if(!$gift_info['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
//        if(!$gift_info['channel_id']){
//            echo json_encode($this->error_msg("请选择渠道"));
//            exit;
//        }

        if(!$gift_info['price']){
            $gift_info['price'] =0;
        }
        if($gift_info['integral']<0 || !is_numeric($gift_info['integral'])){
           die(json_encode($this->error_msg("积分必须为正整数")));
        }
        if($gift_info['num']<=0 || !is_numeric($gift_info['num'])){
            die(json_encode($this->error_msg("请输入礼包数")));
        }
        if(!$gift_info['content']){
            die(json_encode($this->error_msg("请输入礼包内容")));
        }
        if(!$gift_info['get_way']){
            die(json_encode($this->error_msg("请输入领取方式")));
        }
        if(!$gift_info['start_time']){
            die(json_encode($this->error_msg("请输入开始时间")));
        }
        if(!$gift_info['end_time']){
            die(json_encode($this->error_msg("请输入结束时间")));
        }
        $gift_info['start_time']=strtotime($gift_info['start_time']);
        $gift_info['end_time']=strtotime($gift_info['end_time']);
        if($gift_info['effective_time']){
            $gift_info['effective_time']=strtotime($gift_info['effective_time']);
        }
        if($gift_info['expired_time']){
            $gift_info['expired_time']=strtotime($gift_info['expired_time']);
        }
        $this->DAO->upd_gift_info($gift_info);
        echo json_encode($this->succeed_msg("修改成功","gift_list"));
    }

    public function import_view(){
        $gift_info_list= $this->DAO->gift_info_list();
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $this->assign("gift_info_list",$gift_info_list);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("app_gifts_import.html");
    }

    public function do_import(){
        ini_set('display_errors', 'Off');
        $params=$_POST;
        if(!is_numeric($params["batch_id"])){
            die(json_encode($this->error_msg("请选择批次")));
        }
        $info=$this->DAO->get_gift_info($params["batch_id"]);
        if(!$info){
            die(json_encode($this->error_msg("礼包码导入失败")));
        }
        $params['game_id']=$info['game_id'];
        $params['serv_id']=$info['serv_id'];
        $params['channel_id']=$info['channel_id'];
        $params['price']=$info['price'];
        $fileType = $_FILES['codes']['type'];
        if($fileType==="text/plain"){
            $file_name = $_FILES['codes']["tmp_name"];
            $file = fopen($file_name, 'r');
            $i=0;

            $code_arr=array();
            $fail_code=array();
            while (!feof($file)) {
                $code = fgets($file);
                if(strlen(trim($code)) >= 1){
                    /*$params['code']=trim($code);
                    $code_info=$this->DAO->gift_code_isexist($params);
                    if($code_info){
                        $fail_code[$i]=$code;
                        $i++;
                    }else{
                        $this->DAO->add_gift($params);
                    }*/

                    $code_arr[]=trim($code);
                }
            }
            $this->DAO->batch_add_gift($code_arr,$params);
            $fail_code=implode(",",$fail_code);
            fclose($file);
            unlink($file_name);
            $gift_count=$this->DAO->get_gift_count($params["batch_id"]);
            $gift_count['id']=$params["batch_id"];
            $this->DAO->upd_gift_count($gift_count);
            if($fail_code){
                echo json_encode($this->succeed_msg("导入成功（系统已过滤重复礼包码）","get_gifts_list"));
            }else{
                echo json_encode($this->succeed_msg("导入成功","get_gifts_list"));
            }
        }else{
            echo json_encode($this->error_msg("文件格式不正确"));
        }
    }

    public function do_del($id){
        $this->DAO->del_gift($id);
        $result['statusCode']="200";
        echo json_encode($result);
    }
}
?>