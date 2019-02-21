<?php
COMMON('adminBaseCore','pageCore','uploadHelper','QQMailer');
DAO('business_dao','menu_admin_dao','app_admin_dao');

class business_admin extends adminBaseCore{
    public $DAO;
    public $QB;
    public $payment;

    public function __construct() {
        parent::__construct();
        $this->DAO = new business_dao();
        $this->QB = array(
            "爱云"=>0.98,
            "游戏久0303"=>0.98,
            "游戏久0304"=>0.98,
            "游戏久0305"=>0.98,
            "游戏久0306"=>0.98,
        );
        $this->payment = array(
            '10001'=> array('title'=>'建设银行福建福州天福支行','name'=>'林颖','account'=>'6214 9918 2040 2468','type'=>'1'),
            '10002'=>array('title'=>'支付宝','name'=>'林颖','account'=>'18959197777','type'=>'1'),
            '10003'=>array('title'=>'支付宝','name'=>'林颖','account'=>'13509339049','type'=>'1'),
            '10004'=>array('title'=>'中国工商银行福州鼓楼支行','name'=>'福建牛牛网络信息技术有限公司','account'=>'1402 0232 0960 0340 532','type'=>'2'),
            '10005'=>array( 'title'=>'中国工商银行澄迈软件园支行','name'=>'海南牛牛网络信息技术有限公司','account'=>'2201 0805 0910 0051 832','type'=>'2'),
        );
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        if($_SESSION['group_id'] == 14){
            $params['user_id'] = $_SESSION['usr_id'];
            $app_list = $this->get_business_app_list($_SESSION['usr_id']);
        }else{
            $app_list = $this->DAO->get_app_list();
        }
        $order_list = $this->DAO->get_order_list($this->page,$params);
        $guild_list = $this->DAO->get_all_guild_list();
        $page = $this->pageshow($this->page, "business.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("app_list",$app_list);
        $this->assign("params",$params);
        $this->assign("guild_list",$guild_list);
        $this->assign("order_list",$order_list);
        $this->display("chamber/business_list.html");
    }

    public function get_business_app_list($user_id){
        $game_list = $this->DAO->get_business_app_list($user_id);
        if($game_list['game_list']){
            $list = explode(',',$game_list['game_list']);
            foreach($list as $key=>$data){
                $app_info = $this->DAO->get_app_info($data);
                $app_list[$key]['app_name'] = $app_info['app_name'];
                $app_list[$key]['app_id'] = $app_info['app_id'];
            }
        }else{
            $app_list = array();
        }
        return $app_list;
    }

    public function add_view(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $payment_method = explode(',',$user_info['payment_method']);
        $payment_list = array();
        foreach($this->payment as $key=>$data){
            if(in_array($key,$payment_method)){
                $payment_list[$key] = $data;
            }
        }
        $app_list = $this->get_business_app_list($_SESSION['usr_id']);
        $guild_list = $this->DAO->get_all_guild_list();
        $this->assign('payment_list',$payment_list);
        $this->assign("user_info",$user_info);
        $this->assign("guild_list",$guild_list);
        $this->assign("app_list",$app_list);
        $this->display("chamber/business_add.html");
    }

    public function do_add(){
        $params = $_POST;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(!$params['app_id'] || !$params['service_id'] || !$params['role_job'] || !$params['role_sex'] || !$params['role_name'] || !$params['pay_money'] || !$params['pay_mode'] || !$params['payment_method']){
            $this->error_msg("缺少必填参数");
        }
        if($params['money']<=0){
            $this->error_msg("支付金额格式不对");
        }
        if($params['pay_mode'] == 2){
            if(!($_FILES['imgs']['tmp_name'][0] && $_FILES['imgs']['tmp_name'])){
                $this->error_msg("线下支付必须上传凭证");
            }
        }else{
            if($user_info['money_lock'] == 1){
                $this->error_msg("账户余额已冻结，不可使用余额支付");
            }elseif($user_info['money'] < $params['pay_money']){
                $this->error_msg("余额不足，请重新选择支付方式");
            }
        }
        $imgs = '';
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $voucher_img = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($voucher_img as $key=>$img){
                if($img){
                    $imgs .= $img.",";
                }
            }
            $params['img'] = trim($imgs,",");
        }
        $str = '';
        for ($i = 1; $i <= 4; $i++) {
            $str .= chr(rand(65, 90));
        }
        $params['order_id'] = $str.date('Ymd').time();
        $params['money_type'] = 2;
        $this->DAO->guild_lock($user_info['id'],1);
        $this->DAO->insert_money_log($params,$_SESSION['usr_id']);
        $order_id =$this->DAO->insert_order($params,$user_info);
        $qq_list = $this->DAO->get_user_qq();
        if($qq_list){
            foreach($qq_list as $key=>$data){
                $app_list = explode(',', $data['app_list']);
                if(in_array($params['app_id'], $app_list)){
                    $app_info = $this->DAO->get_app_info($params['app_id']);
                    $email = $data['qq'] . '@qq.com';
                    $title = '商会提交订单啦';
                    $content = "收到来自" . $user_info['real_name'] . "的订单，单号为" . $params['order_id'] . "，游戏名称为" . $app_info['app_name'] . "，请尽快处理订单";
                    $this->set_mail($title, $content, $email);
                    $this->DAO->update_business_order($data['id'], $order_id);
                    $this->DAO->update_relation_tb($data['status'] + 1, $data['id']);
                    break;
                }
            }
        }
        $this->DAO->guild_lock($user_info['id'],0);
        $this->succeed_msg("添加成功");
    }

    public function set_mail($title,$content,$email){
        // 实例化 QQMailer
        $mailer = new QQMailer(true);
        // 添加附件
        //        $mailer->addFile('20130VL.jpg');
        // 发送QQ邮件
        $result = $mailer->send($title, $content,$email);
        while(!$result){
            $result = $mailer->send($title, $content,$email);
        }
    }

