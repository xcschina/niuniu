<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('super_orders_dao','app_admin_dao');

class super_orders extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new super_orders_dao();
    }

    public function order_list_view( ){
        $app_dao = new app_admin_dao();
        if($_POST){
            $_SESSION['order_list'] = $params = $_POST;
        }elseif($_GET['page']){
            $params = $_SESSION['order_list'];
        }
        $money = $this->DAO->get_sum_money($params['app_id'],$params);
        $channel_list = $this->DAO->get_channel();
        $list = $this->DAO->get_order_list($this->page, $params['app_id'],$params);
        $page = $this->pageshow($this->page,"super_orders.php?act=list&");
        $this->assign("channel_list",$channel_list);
        $this->assign("datalist", $list);
        $this->assign("params", $params);
        $this->assign("money", $money[0]['money']);
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("page_bar", $page->show());
        $this->display("super_sdk/order_list.html");
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_export_order($params);
        if($dataList){
            $this->master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("充值数据");
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "游戏名");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏区服ID");
        $objActSheet->setCellValue("E1", "充值ID");
        $objActSheet->setCellValue("F1", "角色名");
        $objActSheet->setCellValue("G1", "订单流水号");
        $objActSheet->setCellValue("H1", "cp订单号");
        $objActSheet->setCellValue("I1", "商品");
        $objActSheet->setCellValue("J1", "金额");
        $objActSheet->setCellValue("K1", "购买人ID");
        $objActSheet->setCellValue("L1", "渠道");
        $objActSheet->setCellValue("M1", "订单状态");
        $objActSheet->setCellValue("N1", "下单日期");
        $objActSheet->setCellValue("O1", "时间");
        $objActSheet->setCellValue("P1", "支付日期");
        $objActSheet->setCellValue("Q1", "时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['channel']);
            $objActSheet->setCellValue("D".$n, $info['serv_id']);
            $objActSheet->setCellValue("E".$n, "'".$info['role_id']);
            $objActSheet->setCellValue("F".$n, "'".$info['role_name']);
            $objActSheet->setCellValue("G".$n, "'".$info['order_id']);
            $objActSheet->setCellValue("H".$n, "'".$info['app_order_id']);
            $objActSheet->setCellValue("I".$n, $info['title']);
            $objActSheet->setCellValue("J".$n, $info['pay_money']);
            $objActSheet->setCellValue("K".$n, $info['buyer_id']);
            $objActSheet->setCellValue("L".$n, $info['pay_channel']);
            if($info['status'] == 0){
                $objActSheet->setCellValue("M".$n, "未付款");
            }elseif($info['status'] == 1){
                $objActSheet->setCellValue("M".$n, "已付款");
            }elseif($info['status'] == 2){
                $objActSheet->setCellValue("M".$n, "完成订单");
            }else{
                $objActSheet->setCellValue("M".$n, "取消订单");
            }
            $objActSheet->setCellValue("N".$n, date("Y-m-d",$info['buy_time']));
            $objActSheet->setCellValue("O".$n, date("H:i:s",$info['buy_time']));
            $objActSheet->setCellValue("P".$n, date("Y-m-d",$info['pay_time']));
            $objActSheet->setCellValue("Q".$n, date("H:i:s",$info['pay_time']));
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

    public function error_list(){
        $params = $this->get_params($_POST,$_GET);
        $data  = array();
        if($params['order_id'] || $params['ch_order']){
            $data = $this->DAO->get_error_order_list($params);
        }
        $this->assign("data",$data);
        $this->assign("params",$params);
        $this->display("super_sdk/order_error_list.html");
    }

    public function edit_view($id){
        $this->page_hash();
        $this->assign('id',$id);
        $this->display('super_sdk/order_edit.html');
    }

    public function edit_save(){
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        $info = $this->DAO->get_order_info($params['id']);
        if(!$info){
            $this->error_msg("查无此异常订单");
        }
        if($info['status'] == 0){
            $status = 1;
        }elseif($info['status'] == 1){
            $status = 2;
        }
        $this->DAO->update_order($params['id'],$status);
        $data['ip'] = $this->client_ip();
        $data['operator_id'] = $_SESSION['usr_id'];
        $data['order_id'] = $info['order_id'];
        $this->err_log(var_export($data,','),'super_error_order');
        $this->succeed_msg();
    }
}