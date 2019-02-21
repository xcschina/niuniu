<?php
COMMON('baseCore','uploadHelper');
DAO('game_info_dao','common_dao');

class game_info_web extends baseCore{

    public $DAO;
    public $COMMON;
    public $type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_info_dao();
        $this->COMMON = new common_dao();
        $this->type=array(
            0=>array('9999','首页热门'),
            1=>array('1','首充号'),
            2=>array('2','首充号续充'),
            3=>array('3','代充'),
            4=>array('4','账号'),
            5=>array('5','游戏币'),
            6=>array('6','道具'),
            7=>array('7','手游礼包'),
            8=>array('8','苹果代充'),
            9=>array('8888','我要卖')
        );
        $this->game_type = array(
            101 => '动作',
            102 => '角色',
            103 => '射击',
            104 => '策略',
            105 => '即时',
            106 => '回合',
            107 => '休闲',
            108 => '冒险',
            109 => '模拟',
            110 => '竞技',
            111 => '卡牌',
            112 => '体育',
            113 => '格斗',
            114 => 'MOBA');
    }

    public function get_game_list(){
        $params=$_POST;
        $channels_list=$this->COMMON->get_channels_list();
        $dataList=$this->DAO->get_game_info_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("channels_list",$channels_list);
        $this->assign("params",$params);
        $this->display("game_info_list.html");
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $params['game_name'] = urldecode($params['game_name']);
        $dataList = $this->DAO->get_all_game_info_list($params);
        if($dataList){
            $this->master_excel_out($dataList);
            echo json_encode($this->succeed_msg("导出成功","orders_list"));
        }else{
            echo json_encode($this->error_msg("没有要导出的数据","orders_list"));
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏资料");
        $objActSheet->setCellValue("A1", "No.");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "英文名");
        $objActSheet->setCellValue("D1", "icon");
        $objActSheet->setCellValue("E1", "首字母");
        $objActSheet->setCellValue("F1", "状态");
        $objActSheet->setCellValue("G1", "商品模板");
        $objActSheet->setCellValue("H1", "标签");
        $objActSheet->setCellValue("I1", "截图");
        $objActSheet->setCellValue("J1", "66173");
        $objActSheet->setCellValue("K1", "360");
        $objActSheet->setCellValue("L1", "91");
        $objActSheet->setCellValue("M1", "UC九游");
        $objActSheet->setCellValue("N1", "豌豆荚");
        $objActSheet->setCellValue("O1", "百度多酷");
        $objActSheet->setCellValue("P1", "官方");
        $objActSheet->setCellValue("Q1", "ITools");
        $objActSheet->setCellValue("R1", "快用ios");
        $objActSheet->setCellValue("S1", "果盘ios");
        $objActSheet->setCellValue("T1", "猎宝");
        $objActSheet->setCellValue("U1", "乐8ios");
        $objActSheet->setCellValue("V1", "拇指玩");
        $objActSheet->setCellValue("W1", "XY游戏");
        $objActSheet->setCellValue("X1", "51508");
        $objActSheet->setCellValue("Y1", "APPStore");
        $objActSheet->setCellValue("Z1", "TT");
        $objActSheet->setCellValue("AA1", "当乐");
        $objActSheet->setCellValue("AB1", "快用安卓");
        $objActSheet->setCellValue("AC1", "果盘");
        $objActSheet->setCellValue("AD1", "quickgame");
        $objActSheet->setCellValue("AE1", "应用宝");
        $objActSheet->setCellValue("AF1", "当乐IOS");
        $objActSheet->setCellValue("AG1", "乐游");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, $info['game_name']);
            $objActSheet->setCellValue("C".$n, $info['en_name']);
            $objActSheet->setCellValue("D".$n, $info['game_icon']);
            $objActSheet->setCellValue("E".$n, $info['first_letter']);
            if($info['status']=="1"){
                $objActSheet->setCellValue("F".$n, "启用");
            }else{
                $objActSheet->setCellValue("F".$n, "暂停");
            }
            if($info['img1']||$info['img2']||$info['img3']||$info['img4']){
                $objActSheet->setCellValue("G".$n, "已上传");
            }else{
                $objActSheet->setCellValue("G".$n, "");
            }
            $objActSheet->setCellValue("H".$n, $info['tags']);
            if($info['img1']||$info['img2']||$info['img3']||$info['img4']){
                $objActSheet->setCellValue("I".$n, "已上传");
            }else{
                $objActSheet->setCellValue("I".$n, "");
            }
            $objActSheet->setCellValue("J".$n, ($info['ch_1_1'].'/'.$info['ch_1_2'].'/'.$info['ch_1_3'])=="0/0/0"?"-":$info['ch_1_1'].'/'.$info['ch_1_2'].'/'.$info['ch_1_3']);
            $objActSheet->setCellValue("K".$n, ($info['ch_2_1'].'/'.$info['ch_2_2'].'/'.$info['ch_2_3'])=="0/0/0"?"-":$info['ch_2_1'].'/'.$info['ch_2_2'].'/'.$info['ch_2_3']);
            $objActSheet->setCellValue("L".$n, ($info['ch_3_1'].'/'.$info['ch_3_2'].'/'.$info['ch_3_3'])=="0/0/0"?"-":$info['ch_3_1'].'/'.$info['ch_3_2'].'/'.$info['ch_3_3']);
            $objActSheet->setCellValue("M".$n, ($info['ch_4_1'].'/'.$info['ch_4_2'].'/'.$info['ch_4_3'])=="0/0/0"?"-":$info['ch_4_1'].'/'.$info['ch_4_2'].'/'.$info['ch_4_3']);
            $objActSheet->setCellValue("N".$n, ($info['ch_5_1'].'/'.$info['ch_5_2'].'/'.$info['ch_5_3'])=="0/0/0"?"-":$info['ch_5_1'].'/'.$info['ch_5_2'].'/'.$info['ch_5_3']);
            $objActSheet->setCellValue("O".$n, ($info['ch_6_1'].'/'.$info['ch_6_2'].'/'.$info['ch_6_3'])=="0/0/0"?"-":$info['ch_6_1'].'/'.$info['ch_6_2'].'/'.$info['ch_6_3']);
            $objActSheet->setCellValue("P".$n, ($info['ch_7_1'].'/'.$info['ch_7_2'].'/'.$info['ch_7_3'])=="0/0/0"?"-": $info['ch_7_1'].'/'.$info['ch_7_2'].'/'.$info['ch_7_3']);
            $objActSheet->setCellValue("Q".$n, ($info['ch_8_1'].'/'.$info['ch_8_2'].'/'.$info['ch_8_3'])=="0/0/0"?"-":$info['ch_8_1'].'/'.$info['ch_8_2'].'/'.$info['ch_8_3']);
            $objActSheet->setCellValue("R".$n, ($info['ch_9_1'].'/'.$info['ch_9_2'].'/'.$info['ch_9_3'])=="0/0/0"?"-":$info['ch_9_1'].'/'.$info['ch_9_2'].'/'.$info['ch_9_3']);
            $objActSheet->setCellValue("S".$n, ($info['ch_10_1'].'/'.$info['ch_10_2'].'/'.$info['ch_10_3'])=="0/0/0"?"-":$info['ch_10_1'].'/'.$info['ch_10_2'].'/'.$info['ch_10_3']);
            $objActSheet->setCellValue("T".$n, ($info['ch_11_1'].'/'.$info['ch_11_2'].'/'.$info['ch_11_3'])=="0/0/0"?"-":$info['ch_11_1'].'/'.$info['ch_11_2'].'/'.$info['ch_11_3']);
            $objActSheet->setCellValue("U".$n, ($info['ch_12_1'].'/'.$info['ch_12_2'].'/'.$info['ch_12_3'])=="0/0/0"?"-":$info['ch_12_1'].'/'.$info['ch_12_2'].'/'.$info['ch_12_3']);
            $objActSheet->setCellValue("V".$n, ($info['ch_13_1'].'/'.$info['ch_13_2'].'/'.$info['ch_13_3'])=="0/0/0"?"-":$info['ch_13_1'].'/'.$info['ch_13_2'].'/'.$info['ch_13_3']);
            $objActSheet->setCellValue("W".$n, ($info['ch_14_1'].'/'.$info['ch_14_2'].'/'.$info['ch_14_3'])=="0/0/0"?"-":$info['ch_14_1'].'/'.$info['ch_14_2'].'/'.$info['ch_14_3']);
            $objActSheet->setCellValue("X".$n, ($info['ch_15_1'].'/'.$info['ch_15_2'].'/'.$info['ch_15_3'])=="0/0/0"?"-":$info['ch_15_1'].'/'.$info['ch_15_2'].'/'.$info['ch_15_3']);
            $objActSheet->setCellValue("Y".$n, ($info['ch_16_1'].'/'.$info['ch_16_2'].'/'.$info['ch_16_3'])=="0/0/0"?"-":$info['ch_16_1'].'/'.$info['ch_16_2'].'/'.$info['ch_16_3']);
            $objActSheet->setCellValue("Z".$n, ($info['ch_17_1'].'/'.$info['ch_17_2'].'/'.$info['ch_17_3'])=="0/0/0"?"-":$info['ch_17_1'].'/'.$info['ch_17_2'].'/'.$info['ch_17_3']);
            $objActSheet->setCellValue("AA".$n, ($info['ch_18_1'].'/'.$info['ch_18_2'].'/'.$info['ch_18_3'])=="0/0/0"?"-":$info['ch_18_1'].'/'.$info['ch_18_2'].'/'.$info['ch_18_3']);
            $objActSheet->setCellValue("AB".$n, ($info['ch_19_1'].'/'.$info['ch_19_2'].'/'.$info['ch_19_3'])=="0/0/0"?"-":$info['ch_19_1'].'/'.$info['ch_19_2'].'/'.$info['ch_19_3']);
            $objActSheet->setCellValue("AC".$n, ($info['ch_20_1'].'/'.$info['ch_20_2'].'/'.$info['ch_20_3'])=="0/0/0"?"-":$info['ch_20_1'].'/'.$info['ch_20_2'].'/'.$info['ch_20_3']);
            $objActSheet->setCellValue("AD".$n, ($info['ch_21_1'].'/'.$info['ch_21_2'].'/'.$info['ch_21_3'])=="0/0/0"?"-":$info['ch_21_1'].'/'.$info['ch_21_2'].'/'.$info['ch_21_3']);
            $objActSheet->setCellValue("AE".$n, ($info['ch_22_1'].'/'.$info['ch_22_2'].'/'.$info['ch_22_3'])=="0/0/0"?"-":$info['ch_22_1'].'/'.$info['ch_22_2'].'/'.$info['ch_22_3']);
            $objActSheet->setCellValue("AF".$n, ($info['ch_23_1'].'/'.$info['ch_23_2'].'/'.$info['ch_23_3'])=="0/0/0"?"-":$info['ch_23_1'].'/'.$info['ch_23_2'].'/'.$info['ch_23_3']);
            $objActSheet->setCellValue("AG".$n, ($info['ch_24_1'].'/'.$info['ch_24_2'].'/'.$info['ch_24_3'])=="0/0/0"?"-":$info['ch_24_1'].'/'.$info['ch_24_2'].'/'.$info['ch_24_3']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏资料-".$str_now.'.xls');
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

    public function game_add_view(){
        $channels_list=$this->DAO->get_channels_list();
        $this->assign("channels_list",$channels_list);
        $this->assign("tags",$this->game_type);
        $this->display("game_info_add.html");
    }

    public function add_game(){
        $params=$_POST;
        $params['tags'] = preg_replace('/($s*$)|(^s*^)/m', '',$params['tags']);
        if(!$params['game_name']){
            echo json_encode($this->error_msg("请输入游戏名"));
            exit;
        }
        if(!$params['en_name'] || !preg_match("/^[a-zA-Z\s]+$/",$params['en_name'])){
            echo json_encode($this->error_msg("请输入游戏英文名"));
            exit;
        }

        if(!$_FILES['game_icon']['tmp_name']){
            echo json_encode($this->error_msg("请上传图片ICON"));
            exit;
        }
        $params['en_name']=strtoupper($params['en_name']);
        $info=$this->DAO->get_game_info($params);
        if($info){
            if($info['game_name']==$params['game_name']){
                echo json_encode($this->error_msg("同一渠道游戏名已存在"));
                exit;
            }
            if($info['en_name']==$params['en_name']){
                echo json_encode($this->error_msg("同一渠道游戏英文名已存在"));
                exit;
            }
        }

        if($_FILES['product_img']['tmp_name']){
            $params['product_img']=$this->up_img('product_img',PRODUCT_IMG);
        }else{
            $params['product_img']="";
        }

        if($params['stars']<1 || !is_numeric($params['stars']) || $params['stars']>5){
            echo json_encode($this->error_msg("请输入1-5个星级"));
            exit;
        }

        $params['discount']=floatval($params['discount']);
        $params['first_letter']=mb_substr($params['en_name'],0, 1, "UTF-8");
        $img_path=$this->up_imgs();
        $params['img1']=$img_path['img1'];
        $params['img2']=$img_path['img2'];
        $params['img3']=$img_path['img3'];
        $params['img4']=$img_path['img4'];
        $game_id = $this->DAO->add_game($params);
        $game_icon = $this->up_game_icon("game_icon","",$game_id,0);
        $game_icon = $game_icon['path'];
        $this->DAO->update_game_icon($game_icon, $game_id);
        echo json_encode($this->succeed_msg("游戏信息添加成功","game_list"));
    }


    public function game_edit_view($id){
        $channels_list=$this->DAO->get_channels_list();
        $game_info=$this->DAO->get_game_info_byid($id);
        $arr = explode(',',$game_info['game_tags']);
        $this->assign("arr",$arr);
        $this->assign("channels_list",$channels_list);
        $this->assign("tags",$this->game_type);
        $this->assign("game_info",$game_info);
        $this->display("game_info_edit.html");
    }

    public function edit_game(){
        $params=$_POST;
        if(!$params['game_name']){
            echo json_encode($this->error_msg("请输入游戏名"));
            exit;
        }
        if(!$params['en_name'] || !preg_match("/^[a-zA-Z\s]+$/",$params['en_name'])){
            echo json_encode($this->error_msg("请输入游戏英文名"));
            exit;
        }

        $params['en_name']=strtoupper($params['en_name']);
        $params['tags'] = preg_replace('/($s*$)|(^s*^)/m', '',$params['tags']);
        $info=$this->DAO->get_game_info($params);
        if($info){
            if($info['game_name']==$params['game_name'] && $info['id']!=$params['id']){
                echo json_encode($this->error_msg("同一渠道游戏名已存在"));
                exit;
            }
            if($info['en_name']==$params['en_name'] && $info['id']!=$params['id']){
                echo json_encode($this->error_msg("同一渠道游戏英文名已存在"));
                exit;
            }
        }
        if($params['stars']<1 || !is_numeric($params['stars']) || $params['stars']>5){
            echo json_encode($this->error_msg("请输入1-5个星级"));
            exit;
        }
        if(!$_FILES['product_img']['tmp_name']){
            $params['product_img']=$params['old_product_img'];
        }else{
            $params['product_img']=$this->up_img('product_img',PRODUCT_IMG);
        }

        if(!$_FILES['game_icon']['tmp_name']){
            $params['game_icon']=$params['old_game_icon'];
        }else{
            //$params['game_icon']=$this->up_img('game_icon',GAME_ICON);
            $params['game_icon'] = $this->up_game_icon("game_icon","",$params['id'],0);
            $params['game_icon'] = $params['game_icon']['path'];
        }
        if(!$_FILES['imgs']['tmp_name'][0]){
            $params['img1']=$params['old_img1'];
            $params['img2']=$params['old_img2'];
            $params['img3']=$params['old_img3'];
            $params['img4']=$params['old_img4'];
        }else{
            $img_path=$this->up_imgs();
            $params['img1']=$img_path['img1'];
            $params['img2']=$img_path['img2'];
            $params['img3']=$img_path['img3'];
            $params['img4']=$img_path['img4'];
        }

        $params['discount']=floatval($params['discount']);
        $params['first_letter']=mb_substr($params['en_name'],0, 1, "UTF-8");
        $this->DAO->update_game($params);
        echo json_encode($this->succeed_msg("游戏信息编辑成功","game_list"));
    }

    public function channel_view($game_id){
        $info = $this->DAO->get_game_info_byid($game_id);
        $channels = $this->DAO->get_channels_list();
        $gamechannels = $this->DAO->get_game_channels($game_id);
        unset($gamechannels['game_id']);
        $game_channels = array();
        foreach($gamechannels as $k=>$v){
            $ch_id = explode("_",$k);
            $d_type = $ch_id[2];
            $ch_info = $this->DAO->get_channel_info($ch_id[1]);
            $game_channels[$ch_id[1]][] = array("name"=>$ch_info['channel_name'], "discount"=>$v, "d_type"=>$k);
        }
        $this->assign("info", $info);
        $this->assign("channels", $channels);
        $this->assign("game_channels", $game_channels);
        $this->display("game_ch_edit.html");
    }

    public function del_game($game_id){
        $this->DAO->del_game($game_id);
        echo json_encode($this->unclose_succeed_msg("删除成功"));
    }

    public function game_import_view(){
        $this->display("game_import.html");
    }

    public function do_import(){
        header("Content-Type:text/html;charset=utf-8");
        $fileType = $_FILES['games']['type'];
        if($fileType != 'application/vnd ms-excel' &&
            $fileType != 'application/kset' &&
            $fileType != 'application/vnd.ms-excel' &&
            $fileType != 'application/octet-stream' &&
            $fileType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            echo json_encode($this->error_msg("请选择xls,xlsx文件"));
            exit;
        }
        $file_name = $_FILES['games']["tmp_name"];
        $ext_name = $_FILES['games']["name"];
        $ext_name = explode(".", $ext_name);
        $ext_name = $ext_name[1];
        if($ext_name == 'xlsx'){
            $PHPReader = new PHPExcel_Reader_Excel2007();
        }else{
            $PHPReader = new PHPExcel_Reader_Excel5();
        }

        $PHPExcel = $PHPReader->load($file_name);
        $currentSheet = $PHPExcel->getSheet(0);
        $allRow = $currentSheet->getHighestRow();

        for( $currentRow = 2 ; $currentRow <= $allRow ; $currentRow++){
            $game_name = $currentSheet->getCellByColumnAndRow("0", $currentRow)->getValue();
            $en_name =$currentSheet->getCellByColumnAndRow("1", $currentRow)->getValue();
            $is_hot =$currentSheet->getCellByColumnAndRow("2", $currentRow)->getValue();
            $status =$currentSheet->getCellByColumnAndRow("3", $currentRow)->getValue();
            if(strlen(trim($game_name))>=1 &&
                strlen(trim($en_name)) >= 1 &&
                preg_match("/^[a-zA-Z\s]+$/",$en_name)&&
                is_numeric($is_hot) &&
                is_numeric($status)){
                $en_name=strtoupper($en_name);
                $first_letter=mb_substr($en_name,0, 1, "UTF-8");

                $params = array('game_name'=>$game_name,
                    'en_name'=>$en_name,
                    'tags' => '',
                    'channel_id' => '0',
                    'discount' => '0',
                    'first_letter' => $first_letter,
                    'is_hot' => $is_hot,
                    'status'=>$status,
                    'tags'=>'',
                    'product_img'=>'',
                    'subtitle'=>'',
                    'gift_guide'=>'',
                    'stars'=>'1',
                    'img1'=>'',
                    'img2'=>'',
                    'img3'=>'',
                    'img4'=>'',
                    'discount'=>'0');
                $info=$this->DAO->get_game_info($params);
                if(!$info){
                    $this->DAO->add_game($params);
                }
            }
        }
        unlink($file_name);
        echo json_encode($this->succeed_msg("游戏导入成功！","game_list"));
    }

    public function do_save_ch(){
        //错误 echo json_encode($this->error_msg("错误信息"));
        //成功关闭当前页 echo json_encode($this->succeed_msg("正确信息","刷新tabid"));
        $game_id = $_POST['game_id'];
        unset($_POST['game_id']);
        $this->DAO->update_game_ch($game_id, $_POST);
        $this->save_product_discount($game_id, $_POST);

        //首充号最低折扣计算
        $game_chs = $this->DAO->get_game_channels($game_id);
        unset($game_chs['game_id']);
        //最低折扣
        $min_dis = 10;
        $min_rate = 10;
        foreach($game_chs as $k=>$v){
            if(!empty($v)){
                $ch_id = explode("_",$k);
                if($ch_id[2]=='1' && $min_dis > $v){
                    $min_dis = $v;
                }
                if($ch_id[2]=='2' && $min_rate > $v){
                    $min_rate = $v;
                }
            }
        }
        $this->DAO->up_game_refill_rate($game_id,$min_rate);
        $this->DAO->up_game_char_rate($game_id,$min_dis);
        echo json_encode($this->unclose_succeed_msg("修改成功"));
    }

    public function save_product_discount($game_id, $channels){
        foreach($_POST as $k=>$v){
            $channel = explode("_",$k);
            $type = $channel[2];
            $ch_id = $channel[1];
            $this->DAO->update_product_discount($game_id, $type, $ch_id, $v);
        }
    }

    public function game_type_list(){
        $this->assign("types",$this->type);
        $this->display("game_type_list.html");
    }

    public function hot_game_list(){
        $type=paramUtils::strByGET("type",false);
        $game_list=$this->COMMON->get_game_list();
        $hot_games=$this->DAO->hot_game_list($type);
        $hot_games_arr=array();
        $sel_game_arry=array();
        foreach($hot_games as $game){
            $hot_games_arr[]=$game['game_id'];
            foreach($game_list as $list){
                if($list['id']==$game['game_id']){
                    $sel_game_arry[]=$list;
                    continue;
                }
            }
        }

        $unsel_game_arry=array();
        foreach($game_list as $game){//已选选择中的
            if(!in_array($game['id'],$hot_games_arr)){
                $unsel_game_arry[]=$game;
            }
        }
        $game_list=array_merge ($sel_game_arry,$unsel_game_arry);
        $this->assign("type", $type);
        $this->assign("hot_games", $hot_games_arr);
        $this->assign("game_list", $game_list);
        $this->display("hot_game_list.html");
    }

    public function hot_game_edit_view(){
        ini_set('display_errors', 'On');
        $type=paramUtils::strByGET("type",false);
        $game_list=$this->COMMON->get_game_list();
        $hot_games=$this->DAO->hot_game_list($type);
        $hot_games_arr=array();
        $sel_game_arry=array();
        foreach($hot_games as $game){
            $hot_games_arr[]=$game['game_id'];
            foreach($game_list as $list){
                if($list['id']==$game['game_id']){
                    $sel_game_arry[]=$list;
                    continue;
                }
            }
        }

        $unsel_game_arry=array();
        foreach($game_list as $game){//已选选择中的
            if(!in_array($game['id'],$hot_games_arr)){
                $unsel_game_arry[]=$game;
            }
        }
        $game_list=array_merge ($sel_game_arry,$unsel_game_arry);
        $this->assign("type", $type);
        $this->assign("hot_games", $hot_games_arr);
        $this->assign("game_list", $game_list);
        $this->display("hot_game_edit.html");
    }


    public function save_hot_games(){
        $game_ids=$_POST["game_ids"];
        $type=paramUtils::intByPOST('type',false);
        $hot_games=array();
        foreach($game_ids as $game_id){
            if(trim($game_id)){
                if(in_array($game_id,$hot_games)){
                    echo json_encode($this->error_msg("顺序列表含有重复游戏，请核查！"));
                    exit;
                }
                $hot_games[]=$game_id;
            }
        }
        $this->DAO->del_hot_games($type);
        $this->DAO->add_hot_games($type,$hot_games);
        echo json_encode($this->unclose_succeed_msg("保存成功"));
    }

    protected function up_game_icon($img_name, $is_point_color='', $file_name = '', $is_record = 1) {
        $upFile = new uploadHelper($img_name, GAME_ICON, $is_record);
        $upFile->point_color = $is_point_color;
        $upFile->upload($file_name);

        if ($upFile->occur_err()) {
            $error = $upFile->get_err_msgs();
            $_SESSION['msg'] = $error[0];
            die(json_encode($this->error_msg($img_name."上传失败,失败原因：".$error[0])));
        } else {
            return array(
                'phy_path'=>$upFile->phy_files_path,
                "path"=>$upFile->get_rel_file_path(),
                "width"=>$upFile->img_width,
                "height"=>$upFile->img_height,
                "color"=>$upFile->point_color
            );
        }
    }

    protected function up_imgs(){
        $img_path['img1']='';
        $img_path['img2']='';
        $img_path['img3']='';
        $img_path['img4']='';
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $imgs = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path['img'.($key+1)]=$img;
                }
            }
        }
        return $img_path;
    }

    public function game_config_view($id){
        $game_info=$this->DAO->get_game_info_byid($id);
        $this->assign("game_info", $game_info);
        $this->display("game_config.html");
    }

    public function game_introduce($id){
        $game_introduce = $this->DAO->get_game_introduce_byid($id);
        $this->game_info();
        $this->assign("id", $id);
        $this->assign("game_info", $game_introduce);
        $this->display("game_introduce.html");
    }

    public function game_info(){
        $game_type = array(
            1 => '休闲',
            2 => '模拟',
            3 => '竞技',
            4 => '动作',
            5 => '卡牌',
            6 => '角色',
            7 => '策略',
            8 => '射击',
            9 => '体育',
            10 => '回合',
            11 => '格斗',
            12 => '即时');
        $this->assign("game_type", $game_type);
    }

    public function do_config_save(){
        $params=$_POST;
        $this->DAO->upd_game_config($params);
        echo json_encode($this->succeed_msg("保存成功"));
    }

    public function do_introduce_save(){
        $params=$_POST;
        if(!$params['id']){
            die(json_encode($this->error_msg("网络请求错误,请重新登录后,再尝试.")));
        }
        $game_info = $this->DAO->get_game_introduce_byid($params['id']);
        if($game_info){
            $this->DAO->update_game_introduce($params,$game_info['id']);
        }else{
            $this->DAO->add_game_introduce($params);
        }
        die(json_encode($this->succeed_msg("保存成功")));
    }


    //游戏下载列表
    public function game_download_list(){
        $params=$_POST;
        $dataList=$this->DAO->game_download_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("game_download_list.html");
    }


    public function game_download_add_view(){
        $channels_list=$this->DAO->get_channels_list();
        $game_list=$this->COMMON->get_game_list();
        $this->assign("channels_list",$channels_list);
        $this->assign("game_list",$game_list);
        $this->display("game_download_add.html");
    }

    public function do_game_download_add(){
        $params=$_POST;
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入标题"));
            exit;
        }
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        if(!$params['channel_id']){
            echo json_encode($this->error_msg("请选择渠道"));
            exit;
        }
        if(!$params['url']){
            echo json_encode($this->error_msg("请输入下载地址"));
            exit;
        }
        $isexist=$this->DAO->game_download_isexist($params);
        if($isexist){
            echo json_encode($this->error_msg("该游戏该渠道下载地址存在"));
            exit;
        }
        $this->DAO->do_game_download_add($params);
        echo json_encode($this->succeed_msg("添加成功","game_download_list"));
    }

    public function game_download_edit_view($id){
        $info=$this->DAO->get_game_download_info($id);
        $this->assign("info",$info);
        $this->display("game_download_edit.html");
    }

    public function do_game_download_edit(){
        $params=$_POST;
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入标题"));
            exit;
        }
        if(!$params['url']){
            echo json_encode($this->error_msg("请输入下载地址"));
            exit;
        }
        $this->DAO->do_game_download_edit($params);
        echo json_encode($this->succeed_msg("编辑成功","game_download_list"));
    }

    public function hot_game_remove(){
        echo json_encode($this->unclose_succeed_msg("移除成功"));
    }
}