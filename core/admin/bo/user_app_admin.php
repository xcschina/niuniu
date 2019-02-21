<?php
COMMON('adminBaseCore','pageCore','uploadHelper','ipip/IP4datx.class');
DAO('user_app_dao');

class user_app_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_app_dao();
    }

    public function list_view(){
        $params = $_POST;
        $time = strtotime(date('Y-m-d',time()));
        $start = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')));
        $end = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+1,date('Y')));
        $day = (strtotime($params['end_time'])-strtotime($params['start_time']))/86400;
        if(!$params['channel'] && ($day>3)){
            $msg = "选择渠道且日期相差3天，查询的数据太大，无法查询";
        }else{
            $list = $this->DAO->get_stats_list($params,$time);
        }
        $extension_list = $this->DAO->get_extension_list();
        foreach($extension_list as $key=>$data){
            if(preg_match('/[a-zA-Z]/', $data['user_code']) && preg_match('/[0-9]/', $data['user_code'])){
                unset($extension_list[$key]);
            }
        }
        $app_list = $this->DAO->get_app_list();
        $total = count($list);
        $this->assign('extension_list',$extension_list);
        $this->assign('msg',$msg);
        $this->assign('start',$start);
        $this->assign('end',$end);
        $this->assign('params',$params);
        $this->assign('total',$total);
        $this->assign('app_list',$app_list);
        $this->assign('list',$list);
        $this->display('user_app_list.html');
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $time = strtotime(date('Y-m-d'),time());
        $day = (strtotime($params['end_time'])-strtotime($params['start_time']))/86400;
        if(!$params['channel'] && ($day>3)){
            echo "选择渠道且日期相差3天，查询的数据太大，无法查询";
        }else{
            $dataList = $this->DAO->get_stats_list($params,$time);
        }
        if($dataList){
            $this->excel_out_data($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function excel_out_data($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('J')->setWidth(20);
        $objActSheet->getColumnDimension('K')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(20);
        $objActSheet->getColumnDimension('M')->setWidth(20);
        $objActSheet->getColumnDimension('N')->setWidth(20);
        $objActSheet->setTitle("用户游戏信息");
        $objActSheet->setCellValue("A1", "用户ID");
        $objActSheet->setCellValue("B1", "游戏ID");
        $objActSheet->setCellValue("C1", "区服");
        $objActSheet->setCellValue("D1", "渠道名");
        $objActSheet->setCellValue("E1", "角色ID");
        $objActSheet->setCellValue("F1", "角色名");
        $objActSheet->setCellValue("G1", "角色等级");
        $objActSheet->setCellValue("H1","手机号码");
        $objActSheet->setCellValue("I1", "来自游戏ID");
        $objActSheet->setCellValue("J1", "游戏注册时间");
        $objActSheet->setCellValue("K1", "游戏注册区域");
        $objActSheet->setCellValue("L1", "游戏活跃时间");
        $objActSheet->setCellValue("M1", "游戏活跃区域");
        $objActSheet->setCellValue("N1", "用户注册牛牛时间");
        $n = 2;
        $bs = new IP();
        foreach($data as $info){
            $Act = $bs->find($info['ActIP']);
            $Reg = $bs->find($info['RegIP']);
            $objActSheet->setCellValue("A".$n, $info['UserID']);
            $objActSheet->setCellValue("B".$n, $info['AppID']);
            $objActSheet->setCellValue("C".$n, $info['AreaServerName']);
            $objActSheet->setCellValue("D".$n, $info['Channel']);
            $objActSheet->setCellValue("E".$n, $info['RoleID']);
            $objActSheet->setCellValue("F".$n, $info['RoleName']);
            $objActSheet->setCellValue("G".$n, $info['RoleLevel']);
            $objActSheet->setCellValue("H".$n, $info['mobile']);
            $objActSheet->setCellValue("I".$n, $info['from_app_id']);
            $objActSheet->setCellValue("J".$n, date("Y-m-d H:i:s",$info['RegTime']));
            $objActSheet->setCellValue("K".$n, $Reg[1]);
            $objActSheet->setCellValue("L".$n, date("Y-m-d H:i:s",$info['ActTime']));
            $objActSheet->setCellValue("M".$n, $Act[1]);
            $objActSheet->setCellValue("N".$n, date("Y-m-d H:i:s",$info['u_reg_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","用户游戏信息-".$str_now.'.xls');
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
}