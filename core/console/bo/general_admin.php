<?php
COMMON('baseCore','uploadHelper');
DAO('general_dao');

class general_admin extends baseCore{

    public $DAO;

    public function __construct()
    {
        parent::__construct();
        $this->DAO = new general_dao();
    }

    public function general_list(){
        $params = $_POST;
        $general_name = $this->DAO->get_general_name();
        $general = $this->DAO->get_general_list($params,$this->pageCurrent);
        $game_list = $this->DAO->get_game();
        $this->pageInfo($this->pageCurrent);
        $this->assign("general_name",$general_name);
        $this->assign("game_list",$game_list);
        $this->assign("params",$params);
        $this->assign("general",$general);
        $this->display("general_list.html");
    }

    public function general_add(){
        $game_list = $this->DAO->get_game();
        $this->assign("game_list",$game_list);
        $this->display("general_add.html");
    }

    public function general_save(){
        $params = $_POST;
        if(!$params['title']){
            die(json_encode($this->error_msg("请填写活动标题")));
        }
        if($params['type'] == 1){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }
            if(!$params['batch_name']){
                die(json_encode($this->error_msg("请填写包源前缀")));
            }
        }
        if($params['type'] == 2){
            if(!$params['down_url']){
                die(json_encode($this->error_msg("请填写下载地址")));
            }else{
                $array = get_headers($params['down_url'],1);
                if(!(preg_match('/200/',$array[0]))){
                    die(json_encode($this->error_msg("请输入正确的下载地址")));
                }
                if( $_SERVER['HTTP_REFERER'] == "" ){
                    header("Location:".$params['down_url']); exit;
                }
            }
        }
        if($_FILES['banner']['tmp_name']){
            $params['banner'] = $this->up_img('banner','images/banner_img');
        }else{
            die(json_encode($this->error_msg("请上传游戏详情图")));
        }
        if($_FILES['top_img']['tmp_name']){
            $params['top_img'] = $this->up_img('top_img','images/banner_img');
        }else{
            die(json_encode($this->error_msg("请上传游戏顶部背景图")));
        }
        if($_FILES['middle_img']['tmp_name']){
            $params['middle_img'] = $this->up_img('middle_img','images/banner_img');
        }else{
            die(json_encode($this->error_msg("请上传游戏中部背景图")));
        }
        if($_FILES['bottom_img']['tmp_name']){
            $params['bottom_img'] = $this->up_img('bottom_img','images/banner_img');
        }
        $params['bg_img'] = $this->up_imgs("bg_img");
        if(!$params['bg_img']){
            die(json_encode($this->error_msg("请上传背景图片")));
        }
        $this->DAO->insert_activity($params);
        die(json_encode($this->succeed_msg("添加成功","general_list")));
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

    public function general_edit($id){
        $general_info = $this->DAO->get_general_info($id);
        $game_list = $this->DAO->get_game();
        $bg_img= explode(",",$general_info['bg_img']);
        $this->assign("bg_img",$bg_img);
        $this->assign("id",$id);
        $this->assign("game_list",$game_list);
        $this->assign("general_info",$general_info);
        $this->display("general_edit_view.html");
    }

