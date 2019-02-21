<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('business_stock_dao','business_inside_dao');

class business_stock_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public function __construct() {
        parent::__construct();
        $this->DAO = new business_stock_dao();
        $this->channel = array(
            '00008'=>'应用宝',
            '00009'=>'魔域混服',
            '00010'=>'官服'
        );
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $inside_dao = new business_inside_dao();
        $app_list = $inside_dao->get_app_list();
        $user_info = $inside_dao->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id'] == 1){
            $user_list = $this->DAO->get_user_info_list();
            $arr = '';
            foreach($user_list as $key=>$data){
                $arr .= $data['id'].',';
            }
            $params['user_list'] = trim($arr,',');
            $list = $this->DAO->get_stock_info_list($this->page,$params);
        }else{
            $list = $this->DAO->get_stock_list($_SESSION['usr_id'],$this->page,$params);
        }
        $list = $this->get_channel_name($list);
        if($params['app_id']){
            $service_list = $inside_dao->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_stock.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign('params',$params);
        $this->assign('service_list',$service_list);
        $this->assign('user_info',$user_info);
        $this->assign('user_list',$user_list);
        $this->assign('list',$list);
        $this->assign('app_list',$app_list);
        $this->assign('channel_list',$this->channel);
        $this->display("chamber/stock_list.html");
    }

    public function input_view($id){
        $this->assign('id',$id);
        $this->display("chamber/stock_input.html");
    }

    public function do_input(){
        $params = $_POST;
        if(!$params['stock_num'] || !$params['desc']){
            $this->error_msg('缺少必填');
        }
        if(mb_strlen($params['desc'])<5){
            $this->error_msg("备注说明至少输入5个字符");
        }
        $info = $this->DAO->get_stock_info($params['id']);
        if(!$info){
            $this->error_msg('查无此库存信息');
        }
        $params['stock_collect'] = $info['stock_collect']+$params['stock_num'];
        $params['new_stock_balance'] = $info['stock_balance']+$params['stock_num'];
        $this->DAO->insert_stock_record($params,$info,$_SESSION['usr_id']);
        $params['new_stock_collect'] = $info['stock_collect']+$params['stock_num'];
        $this->DAO->update_stock_info($params);
        $this->succeed_msg();
    }

    public function input_detail($id,$user_id){
        $record_list = $this->DAO->get_record_list($id,$this->page,$user_id);
        foreach($record_list as $item=>$info){
            $record_list[$item]['channel_name'] = $info['channel_name'] = $this->channel[$info['channel']];
        }
        $page = $this->pageshow_new($this->page,$this->DAO,"business_stock.php?act=detail_show&",20);
        $page->open_ajax("record_list_show");
        $this->assign('record_list',$record_list);
        $this->assign("page_bar", $page->show());
        $this->assign("id", $id);
        $this->assign("user_id", $user_id);
        $this->display("chamber/stock_input_detail.html");
    }

    public function detail_show(){
        if(!$_POST['user_id'] || !$_POST['id']){
            $this->error_msg("数据丢失");
        }
        $list = $this->DAO->get_record_list($_POST['id'],$this->page,$_POST['user_id']);
        foreach($list as $item=>$info){
            $list[$item]['channel_name'] = $info['channel_name'] = $this->channel[$info['channel']];
        }
        $page = $this->pageshow_new($this->page,$this->DAO,"business_stock.php?act=detail_show&",20);
        $page->open_ajax("record_list_show");
        $data = array("page"=>$page->show(),"data_list"=>$list);
        $this->succeed_msg(json_encode($data,JSON_UNESCAPED_UNICODE));
    }


    public function initial_value(){
        //以防超时不能插入数据库
        set_time_limit(0);
        $result = array('code'=>0,'msg'=>'网络出错');
        $stock_list = $this->DAO->get_stock_all_list();
        if($stock_list){
            $result['msg'] = "初始值已存在，无重新获取";
            die(json_encode($result));
        }
        $inside_dao = new business_inside_dao();
        $app_list = $inside_dao->get_app_list();
        $user_list = $this->DAO->get_user_list();
        foreach($user_list as $item=>$info){
            foreach($app_list as $key=>$data){
                $service_list = $inside_dao->get_service_list($data['app_id']);
                foreach($service_list as $k=>$d){
                    $this->DAO->insert_stock_info($info['id'],$data,$d);
                }
            }
        }
        $result['code'] = 1;
        $result['msg'] = '获取成功';
        die(json_encode($result));
    }

    public function admins(){
        $params = $this->get_params($_POST,$_GET);
        $inside_dao = new business_inside_dao();
        $app_list = $inside_dao->get_app_list();
        $user_info = $inside_dao->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id'] == 1){
            $user_list = $this->DAO->get_user_info_list();
        }elseif($user_info['group_id'] == 15 && !$user_info['p2'] && !$user_info['p1']){
            $user_list = $this->DAO->get_user_info_list($user_info['id']);
        }else{
            die("您没有该目录的权限，请联系管理员。");
        }
        $arr = '';
        foreach($user_list as $key=>$data){
            $arr .= $data['id'].',';
        }
        $admin_list = $this->DAO->get_stock_admin_list($this->page,$params,trim($arr,','));
        $admin_list = $this->get_channel_name($admin_list);
        if($params['app_id']){
            $service_list = $inside_dao->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_stock.php?act=admins&");
        $this->assign("page_bar", $page->show());
        $this->assign("user_info",$user_info);
        $this->assign("user_list",$user_list);
        $this->assign("service_list",$service_list);
        $this->assign("channel_list",$this->channel);
        $this->assign("app_list",$app_list);
        $this->assign("params",$params);
        $this->assign("admin_list",$admin_list);
        $this->display("chamber/stock_admin.html");
    }

    public function group_info($id){
        $info = $this->DAO->get_stock_info($id);
        $user_list = $this->DAO->get_user_info_list($info['user_id']);
        $arr = '';
        foreach($user_list as $key=>$data){
            $arr .= $data['id'].',';
        }
        if($arr){
            $group_info = $this->DAO->get_stock_group_info($info,trim($arr,','));
            $group_info = $this->get_channel_name($group_info);
        }
        $this->assign('group_info',$group_info);
        $this->display("chamber/stock_group_info.html");
    }

    public function group_input($id){
        $info = $this->DAO->get_stock_info($id);
        $this->assign('info',$info);
        $this->display("chamber/stock_group_input.html");
    }

    public function do_group_input(){
        $params = $_POST;
        if(!$params['stock_num'] || $params['stock_num'] ==0 || !$params['desc']){
            $this->error_msg('缺少必填');
        }
        if(mb_strlen($params['desc'])<5){
            $this->error_msg("备注说明至少输入5个字符");
        }
        $info = $this->DAO->get_stock_info($params['id']);
        if(!$info){
            $this->error_msg('查无此库存信息');
        }
        if($info['stock_balance']+$params['stock_num']<0){
            $this->error_msg("组长代币不足，无法回收");
        }
        $group_info = $this->DAO->get_group_stock_info($info,$_SESSION['usr_id']);
        if($group_info['stock_balance'] - $params['stock_num'] <0){
            $this->error_msg("您的该区服库存余额不足，无法下拨库存");
        }
        $params['stock_collect'] = $info['stock_collect']+$params['stock_num'];
        $params['new_stock_balance'] = $info['stock_balance']+$params['stock_num'];
        $this->DAO->insert_stock_record($params,$info,$_SESSION['usr_id']);
        $params['new_stock_collect'] = $info['stock_collect']+$params['stock_num'];
        $this->DAO->update_stock_info($params);
        if($params['stock_num'] > 0){
            $params['desc'] = "下拨库存给‘".$info['real_name']."’商会";
        }else{
            $params['desc'] = "回收‘".$info['real_name']."’商会代币库存";
        }
        $group_info['new_stock_collect'] = $group_info['stock_collect'];
        $params['stock_collect'] = $group_info['stock_collect'];
        $group_info['stock_collect'] = $group_info['stock_balance']-$params['stock_num'];
        $group_info['new_stock_balance'] = $group_info['stock_balance']-$params['stock_num'];
        $params['new_stock_balance'] = $group_info['stock_balance']-$params['stock_num'];
        $params['id'] = $group_info['id'];
        $this->DAO->insert_stock_record($params,$group_info,$_SESSION['usr_id']);
        $this->DAO->update_stock_info($group_info);
        $this->succeed_msg();
    }

    public function record_list(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->get_user_record_list($params,$this->page,$_SESSION['usr_id']);
        $data_list = $this->get_channel_name($list);
        $page = $this->pageshow($this->page, "business_stock.php?act=record_list&");
        $this->assign("page_bar", $page->show());
        $this->assign('dataList',$data_list);
        $this->display('chamber/stock_recode_list.html');
    }

    public function get_channel_name($data_list){
        foreach($data_list as $key=>$data){
            $data_list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        return $data_list;
    }
}