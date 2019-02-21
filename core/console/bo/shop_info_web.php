<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/5 >
// --------------------------------------
COMMON('baseCore','uploadHelper');
DAO('shop_info_dao');

class shop_info_web extends baseCore{

    public $DAO;
    private $preset = array(
        'shop' => array(
            'msg' => '参数有误',
            'tag' => 'shop_list_view'
            ),
        'shop_game' => array(
            'msg' => '参数有误',
            'tag' => 'shop_game_list_view'
            ),
        'shop_product' => array(
            'msg' => '参数有误',
            'tag' => 'shop_product_list_view'
            )
        );

    public function __construct(){
        parent::__construct();
        $this->DAO     = new shop_info_dao();
        $this->shop_id = $_SESSION['shop_id']?:0;
    }

    // -----------------------
    // 店铺列表
    // -----------------------
    public function shop_list_view(){
        $params = $_POST;
        $shops  = $this->DAO->get_shop_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign('shops',$shops);
        $this->display('shop_list.html');
    }

    public function shop_add_view(){
        $this->display('shop_add.html');
    }

    public function shop_add_do(){
        $params = $_POST;
        if($_FILES['shop_icon']['tmp_name']){
            $params['s_icon'] = $this->up_img('shop_icon',SHOP_ICON);
        }
        $params = $this->shop_qq_encode($params);
        $res = $this->DAO->shop_admin_add_do($params);// 新增店铺并为店铺管理员开通后台权限
        switch ($res) {
            case 0:  $msg = '参数错误';break;
            case -1: $msg = '要升级为店铺后台管理员的普通用户不存在';break;
            case -2: $msg = '该用户已拥有店铺,一个用户账号只允许拥有一个店铺';break;
            case -3: $msg = '店铺信息新增失败';break;
            case -4: $msg = '普通用户升级为店铺后台管理员失败';break;
            case -5: $msg = '为新店铺后台管理员开通权限失败';break;
            case 11: echo json_encode($this->succeed_msg('新增成功！',"shop_list_view")); exit; break;
            default: $msg = '未知问题，新增可能失败！';break;
        }
        die(json_encode($this->error_msg($msg,"shop_list_view")));
    }

    public function shop_edit_view($shop_id){
        $shop = $this->DAO->get_shop_info($shop_id);
        $shop = $this->shop_qq_decode($shop);
        $this->assign('shop',$shop);
        $this->display('shop_edit.html');
    }

    public function shop_edit_do(){
        $params = $_POST;
        if($_FILES['shop_icon']['tmp_name']){
            $params['s_icon'] = $this->up_img('shop_icon',SHOP_ICON);
        }else{
            $params['s_icon'] = $params['old_img'];
        }
        $params = $this->shop_qq_encode($params);
        $this->DAO->shop_edit_do($params);
        die(json_encode($this->succeed_msg("修改成功！","shop_list_view")));
    }

    public function shop_qq_encode($params=array()){
        $s_qq = '';
        if(trim($params['s_qq_1'][0]) && intval($params['s_qq_1'][1])){
            $s_qq .= trim($params['s_qq_1'][0]).'|'.intval($params['s_qq_1'][1]);
            unset($params['s_qq_1']);
        }
        if(trim($params['s_qq_2'][0]) && intval($params['s_qq_2'][1])){
            $s_qq .= ','.trim($params['s_qq_2'][0]).'|'.intval($params['s_qq_2'][1]);
            unset($params['s_qq_2']);
        }
        if(trim($params['s_qq_3'][0]) && intval($params['s_qq_3'][1])){
            $s_qq .= ','.trim($params['s_qq_3'][0]).'|'.intval($params['s_qq_3'][1]);
            unset($params['s_qq_3']);
        }
        $params['s_qq'] = $s_qq;
        return $params;
    }

    public function shop_qq_decode($params=array()){
        list($qq_1, $qq_2, $qq_3) = explode(',', $params['s_qq']);
        unset($params['s_qq']);
        $params['s_qq'][0] = explode('|', $qq_1);
        $params['s_qq'][1] = explode('|', $qq_2);
        $params['s_qq'][2] = explode('|', $qq_3);
        return $params;
    }

    public function shop_lock_do($shop_id, $s_status=0){
        $msg = $s_status ? '锁定' : '解锁';
        $this->DAO->shop_lock_do($shop_id, $s_status);
        die(json_encode($this->succeed_msg($msg.'成功',"shop_list_view"))); 
    }


    // -----------------------
    // 我的店铺
    // -----------------------
    public function shop_info_view($shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        if(!$shop_id){ die('您尚未开店哟~~~'); }
        $shop = $this->DAO->get_shop_info($shop_id);
        $this->assign('shop',$shop);
        $this->display('shop_info.html');
    }

