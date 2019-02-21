<?php
COMMON('adminBaseCore','pageCore');
DAO('device_dao');

class device_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new device_dao();
    }

    public function device_list(){
        if($_POST){
            $_SESSION['device_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['device_list']);
            $params = array();
        }else{
            $params = $_SESSION['device_list'];
        }
        $device_list = $this->DAO->get_device_list($this->page,$params);
        $page = $this->pageshow($this->page,"device.php?act=device_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("dataList",$device_list);
        $this->assign("params",$params);
        $this->display("device_list_view.html");
    }

    public function device_black_add(){
        $this->page_hash();
        $this->display("device_black_add_view.html");
    }

    public function do_device_black_add(){
        $params = $_REQUEST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        $params['device_no_add'] = trim($params['device_no_add']);
        if (!$params['device_type_add'] || !$params['device_no_add']){
            $this->error_msg("参数缺失");
        }
        $device_res = $this->DAO->get_device_black($params['device_no_add']);
        if (empty($device_res)){
            $device_id = $this->DAO->insert_device_black($params);
            $this->DAO->insert_device_opertion_log(array(
                "device_black_id"=>$device_id,
                "operation_type"=>1,
                "operation_id"=>$_SESSION['usr_id'],
                "operation_time"=>time()
            ));
        }else{
            if ($device_res['device_status']==='0' || $device_res['device_status']===0){
                $this->DAO->update_device_black(array(
                    "device_status"=>1,
                    "add_time"=>time(),
                    "id"=>$device_res['id']
                ));
                $this->DAO->insert_device_opertion_log(array(
                    "device_black_id"=>$device_res['id'],
                    "operation_type"=>1,
                    "operation_id"=>$_SESSION['usr_id'],
                    "operation_time"=>time()
                ));
            }else{
                $this->error_msg("此设备已经在黑名单中");
            }
        }
        $this->succeed_msg("操作成功");
    }

    public function relieve_device(){
        if (!$_GET['id']){
            die("id错误");
        }
        $this->page_hash();
        $this->assign('id',$_GET['id']);
        $this->display("relieve_device_view.html");
    }

    public function do_relieve_device(){
        $params = $_REQUEST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if (!$params['id']){
            $this->error_msg("id错误");
        }
        $this->DAO->update_device_black(array(
            "device_status"=>0,
            "add_time"=>time(),
            "id"=>$params['id']
        ));
        $this->DAO->insert_device_opertion_log(array(
            "device_black_id"=>$params['id'],
            "operation_type"=>2,
            "operation_id"=>$_SESSION['usr_id'],
            "operation_time"=>time()
        ));
        $this->succeed_msg("清除成功");
    }

    public function import_view(){
        $this->display('device_import_view.html');
    }

    public function do_import(){
        if(isset($_FILES['suspend_file'])){
            $suspend_file = $_FILES['suspend_file'];
            if(preg_match("/\.xls$/", $suspend_file['name'])){
                $type = 1;
                $temp = dirname($suspend_file['tmp_name']) . '/temp_test.xls';
            }elseif (preg_match("/\.xlsx$/", $suspend_file['name'])){
                $type = 2;
                $temp = dirname($suspend_file['tmp_name']) . '/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if($suspend_file['size'] >= 1024 * 1024 * 5){
                $this->error_msg("上传文件大小不能超过5M！");
            }
            if(move_uploaded_file($suspend_file['tmp_name'], $temp)){
                if(file_exists($temp)){
                    $data_arr = array(
                        array("title_name" => "设备号", "title_field" => "device_no_add"),
                    );
                    $data_list = $this->excel_import_data($temp, $data_arr, $type);
                    unlink($temp);
                    if(!$data_list){
                        $this->error_msg('导入数据格式出错或没有数据');
                    }
                    $error = '';
                    foreach($data_list as $data){
                        $device_res = $this->DAO->get_device_black($data['device_no_add']);
                        if(empty($device_res)){
                            $data['device_type_add'] = $_POST['device_type_add'];//安卓
                            $device_id = $this->DAO->insert_device_black($data);
                            $this->DAO->insert_device_opertion_log(array(
                                "device_black_id" => $device_id,
                                "operation_type" => 1,
                                "operation_id" => $_SESSION['usr_id'],//0代表脚本操作
                                "operation_time" => time()
                            ));
                        }else{
                            if($device_res['device_status'] === '0' || $device_res['device_status'] === 0){
                                $this->DAO->update_device_black(array(
                                    "device_status" => 1,
                                    "add_time" => time(),
                                    "id" => $device_res['id']
                                ));
                                $this->DAO->insert_device_opertion_log(array(
                                    "device_black_id" => $device_res['id'],
                                    "operation_type" => 1,
                                    "operation_id" => $_SESSION['usr_id'],//0代表脚本操作
                                    "operation_time" => time()
                                ));
                            }else{
                                $error .= $data['device_no_add'] .',';
                            }
                        }
                    }
                    if($error){
                        $this->succeed_msg('设备号：' .$error. "；这些设备号已经在黑名单中。已跳过");
                    }else{
                        $this->succeed_msg("导入成功！");
                    }
                }else{
                    $this->error_msg("Excel文件不存在！");
                }
            }else{
                $this->error_msg("Excel文件复制失败！");
            }
        }else{
            $this->error_msg("Excel文件上传失败！");
        }
    }
}