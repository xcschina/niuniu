<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('extend_dao');

class extend_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new extend_dao();
    }

    public function extend_list_view(){
        if($_POST){
            $_SESSION['extend_search'] = $params = $_POST;
        }else{
            $params = $_SESSION['extend_search'];
        }
        $list = $this->DAO->get_list($params,$this->page);
        $game_list = $this->DAO->get_game();
        $page = $this->pageshow($this->page, "extend.php?act=list&");
        $this->assign("page_bar",$page->show());
        $this->assign("game_list",$game_list);
        $this->assign("params",$params);
        $this->assign("list",$list);
        $this->display("extend_list.html");
    }

    public function record_list_view(){
        if($_POST){
            $_SESSION['record_search'] = $params = $_POST;
        }else{
            $params = $_SESSION['extend_search'];
        }
        if(empty($params['start_time'])){
            $params['start_time']=date("Y-m-d",strtotime("-6 day"));
        }
        if(empty($params['end_time'])){
            $params['end_time']=date("Y-m-d");
        }
        $list = $this->DAO->get_record_list($params,$this->page);
        foreach ($list as $key=>$item){
            $ch_array = array('bltjrtt','jrtt','mksucxxl','bjssjrtt');
            if(in_array($item['ch_name'],$ch_array)){
                if($item['type'] == '0'){
                    $list[$key]['click'] = $this->DAO->get_cpa_click_num($item['id'],$params);
                    $list[$key]['callback'] = $this->DAO->get_cpa_callback_num($item['id'],$params);
                    $list[$key]['order'] = $this->DAO->get_cpa_order_num($item['app_id'],$params);
                }
            }else{
                if($item['type'] == '1'){
                    $list[$key]['click'] = $this->DAO->get_click_num($item['id'],$params);
                    if(strtotime($params['start_time']) <= strtotime(date('Y-m-d',time())) && strtotime($params['end_time']) >= strtotime(date('Y-m-d',time()+86400))){
                        $today_num = $this->DAO->get_today_num($item['id']);
                        if($today_num){
                            $list[$key]['click'] = $list[$key]['click'] + $today_num;
                        }
                    }
                    if(strtotime($params['start_time']) == strtotime(date('Y-m-d',time())) && strtotime($params['end_time']) == strtotime(date('Y-m-d',time()+86400))){
                        $today_num = $this->DAO->get_today_num($item['id']);
                        if($today_num){
                            $list[$key]['click'] = $today_num;
                        }
                    }
//                $check_list[$key]['order'] = $this->DAO->get_order_num($item['app_id'],$params);
                    $list[$key]['callback'] = $this->DAO->get_callback_num($item['id'],$params);
                    $list[$key]['order'] = $this->DAO->get_order_num($item['app_id'],$params);
                }elseif ($item['type'] == '0'){
                    $list[$key]['click'] = $this->DAO->get_click_num($item['id'],$params);
                    if(strtotime($params['start_time']) <= strtotime(date('Y-m-d',time())) && strtotime($params['end_time']) >= strtotime(date('Y-m-d',time()+86400))){
                        $today_num = $this->DAO->get_today_num($item['id']);
                        if($today_num){
                            $list[$key]['click'] = $list[$key]['click'] + $today_num;
                        }
                    }
                    if(strtotime($params['start_time']) == strtotime(date('Y-m-d',time())) && strtotime($params['end_time']) == strtotime(date('Y-m-d',time()+86400))){
                        $today_num = $this->DAO->get_today_num($item['ch_code'].'_'.$item['id']);
                        if($today_num){
                            $list[$key]['click'] = $today_num;
                        }
                    }

//                $check_list[$key]['order'] = $this->DAO->get_order_num($item['app_id'],$params);
                    $list[$key]['callback'] = $this->DAO->get_callback_num($item['id'],$params);
                    $list[$key]['order'] = $this->DAO->get_an_order_num($item['app_id'],$item['ch_code'],$params);
                }
            }


        }
        $game_list = $this->DAO->get_game();
        $page = $this->pageshow($this->page, "extend.php?act=record_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("game_list",$game_list);
        $this->assign("params",$params);
        $this->assign("list",$list);
        $this->display("record_list.html");
    }

    public function extend_add(){
        $game_list = $this->DAO->get_game();
        $this->assign("game_list",$game_list);
        $this->display("extend_add.html");
    }

    public function add_save(){
        $params = $_POST;
        $this->extend_params_verify($params);
        $this->DAO->insert_extend($params);
        $this->succeed_msg();
    }

    public function ad_view($id){
//        $this->open_debug();
        $params = $_POST;
        if(!$params['start_time']){
            $params['start_time'] = date('Y-m-d', strtotime('-7 days'));
        }
        if(!$params['end_time']){
            $params['end_time'] = date('Y-m-d');
        }
        $check_list = $this->DAO->get_check_list($id,$params,$this->page);
        foreach ($check_list as $key=>$item){
            $check_list[$key]['install'] = $this->DAO->get_install_num($item);
            if($params['start_time'] >= date('Y-m-d') || $params['end_time'] <= date('Y-m-d')){
                $today_num = $this->DAO->get_today_num($item['ad_id']);
                if($today_num){
                    $check_list[$key]['install'] = $check_list[$key]['install'] + $today_num;
                }
            }
            $check_list[$key]['order'] = $this->DAO->get_order_num($item['app_id'],$params);
//            $check_list[$key]['install'] = $this->DAO->get_sum_order($item);
        }
        $page = $this->pageshow($this->page, "extend.php?act=ad_view&id=".$id."&");
        $this->assign("page_bar",$page->show());
        $this->assign("list",$check_list);
        $this->assign("params",$params);
        $this->display("extend_ad_view.html");
    }

    public function extend_params_verify($params){
        $start_time ='';
        $end_time ='';
        foreach ($params as $name=>$item){
            switch(trim($name)){
                case"cpa_name":
                    if(empty($item)) $this->error_msg("对接名称不能为空.");
                    break;
                case "app_id":
                    if(empty($item)) $this->error_msg("请选择游戏.");
                    break;
                case"ch_name":
                    if(empty($item)) $this->error_msg("渠道缩写不能为空.");
                    if(!preg_match("/^[a-zA-Z\s]+$/",$item)) $this->error_msg("渠道缩写只能为英文字符.");
                    break;
                case"ch_code":
                    if(empty($item)) $this->error_msg("渠道缩写不能为空.");
                    if(preg_match("/[\x7f-\xff]/",$item)) $this->error_msg("渠道代码不能有中文字符.");
                    break;
                case"aid":
                    if(empty($item)) $this->error_msg("AID不能为空.");
                    break;
                case"cid":
                    if(empty($item)) $this->error_msg("CID不能为空.");
                    break;
//                case"url":
//                    if($item){
//                        if(!filter_var($item, FILTER_VALIDATE_URL)){
//                            $this->error_msg("请输入标准的URL.");
//                        }
//                    }
//                    break;
                case"start_time":
                    if(empty($item)) $this->error_msg("请选择起始时间！");
                    $start_time = strtotime(trim($item));
                    break;
                case"end_time":
                    if(empty($item)) $this->error_msg("请选择结束时间！");
                    $end_time =  strtotime(trim($item));
                    if($start_time >= $end_time) $this->error_msg("结束时间不能大于开始时间！");
                    break;
            }
        }
    }



    public function extend_exit($id){
        $info = $this->DAO->get_extend_info($id);
        $game_list = $this->DAO->get_game();
        $this->assign("game_list",$game_list);
        $this->assign("info",$info);
        $this->display("extend_exit.html");
    }

    public function exit_save(){
        $params = $_POST;
        $this->extend_params_verify($params);
        $this->DAO->update_extend($params);
        $this->succeed_msg();
    }

    public function device_search(){
        if($_POST){
            $param = $_POST;
            $cpa_install = $this->DAO->get_cpa_install($param);
            $this->assign("info",$cpa_install);
            $this->assign("params",$param);
        }
        $this->display("device_search.html");
    }

    public function device_del($id){
        $cpa_install = $this->DAO->get_cpa_install_by_id($id);
        if(empty($cpa_install)){
            die("未查询到该账号信息,请联系管理员");
        }
        $this->assign("info", $cpa_install);
        $this->display("device_del_view.html");
    }

    public function device_do_del(){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }
        $id = $_POST['id'];
        $cpa_install = $this->DAO->get_cpa_install_by_id($id);
        if($cpa_install){
            $this->err_log(var_export($cpa_install,1),'device_delete');
            $this->DAO->del_cpa_install($id);
            $this->succeed_msg();
        }else{
            $this->error_msg("无相关信息");
        }

    }
}