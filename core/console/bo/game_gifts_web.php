<?php
COMMON('baseCore','uploadHelper');
DAO('game_gifts_dao','common_dao');
class game_gifts_web extends baseCore{

    public $DAO;
    public $COMMON;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_gifts_dao();
        $this->COMMON=new common_dao();
    }

    public function gift_info_list(){
        $params=$_POST;
        $game_list= $this->COMMON->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->COMMON->get_channels_list();
        $dataList=$this->DAO->gift_info_lis($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("game_gifts_info_list.html");
    }

    public function gift_info_view(){
        $id=$_GET['id'];
        $game_list= $this->COMMON->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->COMMON->get_channels_list();
        $data=$this->DAO->gift_info($id,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("data", $data);
        $this->display("game_gift_info_edit.html");
    }

    public function get_gifts_list(){
        $params=$_POST;
        $gift_batch_list= $this->DAO->get_gift_batch_list();
        $game_list= $this->COMMON->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->COMMON->get_channels_list();
        $dataList=$this->DAO->get_gifts_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("gift_batch_list",$gift_batch_list);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("game_gifts_list.html");
    }

    public function add_view(){
        $game_list= $this->COMMON->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("game_gifts_add.html");
    }

    public function params_verify($data){
        if(!$data['title']){
            die(json_encode($this->error_msg("请输入礼包名")));
        }
        if(!$data['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$data['channel_id']){
            echo json_encode($this->error_msg("请选择渠道"));
        }
        if(!$data['price']){
            $data['price'] = 0;
        }
        if($data['integral']< 0 || !is_numeric($data['integral'])){
            die(json_encode($this->error_msg("积分必须为正整数")));
        }
        if($data['num']<=0 || !is_numeric($data['num'])){
            die(json_encode($this->error_msg("请输入礼包数")));
        }
        if(!$data['content']){
            die(json_encode($this->error_msg("请输入礼包内容")));
        }
        if(!$data['get_way']){
            die(json_encode($this->error_msg("请输入领取方式")));
        }
        if(!$data['start_time']){
            die(json_encode($this->error_msg("请输入开始时间")));
        }
        if(!$data['end_time']){
            die(json_encode($this->error_msg("请输入结束时间")));
        }
        return $data;
    }

    public function add_gift_info($data){
        $data['remain'] = $data['num'];

        $data['start_time']=strtotime($data['start_time']);
        $data['end_time']=strtotime($data['end_time']);
        if($data['effective_time']){
            $data['effective_time']=strtotime($data['effective_time']);
        }
        if($data['expired_time']){
            $data['expired_time']=strtotime($data['expired_time']);
        }
        $id = $this->DAO->add_gift_info($data);
        if(!$id) {
            die(json_encode($this->error_msg("礼包信息录入失败")));
        }
        return $id;
    }

    public function add_batch_gift($batch_id,$data){
        $data['batch_id']=$batch_id;
        $data['price']=floatval($data['price']);
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
        $this->DAO->batch_add_gift($code_arr,$data);
        $fail_code=implode(",",$fail_code);
        fclose($file);
        unlink($file_name);
        if($fail_code){
            echo json_encode($this->succeed_msg("礼包录入成功(系统已自动过滤重复礼包码)","gift_info_list"));
        }else{
            echo json_encode($this->succeed_msg("礼包录入成功","gift_info_list"));
        }
    }

    public function add_booking_gift($batch_id,$data){
        $data['batch_id']=$batch_id;
        $data['price']=floatval($data['price']);
        for ($i = 0; $i < $data['num']; $i++) {
            $this->DAO->batch_add_booking_gift($data);
        }
        echo json_encode($this->succeed_msg("预约礼包录入成功","gift_info_list"));
    }


    public function do_save(){
        $params = $_POST;
        $params = $this->params_verify($params);
        if($params['type']=='3'){
            $batch_id = $this->add_gift_info($params);
            $this->add_booking_gift($batch_id,$params);
        }else{
            $fileType = $_FILES['codes']['type'];
            if($fileType!=="text/plain"){
                die(json_encode($this->error_msg("文件格式不正确")));
            }
            $batch_id = $this->add_gift_info($params);
            $this->add_batch_gift($batch_id,$params);
        }
    }


    public function do_edit(){
        $gift_info = $_POST;
        if(!$gift_info['title']){
            echo json_encode($this->error_msg("请输入礼包名"));
            exit;
        }
        if(!$gift_info['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        if(!$gift_info['channel_id']){
            echo json_encode($this->error_msg("请选择渠道"));
            exit;
        }

        if(!$gift_info['price']){
            $gift_info['price'] =0;
        }
        if($gift_info['integral']<0 || !is_numeric($gift_info['integral'])){
            echo json_encode($this->error_msg("积分必须为正整数"));
            exit;
        }
        if($gift_info['num']<=0 || !is_numeric($gift_info['num'])){
            echo json_encode($this->error_msg("请输入礼包数"));
            exit;
        }
        if(!$gift_info['content']){
            echo json_encode($this->error_msg("请输入礼包内容"));
            exit;
        }
        if(!$gift_info['get_way']){
            echo json_encode($this->error_msg("请输入领取方式"));
            exit;
        }
        if(!$gift_info['start_time']){
            echo json_encode($this->error_msg("请输入开始时间"));
            exit;
        }
        if(!$gift_info['end_time']){
            echo json_encode($this->error_msg("请输入结束时间"));
            exit;
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
        echo json_encode($this->succeed_msg("修改成功","gift_info_list"));
    }

    public function import_view(){
        $gift_info_list= $this->DAO->gift_info_list();
        $game_list= $this->COMMON->get_game_list();
        $channels_list=$this->COMMON->get_channels_list();
        $this->assign("gift_info_list",$gift_info_list);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("game_gifts_import.html");
    }

    public function booking_import_view(){
        $gift_info_list= $this->DAO->gift_booking_info_list();
        $this->assign("gift_info_list",$gift_info_list);
        $this->display("game_booking_gift.html");
    }

    public function do_import(){
        ini_set('display_errors', 'Off');
        $params=$_POST;
        if(!is_numeric($params["batch_id"])){
            echo json_encode($this->error_msg("请选择批次"));
            exit;
        }
        $info=$this->DAO->get_gift_info($params["batch_id"]);
        if(!$info){
            echo json_encode($this->error_msg("礼包码导入失败"));
            exit;
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
                echo json_encode($this->succeed_msg("导入成功（系统已过滤重复礼包码）","gifts_list"));
            }else{
                echo json_encode($this->succeed_msg("导入成功","gifts_list"));
            }
        }else{
            echo json_encode($this->error_msg("文件格式不正确"));
        }
    }

    public function do_booking_import(){
        $params=$_POST;
        if(!is_numeric($params["batch_id"])){
            die(json_encode($this->error_msg("请选择批次")));
        }
        $info = $this->DAO->get_gift_info($params["batch_id"]);
        if(!$info){
            die(json_encode($this->error_msg("礼包码导入失败")));
        }
        $params['game_id'] = $info['game_id'];
        $params['serv_id'] = $info['serv_id'];
        $params['channel_id'] = $info['channel_id'];
        $params['price'] = $info['price'];
        $last_id = $this->DAO->get_last_booking_gift($params["batch_id"]);
        if(!$last_id){
            die(json_encode($this->error_msg("无需更新预约礼包。")));
        }
        $fileType = $_FILES['codes']['type'];
        if($fileType==="text/plain"){
            $file_name = $_FILES['codes']["tmp_name"];
            $file = fopen($file_name, 'r');
            $i=0;
            while (!feof($file)) {
                $code = fgets($file);
                if(strlen(trim($code)) >= 1){
                    $id = $this->DAO->get_last_booking_gift($params["batch_id"]);
                    if(!$id){
                        fclose($file);
                        unlink($file_name);
                        die(json_encode($this->succeed_msg("导入成功", "gifts_list")));
                    }
                    $this->DAO->update_booking_gift($id,$code);
                }
            }
            fclose($file);
            unlink($file_name);
            $count = $this->DAO->get_count_booking_gift($params["batch_id"]);
            if($count > 0){
                $msg = "导入成功，还有".$count."个礼包未导入";
            } else {
                $msg = "全部导入成功";
            }
            die(json_encode($this->succeed_msg($msg , "gifts_list")));
        } else {
            die(json_encode($this->error_msg("文件格式不正确")));
        }
    }


    public function do_del($id){
        $this->DAO->del_gift($id);
        $result['statusCode']="200";
        echo json_encode($result);
    }
}
?>