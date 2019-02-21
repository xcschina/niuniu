<?php
COMMON('adminBaseCore','pageCore','ipip/IP4datx.class');
DAO('service_admin_dao','game_admin_dao');
class service_admin extends adminBaseCore{
    public $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new service_admin_dao();
    }
    public function get_service_list(){
        $params = $_POST;
        $game_admin_dao = new game_admin_dao();
        $app_list = $game_admin_dao->get_all_app();
        $data_list = $this->DAO->get_service_list($this->page,$params);
        $page = $this->pageshow($this->page, "ccm_service.php?act=service_list&");
        $this->assign("app_list",$app_list);
        $this->assign("datalist", $data_list);
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->display("chamber/ccm_service_list.html");
    }
    public function service_add_view(){
        $game_admin_dao = new game_admin_dao();
        $app_list = $game_admin_dao->get_all_app();
        $this->assign("app_list",$app_list);
        $this->display("chamber/ccm_service_add.html");
    }
    public function add_service(){
        if ($_POST['app_id_add'] && $_POST['service_id_add'] && $_POST['service_name_add'] &&$_POST['service_type']){
            $parmas = $_POST;
            //验证同个游戏下区服ID重复
            $service_res = $this->DAO->get_check_service_id($parmas['app_id_add'],$parmas['service_id_add'],$parmas['service_name_add']);
            if (empty($service_res)){
                $app_info = $this->DAO->get_app_info($parmas['app_id_add']);
                if($app_info['chamber_type'] != '0'){
                    $chamber_list = $this->DAO->get_chamber_list();
                    $parmas['app_id'] = $parmas['app_id_add'];
                    $parmas['service_id'] = $parmas['service_id_add'];
                    foreach($chamber_list as $key=>$data){
                        $this->DAO->insert_stock_info($parmas,$data['id']);
                    }
                }
                $res = $this->DAO->insert_service($parmas);
                if ($res){
                    $this->succeed_msg("新增成功");
                }else{
                    $this->error_msg("新增失败");
                }
            }else{
                $this->error_msg("此区服已经被使用");
            }
        }else{
            $this->error_msg("缺少必填项");
        }
    }
    public function service_edit_view($id){
        $game_admin_dao = new game_admin_dao();
        $app_list = $game_admin_dao->get_all_app();
        $service_info = $this->DAO->get_service_by_id($id);
        $this->assign("applist",$app_list);
        $this->assign("serviceinfo",$service_info);
        $this->display("chamber/ccm_service_edit.html");
    }
    public function do_service_edit(){
        if ($_POST['app_id_edit'] && $_POST['service_id_edit'] && $_POST['service_name_edit']){
            $parmas = $_POST;
            //验证同个游戏下区服ID重复
            $service_res = $this->DAO->get_check_service_edit($parmas['app_id_edit'],$parmas['service_id_edit'],$parmas['service_name_edit'],$parmas['id']);
            if (empty($service_res)){
                $this->DAO->update_service($parmas);
                $this->succeed_msg("新增成功");
            }else{
                $this->error_msg("此区服已经被使用");
            }
        }else{
            $this->error_msg("缺少必填项");
        }
    }
    public function service_import_view(){
        $this->display('chamber/ccm_service_import_view.html');
    }
    public function service_import_do(){
        if (isset($_FILES['service_file'])){
            $service_file = $_FILES['service_file'];
            if (preg_match("/\.xls$/",$service_file['name'])){
                $type = 1;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$service_file['name'])){
                $type = 2;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if ($service_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }

            if (move_uploaded_file($service_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name"=>"游戏名称","title_field"=>"app_id","title_type"=>"string"),
                        array("title_name"=>"区服ID","title_field"=>"service_id","title_type"=>"int"),
                        array("title_name"=>"区服名称","title_field"=>"service_name","title_type"=>"string"),
                        array("title_name"=>"区服类型","title_field"=>"service_type","title_type"=>"string")
                    );
                    $service_data = $this->excel_import_data($temp,$data_arr,$type);
                    unlink($temp);
                    $game_admin_dao = new game_admin_dao();
                    $app_list = $game_admin_dao->get_all_app();
                    if (empty($app_list)){
                        $this->error_msg("没有维护对应游戏");
                    }
                    $game_list = array();
                    foreach ($app_list as $app_value){
                        if (!in_array($app_value['app_name'],array_values($game_list))){
                            $game_list[$app_value['app_id']] = $app_value['app_name'];
                        }
                    }
                    $game_service = array();
                    foreach ($service_data as $key=>$value){
                        if (!in_array($value['app_id'],array_values($game_list))){
                            $this->error_msg("没有".$value['app_id']."游戏");
                        }
                        $service_data[$key]['app_id'] = array_search($value['app_id'], $game_list);
                        //isset($game_service[$service_data[$key]['app_id']])?array_push($game_service[$service_data[$key]['app_id']],$value['service_id']):$game_service[$service_data[$key]['app_id']] = array($value['service_id']);
                        if (isset($game_service[$service_data[$key]['app_id']])){
                            array_push($game_service[$service_data[$key]['app_id']]['service_id'],$value['service_id']);
                            array_push($game_service[$service_data[$key]['app_id']]['service_name'],"'".$value['service_name']."'");
                        }else{
                            $game_service[$service_data[$key]['app_id']] = array("service_id"=>array($value['service_id']),"service_name"=>array("'".$value['service_name']."'"));
                        }
                        if ($value['service_type']=="独服"){
                            $service_data[$key]['service_type'] = 2;
                        }else{
                            $service_data[$key]['service_type'] = 1;
                        }
                    }
                    //判断区服ID重复
                    $service_id_all = array();
                    foreach ($game_service as $game_key=>$game_value){
                        if (count($game_value['service_id'])!=count(array_unique($game_value['service_id']))){
                            $this->error_msg("同一个游戏区服ID不能重复");
                        }
                        if (count($game_value['service_name'])!=count(array_unique($game_value['service_name']))){
                            $this->error_msg("同一个游戏区服名称不能重复");
                        }
                        array_push($service_id_all,array("app_id"=>$game_key,"service_id_all"=>implode(",",$game_value['service_id']),"service_name_all"=>implode(",",$game_value['service_name'])));
                    }
                    //查询数据库区服ID是否重复
                    $check_res = $this->DAO->check_services_import($service_id_all);
                    if (!empty($check_res)){
                        $this->error_msg("区服已经存在");
                    }
                    //导入mysql
                    $id = $this->DAO->import_services($service_data);
                    foreach($service_data as $key=>$data){
                        $app_info = $this->DAO->get_app_info($data['app_id']);
                        if($app_info['chamber_type'] != '0'){
                            $chamber_list = $this->DAO->get_chamber_list();
                            foreach($chamber_list as $k=>$d){
                                $this->DAO->insert_stock_info($data,$d['id']);
                            }
                        }
                    }
                    if (!$id){
                        $this->error_msg("导入失败！");
                    }
                    $this->succeed_msg("导入成功！");
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

    public function tpl_down(){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏区服");
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->setCellValue("A1", "游戏名称");
        $objActSheet->setCellValue("B1", "区服ID");
        $objActSheet->setCellValue("C1", "区服名称");
        $objActSheet->setCellValue("D1", "区服类型");
        $objActSheet->setCellValue("A2", "西山居魔域手游-应用宝");
        $objActSheet->setCellValue("B2", "10081");
        $objActSheet->setCellValue("C2", "神域之光1服");
        $objActSheet->setCellValue("D2", "独服");
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏区服导入模版-".$str_now.".xls");
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$title.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }

//ip区域判断
    public function import_do(){
//        $this->open_debug();
        //select * from stats_user_app where AppID=6024 and Channel='jrtt1' and RegTime>1539792000 and RegTime<1541260800 GROUP BY RoleID
        if (isset($_FILES['service_file'])){
            $service_file = $_FILES['service_file'];
            if (preg_match("/\.xls$/",$service_file['name'])){
                $type = 1;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$service_file['name'])){
                $type = 2;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if (move_uploaded_file($service_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name"=>"ActIP","title_field"=>"ActIP"),
                        array("title_name"=>"ActTime","title_field"=>"ActTime"),
                        array("title_name"=>"AppID","title_field"=>"AppID"),
                        array("title_name"=>"AreaServerID","title_field"=>"AreaServerID"),
                        array("title_name"=>"AreaServerName","title_field"=>"AreaServerName"),
                        array("title_name"=>"Channel","title_field"=>"Channel"),
                        array("title_name"=>"GUID","title_field"=>"GUID"),
                        array("title_name"=>"ID","title_field"=>"ID"),
                        array("title_name"=>"RegIP","title_field"=>"RegIP"),
                        array("title_name"=>"RegTime","title_field"=>"RegTime"),
                        array("title_name"=>"RoleID","title_field"=>"RoleID"),
                        array("title_name"=>"RoleLevel","title_field"=>"RoleLevel"),
                        array("title_name"=>"RoleName","title_field"=>"RoleName"),
                        array("title_name"=>"SID","title_field"=>"SID"),
                        array("title_name"=>"UserID","title_field"=>"UserID")
                    );
                    $service_data = $this->excel_import_data($temp,$data_arr,$type);
                    unlink($temp);
                    foreach ($service_data as $key=>$value){
                        $info = $this->DAO->get_role_info($value['RoleID']);
                        $bs = new IP();
                        if(!$info){
                            $Act = $this->DAO->get_ip_session($value['ActIP']);
                            if(!$Act){
                                $Act = $bs->find($value['ActIP']);
                                $this->DAO->set_ip_session($value['ActIP'],$Act);
                            }
                            $Reg = $this->DAO->get_ip_session($value['RegIP']);
                            if(!$Reg){
                                $Reg = $bs->find($value['RegIP']);
                                $this->DAO->set_ip_session($value['RegIP'],$Act);
                            }
                            $this->DAO->insert_user_app($value,$Act[1],$Reg[1]);
                        }
                    }
//                    var_dump($service_data);
                    $this->succeed_msg("导入成功！");
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