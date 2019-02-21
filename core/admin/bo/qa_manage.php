<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('qa_manage_dao');

class qa_manage extends adminBaseCore{
    public $DAO;
    public function __construct() {
        parent::__construct();
        $this->DAO = new qa_manage_dao();
    }

    public function black_list(){
        $data = $this->DAO->get_ios_black_list($this->page,$_GET);
        $page = $this->pageshow($this->page, "qa_manage.php?act=black_list&");
        $this->assign('page_bar',$page->show());
        $this->assign("data", $data);
        $this->assign("sid", $_GET['sid']);
        $this->display("qa_manage/black_list.html");
    }

    public function black_edit($id){
        $blak_info = $this->DAO->get_ios_black_info($id);
        $this->assign("data",$blak_info);
        $this->display("qa_manage/black_edit_view.html");
    }

    public function black_do_edit(){
        $params = $_POST;
        $this->DAO->update_black_info($params);
        $this->succeed_msg();
    }

    public function mm_list(){
        if($_GET['mmc']){
            if($_GET['cache_type'] == '2'){
                $this->DAO->get_redis_info($_GET['mmc'],$_GET['num']);
            }else{
                $this->DAO->get_mmc_info($_GET['mmc']);
            }
//            $this->assign("data", $data);
            $this->assign("mmc", $_GET['mmc']);
        }
        $this->assign("cache_type", $_GET['cache_type']);
        $this->assign("num", $_GET['num']);
        $this->display("qa_manage/mm_list.html");
    }

    public function export(){
        $login_info = $this->DAO->get_this_info();
        foreach($login_info as $key=>$value){
            $user_info = $this->DAO->get_user_info($value['UserID']);
            $login_info[$key]['nick_name'] = $user_info['nick_name'];
            $login_info[$key]['user_name'] = $user_info['user_name'];
            $login_info[$key]['mobile'] = $user_info['mobile'];
            $login_info[$key]['login_name']= $user_info['login_name'];
        }
        if($login_info){
            $this->master_excel_out($login_info);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("111");
        $objActSheet->setCellValue("A1", "服务器名称");
        $objActSheet->setCellValue("B1", "服务器ID");
        $objActSheet->setCellValue("C1", "角色名");
        $objActSheet->setCellValue("D1", "角色ID");
        $objActSheet->setCellValue("E1", "等级");
        $objActSheet->setCellValue("F1", "用户ID");
        $objActSheet->setCellValue("G1", "昵称");
        $objActSheet->setCellValue("H1", "用户名称");
        $objActSheet->setCellValue("I1", "登录名");
        $objActSheet->setCellValue("J1", "手机号");
        $objActSheet->setCellValue("K1", "活跃时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['AreaServerName']);
            $objActSheet->setCellValue("B".$n, $info['AreaServerID']);
            $objActSheet->setCellValue("C".$n, $info['RoleName']);
            $objActSheet->setCellValue("D".$n, $info['RoleID']);
            $objActSheet->setCellValue("E".$n, $info['RoleLevel']);
            $objActSheet->setCellValue("F".$n, $info['UserID']);
            $objActSheet->setCellValue("G".$n, $info['nick_name']);
            $objActSheet->setCellValue("H".$n, $info['user_name']);
            $objActSheet->setCellValue("I".$n, $info['login_name']);
            $objActSheet->setCellValue("J".$n, $info['mobile']);
            $objActSheet->setCellValue("K".$n, date('Y-m-d H:i:s', $info['RecordTime']));
            $n++;
        }

        $title = iconv("UTF-8", "GB2312//IGNORE","充值数据-".$str_now.'.xls');
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


    public function log_list(){
        if($_GET['file_name']){
            $file_list = file(PREFIX . DS . 'logs/' . trim($_GET['file_name']) . '.log'); //返回数组的内容
            $preg = '/^([12]\d\d\d)-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[0-1]) ([0-1]\d|2[0-4]):([0-5]\d)(:[0-5]\d)?$/';
            $key = 0;
            $list = array();
            $arr = '';
            foreach($file_list as $v){
                if(preg_match($preg, trim($v))){
                    $list[$key]['time'] = trim($v);
                    if ($key >= 1){
                        $list[$key - 1]['data'] = $arr;
                    }
                    $key++;
                    $arr = '';
                } else {
                    $arr .= $v;
                }
            }
            $list[$key - 1]['data'] = $arr;
        }
        $this->assign('list',$list);
        $this->assign("file_name", $_GET['file_name']);
        $this->display("qa_manage/file1.html");
    }

    public function file(){
        $result = array('code'=>0,'msg'=>'网络错误');
        var_dump($_FILES);
        if(!$_FILES){
            $result['msg'] = '请上传图片';
            die(json_encode($result));
        }else{
            $str = "";
            foreach($_FILES as $key=>$data){
                if($_FILES[$key]['tmp_name']){
                    $str .= $this->up_img($key,ORDER_IMG).',';
                }
            }
        }
        //所有上传图片的地址
        $img = rtrim($str,',');
    }
}