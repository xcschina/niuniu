<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('message_dao');

class message_admin extends adminBaseCore
{
    public $DAO;

    public function __construct()
    {
        parent::__construct();
        $this->DAO = new message_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $message = $this->DAO->get_message($this->page,$params);
        $game_list = $this->DAO->get_game_list();
        $guild_list = $this->DAO->get_guild_list();
        $page = $this->pageshow($this->page,"message.php?act=list&");
        $this->assign("page_bar",$page->show());
        $this->assign("game_list",$game_list);
        $this->assign("guild_list",$guild_list);
        $this->assign("params",$params);
        $this->assign("message",$message);
        $this->display("message_list.html");
    }

    public function add_view(){
        $game_list = $this->DAO->get_game_list();
        $guild_list = $this->DAO->get_guild_list();
        $this->assign("game_list",$game_list);
        $this->assign("guild_list",$guild_list);
        $this->display("message_add.html");
    }

    public function add_save(){
        $params = $_POST;
        if(!$params['title'] || !$params['type'] || !$params['sort_type'] || !$params['desc'] || !$params['app_id'] || !$params['subtitle']){
            $this->error_msg("缺少必填项");
        }
        if($params['sort_type'] == '1'){
            if($params['way'] == '1'){
                if(!$params['user_group']){
                    $this->error_msg("用户组不能为空");
                }else if(!preg_match('/^[\d,]+$/' ,$params['user_group'])){
                    $this->error_msg("用户组不能包含英文、汉字以及特殊符号");
                }else{
                    $params['user_group'] = implode(array_unique(explode(",",$params['user_group'])),",");
                }
            }elseif($params['way'] == '2') {
                if (!$_FILES['user_file']["tmp_name"]) {
                    $this->error_msg("请选择导入的文件");
                } else {
                    $fileType = $_FILES['user_file']['type'];
                    if ($fileType === "text/plain") {
                        $file_name = $_FILES['user_file']["tmp_name"];
                        $file = fopen($file_name, 'r');
                        $code_arr = array();
                        while (!feof($file)) {
                            $code = fgets($file);
                            if (strlen(trim($code)) >= 1 && is_numeric(trim($code))) {
                                $code_arr[] = trim($code);
                            }
                        }
                        $params['user_group'] = implode(array_unique($code_arr),",");
                        fclose($file);
                        unlink($file_name);
                    } else {
                        $this->error_msg("文件格式不正确");
                    }
                }
            }
        }
        if($params['sort_type'] == '2' && !$params['channel']){
            $this->error_msg("渠道不能为空");
        }
        $this->DAO->insert_message($params);
        $this->succeed_msg();
    }

    public function edit_view($id){
        $info = $this->DAO->get_message_info($id);
        $game_list = $this->DAO->get_game_list();
        $guild_list = $this->DAO->get_guild_list();
        $this->assign("game_list",$game_list);
        $this->assign("guild_list",$guild_list);
        $this->assign("info",$info);
        $this->display("message_edit.html");
    }

    public function edit_save(){
        $params = $_POST;
        if(!$params['title'] || !$params['type'] || !$params['sort_type'] || !$params['desc'] || !$params['app_id'] || !$params['subtitle']){
            $this->error_msg("缺少必填项");
        }
        if($params['sort_type'] == '1'){
            if($params['way'] == '1'){
                if(!$params['user_group']){
                    $this->error_msg("用户组不能为空");
                }else if(!preg_match('/^[\d,]+$/' ,$params['user_group'])){
                    $this->error_msg("用户组不能包含英文、汉字以及特殊符号");
                }else{
                    $params['user_group'] = implode(array_unique(explode(",",$params['user_group'])),",");
                }
            }elseif($params['way'] == '2'){
                if(!$_FILES['user_file']["tmp_name"]){
                    $this->error_msg("请选择导入的文件");
                }else{
                    $fileType = $_FILES['user_file']['type'];
                    if($fileType==="text/plain"){
                        $file_name = $_FILES['user_file']["tmp_name"];
                        $file = fopen($file_name, 'r');
                        $code_arr = array();
                        while(!feof($file)) {
                            $code = fgets($file);
                            if(strlen(trim($code)) >= 1 && is_numeric(trim($code))){
                                $code_arr[] = trim($code);
                            }
                        }
                        $params['user_group'] = implode(array_unique($code_arr),",");
                        fclose($file);
                        unlink($file_name);
                    }else{
                        $this->error_msg("文件格式不正确");
                    }
                }
            }
            if($params['channel']){
                $params['channel'] = '';
            }
        }
        if($params['sort_type'] == '2'){
            if(!$params['channel']){
                $this->error_msg("渠道不能为空");
            }
            if($params['user_group']){
                $params['user_group'] = '';
            }
        }
        if($params['sort_type'] == '3'){
                $params['channel'] = '';
                $params['user_group'] = '';
        }
        if($params['sort_type'] == '3' && $params['channel']){
            $params['channel'] = '';
        }
        $this->DAO->update_message($params);
        $this->succeed_msg();
    }

    public function push_view($id){
        $info = $this->DAO->get_message_info($id);
        $this->assign("info",$info);
        $this->display("message_push.html");
    }

    public function push_save(){
        $params = $_POST;
        if(empty($params['verifyCode']) || strtoupper($params['verifyCode'])!= $_SESSION['c']){
            $this->error_msg('图形验证码不正确');
        }
        $info = $this->DAO->get_message_info($params['id']);
        if($info['user_group']){
            $user_group = explode(",",$info['user_group']);
            foreach($user_group as $key=>$data){
                $message_log = $this->DAO->get_message_log($params['id'],$data);
                if(!$message_log){
                    $this->DAO->insert_message_log($params['id'], $data);
                }
            }
        }
        if($params['send_time'] == 2){
            if(!$params['push_time']){
                $this->error_msg("发布时间不能为空");
            }else{
                $params['push_time'] = strtotime($params['push_time']);
            }
        }else{
            $params['push_time'] = time();
        }
        $this->DAO->update_message_info($params);
        $this->succeed_msg();
    }

    public function uploaded_file(){
        if($_FILES['file']['tmp_name']){
            $params['img'] = $this->up_img('file',INTRO_IMG);
            die(json_encode(array("code"=>1,"url"=>$params['img'])));
        }
        die(json_encode(array("code"=>0,"msg"=>"出错啦")));
    }

    public function do_offline(){
        $this->DAO->offline_message($_POST['id']);
        $this->succeed_msg();
    }

}