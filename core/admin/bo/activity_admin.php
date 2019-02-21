<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('activity_dao');

class activity_admin extends adminBaseCore
{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new activity_dao();
    }

    public function activity_list_view(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        switch ($user_info['group_id']){
            case '1':
                $activity_list = $this->DAO->get_activity_list($this->page,$params);
                break;
            case '10':
                $activity_list = $this->DAO->get_guild_activity($this->page,$params);
                break;
            default:
                die("你没有该目录的权限,需要开启请联系管理员");
                break;
        }
        $activity_name = $this->DAO->get_activity_name();
        foreach($activity_list as $key=>$data){
            $reserve = $this->DAO->get_reserve_review($data['id'],$_SESSION['usr_id']);
            $guild = $this->DAO->get_guild_info($data['id'],$_SESSION['usr_id']);
            if($guild){
                $activity_list[$key]['act_id'] = $guild['id'];
            }
            if($reserve){
                $activity_list[$key]['status'] = $reserve['status'];
            }else{
                $activity_list[$key]['status'] = "";
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $page = $this->pageshow($this->page, "activity.php?act=list&is_del=".$params['is_del']."&title=".$params['title']."&");
        $this->assign("user_info",$user_info);
        $this->assign("params",$params);
        $this->assign("activity_name",$activity_name);
        $this->assign("activity_list",$activity_list);
        $this->assign("page_bar",$page->show());
        $this->display("activity_list.html");
    }

    public function add_view(){
        $guild_list= $this->DAO->get_guild();
        $game = $this->DAO->get_game();
        $gift_list = $this->DAO->get_gift_list();
        $this->assign("gift_list",$gift_list);
        $this->assign("guild_list",$guild_list);
        $this->assign("game_list",$game);
        $this->display("activity_add.html");
    }

    public function do_add(){
        if($_SESSION['group_id'] != 1){
            $this->error_msg("你没有权限添加活动");
        }
        if(!$_POST['title'] || !$_POST['game_id'] ||  !$_POST['undo_time'] || !$_POST['rule'] || !$_POST['start_time'] || !$_POST['end_time'] || !$_POST['share_title'] || !$_FILES['share_img']['tmp_name'] || !$_POST['share_msg'] || !$_POST['share_desc']){
            $this->error_msg("缺少必填项");
        }
        $related_guild = $this->DAO->get_related_guild($_POST['game_ac']);
        if($related_guild){
            $this->error_msg("同一游戏名缩写重复");
        }
        if($_POST['num'] && !$_POST['related_guild']){
            $this->error_msg("请选择关联公会");
        }
        if(strtotime($_POST['start_time']) > strtotime($_POST['end_time'])){
            $this->error_msg("活动结束时间不能比活动开始时间早");
        }
        if($_FILES['share_img']['tmp_name']){
            $_POST['share_img'] = $this->up_img('share_img',PRODUCT_IMG,array(),1,1,time(),0);
        }
        $act_id = $this->DAO->add_activity($_POST);
        if($_POST['num']){
            $rank = $this->DAO->get_reserve_rank($act_id,$_POST['related_guild']);
            if($rank){
                $this->DAO->update_rank($act_id,$_POST);
            }else{
                $this->DAO->add_rank($act_id,$_POST);
            }
        }
        $this->succeed_msg();
    }

    public function edit_view($id){
        $info = $this->DAO->get_activity_info($id);
        $guild_list= $this->DAO->get_guild();
        $game = $this->DAO->get_game();
        $gift_list = $this->DAO->get_gift_list();
        $this->assign("gift_list",$gift_list);
        $this->assign("guild_list",$guild_list);
        $this->assign("game_list",$game);
        $this->assign("info",$info);
        $this->display("activity_edit.html");
    }

    public function do_edit($id){
        if(!$_POST['title'] || !$_POST['game_id'] || !$_POST['undo_time'] || !$_POST['rule'] || !$_POST['start_time'] || !$_POST['end_time'] || !$_POST['share_title'] || !$_POST['game_ac'] || !$_POST['share_msg'] || !$_POST['share_desc']){
            $this->error_msg("缺少必填项");
        }
        $related_guild = $this->DAO->get_related_guild_info($_POST['game_ac'],$id);
        if($related_guild){
            $this->error_msg("同一游戏名缩写重复");
        }
        $info = $this->DAO->get_activity_info($id);
        if($info['related_guild']){
            $_POST['related_guild'] = $info['related_guild'];
        }
        if($_POST['num'] && !$_POST['related_guild']){
            $this->error_msg("请选择关联公会");
        }elseif($_POST['num'] < $info['num']){
            $this->error_msg("虚拟人数不能比开始设置的值小");
        }
        if(strtotime($_POST['start_time']) > strtotime($_POST['end_time'])){
            $this->error_msg("活动结束时间不能比活动开始时间早");
        }
        if($_FILES['share_img']['tmp_name']){
            $_POST['share_img'] = $this->up_img('share_img',PRODUCT_IMG,array(),1,1,time(),0);
        }else{
            $_POST['share_img'] = $_POST['old_share_img'];
        }
        $rank = $this->DAO->get_reserve_rank($id,$_POST['related_guild']);
        if($rank){
            if($rank['reserve_num'] != $_POST['num']){
                $_POST['num'] = $_POST['num']-$info['num']+$rank['reserve_num'];
                $this->DAO->update_rank($id,$_POST);
            }
        }elseif($_POST['num']){
           $this->DAO->add_rank($id,$_POST);
        }
        $this->DAO->update_activity($id,$_POST);
        $this->succeed_msg();
    }

    public function reserve_log(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $list = $this->DAO->get_list($_SESSION['usr_id']);
        $user_id = "";
        foreach($list as $key=>$data){
            $user_id .=$data['id'].",";
        }
        $act_list = $this->DAO->get_activity_name();
        switch ($user_info['group_id']){
            case '1':
                $reserve_log = $this->DAO->get_reserve_log("",$this->page,$params);
                break;
            case '10':
                $reserve_log = $this->DAO->get_reserve_log($user_id.$_SESSION['usr_id'],$this->page,$params);
                break;
            default:
                die("你没有该目录的权限,需要开启请联系管理员");
                break;
        }
        $page = $this->pageshow($this->page, "activity.php?act=reserve_log&activity_id=".$params['activity_id']."&start=".$params['start']."&end=".$params['end']."&ip=".$params['ip']."&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("act_list",$act_list);
        $this->assign("reserve_log",$reserve_log);
        $this->display("activity_reserve_log.html");
    }

    public function apply($id){
        $this->assign("id",$id);
        $this->display("activity_apply.html");
    }

    public function apply_save($id,$guild_id){
        $act_info = $this->DAO->get_activity_info($id);
        $guild_info = $this->DAO->get_user_info($guild_id);
        $this->DAO->insert_reserve_review($act_info,$guild_info);
        $this->succeed_msg();
    }

    public function audit_list(){
        $params = $_GET;
        if(!$_SESSION['usr_id']){
            $this->error_msg("未能获取用户信息,请重新登录");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $list = $this->DAO->get_list($_SESSION['usr_id']);
        $arr = "";
        foreach($list as $key=>$data){
            $arr .=$data['id'].",";
        }
        switch ($user_info['group_id']){
            case '1':
                $admin = 1;
                $reserve_list = $this->DAO->get_reserve_list($this->page,$params);
                break;
            case '10':
                $admin = 0;
                $reserve_list = $this->DAO->get_review($this->page,$params,$arr.$_SESSION['usr_id']);
                break;
            default:
                die("你没有该目录的权限,需要开启请联系管理员");
                break;
        }
        $activity = $this->DAO->get_activity_name();
        $page = $this->pageshow($this->page, "activity.php?act=audit_list&status=".$params['status']."&activity_id=".$params['activity_id']."&guild_id=".$params['guild_id']."&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("admin",$admin);
        $this->assign("activity_list",$activity);
        $this->assign("reserve_list",$reserve_list);
        $this->display("activity_audit_list.html");
    }

    public function audit_view($id){
        $this->assign("id",$id);
        $this->display("activity_audit_view.html");
    }

    public function do_audit($id){
        $params = $_POST;
        if(!$_SESSION['usr_id']){
            $this->error_msg("未能获取用户信息,请重新登录");
        }
        if($params['status'] == 2 && !$params['reason']){
            $this->error_msg("拒绝申请时，理由不能为空");
        }
        if($params['status'] == 3){
            $info = $this->DAO->get_reserve_info($id);
            $this->DAO->insert_reserve_list($info);
        }
        $this->DAO->update_reserve($id,$params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function audit_record($id){
        $info = $this->DAO->get_reserve($id);
        $this->assign("info",$info);
        $this->display("activity_audit_record.html");
    }

    public function detail_list(){
        $params = $_GET;
        if(!$_SESSION['usr_id']){
            $this->error_msg("未能获取用户信息,请重新登录");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        switch ($user_info['group_id']){
            case '1':
                $review_list = $this->DAO->get_review_list($this->page,$params);
                break;
            default:
                die("你没有该目录的权限,需要开启请联系管理员");
                break;
        }
        $guild = $this->DAO->get_guild();
        $activity = $this->DAO->get_activity_name();
        $page = $this->pageshow($this->page, "activity.php?act=detail&is_del=".$params['is_del']."&activity_id=".$params['activity_id']."&guild_id=".$params['guild_id']."&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("guild_list",$guild);
        $this->assign("activity_list",$activity);
        $this->assign("reserve_list",$review_list);
        $this->display("activity_detail_list.html");
    }

    public function detail_view($id){
        $info = $this->DAO->get_guild_reserve($id);
        $this->assign("info",$info);
        $this->display("activity_detail_view.html");
    }

    public function do_detail($id){
        $this->DAO->update_guild_reserve($id,$_POST);
        $this->succeed_msg();
    }

    public function log_list(){
        $params = $this->get_params($_POST,$_GET);
        $log_list = $this->DAO->get_log_list($this->page,$params);
        $page = $this->pageshow($this->page, "activity.php?act=log_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("log_list",$log_list);
        $this->display("activity_log_list.html");
    }

    public function export(){
        $dataList =  $this->DAO->get_log_list_nolimit($_GET);
        if($dataList){
            $this->master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->setTitle("预约成功日志");
        $objActSheet->setCellValue("A1", "手机号码");
        $objActSheet->setCellValue("B1", "预约平台");
        $objActSheet->setCellValue("C1", "预约来源");
        $objActSheet->setCellValue("D1", "预约时间");
        $objActSheet->setCellValue("E1", "ip地址");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit("A".$n, $info['mobile'],PHPExcel_Cell_DataType::TYPE_STRING);
            if($info['system'] == 1){
                $objActSheet->setCellValue("B".$n,"Android");
            }elseif($info['system'] == 2){
                $objActSheet->setCellValue("B".$n,"Ios");
            }
            if($info['from'] == 1){
                $objActSheet->setCellValue("C".$n,"M端");
            }elseif($info['from'] == 2){
                $objActSheet->setCellValue("C".$n,"PC端");
            }
            $objActSheet->setCellValue("D".$n, date('Y-m-d H:i:s',$info['add_time']));
            $objActSheet->setCellValue("E".$n, $info['ip']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","预约成功日志-".$str_now.'.xls');
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