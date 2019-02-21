<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper');
DAO('qb_channel_refund_dao');

class qb_channel_refund_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public $vip;
    public $exit_depot;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qb_channel_refund_dao();
    }



    public function list_view(){
        $params   = $this->get_params($_POST, $_GET);
        $user_info     = $this->DAO->get_user_info($_SESSION['usr_id']);
        $channel_list = $this->DAO->get_channel();
        if($user_info['group_id2']==16){
            $params['financial_mode']  =$user_info['financial_mode'];
        }

        $dataList = $this->DAO->get_list($this->page, $params);
        $page     = $this->pageshow($this->page, "qb_channel_refund.php?act=list&");
        $this->assign("user_info", $user_info);
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("channel_list", $channel_list);
        $this->assign("params", $params);
        $this->display("qb/qb_refund_list.html");
    }
    public function upload($id){
        $this->assign("id", $id);
        $this->display("qb/qb_refund_upload.html");
    }
    public function do_upload(){
        if(empty($_FILES['img1']['tmp_name']) && empty($_FILES['img2']['tmp_name'])&& empty($_FILES['img3']['tmp_name'])){
            $this->error_msg("请上传图片");
        }
        $params = $_POST;
        if($_FILES['img1']['tmp_name']){
            $img1 = $this->up_img('img1',QB_ICON,array(),1,1,"img1".time(),0);
            $params['img1'] = $img1;
        }
        if($_FILES['img2']['tmp_name']){
            $img2 = $this->up_img('img2',QB_ICON,array(),1,1,"img2".time(),0);
            $params['img2'] = $img2;
        }
        if($_FILES['img3']['tmp_name']){
            $img3 = $this->up_img('img3',QB_ICON,array(),1,1,"img3".time(),0);
            $params['img3'] = $img3;
        }
        $order_info   = $this->DAO->get_qb_refund_info($_POST['id']);
        $refund_money        = $order_info['refund_money'];
        $channel_allocated_money = $this->DAO->get_channel_allocated_money($order_info['order_id'], $order_info['channel_id'])['money'];//总分配金额
        if($refund_money>$channel_allocated_money){
            $this->error_msg("退款金额已达上限");
        }
        $refund_info = $this->DAO->get_refund_info($params['id']);
        $order_id                = $refund_info['order_id'];
        $refund_money            = $refund_info['refund_money'];
        $channel_allocated_list  = $this->DAO->get_channel_allocated_list($order_id, $refund_info['channel_id']);//指定渠道分配列表
        for($i = 0; $i < count($channel_allocated_list); $i++){
            $channel_allocated_arr = $channel_allocated_list[$i];
            $flag_money = $refund_money - $channel_allocated_arr['can_distribution_money'];
            if($flag_money >= 0){
                $this->DAO->update_qb_channel_refund_money($channel_allocated_arr['distribution_money'], $channel_allocated_arr['id']);
                $this->DAO->update_qb_channel_distribution_is_del($channel_allocated_arr['id']);
                $refund_money = $refund_money - $channel_allocated_arr['can_distribution_money'];
            }
            if($flag_money<0 && $refund_money >0){
                $money = $channel_allocated_arr['can_distribution_money'] - $refund_money;
                $already_refund_money = $this->DAO->get_qb_channel_refund_money($channel_allocated_arr['id'])['refund_money'];
                $already_refund_money = $already_refund_money + $refund_money;
                $this->DAO->update_qb_channel_can_distribution_money($money, $channel_allocated_arr['id']);
                $this->DAO->update_qb_channel_refund_money($already_refund_money, $channel_allocated_arr['id']);
                $refund_money = $refund_money-$channel_allocated_arr['can_distribution_money'];
            }
        }

        $params['status'] = 1;
        $this->DAO->update_qb_refund($params);
        //订单状态
        $already_allocated_money = $this->DAO->get_already_allocated_money($order_info['order_id'])['money'];//已分配
        $money = $this->DAO->get_money_from_order($order_info['order_id'])['money'];
        if($money > $already_allocated_money){
            $this->DAO->update_qb_order_status($order_info['order_id']);
        }

        $this->succeed_msg();
    }
    public function refuse($id){
        $this->assign("id", $id);
        $this->display("qb/qb_channel_refuse.html");

    }
    public function do_refuse(){
        $params   = $_POST;
        $this->DAO->update_qb_refund_refuse_desc($params);
        $this->succeed_msg();
    }


    public function order_export(){
        set_time_limit(0);
        $params   = $_GET;
        $dataList = $this->DAO->get_all_data($params);
        if($dataList){
            $this->external_excel_out($dataList);
        } else{
            echo "没有数据需要导出！";
        }
    }

    private function external_excel_out($data){
        set_time_limit(0);
        $str_now  = date("Y-m-d H:i:s", strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("退款数据");
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "订单号");
        $objActSheet->setCellValue("C1", "渠道名称");
        $objActSheet->setCellValue("D1", "退款金额");
        $objActSheet->setCellValue("E1", "收款账户");


        $n = 2;
        foreach($data as $info){

            $objActSheet->setCellValueExplicit("A" . $n, date("Y-m-d", $info['add_time']), PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("B" . $n, $info['order_id'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("D" . $n, $info['refund_money'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("C" . $n, $this->DAO->get_channel_name($info['channel_id'])['channel_name'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("E" . $n,  $this->DAO->get_account_name($info['account_id'])['payment_account'], PHPExcel_Cell_DataType::TYPE_STRING);
            $n++;
        }

        $title = iconv("UTF-8", "GB2312//IGNORE", "退款数据-" . $str_now . '.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $title . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}
