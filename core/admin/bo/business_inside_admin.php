<?php
COMMON('adminBaseCore','pageCore','uploadHelper','QQMailer');
DAO('business_inside_dao');

class business_inside_admin extends adminBaseCore{
    public $DAO;
    public $vip;
    public $channel;
    public function __construct() {
        parent::__construct();
        $this->DAO = new business_inside_dao();
        $this->vip = array(
            '5'=>'vip5',
            '10'=>'vip10',
            '12'=>'vip12',
        );
        $this->channel = array(
            '00008'=>'应用宝',
            '00009'=>'魔域混服',
            '00010'=>'官服'
        );
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $app_list = $this->DAO->get_app_list();
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            $order_list = $this->DAO->get_group_order_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $order_list = $this->DAO->get_group_order_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $order_list = $this->DAO->get_group_order_list($params,$this->page,$_SESSION['usr_id']);
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
            if($data['status'] == '0'){
                $next = strtotime(date('Ymd',$data['add_time']+86400).'12:00:00');
                if(time()>$next){
                    $this->DAO->update_group_order($data['id'],1);
                    $order_list[$key]['status'] = 1;
                    $app_info = $this->DAO->get_app_info($data['app_id']);
                    $data['channel'] = $app_info['channel'];
                     //个人月订单、月汇总
                    $data['order_date'] = date('Ym',time());
                    $user = $this->DAO->get_user_info($data['user_id']);
                    $personal_month_order = $this->DAO->get_personal_day_order($data,0,$data['user_id']);
                    $personal_month_money = $this->DAO->get_personal_day_money($data,0,$data['user_id']);
                    //本组月订单、月汇总
                    if($user['p2']){
                        $data['group_id'] =  $user['p1'];
                        $data['business_id'] = $user['p2'];
                    }elseif($user['p1']){
                        $data['group_id'] = $user['id'];
                        $data['business_id'] = $user['p1'];
                    }else{
                        $data['group_id'] = $user['id'];
                        $data['business_id'] = $user['id'];
                    }
                    $group_month_order = $this->DAO->get_group_day_order($data,1,$data['group_id']);
                    $group_month_money = $this->DAO->get_group_day_money($data,1,$data['group_id']);
                    $business_month_order = $this->DAO->get_business_order($data,2,$data['business_id']);
                    $business_month_money = $this->DAO->get_business_money($data,2,$data['business_id']);
                    $this->month_order($personal_month_order,$data,$personal_month_money,0);
                    $this->month_order($group_month_order,$data,$group_month_money,1);
                    $this->month_order($business_month_order,$data,$business_month_money,2);
                }
            }
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("channel_list",$this->channel);
        $this->assign("service_list",$service_list);
        $this->assign("params",$params);
        $this->assign("app_list",$app_list);
        $this->assign("user_info",$user_info);
        $this->assign("order_list",$order_list);
        $this->display('chamber/inside_list.html');
    }

    public function add_view(){
        $app_list = $this->DAO->get_app_list();
        $this->assign("vip_list",$this->vip);
        $this->assign("app_list",$app_list);
        $this->display('chamber/inside_add.html');
    }

