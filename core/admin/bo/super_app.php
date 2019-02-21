<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('super_app_dao');

class super_app extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new super_app_dao();
    }

    public function app_list_view(){
        $list = $this->DAO->get_app_list($this->page);
        $page = $this->pageshow($this->page, "super_app.php?act=list&");
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("super_sdk/app_list.html");
    }

    public function ch_app_list(){
        $params = $_POST;
        $list = $this->DAO->get_ch_app_list($this->page,$params);
        $apps = $this->DAO->get_super_app_list();
        $channel_list = $this->DAO->get_ch_all();
        $page = $this->pageshow($this->page, "channel_app.php?act=list&");
        $this->assign("datalist", $list);
        $this->assign("channel_list", $channel_list);
        $this->assign("apps", $apps);
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->display("super_sdk/ch_app_list.html");
    }

    public function ch_list(){
        $list = $this->DAO->get_ch_open_app_list($this->page);
        foreach($list as $key =>$item){
            $money = $this->DAO->get_ch_count_moeny($item);
            $list[$key]['sum_money'] = $money['sum_money'];
            $list[$key]['balance'] = $item['recharge_count'] - $money['sum_money'];
        }
        $page = $this->pageshow($this->page, "channel_app.php?act=ch_list&");
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("super_sdk/ch_list.html");
    }

    public function app_add_view(){
        $this->display("super_sdk/app_add.html");
    }

    public function ch_app_add(){
        $super_app = $this->DAO->get_super_app_list();
        $this->assign("app", $super_app);
        $this->display("super_sdk/ch_app_add.html");
    }

    public function app_edit_view($app_id){
        $info = $this->DAO->get_super_app_info($app_id);
        $this->assign("info", $info);
        $this->display("super_sdk/app_edit.html");
    }

    public function ch_app_edit($id){
        $info = $this->DAO->get_ch_app_info($id);
        $this->assign("info", $info);
        $this->display("super_sdk/ch_app_edit.html");
    }

    public function do_app_add(){
        if(!$_POST['app_name']){
            $this->error_msg("缺少必填项");
        }
        $app_key = md5(strtotime("now").rand(1,100000));
        $this->DAO->insert_app($_POST,$app_key);
        $this->succeed_msg();
    }

    public function do_ch_app_add(){
        if(!$_POST['app_id'] || !$_POST['app_key'] || !$_POST['channel']|| !$_POST['app_name'] || !$_POST['super_id'] || !$_POST['ch_code']){
            $this->error_msg("缺少必填项");
        }
        $info = $this->DAO->get_ch_app($_POST['ch_code'],$_POST['super_id']);
        if($info){
            $this->error_msg("已存在相同的渠道代码");
        }
        $this->DAO->insert_ch_app($_POST);
        $this->succeed_msg();
    }

    public function do_app_edit($app_id){
        if(!$_POST['app_id'] || !$_POST['app_key'] || !$_POST['app_name']){
            $this->error_msg("缺少必填项");
        }
        $this->DAO->update_app($_POST, $app_id);
        $this->succeed_msg();
    }

    public function do_ch_app_edit($id){
        if(!$_POST['app_id'] || !$_POST['app_key'] || !$_POST['channel']|| !$_POST['app_name'] || !$_POST['super_id'] || !$_POST['ch_code']){
            $this->error_msg("缺少必填项");
        }
        $info = $this->DAO->get_ch_app($_POST['ch_code'],$_POST['super_id']);
        if($info && $info['id'] != $id){
            $this->error_msg("已存在相同的渠道代码");
        }
        $this->DAO->update_ch_app($_POST, $id);
        $this->succeed_msg();
    }

    public function channel_list(){
        $channel = $this->DAO->get_channel_list();
        $this->assign("channel",$channel);
        $this->display("channel_list.html");
    }

    public function channel_add(){
        $this->display("channel_add.html");
    }

    public function channel_save(){
        $this->DAO->insert_channel($_POST);
        $this->succeed_msg();
    }

    public function ch_money_edit($id){
        $info = $this->DAO->get_ch_app_info($id);
        if(!$info){
            $this->error_msg("未查询到该游戏");
        }
        $this->assign("info",$info);
        $this->display("super_sdk/ch_app_money_edit.html");
    }

    public function ch_ratio_set($id){
        $info = $this->DAO->get_ch_app_info($id);
        if(!$info){
            $this->error_msg("未查询到该游戏");
        }
        $this->assign("info",$info);
        $this->display("super_sdk/ch_app_ratio_set.html");
    }


    public function ch_money_set($id){
        $info = $this->DAO->get_ch_app_info($id);
        if(!$info){
            $this->error_msg("未查询到该游戏");
        }
        $this->assign("info",$info);
        $this->page_hash();
        $this->display("super_sdk/ch_money_set.html");
    }

    public function ch_money_save(){
        if($_POST){
            $params = $_POST;
            $ch_info = $this->DAO->get_ch_app_info($params['id']);
            if(empty($ch_info)){
                $this->error_msg("渠道信息异常！");
            }
            if($ch_info['mobile']){
                if(empty($params['warn_money'])){
                    $this->error_msg("预警参数不能为空！");
                }
                $this->DAO->update_ch_warn_money($ch_info,$params['warn_money']);
                $this->succeed_msg();
            }else{
                if(empty($params['new_mobile'])){
                    $this->error_msg("手机号不能为空！");
                }
                $params['mobile']=$params['new_mobile'];
                if(empty($params['code'])){
                    $this->error_msg("验证码不能为空！");
                }
                if(!isset($_SESSION['reg_core']) || $_SESSION['reg_core']!=$params['mobile']."_".$params['code']){
                    $this->error_msg("验证码错误。");
                }
                if(strtotime("now") - $_SESSION['last_send_time']>300){
                    $this->error_msg("验证码超时。".$_SESSION['last_send_time']);
                }
                $this->err_log(var_export($_POST,1),'ch_money_save');
                $this->DAO->update_ch_info_moblie($ch_info,$params);
                $this->succeed_msg();
            }
        }else{
            $this->error_msg("请求异常,数据接收失败！");
        }
    }

    public function ch_ratio_save(){
        if ($_POST){
            $params = $_POST;
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1){
                if (strtoupper($params['code'])!=$_SESSION['c'])
                    $this->error_msg("验证码填写错误！");
                if (!$params['app_ratio_new'])
                    $this->error_msg("比例不能为空！");
                if ($params['app_ratio_new'] > 3 || 0 > $params['app_ratio_new'])
                    $this->error_msg("比例异常！");
                if (!preg_match("/^\d{21}$/",$params['dd_id']))
                    $this->error_msg("钉钉编号非法！");
                $channle_info = $this->DAO->get_ch_app_info($params['id']);
                $this->DAO->update_ch_ratio($params['app_ratio_new'],$params['id']);
                $result = $this->DAO->insert_ch_app_ratio_log($params['id'],$params['dd_id'],$params['app_ratio_new'],$channle_info['ratio'],$user_info['id']);
                if ($result){
                    $this->succeed_msg();
                }else{
                    $this->error_msg("比例修改失败！");
                }
            }else{
                $this->error_msg("你没有此权限，请联系管理员！");
            }
        }else{
            $this->error_msg("请求异常,数据接收失败！");
        }
    }

    public function add_money($id){
        if($_SESSION['group_id'] == '1') {
           $chnnel_info = $this->DAO->get_ch_app_info($id);
           $this->assign('info', $chnnel_info);
        }else{
            $this->error_msg("你没有该目录的权限,需要开启请联系管理员.");
        }
        $this->page_hash();
        $this->display("super_sdk/add_channel_money.html");
    }

    public function do_add_money(){
        if ($_POST){
            $params = $_POST;
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1){
                if(empty($params['amount'])){
                    $this->error_msg("充值金额不能为空！");
                }
                if(!$_FILES['pay_img']['tmp_name']){
                    $this->error_msg("请上传充值截图.");
                }
                if(round((int)$params['amount']*(float)$params['ch_ratio'],2)!=$params['amount_num']){
                    $this->error_msg("上限金额计算有误。");
                }
                if(!$params['password'] || md5($params['password'])!=$user_info['pay_pwd']){
                    $this->error_msg("充值密码错误。");
                }
                if(!$params['page_hash'] || $params['page_hash']!= $_SESSION['page-hash']){
                    $this->error_msg("hash错误，请刷新后重新登录。");
                }
                $orders_id = "YJ".time().mt_rand(1000,9999);
                $img = $this->up_img('pay_img',"images/ch_img",array(),1,1,time(),0);
                $ch_money_log = $this->DAO->get_ch_money_log($params['page_hash']);
                if($ch_money_log){
                    $this->error_msg("重复的充值操作！");
                }
                $ch_app_info = $this->DAO->get_ch_app_info($params['id']);
                if(empty($ch_app_info)){
                    $this->error_msg("渠道信息异常！");
                }
                $new_total = $ch_app_info['recharge_count']+$params['amount_num'];
                $id = $this->DAO->add_channel_moeny_log($ch_app_info,$params,$img,$orders_id,$new_total);
                if(empty($id)){
                    $this->error_msg("录入失败！");
                }
                $this->DAO->update_ch_info($ch_app_info,$new_total);
                $this->succeed_msg();
            }else{
                $this->error_msg("你没有此权限，请联系管理员！");
            }
        }else{
            $this->error_msg("请求异常,数据接收失败！");
        }
    }

    public function do_ch_money_edit(){
        $params = $_POST;
        if(!$params['id']){
            $this->error_msg("未查询到该游戏");
        }
        $info = $this->DAO->get_ch_app_info($params['id']);
        if($params['is_open'] == 1 && !$params['best_money']){
            $this->error_msg("金额状态是开启时，金额数不能为空");
        }
        $this->DAO->update_ch_app_info($params,$info);
        $this->succeed_msg();
    }

    public function app_notice_edit_view($id){
        $info = $this->DAO->get_ch_app_info($id);
        $this->assign("info", $info);
        $this->display("super_sdk/app_notice.html");
    }

    public function do_app_notice_edit($id){
        if($_POST['notice_status']==1 && !$_POST['notice']){
            $this->error_msg("请填写公告");
        }
        $this->DAO->update_app_notice($_POST, $id);
        $this->succeed_msg();
    }

    public function version_edit($id){
        $info = $this->DAO->get_ch_app_info($id);
        $this->assign("info", $info);
        $this->display("super_sdk/app_update_view.html");
    }

    public function version_update($id){
        if(!$_POST['version']){
            $this->error_msg("请填写版本号。");
        }
        if(!$_POST['up_title']){
            $this->error_msg("请填写更新标题。");
        }
        if(!$_POST['up_desc']){
            $this->error_msg("请填写更新内容。");
        }
        $this->DAO->version_update($_POST, $id);
        $this->succeed_msg();
    }
}