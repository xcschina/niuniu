<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper', 'QQMailer');
DAO('qb_channel_distribution_dao');

class qb_channel_distribution_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public $vip;
    public $exit_depot;

    public function __construct(){
        parent::__construct();
        $this->DAO     = new qb_channel_distribution_dao();
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
        }
        if($user_info['group_id2']==16){
            $params['financial_mode']  =$user_info['financial_mode'];
        }

        $dataList = $this->DAO->get_list($this->page, $params);

        foreach($dataList as $key => $data){
            $dataList[$key]['channel_name'] = $this->channel[$data['channel']];
            $dataList[$key]['already_money'] = $this->DAO->get_already_allocated_money($data['order_id'])['money'];
        }
        $this->assign("service_list", $service_list);
        $this->assign("app_list", $app_list);
        $this->assign("channel_list", $this->channel);
        $page = $this->pageshow($this->page, "qb_channel.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("params", $params);
        $this->assign("user_info", $user_info);
        $this->display("qb/qb_channel_distribution_list.html");
    }

    public function distribution($id){
        $type = $this->DAO->get_qb_orders_info($id)['pay_mode'];
        $flag = 0;//判断转账类型
        if($type == 1 ){
            $flag = 1;
        }else{
            $flag = 2;
        }
        $account_list = $this->DAO->get_account_list($type);
        $order_info   = $this->DAO->get_qb_orders_info($id);
        $distribution = $this->DAO->get_qb_channel_distribution_list($order_info['order_id']);

        $result       = array();
        foreach($distribution as $key => $info){
            $result[$info['num']][] = $info;
        }
        $num = $this->DAO->get_max_num($order_info['order_id'])['num'];
        if($num){
            $num = $num + 1;
        } else{
            $num = 1;
        }
        $allocated_money        = $this->DAO->get_allocated_money($order_info['order_id'])['money'];//已分配
        $refund_money        = $this->DAO->get_refund_money($order_info['order_id'])['money'];//已退款
        $allocated_money = $allocated_money-$refund_money;
        $can_distribution_money = $order_info['money'] - $allocated_money;//可分配
        if(empty($allocated_money)){
            $allocated_money = 0;
        }
        $credit_channel_list = $this->DAO->get_credit_channel();

        foreach($credit_channel_list as $key => $value){
            $money = $this->DAO->get_allocated_credit_money($order_info['order_id'], $value['channel_id'])['money'];
            if(empty($money)){
                $money = 0;
            }
            $beyond_money = $money - $value['credit_money'];
            if($beyond_money <= 0){
                $beyond_money = 0;
            }
            $credit_channel_list[$key]['allocated_money'] = $money;
            $credit_channel_list[$key]['beyond_money']    = $beyond_money;
        }
        $this->assign("account_list", $account_list);
        $this->assign("distribution", $result);
        $this->assign("credit_channel_list", $credit_channel_list);
        $this->assign("id", $id);
        $this->assign("num", $num);
        $this->assign("flag", $flag);
        $this->assign("allocated_money", $allocated_money);
        $this->assign("refund_money", $refund_money);
        $this->assign("can_distribution_money", $can_distribution_money);
        $this->display("qb/qb_channel_distribution.html");
    }

    //分配
    public function do_distribution(){
        $params      = $this->get_params($_POST, $_GET);
        $order_info  = $this->DAO->get_qb_orders_info($params['id']);
        $money_arr   = $params['distribution_money'];
        $money_total = array_sum($money_arr);
        $num         = $this->DAO->get_max_num($order_info['order_id'])['num'];
        if($num){
            $num = $num + 1;
        } else{
            $num = 1;
        }

        //判断金额是否超出订单
        $allocated_money = $this->DAO->get_allocated_money($order_info['order_id'])['money'];//已分配
        $refund_money        = $this->DAO->get_refund_money($order_info['order_id'])['money'];//已退款
        $allocated_money = $allocated_money-$refund_money;
        $can_distribution_money = $order_info['money'] - $allocated_money;//可分配
        $params_momney   = array_sum($money_arr);//参数金额总和
        if($can_distribution_money < $params_momney){
            $beyond_money = ($params_momney - $can_distribution_money);
            $this->error_msg("金额分配超出" . $beyond_money . '元');
        }

        for($j = 0; $j < count($money_arr); $j++){
            if($money_arr[$j] != null){
                $data['channel_id']         = $params['channel_id'][$j];
                $data['distribution_money'] = $params['distribution_money'][$j];
                if($data['distribution_money']<=0){
                    $this->error_msg("分配金额必须大于0元");
                }
                $data['can_distribution_money'] = $params['distribution_money'][$j];
                $data['user_name']          = $params['user_name'][$j];
                $data['payment_account']    = $params['payment_account'][$j];
                $data['order_id']           = $order_info['order_id'];
                $data['num']                = $num;
                if($order_info['pay_mode'] == 1){
                    $data['status'] = 1;
                } else{
                    $data['status'] = 0;
                }
                $this->DAO->insert_qb_channel_distribution($data);
                $this->DAO->update_qb_order_distribution($order_info['order_id']);
            }

        }
        if($order_info['pay_mode']==1){//对公
            //财务
            $this->send_email(4,$order_info['order_id'],"，第".$num."次渠道分配成功，请处理打款",2);
        }elseif($order_info['pay_mode']==2){//对私
            //财务
            $this->send_email(3,$order_info['order_id'],"，第".$num."次渠道分配成功，请处理打款",0);
        }
        $this->succeed_msg();
    }

    //退款
    public function refund_view($id){
        $order_info  = $this->DAO->get_qb_orders_info($id);
        $channel_list   = $this->DAO->get_qb_channel_list();
        $financial_list = $this->DAO->get_financial_list($order_info['pay_mode']);
        $this->assign("id", $id);
        $this->assign("datalist", $channel_list);
        $this->assign("financial_list", $financial_list);
        $this->display("qb/qb_refund_view.html");
    }

    public function do_refund(){
        $params                  = $this->get_params($_POST, $_GET);
        $order_info              = $this->DAO->get_qb_orders_info($params['id']);
        $order_id                = $order_info['order_id'];
        $refund_money            = $params['money'];
        $channel_allocated_money = $this->DAO->get_channel_allocated_money($order_id, $params['channel_id'])['money'];//总分配金额
        if($channel_allocated_money < $refund_money){
            $this->error_msg("退款金额超出分配金额");
        }
        $params['order_id'] = $order_id;
        $this->DAO->insert_qb_refund($params);
        $this->succeed_msg();
    }

    public function financial_distribution($id){
        $order_info   = $this->DAO->get_qb_orders_info($id);
        $distribution = $this->DAO->get_qb_channel_distribution_list($order_info['order_id']);

        $user_info    = $this->DAO->get_user_info($_SESSION['usr_id']);
        $result       = array();
        foreach($distribution as $key => $info){
            $result[$info['num']][] = $info;
        }
        $allocated_money        = $this->DAO->get_allocated_money($order_info['order_id'])['money'];//已分配
        $refund_money        = $this->DAO->get_refund_money($order_info['order_id'])['money'];//已退款
        if(empty($refund_money)){
            $refund_money = 0;
        }
        $allocated_money = $allocated_money - $refund_money;
        $can_distribution_money = $order_info['money'] - $allocated_money;//可分配
        if(empty($allocated_money)){
            $allocated_money = 0;
        }
        $credit_channel_list = $this->DAO->get_credit_channel();

        foreach($credit_channel_list as $key => $value){
            $money = $this->DAO->get_allocated_credit_money($order_info['order_id'], $value['channel_id'])['money'];
            $re_money = $this->DAO->get_allocated_refund_money($order_info['order_id'], $value['id'])['money'];
            $money = $money - $re_money;//已分配
            if(empty($money)){
                $money = 0;
            }
            $beyond_money = $money - $value['credit_money'];//已超出
            if($beyond_money <= 0){
                $beyond_money = 0;
            }
            $credit_channel_list[$key]['allocated_money'] = $money;
            $credit_channel_list[$key]['beyond_money']    = $beyond_money;
        }
        $this->assign("user_info", $user_info);
        $this->assign("distribution", $result);
        $this->assign("credit_channel_list", $credit_channel_list);
        $this->assign("id", $id);
        $this->assign("allocated_money", $allocated_money);
        $this->assign("refund_money", $refund_money);
        $this->assign("can_distribution_money", $can_distribution_money);
        $this->display("qb/qb_financial_distribution.html");
    }

    public function do_financial_distribution(){
        $params = $this->get_params($_POST, $_GET);
        $id_arr = $params['id'];//id数组
        for($i = 0; $i < count($id_arr); $i++){
            $img = 'img' . $id_arr[$i];

            if($_FILES[$img]['tmp_name']){
                $img1              = $this->up_img($img, QB_ICON, array(), 1, 1, $img . time(), 0);
                $params['img_url'] = $img1;
                $params['id']      = $id_arr[$i];
                $this->DAO->update_qb_channel_distribution($params);
            }

        }

        //打款金额
        $param['id'] = $id_arr[0];
        $info = $this->DAO->get_qb_channel_distribution_status($param);
        $already_allocated_money = $this->DAO->get_already_allocated_money($info['order_id'])['money'];//已分配
        $money = $this->DAO->get_money_from_order($info['order_id'])['money'];
        if($money == $already_allocated_money){
            $this->DAO->update_qb_order_status(11,$info['order_id']);
        }
        //通知客服
        $param['id'] = $params['id'][0];
        $info = $this->DAO->get_qb_channel_distribution_status($param);
        $this->send_email(5,$info['order_id'],"，已打款啦",0);
        $this->succeed_msg();

    }

    public function public_financial_distribution($id){
        $order_info   = $this->DAO->get_qb_orders_info($id);
        $distribution = $this->DAO->get_qb_channel_distribution_list($order_info['order_id']);
        $user_info    = $this->DAO->get_user_info($_SESSION['usr_id']);
        $result       = array();
        foreach($distribution as $key => $info){
            $result[$info['num']][] = $info;
        }
        $allocated_money        = $this->DAO->get_allocated_money($order_info['order_id'])['money'];//已分配
        $refund_money        = $this->DAO->get_refund_money($order_info['order_id'])['money'];//已退款
        if(empty($refund_money)){
            $refund_money = 0;
        }
        $allocated_money = $allocated_money - $refund_money;
        $can_distribution_money = $order_info['money'] - $allocated_money;//可分配
        if(empty($allocated_money)){
            $allocated_money = 0;
        }
        $credit_channel_list = $this->DAO->get_credit_channel();

        foreach($credit_channel_list as $key => $value){
            $money = $this->DAO->get_allocated_credit_money($order_info['order_id'], $value['channel_id'])['money'];
            if(empty($money)){
                $money = 0;
            }
            $beyond_money = $money - $value['credit_money'];
            if($beyond_money <= 0){
                $beyond_money = 0;
            }
            $credit_channel_list[$key]['allocated_money'] = $money;
            $credit_channel_list[$key]['beyond_money']    = $beyond_money;
        }
        $this->assign("user_info", $user_info);
        $this->assign("distribution", $result);
        $this->assign("credit_channel_list", $credit_channel_list);
        $this->assign("id", $id);
        $this->assign("allocated_money", $allocated_money);
        $this->assign("can_distribution_money", $can_distribution_money);
        $this->assign("refund_money", $refund_money);
        $this->display("qb/qb_public_financial_distribution.html");
    }

    public function voucher($id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $this->assign("id", $id);
        $this->display("qb/qb_voucher_view.html");
    }

    public function do_voucher(){
        $params = $_POST;
        $params['status'] = 2;
        $this->DAO->update_qb_channel_distribution_status($params);
        $info = $this->DAO->get_qb_channel_distribution_status($params);
        $this->send_email(5,$info['order_id'],"，已制单啦",0);
        $this->send_email(2,$info['order_id'],"，已制单啦",0);
        if($info['status'] == 2){
            $result = array('code' => 1, 'msg' => '成功');
        } else{
            $result = array('code' => 0, 'msg' => '失败');
        }
        die(json_encode($result));
    }

    public function authorization($id){
        $this->assign("id", $id);
        $this->display("qb/qb_authorization_view.html");
    }

    public function do_authorization(){
        $params = $_POST;
        $params['status'] = 3;
        $this->DAO->update_qb_channel_distribution_status($params);
        $info = $this->DAO->get_qb_channel_distribution_status($params);
        $this->send_email(4,$info['order_id'],"，已授权啦",2);
        $this->send_email(2,$info['order_id'],"，已授权啦",0);
        if($info['status'] == 3){
            $result = array('code' => 1, 'msg' => '成功');
        } else{
            $result = array('code' => 0, 'msg' => '失败');
        }
        die(json_encode($result));
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
        $objActSheet->setTitle("QB订单列表");
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
        $objActSheet->setCellValue("H1", "支付方式");
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
            if($info['pay_mode'] == 1){
                $objActSheet->setCellValue("H" . $n, "对公");
            } elseif($info['pay_mode'] == 2){
                $objActSheet->setCellValue("H" . $n, "对私");
            }
            $objActSheet->setCellValue("I" . $n, date("Y-m-d H:i:s", $info['order_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE", "QB订单分配列表-" . $str_now . '.xls');
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
        $result = $mailer->send($title, $content, $email, "商会QB");
        while(!$result){
            $result = $mailer->send($title, $content, $email, "商会QB");
        }
    }

    public function send_email($type,$order_id,$content,$financial_type){
        $email_list = $this->DAO->get_email_info($type,$financial_type);
        for($i = 0; $i < count($email_list); $i++){
            $email   = $email_list[$i]['email'];
            $title   = '商会QB订单';
            $content = "收到来自" . "单号为" . $order_id . "的订单" . $content . "http://admin.66173.cn/login.php?act=do_login";
            $this->set_mail($title, $content, $email);
        }
    }
}