    public function do_add(){
        $params = $_POST;
        if(!$params['app_id'] || !$params['service_id'] || is_null($params['buy_name']) || is_null($params['exit_depot']) || is_null($params['in_money']) || is_null($params['loss_num']) || is_null($params['pay_mode']) || is_null($params['token_scale'])){
            $this->error_msg('缺少必填项');
        }
        if($params['in_money'] < 0 || $params['exit_depot'] <0 || $params['loss_num']<0){
            $this->error_msg("收入金额,出仓代币,损耗代币均不能为负数");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $str = '';
        for ($i = 1; $i <= 4; $i++) {
            $str .= chr(rand(65, 90));
        }
        $params['order_id'] = $str.date('Ymd').time().rand(100,999);
        $app_info = $this->DAO->get_app_info($params['app_id']);
        if($params['token_scale'] > $app_info['goods_scale']){
            $params['order_type'] = 2;
        }else{
            $params['order_type'] = 1;
        }
        $params['channel'] = $app_info['channel'];
        $params['order_date']  = date('Ymd',time());
        if($user_info['p2']){
            $params['group_id'] = $user_info['p1'];
            $params['business_id'] = $user_info['p2'];
        }elseif($user_info['p1']){
            $params['group_id'] = $_SESSION['usr_id'];
            $params['business_id'] = $user_info['p1'];
        }else{
            $params['group_id'] = $_SESSION['usr_id'];
            $params['business_id'] = $_SESSION['usr_id'];
        }
//        //该区服该商会的代币余额
//        if($user_info['p2']){
//            $lead_stock_info = $this->DAO->get_stock_info($params,$user_info['p2']);
//        }
        $stock_info = $this->DAO->get_stock_info($params,$params['group_id']);
        if(!$stock_info['stock_balance'] || ($stock_info['stock_balance']-$params['exit_depot']-$params['loss_num'])<0){
            $this->error_msg('当前区服代币不足，请向上级申请拨币');
        }
        //个人日订单、日汇总
        $personal_day_order = $this->DAO->get_personal_day_order($params,0,$_SESSION['usr_id']);
        $personal_day_money = $this->DAO->get_personal_day_money($params,0,$_SESSION['usr_id']);
        //本组日订单、日汇总
        $group_day_order = $this->DAO->get_group_day_order($params,1,$params['group_id']);
        $group_day_money = $this->DAO->get_group_day_money($params,1,$params['group_id']);
        //本商会日订单、日汇总
        $business_day_order = $this->DAO->get_business_order($params,2,$params['business_id']);
        $business_day_money = $this->DAO->get_business_money($params,2,$params['business_id']);
        //个人日订单、日汇总
        $this->day_order($personal_day_order,$params,$personal_day_money,0,$_SESSION['usr_id']);
        //本组日订单、日汇总
        $this->day_order($group_day_order,$params,$group_day_money,1,$_SESSION['usr_id']);
        //本商会日订单、日汇总
        $this->day_order($business_day_order,$params,$business_day_money,2,$_SESSION['usr_id']);
        $this->DAO->insert_group_order($params,$_SESSION['usr_id']);
        //日现金明细
        if($params['pay_mode'] == '1'){
            $params['wx_money'] = $params['in_money'];
            $params['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $params['ali_money'] = $params['in_money'];
            $params['wx_money'] = 0;
        }else{
            $params['ali_money'] = 0;
            $params['wx_money'] = 0;
        }
        $params['type'] = 0;
        $params['do_time'] = time();
        $params['sell_time'] = time();
        $this->DAO->insert_money_detail($params,$_SESSION['usr_id']);
        //更新自己的库存代币
        $this->update_stock($params,$stock_info);
//        if($lead_stock_info){
//            //更新商会长的库存代币
//            $this->update_stock($params,$lead_stock_info,1);
//        }
        //插入库存记录
        if($user_info['p1'] && $user_info['p2']){
            $this->stock_record($params,$stock_info);
        }
        $this->succeed_msg();
    }

    public function stock_record($params,$info,$type='',$order=''){
        //type 1订单修改
        if($type){
            $new_app_info = $this->DAO->get_app_info($params['app_id']);
            $new_service_name = $this->DAO->get_service_name($params);
            $next_app_info = $this->DAO->get_app_info($info['app_id']);
            $next_service_name = $this->DAO->get_service_name($info);
            if($params['app_id'] != $order['app_id'] || $params['service_id'] != $order['service_id']){
                $params['desc'] = "订单修改，将游戏'" . $next_app_info['app_name'] .  "'修改成'" . $new_app_info['app_name'] . "',区服'".$next_service_name['service_name']. "'修改成'".$new_service_name['service_name']."'";
                $params['new_stock_balance'] = $info['stock_balance'] - $params['exit_depot']-$params['loss_num'];
                $params['stock_num'] = -$params['exit_depot'];
            }else{
                $params['desc'] = '订单修改';
                $params['new_stock_balance'] = $info['stock_balance'] + $order['exit_depot'] - $params['exit_depot'] + $order['loss_num'] - $params['loss_num'];
                $params['stock_num'] = $order['exit_depot'] - $params['exit_depot'] - $params['loss_num'];
            }
            $params['new_stock_collect'] = $info['stock_collect'];
        }else{
            $params['new_stock_balance'] = $info['stock_balance']-$params['exit_depot']-$params['loss_num'];
            $params['desc'] = '订单来源';
            $params['new_stock_collect'] = $info['stock_collect'];
            $params['stock_num'] = -$params['exit_depot']-$params['loss_num'];
        }
        $info['user_id'] = $_SESSION['usr_id'];
        $this->DAO->insert_stock_record($params,$info,$_SESSION['usr_id']);
    }

    public function update_stock($params,$info,$type='',$order='',$status=''){
        //type 1表示商会长库存代币
        if($status == '2'){
            $user_id = $params['user_id'];
        }else{
            $user_id = $_SESSION['usr_id'];
        }
        if($order){
            $user_info = $this->DAO->get_user_info($order['user_id']);
            $new_app_info = $this->DAO->get_app_info($params['app_id']);
            $new_service_name = $this->DAO->get_service_name($params);
            if($status == '1'){
                $next_app_info = $this->DAO->get_app_info($info['app_id']);
                $next_service_name = $this->DAO->get_service_name($info);
                if($info['user_id'] == $order['user_id']){
                    $params['desc'] = "订单修改，将游戏'" . $next_app_info['app_name'] . "'修改成'" . $new_app_info['app_name'] . "',区服'".$next_service_name['service_name']."'修改成'".$new_service_name['service_name']."'";
                }else{
                    $params['desc'] = "来自'" . $user_info['real_name'] . "'商会的订单修改，将游戏'" . $next_app_info['app_name'] .  "'修改成'" . $new_app_info['app_name'] . "',区服'".$next_service_name['service_name']."'修改成'".$new_service_name['service_name']."'";
                }
                if($type){
                    $params['new_stock_balance'] = $info['stock_balance'];
                }else{
                    $params['new_stock_balance'] = $info['stock_balance'] + $order['exit_depot'] + $order['loss_num'];
                }
                $params['new_stock_collect'] = $info['stock_collect'];
                $params['stock_num'] = $order['exit_depot'];
                $params['app_id'] = $info['app_id'];
                $params['service_id'] = $info['service_id'];
            }elseif(empty($status)){
                $next_app_info = $this->DAO->get_app_info($order['app_id']);
                $next_service_name = $this->DAO->get_service_name($order);
                if($info['app_id'] != $order['app_id'] || $info['service_id'] != $order['service_id']){
                    if($info['user_id'] == $order['user_id']){
                        $params['desc'] = '订单修改，将游戏"' . $next_app_info['app_name'] . "'修改成'" . $new_app_info['app_name'] . '",区服"'.$next_service_name['service_name']. "'修改成'".$new_service_name['service_name']."'";
                    }else{
                        $params['desc'] = "来自'" . $user_info['real_name'] . "'商会的订单修改，将游戏'" . $next_app_info['app_name'] .  "'修改成'" . $new_app_info['app_name'] . "',区服'".$next_service_name['service_name']. "'修改成'".$new_service_name['service_name']."'";
                    }
                    if($type){
                        $params['new_stock_balance'] = $info['stock_balance'];
                    }else{
                        $params['new_stock_balance'] = $info['stock_balance'] - $params['exit_depot'] - $params['loss_num'];
                    }
                    $params['new_stock_collect'] = $info['stock_collect'];
                    $params['stock_num'] = -$params['exit_depot'];
                }else{
                    if($info['user_id'] == $order['user_id']){
                        $params['desc'] = '订单修改';
                    }else{
                        $params['desc'] = "来自'" . $user_info['real_name'] . "'商会的订单修改";
                    }
                    if($type){
                        $params['new_stock_balance'] = $info['stock_balance'];
                    }else{
                        $params['new_stock_balance'] = $info['stock_balance'] + $order['exit_depot'] - $params['exit_depot'] + $order['loss_num'] - $params['loss_num'];
                    }
                    $params['new_stock_collect'] = $info['stock_collect'];
                    $params['stock_num'] = $order['exit_depot'] - $params['exit_depot'] + $order['loss_num'] - $params['loss_num'];
                }
            }
        }else{
            $user_info = $this->DAO->get_user_info($user_id);
            if($info['user_id'] == $user_id){
                $params['desc'] = '订单来源';
            }else{
                $params['desc'] = "来自'".$user_info['real_name']."'商会的订单来源";
            }
            if($type){
                $params['new_stock_balance'] = $info['stock_balance'];
            }else{
                $params['new_stock_balance'] = $info['stock_balance'] - $params['exit_depot'] - $params['loss_num'];
            }
            $params['new_stock_collect'] = $info['stock_collect'];
            $params['stock_num'] = -$params['exit_depot']-$params['loss_num'];
        }
        if($params['stock_num']){
            $this->DAO->update_stock_info($params,$info['user_id']);
            $this->DAO->insert_stock_record($params,$info,$info['user_id']);
        }
    }

    public function day_order($day_order,$params,$day_money,$status,$user_id){
        //$status：0个人 1本组 2本商会
        // 前一天个人日订单数据
        if($params['order_time']){
//            $params['order_date'] = date('Ymd',strtotime($params['order_time'])-86400);
            $per_day_order['order_time'] = $params['order_time'];
        }else{
//            $params['order_date'] = date('Ymd',time()-86400);
            $per_day_order['order_time'] = $params['order_time'] = date('Ymd',time());
        }
        if(!$day_money){
            $this->insert_money_collect($params,$user_id,$status,0);
        }else{
            $this->update_money_collect($params,$day_money,$user_id);
        }
        if(!$day_order){
            $this->insert_order_collect($params,$status,$user_id);
        }else{
            $this->update_order_collect($day_order,$params,'',1);
        }
    }

    public function month_order($month_order,$params,$month_money,$status){
        //前一个月的数据
        if($params['order_time']){
            $order_time = strtotime($params['order_time']);
            $params['order_date'] = date('Ym',mktime(0,0,0,date('m',$order_time)-1,1,date('Y',$order_time)));;
            $per_month_order['order_time'] = $params['order_time'] = substr($params['order_time'], 0, 6);;
        }else{
            $params['order_date'] = date('Ym',mktime(0,0,0,date('m',time())-1,1,date('Y',time())));
            $per_month_order['order_time'] = $params['order_time'] = date('Ym',time());
        }
        if(!$month_money){
            $this->insert_money_collect($params,$params['user_id'],$status,1);
        }else{
            $this->update_money_collect($params,$month_money,$params['user_id']);
        }
        if(!$month_order){
            $this->insert_order_collect($params,$status,$params['user_id'],1);
        }else{
            $this->update_order_collect($month_order,$params,'',1);
        }
    }

    public function get_service(){
        if(!$_POST['app_id']){
            die(json_encode(array('code'=>0,'msg'=>"游戏出错啦")));
        }else{
            $service_list = $this->DAO->get_service_list($_POST['app_id']);
            $app_info = $this->DAO->get_app_info($_POST['app_id']);
            die(json_encode(array('code'=>1,'list'=>$service_list,'app_info'=>$app_info)));
        }
    }

    public function configure(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id'] == 15 && ($user_info['p2'] || $user_info['p1'])){
            die("您没有该目录的权限，请联系管理员。");
        }
        $info = $this->DAO->get_configure();
        $this->assign("info",$info);
        $this->display("chamber/inside_configure.html");
    }

    public function do_configure(){
        $params = $_POST;
        if($params['vip5'] && $params['vip5']<0){
            $this->error_msg("整号vip5值不能小于0");
        }
        if($params['vip10'] && $params['vip10']<0){
            $this->error_msg("整号vip10值不能小于0");
        }
        if($params['vip12'] && $params['vip12']<0){
            $this->error_msg("整号vip12值不能小于0");
        }
        $info = $this->DAO->get_configure();
        $remarks = '';
        if($info){
            $this->DAO->update_configure($params,$info['id']);
            $desc = "后台使用者：".$_SESSION['usr_id']."商会配置信息修改成功";
            if($info['vip5'] != $params['vip5']){
                $remarks .= "整号V5:".$info['vip5']."更改成".$params['vip5'];
            }
            if($info['vip10'] != $params['vip10']){
                $remarks .= "整号V10:".$info['vip10']."更改成".$params['vip10'];
            }
            if($info['vip12'] != $params['vip12']){
                $remarks .= "整号V12:".$info['vip12']."更改成".$params['vip12'];
            }
        }else{
            $this->DAO->insert_configure($params);
            $desc = "后台使用者：".$_SESSION['usr_id']."商会配置信息添加成功";
            $remarks = "回款比例：".$params['payment_scale'].",整号V5：".$params['vip5'].",整号V10：".$params['vip10'].",整号V12：".$params['vip12'].",魔石比例：".$params['goods_scale'];
        }
        if($remarks){
            $this->DAO->insert_operation_log($_SESSION['usr_id'],$desc,$remarks,1);
        }
        $this->succeed_msg();
    }

    public function edit($id){
        $order = $this->DAO->get_order_info($id);
        $service_list = $this->DAO->get_service_list($order['app_id']);
        $app_list = $this->DAO->get_app_list();
        $this->assign("vip_list",$this->vip);
        $this->assign("service_list",$service_list);
        $this->assign("app_list",$app_list);
        $this->assign("order",$order);
        $this->display("chamber/inside_edit.html");
    }

    public function do_edit(){
        $params = $_POST;
        if(!$params['app_id'] || !$params['service_id'] || is_null($params['buy_name']) || is_null($params['exit_depot']) || is_null($params['in_money']) || is_null($params['loss_num']) || is_null($params['pay_mode']) || is_null($params['token_scale'])){
            $this->error_msg('缺少必填项');
        }
        if($params['in_money'] < 0 || $params['exit_depot'] <0 || $params['loss_num']<0){
            $this->error_msg("收入金额,出仓代币,损耗代币均不能为负数");
        }
        $app_info = $this->DAO->get_app_info($params['app_id']);
        if($params['token_scale'] > $app_info['goods_scale']){
            $params['order_type'] = 2;
        }else{
            $params['order_type'] = 1;
        }
        $order = $this->DAO->get_order_info($params['id']);
        $user_info = $this->DAO->get_user_info($order['user_id']);
        if($user_info['p2']){
            $params['group_id'] = $user_info['p1'];
            $params['business_id'] = $user_info['p1'];
        }elseif($user_info['p1']){
            $params['group_id'] = $user_info['id'];
            $params['business_id'] = $user_info['p1'];
        }else{
            $params['group_id'] = $user_info['id'];
            $params['business_id'] = $user_info['id'];
        }
        //该区服该商会的代币余额
        $stock_info = $this->DAO->get_stock_info($params,$params['group_id']);
        if($params['app_id'] != $order['app_id'] || $params['service_id'] != $order['service_id']){
            if(($stock_info['stock_balance'] - $params['exit_depot'] - $params['loss_num']) < 0){
                $this->error_msg('当前区服代币不足，请向上级申请拨币');
            }
            $next_stock_info = $this->DAO->get_stock_info($order,$order['user_id']);
//            if($user_info['p2']){
//                $new_lead_stock_info = $this->DAO->get_stock_info($params,$user_info['p2']);
//                $next_lead_stock_info = $this->DAO->get_stock_info($order,$user_info['p2']);
//            }
        }else{
            if(($stock_info['stock_balance']+$order['exit_depot']+$order['loss_num']-$params['exit_depot']-$params['loss_num'])< 0){
                $this->error_msg('当前区服代币不足，请向上级申请拨币');
            }
//            if($user_info['p2']){
//                $lead_stock_info = $this->DAO->get_stock_info($params,$user_info['p2']);
//            }
        }
        $params['order_date'] = $order['order_date'] = date('Ymd',$order['add_time']);
//        $app_info = $this->DAO->get_app_info($params['app_id']);
        $params['channel'] = $order['channel'] = $app_info['channel'];
        //原订单、汇总信息
        $personal_day_order_info = $this->DAO->get_personal_day_order($order,0,$order['user_id']);
        $group_day_order_info = $this->DAO->get_group_day_order($order,1,$params['group_id']);
        $business_order_info = $this->DAO->get_business_order($order,2,$params['business_id']);

        $personal_day_money_info = $this->DAO->get_personal_day_money($order,0,$order['user_id']);
        $group_day_money_info = $this->DAO->get_group_day_money($order,1,$params['group_id']);
        $business_money_info = $this->DAO->get_business_money($order,2,$params['business_id']);
        $personal_day_order = $this->DAO->get_personal_day_order($params,0,$order['user_id']);
        $personal_day_money = $this->DAO->get_personal_day_money($params,0,$order['user_id']);
        $group_day_order = $this->DAO->get_group_day_order($params,1,$params['group_id']);
        $group_day_money = $this->DAO->get_group_day_money($params,1,$params['group_id']);
        $business_money = $this->DAO->get_business_money($params,2,$params['business_id']);
        $business_order = $this->DAO->get_business_order($params,2,$params['business_id']);
        $desc = "内部商会订单ID为".$order['id']."修改";
        $remarks = $this->order_verify($order,$params);
        if($remarks){
            $this->DAO->insert_operation_log($_SESSION['usr_id'],$desc,$remarks,5);
            //个人日订单、日汇总
            $this->edit_order($params,$personal_day_order,$order,$user_info,$personal_day_money,0,$personal_day_order_info,$personal_day_money_info);
            //本组日订单、日汇总
            $this->edit_order($params,$group_day_order,$order,$user_info,$group_day_money,1,$group_day_order_info,$group_day_money_info);
            //本商会日订单、日汇总
            $this->edit_order($params,$business_order,$order,$user_info,$business_money,2,$business_order_info,$business_money_info);
            if($params['app_id']!=$order['app_id'] || $params['service_id'] != $order['service_id']){
                //更新自己的库存代币
                $this->update_stock($params,$stock_info,'',$order);
                //更新修改前游戏区服自己的库存代币
                $this->update_stock($params,$next_stock_info,'',$order,1);
//                if($new_lead_stock_info){
//                    //更新商会长的库存代币
//                    $this->update_stock($params,$new_lead_stock_info,1,$order);
//                    //更新修改前游戏区服商会长的库存代币
//                    $this->update_stock($params,$next_lead_stock_info,1,$order,1);
//                    //更新自己修改前的游戏区服库存代币
//                    $this->update_stock($params,$next_stock_info,'',$order);
//                }
            }else{
                //更新自己的库存代币
                $this->update_stock($params,$stock_info,'',$order);
//                if($lead_stock_info){
//                    //更新商会长的库存代币
//                    $this->update_stock($params,$lead_stock_info,1,$order);
//                }
            }
            //插入库存记录
            if($user_info['p1'] && $user_info['p2']){
                $this->stock_record($params,$stock_info,1,$order);
            }
            //修改现金明细的金额
            $this->update_money_detail($params,$order);
            $this->DAO->update_group_order_info($params);
        }
        $this->succeed_msg();
    }

    public function update_money_detail($params,$order){
        if($params['pay_mode'] == '1'){
            $params['wx_money'] = $params['in_money'];
            $params['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $params['ali_money'] = $params['in_money'];
            $params['wx_money'] = 0;
        }
        $this->DAO->update_edit_money_detail($params,$order['order_id']);
    }

    public function order_verify($order,$params){
        $remarks = '';
        if($order['app_id'] != $params['app_id']){
            $remarks .= '游戏ID由'.$order['app_id']."更改为".$params['app_id'].',';
        }
        if($order['service_id'] != $params['service_id']){
            $remarks .= '区服ID由'.$order['service_id']."更改为".$params['service_id'].',';
        }
        if($order['pay_mode'] != $params['pay_mode']){
            if($order['pay_mode'] == '1'){
                $order['mode'] = '微信';
            }elseif($order['pay_mode'] == '2'){
                $order['mode'] = '支付宝';
            }else{
                $order['mode'] = '无';
            }
            if($params['pay_mode'] == '1'){
                $params['mode'] = '微信';
            }elseif($params['pay_mode'] == '2'){
                $params['mode'] = '支付宝';
            }else{
                $params['mode'] = '无';
            }
            $remarks .= '支付方式由'.$order['mode']."更改为".$params['mode'].',';
        }
        if($order['in_money'] != $params['in_money']){
            $remarks .= '收入金额由'.$order['in_money']."更改为".$params['in_money'].',';
        }
        if($order['exit_depot'] != $params['exit_depot']){
            $remarks .= '出仓魔石由'.$order['exit_depot']."更改为".$params['exit_depot'].',';
        }
        if($order['buy_name'] != $params['buy_name']){
            $remarks .= '购买人由'.$order['buy_name']."更改为".$params['buy_name'].',';
        }
        if($order['desc'] != $params['desc']){
            $remarks .= '备注说明由'.$order['desc']."更改为".$params['desc'].',';
        }
        if($order['loss_num'] != $params['loss_num']){
            $remarks .= '损耗魔石由'.$order['loss_num']."更改为".$params['loss_num'].',';
        }
        return $remarks;
    }

    public function edit_order($params,$order,$info,$user_info,$money,$status,$order_info,$money_info){
        $params['order_date'] = date('Ymd',time()-86400);
        $per_day_order['order_time'] = $params['order_time'] = date('Ymd',time());
        $per_day_order['group_id'] = $params['group_id'] = $order_info['group_id'];
        if($params['service_id'] != $info['service_id'] || $params['app_id'] != $info['app_id']){
            //游戏区服不同时
            $per_day_order['order_time'] = $params['order_time'];
            if($params['pay_mode'] == '1'){
                $new_per_day_money['wx_money'] = $money_info['wx_money'] - $params['in_money']+($params['in_money']-$info['in_money']);
                $new_per_day_money['ali_money'] = $money_info['ali_money'];
            }elseif($params['pay_mode'] == '2'){
                $new_per_day_money['ali_money'] = $money_info['ali_money'] - $params['in_money']+($params['in_money']-$info['in_money']);
                $new_per_day_money['wx_money'] = $money_info['wx_money'];
            }
            $new_per_day_money['total_money'] = $new_per_day_money['wx_money'] + $new_per_day_money['ali_money'];
            $new_per_day_money['service_charge'] = $new_per_day_money['total_money']*0.001;
            $new_per_day_money['actual_arrive'] = $new_per_day_money['total_money']-$new_per_day_money['service_charge'];
            if($user_info['group_id'] == 15 && !$user_info['p1'] && !$user_info['p2']){
                $new_per_day_money['enter_money'] = $money_info['enter_money'] - $params['in_money'] + ($params['in_money']-$info['in_money'])+$info['in_money']*0.001;
                if(!$new_per_day_money['enter_money'] && $money_info['enter_num'] >0){
                    $new_per_day_money['enter_num'] = $money_info['enter_num'] - 1;
                }else{
                    $new_per_day_money['enter_num'] = 1;
                }
            }else{
                $new_per_day_money['enter_num'] = $money_info['enter_num'];
                $new_per_day_money['enter_money'] = $money_info['enter_money'];
            }
            $this->DAO->update_money_collect($new_per_day_money,$money_info['id']);
            if(!$money){
                $this->insert_money_collect($params,$info['user_id'],$status,0);
            }else{
                $this->update_money_collect($params,$money,$info['user_id'],$info,1);
            }

            if($info['token_type'] == '1'){
                $new_per_day_order['actual_payment'] = $order_info['actual_payment'] - $info['in_money'];
            }else{
                $new_per_day_order['actual_payment'] =  $order_info['actual_payment'] - $info['in_money'] + $info['in_money']*0.001;
            }
            $new_per_day_order['payment'] = $order_info['payment'] - $info['in_money'];
            $new_per_day_order['sell_num'] = $order_info['sell_num'] - $info['exit_depot'];
            $new_per_day_order['loss_num'] = $order_info['loss_num'] - $info['loss_num'];
            $this->DAO->update_orders_collect($new_per_day_order,$order_info['id']);
            if($order){
                $this->update_order_collect($order,$params,$info,1);
            }else{
                $this->insert_order_collect($params,$status,$info['user_id']);
            }
        }else{
            if(!$money){
                $this->insert_money_collect($params,$info['user_id'],$status,0);
            }else{
                $this->update_money_collect($params,$money,$info['user_id'],$info,1);
            }
            if($order){
                $this->update_order_collect($order,$params,$info);
            }else{
                $this->insert_order_collect($params,$status,$info['user_id']);
            }
        }
    }

    public function insert_money_collect($params,$user_id,$status,$collect_type){
        $per_day_order['order_time'] = $params['order_time'];
        if($params['pay_mode'] == '1'){
            $per_day_order['wx_money'] = $params['in_money'];
            $per_day_order['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $per_day_order['ali_money'] = $params['in_money'];
            $per_day_order['wx_money'] = 0;
        }else{
            $per_day_order['ali_money'] = 0;
            $per_day_order['wx_money'] = 0;
        }
        $per_day_order['collect_type'] = $collect_type;
        $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
        $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
        $per_day_order['actual_arrive'] = $per_day_order['total_money']-$per_day_order['service_charge'];
        if($params['business_id'] == $user_id){
            $per_day_order['status'] = 3;
            $per_day_order['enter_num'] = 1;
            $per_day_order['enter_money'] = $per_day_order['actual_arrive'];
        }else{
            $per_day_order['status'] = 0;
            $per_day_order['enter_num'] = 0;
            $per_day_order['enter_money'] = 0;
        }
        $this->DAO->insert_money_collect($per_day_order,$params,$user_id,$status);
    }

    public function update_money_collect($params,$day_money,$user_id,$info='',$status=''){
        if($status){
            if($params['pay_mode'] != $info['pay_mode']){
                if($params['pay_mode'] == '1'){
                    $per_day_order['wx_money'] = $day_money['wx_money'] + $params['in_money'] - $info['in_money'];
                    $per_day_order['ali_money'] = $day_money['ali_money'] - $info['in_money'];
                }elseif($params['pay_mode'] == '2'){
                    $per_day_order['ali_money'] = $day_money['ali_money'] + $params['in_money'] - $info['in_money'];
                    $per_day_order['wx_money'] = $day_money['wx_money'] - $info['in_money'];
                }else{
                    $per_day_order['wx_money'] = $day_money['wx_money'] + $info['in_money'];
                    $per_day_order['ali_money'] = $day_money['ali_money'] + $info['in_money'];
                }
            }else{
                if($params['pay_mode'] == '1'){
                    $per_day_order['wx_money'] =  $day_money['wx_money'] + $params['in_money'] - $info['in_money'];
                    $per_day_order['ali_money'] = $day_money['ali_money'];
                }elseif($params['pay_mode'] == '2'){
                    $per_day_order['ali_money'] = $day_money['ali_money'] + $params['in_money'] - $info['in_money'];
                    $per_day_order['wx_money'] = $day_money['wx_money'];
                }else{
                    $per_day_order['wx_money'] = $day_money['wx_money'] + $info['in_money'];
                    $per_day_order['ali_money'] = $day_money['ali_money'] + $info['in_money'];
                }
            }
            $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
            $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
            $per_day_order['actual_arrive'] = $per_day_order['total_money']-$per_day_order['service_charge'];
            if($day_money['group_id'] == $user_id){
                $per_day_order['enter_money'] = $day_money['enter_money'] - ($info['in_money']-$info['in_money']*0.001) + $params['in_money'] - $params['in_money']*0.001 + $info['in_money'] - $info['in_money']*0.001;
                if(!$per_day_order['enter_money'] && $day_money['enter_num'] !=0){
                    $per_day_order['enter_num'] = $day_money['enter_num'] - 1;
                }else{
                    $per_day_order['enter_num'] = 1;
                }
            }else{
                $per_day_order['enter_money'] = $day_money['enter_money'];
                $per_day_order['enter_num'] = $day_money['enter_num'];
            }
        }else{
            if($params['pay_mode'] == '1'){
                $per_day_order['wx_money'] = $params['in_money'] + $day_money['wx_money'];
                $per_day_order['ali_money'] = $day_money['ali_money'];
            }elseif($params['pay_mode'] == '2'){
                $per_day_order['ali_money'] = $params['in_money'] + $day_money['ali_money'];
                $per_day_order['wx_money'] = $day_money['wx_money'];
            }else{
                $per_day_order['wx_money'] = $day_money['wx_money'];
                $per_day_order['ali_money'] = $day_money['ali_money'];
            }
            $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
            $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
            $per_day_order['actual_arrive'] = $per_day_order['total_money']-$per_day_order['service_charge'];
            if($params['business_id'] == $user_id){
                $per_day_order['enter_money'] = $day_money['enter_money'] + $params['in_money'] - $params['in_money']*0.001;
                if($day_money['enter_num'] == 0 || $per_day_order['enter_money']){
                    $per_day_order['enter_num'] = 1;
                }else{
                    $per_day_order['enter_num'] = $day_money['enter_num'] - 1;
                }
//                $per_day_order['enter_num'] = $day_money['enter_num'] + 1;
            }else{
                $per_day_order['enter_money'] = $day_money['enter_money'];
                $per_day_order['enter_num'] = $day_money['enter_num'];
            }
        }
        $this->DAO->update_money_collect($per_day_order,$day_money['id']);
    }

    public function insert_order_collect($params,$status,$user_id,$type=''){
        $per_day_order['order_time'] = $params['order_time'];
        $per_day_order['payment'] = $params['in_money'];
        $per_day_order['actual_payment'] =  $params['in_money'] - $params['in_money']*0.001;
        $per_day_order['loss_num'] = $params['loss_num'];
        $per_day_order['sell_num'] = $params['exit_depot'];
        if($type){
            $per_day_order['type'] = $type;
        }else{
            $per_day_order['type'] = '0';
        }
        $this->DAO->insert_orders_collect($per_day_order,$params,$user_id,$status);
    }

    public function update_order_collect($order,$params,$info='',$status=''){
        //$status 1 非编辑时
        if($status){
            $per_day_order['payment'] = $order['payment'] + $params['in_money'];
            $per_day_order['actual_payment'] = $order['actual_payment'] + $params['in_money'] - $params['in_money']*0.001;
            $per_day_order['sell_num'] = $order['sell_num'] + $params['exit_depot'];
            $per_day_order['loss_num'] = $order['loss_num'] + $params['loss_num'];
        }else{
            $per_day_order['actual_payment'] =  $order['actual_payment'] + $params['in_money'] - $params['in_money']*0.001 - ($info['in_money']-$info['in_money']*0.001);
            if($params['in_money'] != $info['in_money']){
                $per_day_order['payment'] = $order['payment']+$params['in_money']-$info['in_money'];
            }else{
                $per_day_order['payment'] = $order['payment'];
            }
            if($params['exit_depot'] != $info['exit_depot']){
                $per_day_order['sell_num'] = $order['sell_num'] + $params['exit_depot']-$info['exit_depot'];
            }else{
                $per_day_order['sell_num'] = $order['sell_num'];
            }
            if($params['loss_num'] != $info['loss_num']){
                $per_day_order['loss_num'] = $order['loss_num'] + $params['loss_num'] - $info['loss_num'];
            }else{
                $per_day_order['loss_num'] = $order['loss_num'];
            }
        }
        $this->DAO->update_orders_collect($per_day_order,$order['id']);
    }

    public function order_export(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $datalist = $this->DAO->get_all_order_list($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $datalist = $this->DAO->get_all_order_list($params,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $datalist = $this->DAO->get_all_order_list($params,$_SESSION['usr_id']);
        }
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
        $objActSheet->setTitle("内部商会订单列表");
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue("A1", "执行员");
        $objActSheet->setCellValue("B1", "订单号");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "出仓代币");
        $objActSheet->setCellValue("G1", "代币比例");
        $objActSheet->setCellValue("H1", "购买人");
        $objActSheet->setCellValue("I1", "损耗代币");
        $objActSheet->setCellValue("J1", "收入金额");
        $objActSheet->setCellValue("K1", "收款方式");
        $objActSheet->setCellValue("L1", "下单时间");
        $n = 2;
        foreach($data as $info){
            if($info['p1'] && !$info['p2']){
                $objActSheet->setCellValue("A".$n, $info['account']);
            }else{
                $objActSheet->setCellValue("A".$n, $info['real_name']);
            }
            $objActSheet->setCellValueExplicit("B".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info["exit_depot"]);
            $objActSheet->setCellValue("G".$n, $info["token_scale"]);
            $objActSheet->setCellValue("H".$n, $info["buy_name"]);
            $objActSheet->setCellValue("I".$n, $info["loss_num"]);
            $objActSheet->setCellValue("J".$n, $info["in_money"]);
            if($info['pay_mode'] == 1){
                $objActSheet->setCellValue("K".$n, "微信");
            }elseif($info['pay_mode'] == 2){
                $objActSheet->setCellValue("K".$n, "支付宝");
            }else{
                $objActSheet->setCellValue("K".$n, "无");
            }
            $objActSheet->setCellValue("L".$n, date("Y-m-d H:i:s",$info['add_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","内部商会订单列表-".$str_now.'.xls');
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

    public function order_collect(){
        $params = $this->get_params($_POST,$_GET);
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($params['start_time']){
            if($params['type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        if($_SESSION['group_id'] == '1'){
            $order_list = $this->DAO->get_order_collect_list($params,$this->page);
            $group_list = $this->DAO->get_group_all_list();
            $personal_list = $this->DAO->get_personal_all_list();
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p2'] || !$user_info['p1'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $personal_list = $this->DAO->get_personal_list($_SESSION['usr_id']);
            $group_list = $this->DAO->get_group_list($_SESSION['usr_id']);
            $order_list = $this->DAO->get_order_collect_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $order_list = $this->DAO->get_order_collect_list($params,$this->page,$_SESSION['usr_id']);
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
            if($data['type'] == '0'){
                $next = strtotime(date('Ymd',strtotime($data['order_time'])+86400).'12:00:00');
            }elseif($data['type'] == '1'){
                $next = strtotime(date("Ym",strtotime("+1 month",strtotime($data['order_time']))).'03');
            }
            if(time()>$next){
                $this->DAO->update_order_collect_status($data['id'],1);
                $order_list[$key]['order_status'] = 1;
            }
            $group_info = $this->DAO->get_user_info($data['group_id']);
            $order_list[$key]['group_name'] = $group_info['real_name'];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside.php?act=order_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("params",$params);
        $this->assign("group_list",$group_list);
        $this->assign("personal_list",$personal_list);
        $this->assign("service_list",$service_list);
        $this->assign("order_list",$order_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display("chamber/inside_order_collect.html");
    }

    public function order_edit($id){
        $info = $this->DAO->get_order_collect_info($id);
        $info['channel_name'] = $this->channel[$info['channel']];
        if($info['type'] == '0'){
            $info['time'] = date("Y年m月d日",strtotime($info['order_time']));
        }elseif($info['type'] == '1'){
            $info['time'] = date("Y年m月",strtotime($info['order_time']));
        }
        $this->assign('info',$info);
        $this->display("chamber/inside_order_edit.html");
    }

    public function do_order_edit(){
        $params = $_POST;
        $info = $this->DAO->get_order_collect_info($params['id']);
        if($info['order_status'] == 0){
            if($info['type'] == '0'){
                $next = strtotime(date('Ymd',strtotime($info['order_time'])+86400).'12:00:00');
            }elseif($info['type'] == '1'){
                $next = strtotime(date("Ym",strtotime("+1 month",strtotime($info['order_time']))).'03');
            }
            if(time()>$next){
                $this->DAO->update_order_collect_status($info['id'],1);
                $this->error_msg("已过可修改时间");
            }
        }else{
            $this->error_msg('已过可修改时间');
        }
        $params['actual_payment'] = $info['actual_payment'] + $info['other_costs'] - $params['other_costs'];
        $desc = '个人订单汇总ID为'.$info['id'].'修改';
        $remarks = '';
        if($info['desc'] != $params['desc']){
            $remarks .= "备注说明由".$info['desc'].'更改成'.$params['desc'].",";
        }
        if($info['other_costs'] != $params['other_costs']){
            $remarks .= '其他成本由'.$info['other_costs'].'更改成'.$params['other_costs'].',';
            $remarks .= '实际回款由'.$info['actual_payment'].'更改成'.$params['actual_payment'];
        }
        if($remarks){
            $this->DAO->insert_operation_log($_SESSION['usr_id'],$desc,$remarks,2);
        }
        $info['order_date'] = $info['order_time'];
        $group_order = $this->DAO->get_group_day_order($info,1,$info['group_id']);
        if($group_order){
            $order['other_costs'] = $group_order['other_costs'] - $info['other_costs'] + $params['other_costs'];
            $order['actual_payment'] = $group_order['actual_payment'] + $info['other_costs'] - $params['other_costs'];
            $this->DAO->update_business_group_order($order,$group_order['id']);
        }
        $this->DAO->update_business_order_collect($params);
        $this->succeed_msg();
    }

    public function order_collect_export(){
        $params = $_GET;
        $params['start'] = date('Ymd',strtotime($params['start_time']));
        $params['end'] = date('Ymd',strtotime($params['end_time']));
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $datalist = $this->DAO->get_all_order_collect($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $datalist = $this->DAO->get_all_order_collect($params,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $datalist = $this->DAO->get_all_order_collect($params,$_SESSION['usr_id']);
        }
        if($datalist){
            $this->order_master_excel_out($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function order_master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("内部商会订单汇总");
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('L')->setWidth(15);
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "执行员");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "收入金额");
        $objActSheet->setCellValue("G1", "实际回款");
        $objActSheet->setCellValue("H1", "损耗代币数");
        $objActSheet->setCellValue("I1", "出售代币数");
        $n = 2;
        foreach($data as $info){
            if($info['type'] == '0'){
                $order_time = date('Y年m月d日',strtotime($info['order_time']));
            }elseif($info['type'] == '1'){
                $order_time = date('Y年m月',strtotime($info['order_time']));
            }
            $objActSheet->setCellValue("A".$n, $order_time);
            if($info['p1'] && !$info['p2']){
                $objActSheet->setCellValue("B".$n, $info['account']);
            }else{
                $objActSheet->setCellValue("B".$n, $info['real_name']);
            }
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info['payment']);
            $objActSheet->setCellValue("G".$n, $info['actual_payment']);
            $objActSheet->setCellValue("H".$n, $info['loss_num']);
            $objActSheet->setCellValue("I".$n, $info["sell_num"]);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","内部商会订单汇总-".$str_now.'.xls');
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

    public function money_collect(){
        $params = $this->get_params($_POST,$_GET);
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_timse']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($_SESSION['group_id'] == '1'){
            $params['order_status'] = 1;
            $group_list = $this->DAO->get_group_all_list();
            $personal_list = $this->DAO->get_personal_all_list();
            $order_list = $this->DAO->get_money_collect_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p2'] || !$user_info['p1'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $params['order_status'] = 1;
            $group_list = $this->DAO->get_group_list($_SESSION['usr_id']);
            $personal_list = $this->DAO->get_personal_list($_SESSION['usr_id']);
            $order_list = $this->DAO->get_money_collect_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $order_list = $this->DAO->get_money_collect_list($params,$this->page,$_SESSION['usr_id']);
        }
        $wx_total = '';
        $ali_total = '';
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
            if($params['collect_type'] == 1){
                $wx_total += $data['wx_money'];
                $ali_total += $data['ali_money'];
            }elseif($data['status'] == 0){
                $next = strtotime(date('Ymd',strtotime($data['collect_time'])+86400).'12:00:00');
                if(time()>$next){
                    $order_list[$key]['status'] = 1;
                    $this->DAO->update_business_money($data['id'],1);
                }
            }
            $group_name = $this->DAO->get_user_info($data['group_id']);
            $order_list[$key]['group_name'] = $group_name['real_name'];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $total = $wx_total + $ali_total;
        $page = $this->pageshow($this->page, "business_inside.php?act=money_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("user_info",$user_info);
        $this->assign("wx_total",$wx_total);
        $this->assign("ali_total",$ali_total);
        $this->assign("total",$total);
        $this->assign("params",$params);
        $this->assign("service_list",$service_list);
        $this->assign("personal_list",$personal_list);
        $this->assign("group_list",$group_list);
        $this->assign("order_list",$order_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display("chamber/inside_money_collect.html");
    }

    public function money_collect_edit($id){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $info = $this->DAO->get_money_collect_info($id);
        $this->assign("info",$info);
        $this->assign("user_info",$user_info);
        $this->display("chamber/inside_money_edit.html");
    }

    public function do_money_edit(){
        $params = $_POST;
        if($params['wx_money'] != 0){
            if($_FILES['wx_img']['tmp_name']){
                $params['wx_img'] = $this->up_img('wx_img','images/banner_img');
            }elseif($params['old_wx_img']){
                $params['wx_img'] = $params['old_wx_img'];
            }else{
                $this->error_msg("请上传微信回款凭证图");
            }
        }
        if($params['ali_money']){
            if($_FILES['ali_img']['tmp_name']){
                $params['ali_img'] = $this->up_img('ali_img','images/banner_img');
            }elseif($params['old_ali_img']){
                $params['ali_img'] = $params['old_ali_img'];
            }else{
                $this->error_msg("请上传支付宝回款凭证图");
            }
        }
        $this->DAO->update_money_collect_info($params);
        $this->succeed_msg();
    }

    public function group_order_collect(){
        $params = $_POST;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($_SESSION['group_id'] == '1' ){
            $order_list = $this->DAO->get_group_order_collect_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $group_list = $this->DAO->get_group_list($_SESSION['usr_id']);
            $order_list = $this->DAO->get_group_order_collect_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            die("您没有该目录的权限，请联系管理员");
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside.php?act=group_order_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("group_list",$group_list);
        $this->assign("service_list",$service_list);
        $this->assign("user_info",$user_info);
        $this->assign("params",$params);
        $this->assign("order_list",$order_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display("chamber/inside_group_order_collect.html");

    }

    public function group_money_collect(){
        $params = $this->get_params($_POST,$_GET);
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($_SESSION['group_id'] == '1'){
            $order_list = $this->DAO->get_group_money_collect_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $order_list = $this->DAO->get_group_money_collect_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            die("您没有该目录的权限，请联系管理员");
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside.php?act=group_money_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("user_info",$user_info);
        $this->assign("params",$params);
        $this->assign("order_list",$order_list);
        $this->assign("service_list",$service_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display("chamber/inside_group_money_collect.html");
    }

    public function verify_view($id){
        $info = $this->DAO->get_money_collect_info($id);
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $this->assign("user_info",$user_info);
        $this->assign("info",$info);
        $this->display("chamber/inside_money_verify.html");
    }

    public function do_verify(){
        $params = $this->get_params($_POST,$_GET);
        if($params['status'] == 4 && !$params['desc']){
            $this->error_msg("审核失败必须填写备注说明");
        }
        $order = $this->DAO->get_money_collect_info($params['id']);
        if($params['status'] == 3){
            //本组、商会日现金汇总
            $order['order_date'] = $order['collect_time'];
            $group_day_money = $this->DAO->get_group_day_money($order,1,$order['group_id']);
            $business_day_money = $this->DAO->get_business_money($order,2,$order['business_id']);
            //本组、商会月现金汇总
            $order['order_date'] = date('Ym',strtotime($order['collect_time']));
            $group_month_money = $this->DAO->get_group_day_money($order,1,$order['group_id']);
            $business_month_money = $this->DAO->get_business_money($order,2,$order['business_id']);
            //本组、商会日现金汇总
            $this->update_money_info($group_day_money,$order);
            $this->update_money_info($business_day_money,$order);
            //本组、商会月现金汇总
            $this->update_money_info($group_month_money,$order);
            $this->update_money_info($business_month_money,$order);
        }else{
            $desc = "现金汇总审核失败";
            if($order['desc'] != $params['desc']){
                $remarks = "备注说明由'".$order['desc']."'更改为'".$params['desc'];
            }else{
                $remarks = "备注说明'".$params['desc']."'";
            }
            $this->DAO->insert_operation_log($_SESSION['usr_id'],$desc,$remarks,4);
        }
        $this->DAO->update_money_status($params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function update_money_info($money,$order){
        $params['enter_money'] = $money['enter_money'] + ($order['wx_money']+$order['ali_money']-($order['wx_money']+$order['ali_money'])*0.001);
        $params['enter_num'] = $money['enter_num'] + 1;
        $this->DAO->update_money_info($params,$money['id']);
    }

    public function group_order_export(){
        $params = $_GET;
        if($params['start_time']){
            if($params['type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $dataList = $this->DAO->get_group_order_collect_all($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $dataList = $this->DAO->get_group_order_collect_all($params,$_SESSION['usr_id']);
        }
        if($dataList){
            $this->group_master_excel_out($dataList);
        }else{
            echo "没有数据需要导出";
        }
    }

    public function group_master_excel_out($data,$status=''){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        if($status){
            $title = '整个商会订单汇总';
            $b_name = '商会名';
        }else{
            $title = '组商会订单汇总';
            $b_name = '执行员';
        }
        $objActSheet->setTitle($title);
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('L')->setWidth(15);
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", $b_name);
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "收入金额");
        $objActSheet->setCellValue("G1", "实际回款");
        $objActSheet->setCellValue("H1", "损耗代币数");
        $objActSheet->setCellValue("I1", "出售代币数");
        $n = 2;
        foreach($data as $info){
            if($info['type'] == '0'){
                $order_time = date('Y年m月d日',strtotime($info['order_time']));
            }elseif($info['type'] == '1'){
                $order_time = date('Y年m月',strtotime($info['order_time']));
            }
            $objActSheet->setCellValue("A".$n, $order_time);
            $objActSheet->setCellValue("B".$n, $info['real_name']);
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info['payment']);
            $objActSheet->setCellValue("G".$n, $info['actual_payment']);
            $objActSheet->setCellValue("H".$n, $info['loss_num']);
            $objActSheet->setCellValue("I".$n, $info["sell_num"]);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE",$title."-".$str_now.'.xls');
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

    public function money_export(){
        $params = $_GET;
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $params['order_status'] = 1;
            $dataList = $this->DAO->get_money_collect_all($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $params['order_status'] = 1;
            $dataList = $this->DAO->get_money_collect_all($params,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $dataList = $this->DAO->get_money_collect_all($params,$_SESSION['usr_id']);
        }
        if($dataList){
            $this->money_master_excel_out($dataList,$params);
        }else{
            echo "没有数据需要导出";
        }
    }

    public function money_master_excel_out($data,$params){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("个人现金汇总");
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('L')->setWidth(15);
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "执行员");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "微信收款");
        $objActSheet->setCellValue("G1", "支付宝收款");
        $objActSheet->setCellValue("H1", "总计");
        if(!$params['collect_type'] || $params['collect_type']== 0){
            $objActSheet->setCellValue("F1", "状态");
        }
        $n = 2;
        foreach($data as $info){
            if($info['collect_type'] == '0'){
                $order_time = date('Y年m月d日',strtotime($info['collect_time']));
            }elseif($info['collect_type'] == '1'){
                $order_time = date('Y年m月',strtotime($info['collect_time']));
            }
            $objActSheet->setCellValue("A".$n, $order_time);
            if($info['p1'] && !$info['p2']){
                $objActSheet->setCellValue("B".$n, $info['account']);
            }else{
                $objActSheet->setCellValue("B".$n, $info['real_name']);
            }
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info['wx_money']);
            $objActSheet->setCellValue("G".$n, $info['ali_money']);
            $objActSheet->setCellValue("H".$n, $info['total_money']);
            if(!$params['collect_type'] || $params['collect_type']== 0){
                if($info['status'] == '0'){
                    $objActSheet->setCellValue("F".$n, '次日提交');
                }elseif($info['status'] == '1'){
                    $objActSheet->setCellValue("F".$n, '未提交审核');
                }elseif($info['status'] == '2'){
                    $objActSheet->setCellValue("F".$n, '待审核');
                }elseif($info['status'] == '3'){
                    $objActSheet->setCellValue("F".$n, '审核成功');
                }elseif($info['status'] == '4'){
                    $objActSheet->setCellValue("F".$n, '审核失败');
                }
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","个人现金汇总-".$str_now.'.xls');
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

    public function group_money_export(){
        $params = $_GET;
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $dataList = $this->DAO->get_group_money_collect_all($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $dataList = $this->DAO->get_group_money_collect_all($params,$_SESSION['usr_id']);
        }
        if($dataList){
            $this->group_money_excel_out($dataList);
        }else{
            echo "没有数据需要导出";
        }
    }

    public function group_money_excel_out($data,$status=''){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        if($status){
            $title = '商会现金汇总';
            $b_name = '商会名';
        }else{
            $title = '组现金汇总';
            $b_name = '组名';
        }
        $objActSheet->setTitle($title);
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('L')->setWidth(15);
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", $b_name);
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名");
        $objActSheet->setCellValue("E1", "区服名");
        $objActSheet->setCellValue("F1", "微信收款");
        $objActSheet->setCellValue("G1", "支付宝收款");
        $objActSheet->setCellValue("H1", "总计");
        $objActSheet->setCellValue("I1", "手续费");
        $objActSheet->setCellValue("J1", "实际到帐银行");
        $objActSheet->setCellValue("K1", "已入账金额");
        $n = 2;
        foreach($data as $info){
            if($info['collect_type'] == '0'){
                $order_time = date('Y年m月d日',strtotime($info['collect_time']));
            }elseif($info['collect_type'] == '1'){
                $order_time = date('Y年m月',strtotime($info['collect_time']));
            }
            $objActSheet->setCellValue("A".$n, $order_time);
            $objActSheet->setCellValue("B".$n, $info['real_name']);
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info['wx_money']);
            $objActSheet->setCellValue("G".$n, $info['ali_money']);
            $objActSheet->setCellValue("H".$n, $info['total_money']);
            $objActSheet->setCellValue("I".$n, $info['service_charge']);
            $objActSheet->setCellValue("J".$n, $info['actual_arrive']);
            $objActSheet->setCellValue("K".$n, $info['enter_money']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE",$title."-".$str_now.'.xls');
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

    public function orders_import_view(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '15' && (!$user_info['p2'] || !$user_info['p1'])){
            $group_list = $this->DAO->get_group_list($_SESSION['usr_id']);
            foreach($group_list as $key=>$data){
                if($data['id'] == $_SESSION['usr_id']){
                    unset($group_list[$key]);
                }
            }
        }
        $this->assign('group_list',$group_list);
        $this->display('chamber/inside_import_view.html');
    }

    public function order_import_do(){
        if(isset($_FILES['order_file'])){
            $service_file = $_FILES['order_file'];
            if(preg_match("/\.xls$/",$service_file['name'])){
                $type = 1;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$service_file['name'])){
                $type = 2;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if($service_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过5M！");
            }
            if(move_uploaded_file($service_file['tmp_name'],$temp)){
                if(file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name"=>"日期","title_field"=>"order_time","title_type"=>""),
                        array("title_name"=>"游戏名称","title_field"=>"app_id","title_type"=>"string"),
                        array("title_name"=>"区服名称","title_field"=>"service_name","title_type"=>"string"),
                        array("title_name"=>"出仓代币","title_field"=>"exit_depot"),
                        array("title_name"=>"代币比例","title_field"=>"token_scale"),
                        array("title_name"=>"购买人","title_field"=>"buy_name","title_type"=>"string"),
                        array("title_name"=>"损耗代币","title_field"=>"loss_num"),
                        array("title_name"=>"收入金额","title_field"=>"in_money","title_type"=>"float"),
                        array("title_name"=>"收款方式","title_field"=>"pay_mode","title_type"=>"string")
                    );
                    $order_data = $this->excel_import_data($temp,$data_arr,$type);
                    unlink($temp);
                    $app_list = $this->DAO->get_app_list();
                    if(empty($app_list)){
                        $this->error_msg("没有维护对应游戏");
                    }
                    $game_list = array();
                    foreach($app_list as $app_value){
                        if(!in_array($app_value['app_name'],array_values($game_list))){
                            $game_list[$app_value['app_id']] = $app_value['app_name'];
                        }
                    }
                    $game_service = array();
                    $error = '';
                    $balance_error = '';
                    $status_error = '';
                    if($_POST['user_id']){
                        $user_id = $_POST['user_id'];
                    }else{
                        $user_id = $_SESSION['usr_id'];
                    }
                    foreach($order_data as $key=>$value){
                        if(!in_array($value['app_id'],array_values($game_list))){
                            $error .= ($key+2).',';
                            continue;
                        }
                        $order_data[$key]['order_time'] = date('Ymd',strtotime($value['order_time']));
                        $order_data[$key]['app_id'] = $value['app_id'] = array_search($value['app_id'], $game_list);
                        $service_list = $this->DAO->get_service_list($order_data[$key]['app_id']);
                        foreach($service_list as $service_value){
                            if(!in_array($service_value['service_name'],array_values($service_list))){
                                $game_service[$service_value['service_id']] = $service_value['service_name'];
                            }
                        }
                        if(!in_array($value['service_name'],array_values($game_service))){
                            $error .= ($key+2).',';
                            continue;
                        }
                        $order_data[$key]['service_id'] = $value['service_id'] = array_search($value['service_name'], $game_service);
                        if(!$value['app_id'] || !$value['service_id'] || is_null($value['buy_name']) || is_null($value['exit_depot'])
                            || is_null($value['in_money']) || is_null($value['loss_num']) || is_null($value['pay_mode']) || is_null($value['token_scale'])){
                            $error .= ($key+2).',';
                            continue;
                        }
                        if($value['in_money'] < 0 || $value['exit_depot'] <0 || $value['loss_num']<0){
                            $error .= ($key+2).',';
                            continue;
                        }
                        $user_info = $this->DAO->get_user_info($user_id);
                        if($user_info['p2']){
                            $order_data[$key]['group_id'] = $group_id = $user_info['p1'];
                            $order_data[$key]['business_id'] = $business_id = $user_info['p2'];
                        }elseif($user_info['p1']){
                            $order_data[$key]['group_id'] = $group_id = $user_id;
                            $order_data[$key]['business_id'] = $business_id = $user_info['p1'];
                        }else{
                            $order_data[$key]['group_id'] = $group_id = $user_id;
                            $order_data[$key]['business_id'] = $business_id = $user_id;
                        }
                        $order_data[$key]['user_id'] = $user_id;
                        $money_collect = $this->DAO->get_money_collect($value,$user_id);
                        if($money_collect && $user_info['group_id']=='15' && ($user_info['p1'] || $user_info['p2']) ){
                            $status_error .= ($key+2).',';
                            continue;
                        }
                        //该区服该商会的代币余额
                        $stock_info = $this->DAO->get_stock_info($value,$group_id);
                        if(!$stock_info['stock_balance'] || ($stock_info['stock_balance']-$value['exit_depot']-$value['loss_num'])<0){
                            $balance_error .= ($key+2).',';
                            continue;
                        }
                    }
                    if($balance_error){
                        $this->error_msg($balance_error.'导入的订单中这些订单的区服代币不足,请及时向上级申请拨币');
                    }elseif($status_error){
                        $this->error_msg($status_error.'导入订单中这些订单已经提交审核，不能导入数据了');
                    }elseif($error){
                        $this->error_msg($error.'导入订单中这些订单出错啦');
                    }else{
                        //导入mysql
                        foreach($order_data as $key =>$data){
                            $app_info = $this->DAO->get_app_info($data['app_id']);
                            if(($data['loss_num'] && $data['loss_num'] != '0') || !$data['in_money']){
                                $data['token_scale'] = 0;
                            }
                            if(!$data['token_scale'] && $data['exit_depot'] != '0'){
                                $data['token_scale'] = $data['exit_depot']/$data['in_money'];
                            }
                            if($data['pay_mode']=="支付宝"){
                                $data['pay_mode'] = 2;
                            }else{
                                $data['pay_mode'] = 1;
                            }
                            if($data['token_scale'] > $app_info['goods_scale']){
                                $data['order_type'] = 2;
                            }else{
                                $data['order_type'] = 1;
                            }
                            $this->import_orders($data,$user_id);
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

    public function import_orders($order_data,$user_id){
        $str = '';
        for($i = 1; $i <= 4; $i++){
            $str .= chr(rand(65, 90));
        }
        //日现金明细
        if($order_data['pay_mode'] == '1'){
            $order_data['wx_money'] = $order_data['in_money'];
            $order_data['ali_money'] = 0;
        }elseif($order_data['pay_mode'] == '2'){
            $order_data['ali_money'] = $order_data['in_money'];
            $order_data['wx_money'] = 0;
        }else{
            $order_data['ali_money'] = 0;
            $order_data['wx_money'] = 0;
        }
        $order_data['order_id'] = $str.$order_data['order_time'].time().rand(100,999);
        $app_info = $this->DAO->get_app_info($order_data['app_id']);
        $order_data['order_date'] = $order_data['order_time'];
        $order_data['do_time'] = strtotime($order_data['order_time']);
        $order_data['sell_time'] = strtotime($order_data['order_time']);
        $order_data['channel'] = $app_info['channel'];
        //个人日订单、日汇总
        $personal_day_order = $this->DAO->get_personal_day_order($order_data,0,$order_data['user_id']);
        $personal_day_money = $this->DAO->get_personal_day_money($order_data,0,$order_data['user_id']);
        //本组日订单、日汇总
        $group_day_order = $this->DAO->get_group_day_order($order_data,1,$order_data['group_id']);
        $group_day_money = $this->DAO->get_group_day_money($order_data,1,$order_data['group_id']);
        //本商会日订单、日汇总
        $business_day_order = $this->DAO->get_business_order($order_data,2,$order_data['business_id']);
        $business_day_money = $this->DAO->get_business_money($order_data,2,$order_data['business_id']);
        //个人日订单、日汇总
        $this->day_order($personal_day_order,$order_data,$personal_day_money,0,$order_data['user_id']);
        //本组日订单、日汇总
        $this->day_order($group_day_order,$order_data,$group_day_money,1,$order_data['user_id']);
        //本商会日订单、日汇总
        $this->day_order($business_day_order,$order_data,$business_day_money,2,$order_data['user_id']);
        $id = $this->DAO->insert_group_order($order_data,$order_data['user_id'],$order_data['order_time']);
        $data = $this->DAO->get_order_info($id);
        $next = strtotime(date('Ymd',$data['add_time']+86400).'12:00:00');
        $order_data['channel'] = $data['channel'];
        if(time()>$next){
            $this->DAO->update_group_order($data['id'],1);
            //个人月订单、月汇总
            $order_data['order_date'] = substr($order_data['order_time'], 0, 6);
            $personal_month_order = $this->DAO->get_personal_day_order($order_data,0,$data['user_id']);
            $personal_month_money = $this->DAO->get_personal_day_money($order_data,0,$data['user_id']);
            //本组月订单、月汇总
            $group_month_order = $this->DAO->get_group_day_order($order_data,1,$order_data['group_id']);
            $group_month_money = $this->DAO->get_group_day_money($order_data,1,$order_data['group_id']);
            //本商会月订单、月汇总
            $business_month_order = $this->DAO->get_business_order($order_data,2,$order_data['business_id']);
            $business_month_money = $this->DAO->get_business_money($order_data,2,$order_data['business_id']);
            //个人月订单、月汇总
            $this->month_order($personal_month_order,$order_data,$personal_month_money,0);
            //本组月订单、月汇总
            $this->month_order($group_month_order,$order_data,$group_month_money,1);
            //本商会月订单、月汇总
            $this->month_order($business_month_order,$order_data,$business_month_money,2);
        }
        $stock_info = $this->DAO->get_stock_info($order_data,$order_data['group_id']);
//        if($user_info['p2']){
//            $lead_stock_info = $this->DAO->get_stock_info($order_data,$user_info['p2']);
//        }
        $order_data['type'] = 0;
        $this->DAO->insert_money_detail($order_data,$user_id);
        //更新自己的库存代币
        $this->update_stock($order_data,$stock_info,'','',2);
//        if($lead_stock_info) {
//            //更新商会长的库存代币
//            $this->update_stock($order_data, $lead_stock_info, '','', 2);
//        }
    }

    public function tpl_down(){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->setTitle("导入订单");
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "区服名称");
        $objActSheet->setCellValue("D1", "出仓代币");
        $objActSheet->setCellValue("E1", "代币比例");
        $objActSheet->setCellValue("F1", "购买人");
        $objActSheet->setCellValue("G1", "损耗代币");
        $objActSheet->setCellValue("H1", "收入金额");
        $objActSheet->setCellValue("I1", "收款方式");
        $objActSheet->setCellValue("A2", "20180710");
        $objActSheet->setCellValue("B2", "西山居魔域手游-应用宝");
        $objActSheet->setCellValue("C2", "神域之光5服");
        $objActSheet->setCellValue("D2", "18500");
        $objActSheet->setCellValue("E2", "");
        $objActSheet->setCellValue("F2", "玩家");
        $objActSheet->setCellValue("G2", " ");
        $objActSheet->setCellValue("H2", "500");
        $objActSheet->setCellValue("I2", "微信");
        $title = iconv("UTF-8", "GB2312//IGNORE","内部订单导入模版-".$str_now.".xls");
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