    // -----------------------
    // 我的游戏
    // -----------------------
    public function shop_game_list_view($shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        if(!$shop_id){ die('您尚未开店哟~~~'); }
        $params = $_POST;
        $params['shop_id'] = $shop_id;
        $games = $this->DAO->get_shop_game_list($params,0);
        $page_games = $this->DAO->get_shop_game_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign('games',$games);
        $this->assign('page_games',$page_games);
        $this->assign('params',$params);
        $this->display('shop_game_list.html');
    }

    public function shop_game_add_view($shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        $this->check_empty($shop_id, array(
            'msg' => '您尚未开店哟~~~',
            'tag' => 'shop_game_list_view'
            ));
        $params['shop_id'] = $shop_id;
        $game_list= $this->DAO->get_master_game_list();
        $this->assign('game_list',$game_list);
        $this->assign('params',$params);
        $this->display('shop_game_add.html');
    }

    public function shop_game_add_do(){
        $params = $_POST;
        $params['shop_id'] = $params['shop_id']?:$this->shop_id;
        $this->check_empty($params['shop_id'], array(
            'msg' => '您尚未开店哟~~~',
            'tag' => 'shop_game_list_view'
            ));
        $res = $this->DAO->shop_game_add_do($params);
        if($res == -1){
            $this->f_alert(array(
                'msg' => '您的店铺已存在该游戏，无须再次添加！',
                'tag' => 'shop_game_list_view'
                ),'error');

        }else{
            $this->f_alert(array(
                'msg' => '游戏添加成功！',
                'tag' => 'shop_game_list_view'
                ),'success');
        }
    }

    public function shop_game_edit_view($sg_id=0){
        $this->check_empty($sg_id, $this->preset['shop_game']);
        $game = $this->DAO->get_shop_game_info($sg_id);
        $this->assign('game',$game);
        $this->display('shop_game_edit.html');
    }

    public function shop_game_edit_do(){
        $params = $_POST;
        $params['s_id'] = $params['s_id']?:$this->shop_id;
        $this->DAO->shop_game_edit_do($params);
        die(json_encode($this->succeed_msg("修改成功！","shop_game_list_view")));
    }

    // 店铺游戏屏蔽渠道编辑
    public function shop_game_ch_edit_view($sg_id=0){
        $this->check_empty($sg_id, $this->preset['shop_game']);
        $game = $this->DAO->get_shop_game_info($sg_id);
        $chs = $this->DAO->get_channel_list();
        foreach ($chs as $key => $val) {
            $chs[$key]['firstpay'] = $game['ch_'.$val['id'].'_1'];
            $chs[$key]['recharge'] = $game['ch_'.$val['id'].'_2'];
        }
        $this->assign('game',$game);
        $this->assign('game_channels',$chs);
        $this->display('shop_game_ch_edit.html');
    }

    public function shop_game_ch_edit_do(){
        $params = $_POST;
        $this->DAO->shop_game_ch_edit_do($params);
        die(json_encode($this->succeed_msg("修改成功！","shop_game_list_view")));
    }

    public function shop_product_list_view($game_id=0, $shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        if(!$game_id){ die('您尚未指定游戏~~~'); }
        if(!$shop_id){ die('您尚未开店哟~~~'); }
        $params = $_POST;
        $params['game_id'] = $game_id;
        $params['shop_id'] = $shop_id;
        $master_products = $this->DAO->get_master_product_list($game_id);
        $products = $this->DAO->get_shop_product_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign('master_products',$master_products);
        $this->assign('products',$products);
        $this->assign('params',$params);
        $this->display('shop_product_list.html');
    }

    // 店铺商品编辑 - 只能修改 店内渠道折扣
    public function shop_product_ch_edit_view($product_id=0, $shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        $this->check_empty($shop_id, array('msg'=>'未知店铺','tag'=>'shop_product_ch_edit_view'));
        $this->check_empty($product_id, $this->preset['shop_product']);
        $params['product_id'] = $product_id;
        $params['shop_id'] = $shop_id;

        // 66173某上架商品各个渠道的叠加折扣 - 商品有效渠道优先折扣混合计算器
        $temp = $this->shop_product_priority_discount(array('id'=>$product_id));
        $master_ch = $temp['chs'];
        $params['chs_num'] = count($temp['chs']);
        $params['game_id'] = $temp['product_info']['game_id'];
        $params['type']    = $temp['product_info']['type'];

        // 店铺特价商品信息
        $shop_ch = $this->DAO->get_shop_product_info($params);
        foreach ($master_ch as $key => $val) {
            $master_ch[$key]['shop_discount'] = $shop_ch['ch_'.$val['id']]?:0;
        }
        $params['sp_id'] = $shop_ch['sp_id'] ? $shop_ch['sp_id'] : 0;
        $this->assign('channels_discount', $master_ch);
        $this->assign('params', $params);
        $this->display('shop_product_ch_edit.html');
    }