    public function edit_view($id){
        $info = $this->DAO->get_order_info($id);
        $order_log = $this->DAO->get_order_log_money($id);
        $account_list = $this->DAO->get_my_account_list($info);
        $img = explode(',',$info['img']);
        $user_info = $this->DAO->get_user_info($info['guild_id']);
        $payment_method = explode(',',$user_info['payment_method']);
        $payment_list = array();
        foreach($this->payment as $key=>$data){
            if(in_array($key,$payment_method)){
                $payment_list[$key] = $data;
            }
        }
        $this->assign('payment_list',$payment_list);
        $this->assign("account_list",$account_list);
        $this->assign("order_log",$order_log);
        $this->assign("img_list",$img);
        $this->assign("info",$info);
        $this->display("chamber/business_edit.html");
    }

    public function do_edit($id){
        $params = $_POST;
        $user_info = $this->DAO->get_order_info($id);
            if($params['type'] == '2'){
                if(!$params['feedback_desc']){
                    $this->error_msg("受理失败，必须说明原因");
                }
            }elseif($params['type'] == '3'){
                if($params['account_type'] == '1'){
                    if(!$params['money']){
                        $this->error_msg("受理中，金额不能为空");
                    }elseif(!preg_match("/^[0-9]{0}([0-9]|[.][0-9])+$/",$params['money'])){
                        $this->error_msg("金额格式不对");
                    }elseif($params['money']>20000){
                        $this->error_msg("一次受理的金额不能超过两万元");
                    }elseif($params['money']>$params['pay_money']){
                        $this->error_msg("受理金额不能超过订单金额");
                    }elseif($params['money']-$params['pay_money']==0){
                        $params['type'] = 1;
                    }
                }
            }
            if(!$params['pay_type']){
                $params['pay_type'] = 1;
            }
            $params['discount'] = 0.71;
            if($params['channel']){
                $params['discount'] = $this->QB[$params['channel']];
            }
            $params['pay_type'] = $this->get_pay_type($params['channel']);
            if(($params['type'] == '1' || $params['type'] == '2')){
                $params['money'] = $params['pay_money'];
            }
            $params['pay_money'] = $params['money']*$params['discount'];
            $info = $this->DAO->get_order_info($id);
            if($params['type'] == '1' || $params['type'] == '3'){
                if($info['status'] == 1){
                    $params = array_merge($info, $params);
                    $this->DAO->insert_order_log($id, $params, $_SESSION['usr_id']);
                    $params['role_account'] = $info['role_account'];
                }else{
                    if($params['account_type'] == '1'){
                        if(!$params['role_account'] || !$params['role_pwd']){
                            $this->error_msg("请填写角色账号密码");
                        }
                        $params = array_merge($info,$params);
                        $check = $this->DAO->check_account($params);
                        if($check){
                            $this->error_msg("游戏帐号已存在，请重新输入");
                        }
                        $this->DAO->insert_order_log($id,$params,$_SESSION['usr_id']);
                    } elseif($params['account_type'] == '2'){
                        if(!$params['account_id']){
                            $this->error_msg("请选择游戏账号");
                        }
                        $account_info = $this->DAO->get_account_info($params['account_id']);
                        if($account_info['money'] > $params['pay_money']){
                            $this->error_msg("所关联的金额不能大于未受理金额");
                        }
                        $check = $this->DAO->check_account($account_info);
                        if($check){
                            $this->error_msg("游戏帐号已存在，请重新选择");
                        }
                        $params['role_account'] = $account_info['account'];
                        $params['role_pwd'] = $account_info['pwd'];
                        $this->DAO->update_order_log($params['account_id'],$id);
                    }
                }
            }elseif($params['type']  == '2'){
                $info['money_type'] = 3;
                if($user_info['pay_mode'] == 1){
                    $this->DAO->insert_money_log($info,$_SESSION['usr_id']);
                    $this->DAO->update_admins($user_info);
                }
            }
        if($params['type'] == '1' || $params['type'] == '2'){
            $user = $this->DAO->get_user_info($_SESSION['usr_id']);
            $this->DAO->update_relation_tb($user['status']-1,$_SESSION['usr_id']);
            $user_message = $this->DAO->get_user_info($_SESSION['usr_id']);
            if($user_message['qq']){
                $params['order_status'] = 2;
                $orders = $this->DAO->get_orders($user_message['app_list']);
                if($orders){
                    $email = $user_message['qq'].'@qq.com';
                    $title = '商会提交订单啦';
                    $content = "收到来自".$user_info['real_name']."的订单，单号为".$orders[0]['order_id']."，游戏名称为".$orders[0]['app_name']."，请尽快处理订单";
                    $this->set_mail($title,$content,$email);
                    $this->DAO->update_business_order($user_message['id'],$orders[0]['id']);
                    $this->DAO->update_relation_tb($user_message['status']+1,$user_message['id']);
                }
            }
        }else{
            $params['order_status'] = $user_info['order_status'];
        }
        $this->DAO->update_order_feedback($params,$id);
        $this->succeed_msg();
    }

    public function verify_view($id){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $info = $this->DAO->get_order_info($id);
        $order_log = $this->DAO->get_order_log_money($id);
        $img = explode(',',$info['img']);
        $app_list = $this->DAO->get_app_list();
        $server_list = $this->DAO->get_service_list($info['app_id']);
        $account_list = $this->DAO->get_account_list($info);
        $this->assign("account_list",$account_list);
        $this->assign("order_log",$order_log);
        $this->assign("server_list",$server_list);
        $this->assign("app_list",$app_list);
        $this->assign("img_list",$img);
        $this->assign("info",$info);
        $this->assign("qb_list",$this->QB);
        $this->assign("user_info",$user_info);
        $this->display("chamber/business_verify.html");
    }

