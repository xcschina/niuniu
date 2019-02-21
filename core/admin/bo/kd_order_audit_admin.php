<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper', 'QQMailer');
DAO('kd_order_audit_dao');

class kd_order_audit_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public $vip;
    public $exit_depot;

    public function __construct(){
        parent::__construct();
        $this->DAO     = new kd_order_audit_dao();
        $this->channel = array(
            '00008' => '应用宝',
            '00009' => '魔域混服',
            '00010' => '官服'
        );
    }

    public function list_view(){
        $params    = $_POST;
        $app_list  = $this->DAO->get_app_list();
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
            $arr          = [
                "service_id" => '0',
                "service_name" => "全区服"
            ];
            array_unshift($service_list, $arr);
        }
        if($user_info['group_id2'] ==16){
            if($user_info['financial_mode']==2){
                $params['status'] = 4;
            }
            $dataList = $this->DAO->get_first_audit_list($this->page, $params);
        }elseif($user_info['group_id2'] ==17){
            $dataList = $this->DAO->get_all_audit_list($this->page, $params);
        }

        foreach($dataList as $key => $data){
            $dataList[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        $this->assign("service_list", $service_list);
        $this->assign("app_list", $app_list);
        $this->assign("channel_list", $this->channel);
        $page = $this->pageshow($this->page, "kd_order_audit.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("params", $params);
        $this->assign("user_info", $user_info);
        $this->display("qb/kd_order_audit_list.html");
    }


    public function get_service(){
        if(!$_POST['app_id']){
            die(json_encode(array('code' => 0, 'msg' => "游戏出错啦")));
        } else{
            $service_list = $this->DAO->get_service_list($_POST['app_id']);
            $app_info     = $this->DAO->get_app_info($_POST['app_id']);
            die(json_encode(array('code' => 1, 'list' => $service_list, 'app_info' => $app_info)));
        }
    }

    public function first_audit($id){
        $this->assign("id", $id);
        $order = $this->DAO->get_qb_orders_info($id);
        $this->assign("order", $order);
        $this->display("qb/kd_order_first_audit_view.html");
    }

    public function do_first_audit(){
        $params           = $this->get_params($_POST, $_GET);
        $params['status'] = 4;
        $this->DAO->update_qb_order_status($params);

        $email_list = $this->DAO->get_email_info(6);
        for($i=0;$i<count($email_list);$i++){
            $email = $email_list[$i]['email'];
            $title = '商会QB订单';
            $content = "收到来自" . "单号为" . $params['order_id'] . "的订单". "，审核通过啦"."http://admin.66173.cn/login.php?act=do_login";
            $this->set_mail($title, $content, $email);
        }

        $this->succeed_msg();
    }


    public function refuse($id){
        $this->assign("id", $id);
        $this->display("qb/kd_order_refuse_view.html");
    }

    public function do_refuse(){
        $params           = $this->get_params($_POST, $_GET);
        $params['status'] = 9;

        $this->DAO->update_qb_order_status($params);
        $order_info = $this->DAO->get_qb_orders_info($params['id']);

        $email_list = $this->DAO->get_email_info(6);
        for($i=0;$i<count($email_list);$i++){
            $email = $email_list[$i]['email'];
            $title = '商会QB订单';
            $content = "收到来自" . "单号为" . $order_info['order_id'] . "的订单". "，审核未通过"."http://admin.66173.cn/login.php?act=do_login";
            $this->set_mail($title, $content, $email);
        }

        $this->succeed_msg();
    }

    public function out_money($id){
        $this->assign("id", $id);
        $this->display("qb/kd_order_out_money_view.html");
    }

    public function do_out_money(){
        $params           = $this->get_params($_POST, $_GET);
        $params['is_out'] = 2;
        $this->DAO->update_qb_order_out_money($params);
        $order_info = $this->DAO->get_qb_orders_info($params['id']);
        $email_list = $this->DAO->get_email_info(6);
        for($i=0;$i<count($email_list);$i++){
            $email = $email_list[$i]['email'];
            $title = '商会QB订单';
            $content = "收到来自" . "单号为" . $order_info['order_id'] . "的订单". "，打款了"."http://admin.66173.cn/login.php?act=do_login";
            $this->set_mail($title, $content, $email);
        }
        $this->succeed_msg();
    }

    public function order_export(){
        $params   = $_GET;
        $datalist = $this->DAO->get_list($this->page, $params);
        if($datalist){
            $this->master_excel_out($datalist);
        } else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_out($data){
        set_time_limit(0);
        $str_now  = date("Y-m-d H:i:s", strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("商会口袋审核订单列表");
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue("A1", "执行员");
        $objActSheet->setCellValue("B1", "订单号");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "申请账号数量");
        $objActSheet->setCellValue("G1", "申请金额");
        $objActSheet->setCellValue("H1", "订单状态");
        $objActSheet->setCellValue("I1", "下单时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A" . $n, $info['real_name']);
            $objActSheet->setCellValueExplicit("B" . $n, $info['order_id'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("C" . $n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D" . $n, $info['app_name']);
            $objActSheet->setCellValue("E" . $n, $info['service_name']);
            $objActSheet->setCellValue("F" . $n, $info["num"]);
            $objActSheet->setCellValue("G" . $n, $info["money"]);
            if($info['status'] == 1){
                $objActSheet->setCellValue("H" . $n, "审核中");
            } elseif($info['status'] == 4){
                $objActSheet->setCellValue("H" . $n, "审核通过");
            }elseif($info['status'] == 9){
                $objActSheet->setCellValue("H" . $n, "审核拒绝");
            }
            $objActSheet->setCellValue("I" . $n, date("Y-m-d H:i:s", $info['order_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE", "商会口袋审核订单列表-" . $str_now . '.xls');
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

    public function set_mail($title, $content, $email){
        // 实例化 QQMailer
        $mailer = new QQMailer(true);
        // 添加附件
        //        $mailer->addFile('20130VL.jpg');
        // 发送QQ邮件
        $result = $mailer->send($title, $content,$email,"商会QB");
        while(!$result){
            $result = $mailer->send($title, $content,$email,"商会QB");
        }
    }

}