    public function shop_product_ch_edit_do(){
        $params = $_POST;
        // 输入的折扣数据校验
        for ($i=1; $i <= $params['chs_num']; $i++) { 
            if(abs($params['ch_'.$i]) > 10){
                $this->f_alert(array(
                    'msg' => '折扣范围必须在0 ~ 9.9之间! 0表示使用官网折扣！',
                    'tag' => 'shop_product_ch_edit_view'
                    ),'error');
            }
        }
        $this->DAO->shop_product_ch_edit_do($params);
        $msg = $params['sp_id'] ? '修改' : '添加';
        die(json_encode($this->succeed_msg($msg."成功！","shop_game_list_view")));
    }


    // -----------------------
    // 我的订单
    // -----------------------

    public function shop_order_list_view($shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        if(!$shop_id){ die('您尚未开店哟~~~'); }
        $params = $_POST;
        $params['shop_id'] = $shop_id;
        if(!empty($params['time'])){
            $params['buy_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['buy_time2']=strtotime($params['time2']);
        }
        $chs_list   = $this->DAO->get_channel_list();
        $game_list  = $this->DAO->get_shop_game_list($params,0,0);
        $order_list = $this->DAO->get_shop_order_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign('params', $params);
        $this->assign("dataList", $order_list);
        $this->assign("game_list", $game_list);
        $this->assign("channels_list", $chs_list);
        $this->assign("p_type", array( '1'=>'首充号', '2'=>'首充号续充'));
        $this->display('shop_order_list.html');
    }

    public function shop_order_list_export_do($shop_id=0){
        $shop_id = $shop_id?:$this->shop_id;
        if(!$shop_id){ die('您尚未开店哟~~~'); }
        set_time_limit(0);
        $params=$_GET;
        $params['shop_id'] = $shop_id;
        if(!empty($params['time'])){
            $params['buy_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['buy_time2']=strtotime($params['time2']);
        }
        $dataList=$this->DAO->get_shop_order_list($params);

        if($dataList){
            $this->shop_order_excel_out($dataList);
            die(json_encode($this->succeed_msg("导出成功","shop_order_list_view")));
        }else{
            die(json_encode($this->error_msg("没有要导出的数据","shop_order_list_view")));
        }
    }

    // -----------------------

    private function f_alert($params=array(), $type='error'){
        $oper = $type=='success'?'succeed_msg':'error_msg';
        if(empty($params['msg'])){ $params['msg'] = '参数错误'; }
        if(empty($params['tag'])){ $params['tag'] = 'shop_game_list_view'; }
        die(json_encode($this->$oper($params['msg'], $params['tag'])));
    }

    private function check_empty($obj=null, $params=array()){
        if(empty($obj)){ $this->f_alert($params); }
    }

    private function shop_order_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        //设置当前的sheet
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("订单信息");
        $objActSheet->setCellValue("A1", "编号");
        $objActSheet->setCellValue("B1", "商品");
        $objActSheet->setCellValue("C1", "订单ID");
        $objActSheet->setCellValue("D1", "购买人");
        $objActSheet->setCellValue("E1", "商品ID");
        $objActSheet->setCellValue("F1", "购买数量");
        $objActSheet->setCellValue("G1", "单价");
        $objActSheet->setCellValue("H1", "金额");
        $objActSheet->setCellValue("I1", "应付金额");
        $objActSheet->setCellValue("J1", "区服");
        $objActSheet->setCellValue("K1", "渠道");
        $objActSheet->setCellValue("L1", "销售人ID");
        $objActSheet->setCellValue("M1", "订单状态");
        $objActSheet->setCellValue("N1", "购买时间");
        $objActSheet->setCellValue("O1", "支付时间");
        $objActSheet->setCellValue("P1", "结单时间");
        $objActSheet->setCellValue("Q1", "支付渠道");
        // $objActSheet->setCellValue("R1","支付渠道订单号");
        // $objActSheet->setCellValue("S1", "银行单号");
        // $objActSheet->setCellValue("T1", "支付人");
        // $objActSheet->setCellValue("U1", "发货备注");
        // $objActSheet->setCellValue("V1", "支付备注");
        // $objActSheet->setCellValue("W1","QQ");
        // $objActSheet->setCellValue("X1","电话");
        $objActSheet->setCellValue("Y1", "折扣");
        // $objActSheet->setCellValue("Z1", "折扣依赖表ID");
        $objActSheet->setCellValue("AA1", "首充号角色名");
        $objActSheet->setCellValue("AB1", "首充号备用角色名");
        $objActSheet->setCellValue("AC1", "客服");
        $objActSheet->setCellValue("AD1", "代充账号");
        // $objActSheet->setCellValue("AE1", "代充账号密码");
        $objActSheet->setCellValue("AF1", "首充号角色属性");
        $objActSheet->setCellValue("AG1", "赠送礼包码");
        $objActSheet->setCellValue("AH1", "店铺ID");
        $objActSheet->setCellValue("AI1", "游戏名");
        $objActSheet->setCellValue("AJ1", "游戏区服");

        $n = 2;
        foreach($data as $info){
            if($info['status'] ==0){
                $info['status']="提交中";
            }elseif($info['is_del']=='2'){
                $info['status']="已退款";
            }else if($info['status'] ==1){
                $info['status']="已付款";
            }else{
                $info['status']="已结单";
            }
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, $info['title']);
            $objActSheet->setCellValueExplicit("C".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("D".$n, $info['buyer_id']);
            $objActSheet->setCellValue("B".$n, $info['product_id']);
            $objActSheet->setCellValue("E".$n, $info['amount']);
            $objActSheet->setCellValue("F".$n, $info['unit_price']);
            $objActSheet->setCellValue("G".$n, $info['money']);
            $objActSheet->setCellValue("H".$n, $info['pay_money']);
            $objActSheet->setCellValue("I".$n, $info['game_id']);
            $objActSheet->setCellValue("J".$n, $info['serv_id']);
            $objActSheet->setCellValue("K".$n, $info['game_channel']);
            $objActSheet->setCellValue("L".$n, $info['seller_id']);
            $objActSheet->setCellValue("M".$n, $info['status']);
            $objActSheet->setCellValue("N".$n, date("Y-m-d H:i:s", $info['buy_time']));
            $objActSheet->setCellValue("O".$n, date("Y-m-d H:i:s", $info['pay_time'])=="1970-01-01 08:00:00"?"":date("Y-m-d H:i:s", $info['pay_time']));
            $objActSheet->setCellValue("P".$n, date("Y-m-d H:i:s", $info['ship_time'])=="1970-01-01 08:00:00"?"":date("Y-m-d H:i:s", $info['ship_time']));
            $objActSheet->setCellValue("Q".$n, $this->pay_channel[$info['pay_channel']]);
            // $objActSheet->setCellValueExplicit("R".$n, $info['channel_order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            // $objActSheet->setCellValue("S".$n, $info['bank_order_id']);
            // $objActSheet->setCellValue("T".$n, $info['payer']);
            // $objActSheet->setCellValue("U".$n, $info['ship_memo']);
            // $objActSheet->setCellValue("V".$n, $info['pay_memo']);
            // $objActSheet->setCellValueExplicit("W".$n, $info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            // $objActSheet->setCellValue("X".$n, $info['tel']);
            $objActSheet->setCellValue("Y".$n, $info['discount']);
            // $objActSheet->setCellValue("Z".$n, $info['discount_in']);
            $objActSheet->setCellValue("AA".$n, $info['role_name']);
            $objActSheet->setCellValue("AB".$n, $info['role_back_name']);
            $objActSheet->setCellValue("AC".$n, $info['service_id']);
            $objActSheet->setCellValue("AD".$n, $info['game_user']);
            // $objActSheet->setCellValue("AE".$n, $info['game_pwd']);
            $objActSheet->setCellValue("AF".$n, $info['attr']);
            $objActSheet->setCellValue("AG".$n, $info['gift_code']);
            $objActSheet->setCellValue("AH".$n, $info['shop_id']);
            $objActSheet->setCellValue("AI".$n, $info['game_name']);
            $objActSheet->setCellValue("AJ".$n, $info['serv_name']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","订单信息-".$str_now.'.xls');
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

    /**
     * [商品有效渠道优先折扣计算]
     * 计算当前 指定商品可用渠道的有效折扣
     * 优先级：游戏折扣 < 商品折扣
     * @param  array  $par       <参数数组 如下>
     *         int    $par.id    <product_id>
     * @return array <当前可用的各个渠道的有效折扣，折扣挂载在渠道数组中>
     * @author <zbc>
     */
    private function shop_product_priority_discount($par = array()){
        $return = array();
        $product_id = $par['id'];
        $product_info = $this->DAO->get_product_discount($product_id);
        if($product_info){
            $ptype   = $product_info['type'];
            $game_id = $product_info['game_id'];
        }
        $game_chs = $this->DAO->get_game_ch_discount($game_id);
        $chs = $this->DAO->get_channel_list();
        foreach ($chs as $k => $v) {
            $g = floatval($game_chs['ch_'.$v['id'].'_'.$ptype]);
            $p = floatval($product_info['ch_'.$v['id']]);
            $priority_disc = $p?:($g?:0);
            $chs[$k]['priority_discount'] = $priority_disc;
        }
        $return['chs'] = $chs;
        if($product_info){ $return['product_info'] = $product_info; }
        return $return;
    }
}