    public function edit_save($id){
        $params = $_POST;
        if(!$params['title']){
            die(json_encode($this->error_msg("请填写活动标题")));
        }
        if($params['type'] == 1){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }
            if(!$params['batch_name']){
                die(json_encode($this->error_msg("请填写源包前缀")));
            }
        }
        if($params['type'] == 2){
            if(!$params['down_url']){
                die(json_encode($this->error_msg("请填写下载地址")));
            }else{
                $array = get_headers($params['down_url'],1);
                if(!(preg_match('/200/',$array[0]))){
                    die(json_encode($this->error_msg("请输入正确的下载地址")));
                }
                if( $_SERVER['HTTP_REFERER'] == "" ){
                    header("Location:".$params['down_url']); exit;
                }
            }
        }
        if($_FILES['banner']['tmp_name']){
            $params['banner'] = $this->up_img('banner','images/banner_img');
        }else{
            $params['banner'] = $params['old_banner'];
        }
        if($_FILES['top_img']['tmp_name']){
            $params['top_img'] = $this->up_img('top_img','images/banner_img');
        }else{
            $params['top_img'] = $params['old_top_img'];
        }
        if($_FILES['middle_img']['tmp_name']){
            $params['middle_img'] = $this->up_img('middle_img','images/banner_img');
        }else{
            $params['middle_img'] = $params['old_middle_img'];
        }
        if($_FILES['bottom_img']['tmp_name']){
            $params['bottom_img'] = $this->up_img('bottom_img','images/banner_img');
        }else{
            $params['bottom_img'] = $params['old_bottom_img'];
        }
        $params['bg_img'] = $this->up_imgs("bg_img");
        if(!$params['bg_img']){
            $params['bg_img'] = $params['old_bg_img'];
        }
        $this->DAO->update_general_info($id,$params);
        $list = $this->DAO->get_general($id);
        foreach($list as $key=>$data){
            $params['new_url'] = "http://apk.66173yx.com/ad/".$params['batch_name']."_".$data['code'].".apk";
            $this->DAO->update_general_msg($data['id'],$params);
        }
        die(json_encode($this->succeed_msg("编辑成功","general_list")));
    }

    public function batch_view($id){
        $this->assign("id",$id);
        $this->display("general_batch.html");
    }

    public function batch_save($id){
        $params = $_POST;
        if(!$params['start_num']){
            die(json_encode($this->error_msg("请输入数字")));
        }
        if(!$params['code_name']){
            die(json_encode($this->error_msg("请输入链接前缀")));
        }elseif(preg_match("/[\x7f-\xff]/", $params['code_name']) || preg_match("/\d+/", $params['code_name'])){
            die(json_encode($this->error_msg("链接前缀必须是英文")));
        }
        $start_num = $this->DAO->get_general_max($params['code_name']);
        if(!$start_num){
            $start_num['code'] = 0;
        }else{
            $start_num['code'] = preg_replace('/'.$params['code_name'].'/','', $start_num['code']);
        }
        $info = $this->DAO->get_general_info($id);
        for($i = (int)$start_num['code']+1; $i < (int)($params['start_num']+$start_num['code']+1); $i++){
            $code = $params['code_name'].$i;
            $params['down_url'] = "http://apk.66173yx.com/ad/".$info['batch_name']."_".$code.".apk";
            $this->DAO->insert_general($params,$info,$code,$id);
        }
        die(json_encode($this->succeed_msg("导入成功")));
    }

    public function del_general($id){
        $this->DAO->update_general($id);
        die(json_encode($this->succeed_msg("删除成功")));
    }

    public function log_list(){
        $params = $_POST;
        $game_list = $this->DAO->get_game();
        $log_list = $this->DAO->get_log_list($params,$this->pageCurrent);
        require_once (PREFIX.DS.'core/admin/bo/qa_admin.php');
        $bo = new qa_admin();
        foreach($log_list as $key=>$data){
            $visit_num = $this->DAO->get_visit_num($data['id']);
            $down_num = $this->DAO->get_down_num($data['id']);
            $log_list[$key]['visit_num'] = $visit_num['num'];
            $log_list[$key]['down_num'] = $down_num['num'];
            $log_list[$key]['device_count'] = $bo->ch_device_count($data['app_id'],$data['code']);
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("game_list",$game_list);
        $this->assign("log_list",$log_list);
        $this->display("general_log_list.html");
    }

    public function down_log($id){
        $params = $_POST;
        if($params['start_time']){
            $params['start_time'] = strtotime($params['start_time']);
        }
        if($params['end_time']){
            $params['end_time'] = strtotime($params['end_time']);
        }
        $down_log = $this->DAO->get_down_log($params,$id,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("id",$id);
        $this->assign("params",$params);
        $this->assign("down_log",$down_log);
        $this->display("general_down_log.html");
    }

    public function visit_log($id){
        $params = $_POST;
        if($params['start_time']){
            $params['start_time'] = strtotime($params['start_time']);
        }
        if($params['end_time']){
            $params['end_time'] = strtotime($params['end_time']);
        }
        $visit_log = $this->DAO->get_visit_log($params,$id,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("id",$id);
        $this->assign("params",$params);
        $this->assign("visit_log",$visit_log);
        $this->display("general_visit_log.html");
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $params['title'] = urldecode($params['title']);
        $dataList = $this->DAO->get_all_general_log_list($params);
        if($dataList){
            foreach($dataList as $key => $data){
                $dataList[$key]['down_url'] = "http://ad.66173yx.com/website.php?act=general_down&id=".$data['id'];
//                $dataList[$key]['down_url'] = "http://www.66173.cn/website.php?act=general_down&id=".$data['id'];
                if( $_SERVER['HTTP_REFERER'] == "" ){
                    header("Location:". $dataList[$key]['down_url']); exit;
                }
//                $dataList[$key]['visit_url'] ="http://www.66173.cn/website.php?act=general&id=".$data['id'];
                $dataList[$key]['visit_url'] ="http://ad.66173yx.com/website.php?act=general&id=".$data['id'];
            }
            $this->master_excel_out($dataList);
            die(json_encode($this->succeed_msg("导出成功","log_list")));
        }else{
            echo "没有要导出的数据";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setAutoSize(true);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setAutoSize(true);
        $objActSheet->getColumnDimension('D')->setAutoSize(true);
        $objActSheet->getColumnDimension('E')->setAutoSize(true);
        $objActSheet->setTitle("落地页推广地址");
        $objActSheet->setCellValue("A1", "No.");
        $objActSheet->setCellValue("B1", "活动名");
        $objActSheet->setCellValue("C1", "包ID");
        $objActSheet->setCellValue("D1", "访问地址");
        $objActSheet->setCellValue("E1", "下载地址");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n,$info['title']);
            $objActSheet->setCellValue("C".$n, $info['code']);
            $objActSheet->setCellValue("D".$n, $info['visit_url']);
            $objActSheet->setCellValue("E".$n, $info['down_url']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","落地页推广地址-".$str_now.'.xls');
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

    public function preview($id){
        $info = $this->DAO->get_general_info($id);
        $bg_img = explode(",",$info['bg_img']);
        $this->assign("bg_img",$bg_img);
        $this->assign("info",$info);
        $this->display("general_preview.html");
    }
}