    public function export(){
        $params = $_GET;
        if($_SESSION['group_id'] == 14){
            $params['user_id'] = $_SESSION['usr_id'];
        }
        $datalist = $this->DAO->get_all_order_list($params);
        if($datalist){
            $this->master_excel_out($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("订单列表");
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(35);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(20);
        $objActSheet->getColumnDimension('K')->setWidth(20);
        $objActSheet->getColumnDimension('N')->setWidth(20);
        $objActSheet->getColumnDimension('P')->setWidth(30);
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "游戏名");
        $objActSheet->setCellValue("C1", "订单号");
        $objActSheet->setCellValue("D1", "商会名称");
        $objActSheet->setCellValue("E1", "补充说明");
        $objActSheet->setCellValue("F1", "区服名");
        $objActSheet->setCellValue("G1", "角色职业");
        $objActSheet->setCellValue("H1", "角色名");
        $objActSheet->setCellValue("I1", "充值金额");
        $objActSheet->setCellValue("J1", "实付金额");
        $objActSheet->setCellValue("K1", "登录账号");
        $objActSheet->setCellValue("L1", "账号密码");
        $objActSheet->setCellValue("M1", "支付方式");
        $objActSheet->setCellValue("N1", "下单时间");
        $objActSheet->setCellValue("O1", "订单状态");
        $objActSheet->setCellValue("P1", "回执备注");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['app_name']);
            $objActSheet->setCellValueExplicit("C".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("D".$n, $info['real_name']);
            $objActSheet->setCellValue("E".$n, $info['desc']);
            $objActSheet->setCellValue("F".$n, $info['service_name']);
            $objActSheet->setCellValue("G".$n, $info['role_job']);
            $objActSheet->setCellValue("H".$n, $info['role_name']);
            $objActSheet->setCellValue("I".$n, $info['money']);
            $objActSheet->setCellValue("J".$n, $info['pay_money']);
            $objActSheet->setCellValue("K".$n, $info['role_account']);
            $objActSheet->setCellValue("L".$n, $info['role_pwd']);
            if($info['pay_mode'] == 1){
                $objActSheet->setCellValue("M".$n, "余额支付");
            }else{
                $objActSheet->setCellValue("M".$n, "线下支付");
            }
            $objActSheet->setCellValue("N".$n, date("Y-m-d H:i:s",$info['add_time']));
            if($info['type'] == 0){
                $objActSheet->setCellValue("O".$n, "未受理");
            }elseif($info['type'] == 1){
                $objActSheet->setCellValue("O".$n, "受理成功");
            }elseif($info['type'] == 2){
                $objActSheet->setCellValue("O".$n, "受理失败");
            }elseif($info['type'] == 3){
                $objActSheet->setCellValue("O".$n, "受理中");
            }
            $objActSheet->setCellValue("P".$n, $info['feedback_desc']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","订单列表-".$str_now.'.xls');
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

    public function again_edit_view($id){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $info = $this->DAO->get_order_info($id);
        $img = explode(',',$info['img']);
        $app_list = $this->get_business_app_list($_SESSION['usr_id']);
        $game_list = $this->DAO->get_business_app_list($_SESSION['usr_id']);
        $server_list = $this->DAO->get_business_service_list($info['app_id'],$game_list['service_list']);
        $payment_method = explode(',',$user_info['payment_method']);
        $payment_list = array();
        foreach($this->payment as $key=>$data){
            if(in_array($key,$payment_method)){
                $payment_list[$key] = $data;
            }
        }
//        $app_list = $this->DAO->get_app_list();
//        $server_list = $this->DAO->get_service_list($info['app_id']);
//        $guild_list = $this->DAO->get_all_guild_list();
        $this->assign('payment_list',$payment_list);
        $this->assign("server_list",$server_list);
        $this->assign("app_list",$app_list);
        $this->assign("img_list",$img);
        $this->assign("info",$info);
        $this->assign("user_info",$user_info);
        $this->display("chamber/business_again_edit.html");
    }

    public function get_service(){
        if(!$_POST['app_id'] || !$_POST['user_id']){
            die(json_encode(array('code'=>0,'msg'=>"游戏出错啦")));
        }else{
            if($_SESSION['group_id'] ==  '1'){
                $list = $this->DAO->get_service_list($_POST['app_id']);
            }else{
                $service = $this->DAO->get_business_app_list($_POST['user_id']);
                $list = $this->DAO->get_business_service_list($_POST['app_id'],$service['service_list']);
            }
            $info = $this->DAO->get_app_info($_POST['app_id']);
            die(json_encode(array('code'=>1,'list'=>$list,'info'=>$info)));
        }
    }

    public function review_view(){
        if($_SESSION['group_id'] == '1'){
            $recharge_list = $this->DAO->get_recharge_list($this->page,$_GET);
        }else{
            echo "你没有该目录的权限,需要开启请联系管理员.";
//            $this->error_msg("");
            exit();
        }
        foreach ($recharge_list as $key=>$data) {
            $info = $this->DAO->get_user_info($data['user_id']);
            $recharge_list[$key]['real_name'] = $info['real_name'];
            $recharge_list[$key]['account'] = $info['account'];
        }
        $page = $this->pageshow($this->page, "business.php?act=review&start_time=".$_GET['start_time']."&end_time=".$_GET['end_time']."&status=".$_GET['status']."&");
        $this->assign("page_bar", $page->show());
        $this->assign("params", $_GET);
        $this->assign('list', $recharge_list);
        $this->display("chamber/business_recharge_view.html");
    }

    public function reason_view($id){
        $recharge_record = $this->DAO->get_recharge_record($id);
        $info = $this->DAO->get_user_info($recharge_record['user_id']);
        $recharge_record['real_name'] = $info['real_name'];
        $this->page_hash();
        $this->assign('info', $recharge_record);
        $this->display("chamber/review_view.html");
    }

    public function do_review($id){
        $params = $_POST;
        if(!$params['pagehash'] || $params['pagehash']!= $_SESSION['page-hash']){
            $this->error_msg("非法的操作.");
        }
        if(!$params['reason'] || empty($params['reason'])){
            $this->error_msg("请填写理由.");
        }
        $recharge_record = $this->DAO->get_recharge_record($id);
        if(!$recharge_record){
            $this->error_msg("充值信息不存在.");
        }
        $this->DAO->update_recharge_record($params,$_SESSION['usr_id'],$recharge_record['id']);
        if($params['status']==2){
            $son_info = $this->DAO->get_user_info($recharge_record['user_id']);
            $son_info['money'] = $son_info['money'] + $recharge_record['amount'];
            $this->DAO->guild_lock($son_info['id'],1);
            $this->DAO->update_guild_money($son_info['money'],$son_info['id']);
            $this->DAO->guild_lock($son_info['id'],0);
            $data['son_id'] = $son_info['id'];
            $data['amount'] = $recharge_record['amount'];
            $data['parent_id'] = $_SESSION['usr_id'];
            $data['remarks'] = $_SESSION['remarks'];
//            $this->DAO->insert_guild_nnb_log($this->create_guid(),$data,1);
        }
        $this->succeed_msg();
    }

    public function do_again_edit($id){
        $params = $_POST;
        if($_SESSION['group_id'] != '2' && $_SESSION['group_id'] != '3'){
            $this->error_msg("你没有该目录的权限,需要开启请联系管理员.");
        }
            if(!$params['money']){
                $this->error_msg("受理中，金额不能为空");
            }elseif(!preg_match("/^[0-9]{0}([0-9]|[.][0-9])+$/",$params['money'])){
                $this->error_msg("金额格式不对");
            }elseif($params['money']>20000){
                $this->error_msg("一次受理的金额不能超过两万元");
            }elseif($params['money']>$params['remain_money']){
                $this->error_msg("受理金额不能超过未受理金额");
            }
            $params['discount'] = 0.71;
            if($params['pay_type']=='2'){
                $params['discount'] = $this->QB['爱云'];
            }elseif($params['pay_type']=='3'){
                $params['discount'] = $this->QB['游戏久0303'];
            }elseif($params['pay_type']=='4'){
                $params['discount'] = $this->QB['游戏久0304'];
            }elseif($params['pay_type']=='5'){
                $params['discount'] = $this->QB['游戏久0305'];
            }elseif($params['pay_type']=='6'){
                $params['discount'] = $this->QB['游戏久0306'];
            }
            $params['pay_money'] = $params['money']*$params['discount'];
            if($params['money']-$params['remain_money'] == 0){
                $params['type'] = 1;
                $user = $this->DAO->get_user_info($_SESSION['usr_id']);
                $this->DAO->update_relation_tb($user['status']-1,$_SESSION['usr_id']);
                $user_message = $this->DAO->get_user_info($_SESSION['usr_id']);
                $params['order_status'] = 2;
                $orders = $this->DAO->get_orders($user_message['app_list']);
                if($orders){
                    $email = $user_message['qq'].'@qq.com';
                    $title = '商会提交订单啦';
                    $content = "收到来自".$orders[0]['real_name']."的订单，单号为".$orders[0]['order_id']."，游戏名称为".$orders[0]['app_name']."，请尽快处理订单";
                    $this->set_mail($title,$content,$email);
                    $this->DAO->update_business_order($user_message['id'],$orders[0]['id']);
                    $this->DAO->update_relation_tb($user_message['status']+1,$user_message['id']);
                }
                $this->DAO->update_order_type($params,$id);
            }
            $info = $this->DAO->get_order_info($id);
            $params = array_merge($info,$params);
            $this->DAO->insert_order_log($id,$params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function get_order_log($id){
        $log_list = $this->DAO->get_order_log_list($id);
        $this->assign("id",$id);
        $this->assign("log_list",$log_list);
        $this->display("chamber/business_order_log.html");
    }

    public function order_log_list(){
        $params = $this->get_params($_POST,$_GET);
        $params['pay_type'] = $this->get_pay_type($params['type']);
        $order_list = $this->DAO->get_log_list($this->page,$params);
        $money_num = $this->DAO->get_money_num($params);
        $app_list = $this->DAO->get_app_list();
        $guild_list = $this->DAO->get_all_guild_list();
        $page = $this->pageshow($this->page, "business.php?act=order_log_list&");
        $this->assign("guild_list", $guild_list);
        $this->assign("qb_list", $this->QB);
        $this->assign("money_num",$money_num['num']);
        $this->assign("params",$params);
        $this->assign("app_list", $app_list);
        $this->assign("page_bar", $page->show());
        $this->assign("order_list", $order_list);
        $this->display("chamber/business_log_list.html");
    }

    public function add_charge(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        $this->assign("user_info", $user_info);
        $this->assign("app_list", $app_list);
        $this->assign("qb_list", $this->QB);
        $this->display("chamber/business_add_charge.html");
    }

    public function do_add_charge(){
        $params = $_POST;
        if(!$params['app_id'] || !$params['service_id'] || !$params['role_job'] || !$params['role_sex'] || !$params['role_name'] || !$params['account'] || !$params['pwd'] || !$params['money']){
            $this->error_msg("缺少必填参数");
        }
        if(!preg_match("/^[0-9]{0}([0-9]|[.][0-9])+$/",$params['money'])){
            $this->error_msg("金额格式不对");
        }elseif($params['money']>20000){
            $this->error_msg("一次受理的金额不能超过两万元");
        }
        $params['discount'] = 1;
        if($params['pay_status']){
            $params['pay_type'] = 1;
        }elseif($params['type']){
            $params['discount'] = $this->QB[$params['type']];
            $params['pay_type'] = $this->get_pay_type($params['type']);
        }
        $params['pay_money'] = $params['money']*$params['discount'];
        $this->DAO->insert_log($params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function log_details($id){
        $log_info = $this->DAO->get_log_info($id);
        $server_list = $this->DAO->get_service_list($log_info['app_id']);
        $app_list = $this->DAO->get_app_list();
        $this->assign("app_list",$app_list);
        $this->assign("server_list",$server_list);
        $this->assign("info",$log_info);
        $this->display("chamber/business_log_details.html");
    }

    public function do_edit_charge(){
        $params = $_POST;
        if(!$params['money']){
            $this->error_msg("请填写金额");
        }elseif(!preg_match("/^[0-9]{0}([0-9]|[.][0-9])+$/",$params['money'])){
            $this->error_msg("金额格式不对");
        }elseif($params['money']>20000){
            $this->error_msg("一次受理的金额不能超过两万元");
        }
        $info = $this->DAO->get_account_info($params['id']);
        $params['pay_money'] = $info['qb_discount']*$params['money'];
        $this->DAO->update_log($params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function get_account(){
        if(!$_POST['app_id'] || !$_POST['service_id']){
            die(json_encode(array('code'=>0,'msg'=>"游戏或者区服出错啦")));
        }else{
            $list = $this->DAO->get_account_list($_POST);
            die(json_encode(array('code'=>1,'info'=>$list)));
        }
    }

    public function payment_log(){
        $params = $this->get_params($_POST,$_GET);
        $params['pay_type'] = $this->get_pay_type($params['type']);
        $order_list = $this->DAO->get_pay_log_list($this->page,$params);
        foreach($order_list as $key=>$data){
            if($data['payment_method']){
                $order_list[$key]['payment'] = $this->payment[$data['payment_method']]['account'];
            }
        }
        $money = $this->DAO->get_payment_num($params);
        $pay_money = $this->DAO->get_pay_money_num($params);
        $app_list = $this->DAO->get_app_list();
        $guild_list = $this->DAO->get_all_guild_list();
        $page = $this->pageshow($this->page, "business.php?act=payment_log&");
        $this->assign("guild_list", $guild_list);
        $this->assign("payment_list",$this->payment);
        $this->assign("money_total",$money['num']);
        $this->assign("pay_money",$pay_money['num']);
        $this->assign("params",$params);
        $this->assign("app_list", $app_list);
        $this->assign("qb_list", $this->QB);
        $this->assign("page_bar", $page->show());
        $this->assign("order_list", $order_list);
        $this->display("chamber/business_payment_list.html");
    }

    public function add_payment(){
        $this->assign("qb_list",$this->QB);
        $this->display("chamber/business_add_payment.html");
    }

    public function get_pay_type($type){
        if($type == '现金'){
            $pay_type = 1;
        }elseif($type == '爱云'){
            $pay_type = 2;
        }elseif($type == '游戏久0303'){
            $pay_type = 3;
        }elseif($type == '游戏久0304'){
            $pay_type = 4;
        }elseif($type == '游戏久0305'){
            $pay_type = 5;
        }elseif($type == '游戏久0306'){
            $pay_type = 6;
        }
        return $pay_type;
    }

    public function do_add_payment(){
        $params = $_POST;
        if(!$params['type'] || !$params['money'] || !$params['payment_time']){
            $this->error_msg("缺少必填参数");
        }
        if(!preg_match("/^[0-9]{0}([0-9]|[.][0-9])+$/",$params['money'])){
            $this->error_msg("金额格式不对");
        }
        $params['pay_type'] = $this->get_pay_type($params['type']);
        $info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $this->DAO->insert_payment($params,$info);
        $this->succeed_msg();
    }

    public function import_view(){
        $this->display('chamber/business_orders_import_view.html');
    }

    public function do_import(){
        if (isset($_FILES['orders_file'])){
            $orders_file = $_FILES['orders_file'];
            if (preg_match("/\.xls$/",$orders_file['name'])){
                $type = 1;
                $temp = dirname($orders_file['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$orders_file['name'])){
                $type = 2;
                $temp = dirname($orders_file['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if ($orders_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }

            if (move_uploaded_file($orders_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name"=>"商会名称","title_field"=>"guild_id","title_type"=>"string"),
                        array("title_name"=>"游戏名称","title_field"=>"app_id","title_type"=>"string"),
                        array("title_name"=>"区服名称","title_field"=>"service_id","title_type"=>"string"),
                        array("title_name"=>"职业名称","title_field"=>"role_job","title_type"=>"string"),
                        array("title_name"=>"角色性别","title_field"=>"role_sex","title_type"=>"string"),
                        array("title_name"=>"首选角色名","title_field"=>"role_name","title_type"=>"string"),
                        array("title_name"=>"备选角色名","title_field"=>"spare_role","title_type"=>""),
                        array("title_name"=>"备用账号","title_field"=>"spare_account","title_type"=>""),
                        array("title_name"=>"补充说明","title_field"=>"desc","title_type"=>""),
                        array("title_name"=>"支付方式","title_field"=>"pay_mode","title_type"=>"string"),
                        array("title_name"=>"支付金额","title_field"=>"money","title_type"=>"float")
                        );
                    $orders_data = $this->excel_import_data($temp,$data_arr,$type);
                    unlink($temp);
                    //获取游戏及区服信息
                    $game_list = $this->DAO->get_business_app_list($_SESSION['usr_id']);
                    $res = $this->DAO->get_game_services_all($game_list);
                    if (empty($res)){
                        $this->error_msg("没有维护对应游戏");
                    }
                    //获取商会信息
                    $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
                    $game_list = array();
                    $service_list = array();
                    $service_id_list = array();
                    $discount_list = array();
                    foreach ($res as $res_value){
                        if (!in_array($res_value['app_name'],array_values($game_list))){
                            $game_list[$res_value['app_id']] = $res_value['app_name'];
                            $discount_list[$res_value['app_id']] = $res_value['discount'];
                        }
                        if (!in_array($res_value['service_name'],array_values($service_list))){
                            $service_list[$res_value['id']] = $res_value['service_name'];
                            $service_id_list[$res_value['id']] = $res_value['service_id'];
                        }
                    }
                    $sum = 0;
                    foreach ($orders_data as $key=>$value){
                        if ($value['guild_id']!=$user_info['real_name']){
                            $this->error_msg("商会名称不正确");
                        }
                        $orders_data[$key]['guild_id'] = (int)$user_info['id'];
                        if (!in_array($value['app_id'],array_values($game_list))){
                            $this->error_msg("没有".$value['app_id']."游戏");
                        }
                        $orders_data[$key]['app_id'] = array_search($value['app_id'], $game_list);
                        if (!in_array($value['service_id'],array_values($service_list))){
                            $this->error_msg("没有".$value['service_id']."区服");
                        }
                        $orders_data[$key]['service_id'] = $service_id_list[array_search($value['service_id'], $service_list)];
                        if ($value['role_sex']=="男"){
                            $orders_data[$key]['role_sex'] = 1;
                        }elseif ($value['role_sex']=="女"){
                            $orders_data[$key]['role_sex'] = 2;
                        }else{
                            $this->error_msg("性别填写错误");
                        }
                        $orders_data[$key]['pay_money'] = $value['money']*(float)$discount_list[$orders_data[$key]['app_id']];
                        if ($value['pay_mode']=="余额"){
                            $orders_data[$key]['pay_mode'] = 1;
                            $sum += $orders_data[$key]['pay_money'];
                        }elseif ($value['pay_mode']=="线下"){
                            $orders_data[$key]['pay_mode'] = 2;
                        }else{
                            $this->error_msg("支付方式填写错误");
                        }
                        $orders_data[$key]['img'] = "";
                        $str = '';
                        for ($i = 1; $i <= 4; $i++) {
                            $str .= chr(rand(65, 90));
                        }
                        $orders_data[$key]['order_id'] = $str.date('Ymd').time();
                    }
                    if($user_info['money_lock'] == 1){
                        $this->error_msg("账户余额已冻结，不可使用余额支付");
                    }elseif($user_info['money'] < $sum){
                        $this->error_msg("余额不足，请重新选择支付方式");
                    }
                    //导入mysql
                    if ($sum===0){
                        $user_info['update_money'] = 0;
                    }else{
                        $user_info['update_money'] = (float)$user_info['money']-$sum;
                    }
                    $id = $this->DAO->import_orders($orders_data,$user_info);
                    if (!$id){
                        $this->error_msg("导入失败！");
                    }
                    $orders = $this->DAO->get_orders();
                    foreach($orders as $key=>$data){
                        $qq_list = $this->DAO->get_user_qq();
                        if(count($qq_list)==0){
                            break;
                        }else{
                            foreach($qq_list as $k=>$d){
                                $app_list = explode(',', $d['app_list']);
                                if(in_array($data['app_id'], $app_list)){
                                    $app_info = $this->DAO->get_app_info($data['app_id']);
                                    $email = $d['qq'] . '@qq.com';
                                    $title = '商会提交订单啦';
                                    $content = "收到来自" . $user_info['real_name'] . "的订单，单号为" . $data['order_id'] . "，游戏名称为" . $app_info['app_name'] . "，请尽快处理订单";
                                    $this->set_mail($title, $content, $email);
                                    $this->DAO->update_business_order($d['id'], $data['id']);
                                    $this->DAO->update_relation_tb($d['status'] + 1, $d['id']);
                                    break;
                                }
                            }
                        }
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

    public function order_edit($id){
        $info = $this->DAO->get_order_info($id);
        $order_log = $this->DAO->get_order_log_money($id);
        $img = explode(',',$info['img']);
        $app_list = $this->get_business_app_list($info['guild_id']);
//        $app_list = $this->DAO->get_business_app_list($_SESSION['usr_id']);
//        $server_list = $this->DAO->get_service_list($info['app_id']);
        $game_list = $this->DAO->get_business_app_list($info['guild_id']);
        $server_list = $this->DAO->get_business_service_list($info['app_id'],$game_list['service_list']);
        $account_list = $this->DAO->get_account_list($info);
        $user_info = $this->DAO->get_user_info($info['guild_id']);
        $payment_method = explode(',',$user_info['payment_method']);
        $payment_list = array();
        foreach($this->payment as $key=>$data){
            if(in_array($key,$payment_method)){
                $payment_list[$key] = $data;
            }
        }
        $this->assign('payment_list',$payment_list);
        $this->assign("account_list",$account_list);
        $this->assign("order_log",$order_log);
        $this->assign("server_list",$server_list);
        $this->assign("app_list",$app_list);
        $this->assign("img_list",$img);
        $this->assign("info",$info);
        $this->display("chamber/business_order_edit.html");
    }

    public function do_order_edit($id){
        $params = $_POST;
        if(!$params['app_id'] || !$params['service_id'] || !$params['role_job'] || !$params['role_sex'] || !$params['role_name'] || !$params['payment_method']){
            $this->error_msg("缺少必填参数");
        }
        $imgs = '';
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $voucher_img = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($voucher_img as $key=>$img){
                if($img){
                    $imgs .= $img.",";
                }
            }
            $params['img'] = trim($imgs,",");
        }else{
            $params['img'] = $params['old_img'];
        }
        $info = $this->DAO->get_order_info($id);
        if($_SESSION['group_id'] != '14'){
            if(!$params['edit_reason']){
                $this->error_msg("编辑必须填写理由");
            }
            $reason = "";
            if($info['app_id'] != $params['app_id']){
                $reason .= "游戏由'".$info['app_id']."'变更为'".$params['app_id']."',";
            }
            if($info['payment_method'] != $params['payment_method']){
                $reason .= "付款方式由'".$info['payment_method']."'变更为'".$params['payment_method']."',";
            }
            if($info["service_id"] != $params['service_id']){
                $reason .= "区服由'".$info['service_id']."'变更为'".$params['service_id']."',";
            }
            if($info["role_job"] != $params['role_job']){
                $reason .= "职业由'".$info['role_job']."'变更为'".$params['role_job']."',";
            }
            if($info["role_name"] != $params['role_name']){
                $reason .= "首选角色名由'".$info['role_name']."'变更为'".$params['role_name']."',";
            }
            if($info["spare_role"] != $params['spare_role']){
                $reason .= "备用角色名由'".$info['spare_role']."'变更为'".$params['spare_role']."',";
            }
            if($info["spare_account"] != $params['spare_account']){
                $reason .= "备用账号由'".$info['spare_account']."'变更为'".$params['spare_account']."',";
            }
            if($info["role_sex"] != $params['role_sex']){
                $reason .= "性别由'".$info['role_sex']."'变更为'".$params['role_sex']."',";
            }
            if($info["desc"] != $params['desc']){
                $reason .= "备用账号由'".$info['desc']."'变更为'".$params['desc']."'";
            }
            $reason .= "客服编辑原因'".$params['edit_reason']."'";
            $this->DAO->insert_operation_log(trim($reason,","),$_SESSION['usr_id']);
        }else{
            $params['edit_reason'] = '';
        }
        if($info['order_status'] == '0' && $info['app_id'] != $params['app_id']){
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            $qq_list = $this->DAO->get_user_qq($params['app_id']);
            if($qq_list){
                $key = array_rand($qq_list);
                $app_info = $this->DAO->get_app_info($params['app_id']);
                $email = $qq_list[$key]['qq'].'@qq.com';
                $title = '商会提交订单啦';
                $content = "收到来自".$user_info['real_name']."的订单，单号为".$info['order_id']."，游戏名称为".$app_info['app_name']."，请尽快处理订单";
                $this->set_mail($title,$content,$email);
                $this->DAO->update_business_order($qq_list[$key]['id'],$id);
                $this->DAO->update_relation_tb($qq_list[$key]['status']+1,$qq_list[$key]['id']);
            }
        }
        $this->DAO->update_order($params,$id);
        $this->succeed_msg();
    }

    public function del_log($id){
        $this->assign("id",$id);
        $this->display("chamber/business_order_del.html");
    }

    public function do_del($id){
        $info = $this->DAO->get_order_log_info($id);
        if(!$info){
            $this->error_msg("订单ID出错啦");
        }
        $this->DAO->del_order($id);
        $this->succeed_msg();
    }

    public function detail_export(){
        $params = $_GET;
        $datalist = $this->DAO->get_all_detail_list($params);
        if($datalist){
            $total_money = 0;
            krsort($datalist);
            foreach($datalist as $key=>$data){
                if($data['status'] == 10){
                    $datalist[$key]['remain_money'] = $total_money = $total_money+$data['pay_money'];
                }else{
                    $datalist[$key]['remain_money'] = $total_money = $total_money-$data['pay_money'];
                }
            }
            arsort($datalist);
            $this->master_excel_detail_out($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_detail_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("收支明细");
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(20);
        $objActSheet->getColumnDimension('M')->setWidth(20);
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "游戏名");
        $objActSheet->setCellValue("C1", "账号");
        $objActSheet->setCellValue("D1", "角色名");
        $objActSheet->setCellValue("E1", "角色性别");
        $objActSheet->setCellValue("F1", "区服名");
        $objActSheet->setCellValue("G1", "角色职业");
        $objActSheet->setCellValue("H1", "充值金额");
        $objActSheet->setCellValue("I1", "实付金额");
        $objActSheet->setCellValue("J1", "账户余额");
        $objActSheet->setCellValue("K1", "支付方式");
        $objActSheet->setCellValue("L1", "录入时间");
        $objActSheet->setCellValue("M1", "出库时间");
        $objActSheet->setCellValue("N1", "订单状态");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            if($info['app_id'] == '99999'){
                $objActSheet->setCellValue("B".$n, "金额录入");
            }else{
                $objActSheet->setCellValue("B".$n, $info['app_name']);
            }
            $objActSheet->setCellValueExplicit("C".$n, $info['account'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("D".$n, $info['role_name']);
            if($info['role_sex'] == 1){
                $objActSheet->setCellValue("E".$n, "男");
            }elseif($info['role_sex'] == 2){
                $objActSheet->setCellValue("E".$n, "女");
            }else{
                $objActSheet->setCellValue("E".$n, "未知");
            }
            if($info['service_id'] == '88888'){
                $objActSheet->setCellValue("F".$n, "牛牛区服");
            }else{
                $objActSheet->setCellValue("F".$n, $info['service_name']);
            }
            $objActSheet->setCellValue("G".$n, $info['role_job']);
            $objActSheet->setCellValue("H".$n, $info['money']);
            $objActSheet->setCellValue("I".$n, $info['pay_money']);
            $objActSheet->setCellValue("J".$n, $info['remain_money']);
            if($info['pay_type'] == 1){
                $objActSheet->setCellValue("K".$n, "支付宝");
            }else{
                $objActSheet->setCellValue("K".$n, "QB");
            }
            $objActSheet->setCellValue("L".$n, date("Y-m-d H:i:s",$info['add_time']));
            $objActSheet->setCellValue("M".$n, date("Y-m-d H:i:s",$info['finish_time']));
            if($info['status'] == 2){
                $objActSheet->setCellValue("N".$n, "已出库");
            }elseif($info['status'] == 10){
                $objActSheet->setCellValue("N".$n, "录入");
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","收支明细-".$str_now.'.xls');
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

    public function del_payment($id){
        $this->assign("id",$id);
        $this->display("chamber/business_payment_del.html");
    }

    public function do_del_payment($id){
        $info = $this->DAO->get_order_log_info($id);
        if(!$info){
            $this->error_msg("订单ID出错啦");
        }
        $desc = "录入记录ID为'".$id."',金额为'".$info['pay_money']."'这条记录删除";
        $this->DAO->insert_operation_log($desc,$_SESSION['usr_id']);
        $this->DAO->del_order($id);
        $this->succeed_msg();
    }

    public function refill_view($id){
        $info = $this->DAO->get_order_info($id);
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $this->assign("user_info",$user_info);
        $this->assign("info",$info);
        $this->display("chamber/business_refill.html");
    }

    public function do_refill(){
        $params = $_POST;
        if(!$params['pay_mode'] || !$params['money'] || !$params['pay_money']){
            $this->error_msg("缺少必填项");
        }
        if($params['money']<=0){
            $this->error_msg("支付金额格式不对");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($params['pay_mode'] == 2){
            if(!($_FILES['imgs']['tmp_name'][0] && $_FILES['imgs']['tmp_name'])){
                $this->error_msg("线下支付必须上传凭证");
            }
        }else{
            if($user_info['money_lock'] == 1){
                $this->error_msg("账户余额已冻结，不可使用余额支付");
            }elseif($user_info['money'] < $params['pay_money']){
                $this->error_msg("余额不足，请重新选择支付方式");
            }
        }
        $imgs = '';
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $voucher_img = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($voucher_img as $key=>$img){
                if($img){
                    $imgs .= $img.",";
                }
            }
            $params['img'] = trim($imgs,",");
        }
        $str = '';
        for ($i = 1; $i <= 4; $i++) {
            $str .= chr(rand(65, 90));
        }
        $info = $this->DAO->get_order_info($params['id']);
        $params['order_id'] = $str.date('Ymd').time();
        $params['role_name'] = $info['role_name'];
        $params['role_job'] = $info['role_job'];
        $params['role_sex'] = $info['role_sex'];
        $params['app_id'] = $info['app_id'];
        $params['service_id'] = $info['service_id'];
        $params['role_account'] = $info['role_account'];
        $params['role_pwd'] = $info['role_pwd'];
        $params['pay_type'] = $info['pay_type'];
        $order_id = $this->DAO->insert_refill_order($params,$user_info);
        $qq_list = $this->DAO->get_user_qq();
        if($qq_list){
            foreach($qq_list as $key=>$data){
                $app_list = explode(',',$data['app_list']);
                if(in_array($params['app_id'],$app_list)){
                    $app_info = $this->DAO->get_app_info($params['app_id']);
                    $email = $data['qq'].'@qq.com';
                    $title = '商会提交续充订单啦';
                    $content = "收到来自".$user_info['real_name']."的订单，单号为".$params['order_id']."，游戏名称为".$app_info['app_name']."，请尽快处理订单";
                    $this->set_mail($title,$content,$email);
                    $this->DAO->update_business_order($data['id'],$order_id);
                    $this->DAO->update_relation_tb($data['status']+1,$data['id']);
                    break;
                }
            }
        }
        $this->succeed_msg();
    }

    public function service_online(){
        $game_admin_dao = new game_admin_dao();
        $game_all = $game_admin_dao->get_all_app();
        foreach($game_all as $key=>$data){
            if($data['chamber_type'] == 1){
                unset($game_all[$key]);
            }
        }
        $this->assign("games",$game_all);
        $this->display('chamber/service_online.html');
    }

    public function do_service_online(){
        if(empty($_POST['is_online'])){
            die(json_encode(array('code'=>0,'msg'=>'请设置接单状态')));
        }
        if(empty($_POST['app_id'])){
            die(json_encode(array('code'=>0,'msg'=>'请选择接单游戏')));
        }
        $_POST['app_list'] = implode($_POST['app_id'],',');
        $user_info = $this->DAO->get_relation_info($_SESSION['usr_id']);
        if($user_info){
            $this->DAO->update_relation_info($_SESSION['usr_id'],$_POST);
        }else{
            $this->DAO->insert_relation_tb($_SESSION['usr_id'],$_POST);
        }
        if($_POST['is_online'] == '1'){
            if($user_info['status'] < 3){
                $orders = $this->DAO->get_orders($_POST['app_list']);
                if($orders){
                    foreach($orders as $key=>$data){
                        $user_message = $this->DAO->get_user_info($_SESSION['usr_id']);
                        if($user_message['status'] < 3){
                            $email = $user_message['qq'].'@qq.com';
                            $title = '商会提交订单啦';
                            $content = "收到来自".$data['real_name']."的订单，单号为".$data['order_id']."，游戏名称为".$data['app_name']."，请尽快处理订单";
                            $this->set_mail($title,$content,$email);
                            $this->DAO->update_business_order($user_message['id'],$data['id']);
                            $this->DAO->update_relation_tb($user_message['status']+1,$user_message['id']);
                        }else{
                            break;
                        }
                    }
                }
            }
        }
        die(json_encode(array('code'=>1,'msg'=>'设置成功')));
    }

    public function tpl_down(){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("导入订单");
        $objActSheet->setCellValue("A1", "商会名称");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "区服名称");
        $objActSheet->setCellValue("D1", "职业名称");
        $objActSheet->setCellValue("E1", "角色性别");
        $objActSheet->setCellValue("F1", "首选角色名");
        $objActSheet->setCellValue("G1", "备选角色名");
        $objActSheet->setCellValue("H1", "备用账号");
        $objActSheet->setCellValue("I1", "补充说明");
        $objActSheet->setCellValue("J1", "支付方式");
        $objActSheet->setCellValue("K1", "支付金额");
        $objActSheet->setCellValue("A2", " ");
        $objActSheet->setCellValue("B2", " ");
        $objActSheet->setCellValue("C2", " ");
        $objActSheet->setCellValue("D2", " ");
        $objActSheet->setCellValue("E2", " ");
        $objActSheet->setCellValue("F2", " ");
        $objActSheet->setCellValue("G2", " ");
        $objActSheet->setCellValue("H2", " ");
        $objActSheet->setCellValue("I2", " ");
        $objActSheet->setCellValue("J2", " ");
        $objActSheet->setCellValue("K2", " ");
        $title = iconv("UTF-8", "GB2312//IGNORE","订单导入模版-".$str_now.".xls");
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

    public function log_export(){
        $params = $_GET;
        $datalist = $this->DAO->get_log_list_all($params);
        if($datalist){
            $this->master_excel_order_log($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_order_log($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏账号管理");
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(20);
        $objActSheet->getColumnDimension('N')->setWidth(20);
        $objActSheet->getColumnDimension('M')->setWidth(20);
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "执行员");
        $objActSheet->setCellValue("C1", "录入时间 ");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服信息");
        $objActSheet->setCellValue("F1", "角色名");
        $objActSheet->setCellValue("G1", "账号");
        $objActSheet->setCellValue("H1", "支付方式");
        $objActSheet->setCellValue("I1", "支付金额");
        $objActSheet->setCellValue("J1", "实付金额");
        $objActSheet->setCellValue("K1", "角色性别");
        $objActSheet->setCellValue("L1", "角色职业");
        $objActSheet->setCellValue("M1", "商会名称");
        $objActSheet->setCellValue("N1", "出库时间");
        $n = 2;
        foreach($data as $info){
            $user_info = $this->DAO->get_user_info($info['guild_id']);
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['real_name']);
            $objActSheet->setCellValueExplicit("C".$n, date("Y-m-d H:i:s",$info['add_time']));
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info['role_name']);
            $objActSheet->setCellValue("G".$n, $info['account']);
            if($info['pay_type'] == 1){
                $objActSheet->setCellValue("H".$n, "支付宝");
            }elseif($info['pay_type'] == 2){
                $objActSheet->setCellValue("H".$n, "爱云");
            }elseif($info['pay_type'] == 3){
                $objActSheet->setCellValue("H".$n, "游戏久0303");
            }elseif($info['pay_type'] == 4){
                $objActSheet->setCellValue("H".$n, "游戏久0304");
            }elseif($info['pay_type'] == 5){
                $objActSheet->setCellValue("H".$n, "游戏久0305");
            }elseif($info['pay_type'] == 6){
                $objActSheet->setCellValue("H".$n, "游戏久0306");
            }
            $objActSheet->setCellValue("I".$n, $info['money']);
            $objActSheet->setCellValue("J".$n, $info['pay_money']);
            if($info['role_sex'] == '1'){
                $objActSheet->setCellValue('K'.$n, '男');
            }elseif($info['role_sex'] == '2'){
                $objActSheet->setCellValue('K'.$n, '女');
            }else{
                $objActSheet->setCellValue('K'.$n, '未知');
            }
            $objActSheet->setCellValue("L".$n, $info['role_job']);
            $objActSheet->setCellValue("M".$n, $user_info['real_name']);
            $objActSheet->setCellValue("N".$n, date("Y-m-d H:i:s",$info['finish_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏账号管理-".$str_now.'.xls');
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