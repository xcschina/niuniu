<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('account_admin_dao','menu_admin_dao','app_admin_dao','game_admin_dao','service_admin_dao','business_stock_dao','business_inside_dao');

class account_admin extends adminBaseCore{
    public $DAO;
    public $payment;
    public function __construct() {
        parent::__construct();
        $this->DAO = new account_admin_dao();
        $this->payment = array(
            '10001' => array('title'=>'建设银行福建福州天福支行','name'=>'林颖','account'=>'6214 9918 2040 2468','type'=>'1'),
            '10002' => array('title'=>'支付宝','name'=>'林颖','account'=>'18959197777','type'=>'1'),
            '10003' => array('title'=>'支付宝','name'=>'林颖','account'=>'13509339049','type'=>'1'),
            '10004' => array('title'=>'中国工商银行福州鼓楼支行','name'=>'福建牛牛网络信息技术有限公司','account'=>'1402 0232 0960 0340 532','type'=>'2'),
            '10005' => array('title'=>'中国工商银行澄迈软件园支行','name'=>'海南牛牛网络信息技术有限公司','account'=>'2201 0805 0910 0051 832','type'=>'2'),
        );
    }

    public function account_list_view(){
        if(!$_SESSION['usr_id']){
            header("location:login.php?act=login");
        }
        //权限判断
        $this->user_permissions($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '10') {
            $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            $list = $this->DAO->get_account_by_group($_SESSION['usr_id'],$this->page,$_GET);
        }elseif($_SESSION['group_id'] == '1'){
            $guild_list = $this->DAO->get_all_guild_list();
            $list = $this->DAO->get_account_list($this->page,$_GET);
        }else{
            $list = $this->DAO->get_account_by_group($_SESSION['usr_id'],$this->page,$_GET);
        }
        foreach($list as $key=>$items){
            if(!empty($items['p1'])){
                $p1_info = $this->DAO->get_user_info($items['p1']);
                if($p1_info){
                    $list[$key]['p1_name']=$p1_info['account'];
                }
            }
            if(!empty($items['p2'])){
                $p2_info = $this->DAO->get_user_info($items['p2']);
                if($p2_info){
                    $list[$key]['p2_name']=$p2_info['account'];
                }
            }
        }
        $group_list = $this->DAO->get_group_list();
        $page = $this->pageshow($this->page, "account.php?act=list&type=".$_GET['type']."&channel=".$_GET['channel']."&user_id=".$_GET['user_id']."&group_id=".$_GET['group_id']."&account=".$_GET['account'].'&real_name='.$_GET['real_name']."&");
        $this->assign("guild_list", $guild_list);
        $this->assign("group_list", $group_list);
        $this->assign("params", $_GET);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->assign("user_id", $_GET["user_id"]);
        $this->display("account_list.html");
    }

    private function user_permissions($user_id){
        $user_info = $this->DAO->get_user_info($user_id);
        if($user_info['group_id']=='1'){
            $this->assign("status", '1');
        }elseif($user_info['group_id']=='10'){
            if($user_info['p1']=='0' and $user_info['p2']=='0'){
                $this->assign("status", '101');
            }elseif($user_info['p2']=='0' and $user_info['p1']!='0'){
                $this->assign("status", '102');
            }else{
                $this->assign("status", '103');
            }
        }else{
            $this->assign("status",'2');
        }
    }

    public function account_add_view(){
        $groups = $this->DAO->get_groups();
        $ch_list = $this->DAO->get_ch_list();
        $this->assign("ch_list", $ch_list);
        $this->assign("groups", $groups);
        $this->display("account_add.html");
    }

    public function account_edit_view($id){
        $groups = $this->DAO->get_groups();
        $info = $this->DAO->get($id);
        $ch_list = $this->DAO->get_ch_list();
        $this->assign("ch_list", $ch_list);
        $this->assign("info", $info);
        $this->assign("groups", $groups);
        $this->display("account_edit.html");
    }

    public function account_pwd_view($id){
        $info = $this->DAO->get($id);
        $this->assign("info", $info);
        $this->display("account_pwd_edit.html");
    }

    public function account_group_view(){
        $info = $this->DAO->get_groups();
        $this->assign("dataList", $info);
        $this->display("account_group_list.html");
    }

    public function add_groups(){
        $this->display("account_group_add.html");
    }

    public function do_add_groups(){
        $params = $_POST;
        if(!$params['group_name'] || !$params['ch_name'] || !$params['status']){
            $this->error_msg('缺少必填项');
        }
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $params['group_name'])>0){
            $this->error_msg("组代码不能包含汉字");
        }
        $groups_name = $this->DAO->get_groups_name($params['ch_name']);
        if($groups_name){
            $this->error_msg("该组名已存在，请重新填写");
        }
        $ch_name = $this->DAO->get_groups_ch_name($params['group_name']);
        if($ch_name){
            $this->error_msg("该组代码已存在，请重新填写");
        }
        $this->DAO->insert_admin_groups($params);
        $this->succeed_msg();
    }

    public function account_app_view($id){
        $app_dao = new app_admin_dao();
        $info = $this->DAO->get($id);
        $apps = $app_dao->get_all_app();
        if($info['p2']=='0' && $info['p1']=='0'){
            $this->assign("apps", $apps);
        }elseif(!empty($info['p1'])){
            $this->get_new_appList($info['p1'],$apps);
        }
        $info['apps'] = explode(",", $info['apps']);

        $this->assign("info", $info);
        $this->display("account_app_edit.html");
    }

    public function account_rh_app_view($id){
        $app_dao = new app_admin_dao();
        $info = $this->DAO->get($id);
        $apps = $app_dao->get_all_rh_app();
        if($info['p2']=='0' && $info['p1']=='0'){
            $this->assign("apps", $apps);
        }elseif(!empty($info['p1'])){
            $this->get_rh_new_appList($info['p1'],$apps);
        }
        $info['rh_apps'] = explode(",", $info['rh_apps']);
        $this->assign("info", $info);
        $this->display("account_app_edit_rh.html");
    }

    public function get_new_appList($guild_id,$apps){
        $app_list=array();
        $guild_info = $this->DAO->get($guild_id);
        $guild_info['apps'] = explode(",", $guild_info['apps']);
        foreach($apps as $key=>$data){
            if(in_array($data['app_id'],$guild_info['apps'])){
                array_push($app_list,$data);
            }
        }
        $this->assign("apps", $app_list);
    }

    public function get_rh_new_appList($guild_id,$apps){
        $app_list=array();
        $guild_info = $this->DAO->get($guild_id);
        $guild_info['rh_apps'] = explode(",", $guild_info['rh_apps']);
        foreach($apps as $key=>$data){
            if(in_array($data['app_id'],$guild_info['rh_apps'])){
                array_push($app_list,$data);
            }
        }
        $this->assign("apps", $app_list);
    }

    public function do_account_rh_app($id){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }
        $apps = implode(",", $_POST['app']);
        $this->DAO->update_user_rh_apps($id, $apps);
        $this->succeed_msg();
    }

    public function account_group_menu_view($id){
        $info = $this->DAO->get_group($id);

        $menu_dao = new menu_admin_dao();
        $menu_list = $menu_dao->get_cate_menu(0);
        foreach ($menu_list as $k=>$v){
            $sub_list = $menu_dao->get_cate_menu($v['id']);
            $menu_list[$k]['sub_list'] = $sub_list;
            foreach ($sub_list as $kk=>$vv){
                $child_list = $menu_dao->get_cate_menu($vv['id']);
                $menu_list[$k]['sub_list'][$kk]['child_list'] = $child_list;
            }
        }
        $modules = explode(",", $info['module']);
        $this->assign("info", $info);
        $this->assign("menu_list", $menu_list);
        $this->assign("modules", $modules);
        $this->display("account_group_menu_edit.html");
    }

    public function account_menu_view($id){
        $info = $this->DAO->get($id);

        $menu_dao = new menu_admin_dao();
        $menu_list = $menu_dao->get_cate_menu(0);
        foreach ($menu_list as $k=>$v){
            $sub_list = $menu_dao->get_cate_menu($v['id']);
            $menu_list[$k]['sub_list'] = $sub_list;
            foreach ($sub_list as $kk=>$vv){
                $child_list = $menu_dao->get_cate_menu($vv['id']);
                $menu_list[$k]['sub_list'][$kk]['child_list'] = $child_list;
            }
        }
        $user_modules = $this->DAO->get_user_modules($id);
        $modules = explode(",", $user_modules['module']);
        $this->assign("info", $info);
        $this->assign("menu_list", $menu_list);
        $this->assign("modules", $modules);
        $this->display("account_menu_edit.html");
    }

    public function do_account_edit($id){
        if(!$_POST['id'] || !$_POST['account'] || !$_POST['real_name']){
            $this->error_msg("缺少必填项");
        }
        if($_POST['group_id'] == '11' && !$_POST['ch_id']){
            $this->error_msg("必须选择关联渠道.");
        }
        if($_POST['email']){
            if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $_POST['email'])){
                $this->error_msg("邮箱格式出错.");
            }
        }
        $this->DAO->update_account($_POST, $id);
        $this->succeed_msg();
    }

    public function do_account_add(){
        if(!$_POST['user_code'] || !$_POST['account'] || !$_POST['real_name'] || !$_POST['usr_pwd']){
            $this->error_msg("缺少必填项");
        }
        if($_POST['group_id'] == '11' && !$_POST['ch_id']){
            $this->error_msg("必须选择关联渠道.");
        }
        if(preg_match("/[\x7f-\xff]/", $_POST['user_code'])){
            $this->error_msg("代码不能含中文.");
        }
        if($this->DAO->check_account($_POST)){
            $this->error_msg("账号或代码已被使用");
        }
        if($_POST['email']){
            if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $_POST['email'])){
                $this->error_msg("邮箱格式出错.");
            }
        }
        $_POST['usr_pwd'] = md5($_POST['usr_pwd']);
        $this->DAO->insert_account($_POST);
        $this->succeed_msg();
    }

    public function account_add_guild(){
        if(!$_SESSION['usr_id']){
            die("未能获取用户信息,请重新登录");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(empty($user_info)){
            die("未查询到你的账号,请联系管理员");
        }
        if($user_info['group_id'] == '1') {
            $guild_list = $this->DAO->get_admin_guild_list();
            $this->assign("guild_list", $guild_list);
        }else if($user_info['group_id'] == '10') {
            $guild_list = $this->DAO->get_guild_list($user_info['id']);
            $this->assign("guild_list", $guild_list);
        }
        $this->assign("user_info", $user_info);
        $this->display("account_add_guild.html");
    }

    public function get_min_guild(){
        $guild_id = $_POST['guild_id'];
        $data_list = $this->DAO->get_guild_list($guild_id);
        echo json_encode($data_list);
    }

    public function do_add_guild(){
        if(!$_SESSION['usr_id']){
            $this->error_msg("未能获取用户信息,请重新登录");
        }
        if(!$_POST['user_code'] || !$_POST['account'] || !$_POST['real_name'] || !$_POST['usr_pwd']){
            $this->error_msg("缺少必填项");
        }
        if(!$_POST['status']){
            $this->error_msg("未获取到公会类型,请刷新后,重新提交.");
        }
        if(preg_match("/[\x7f-\xff]/", $_POST['user_code'])){
            $this->error_msg("代码不能含中文.");
        }
        if($this->DAO->check_account($_POST)){
            $this->error_msg("账号或代码已被使用");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(empty($user_info)){
            $this->error_msg("未查询到你的账号,请联系管理员");
        }
        $_POST['usr_pwd'] = md5($_POST['usr_pwd']);
        $this->guild_verify($user_info,$_POST);
        $this->succeed_msg();
    }

    private function guild_verify($info,$params){
        //验证是否公会组
        switch ($info['group_id']){
            case '1':
                $this->add_admin_guild($params);
                break;
            case '10':
                $this->add_guild($info,$params);
                break;
            default:
                $this->error_msg("你的账号目前没有添加公会的权限,请联系管理员.");
                break;
        }
    }

    private function chamber_verify($info,$params){
        //验证是否商会组
        switch ($info['group_id']){
            case '1':
                $this->add_chamber_guild($params);
                break;
            case '15':
                $this->add_chamber($info,$params);
                break;
            default:
                $this->error_msg("你的账号目前没有添加商会的权限,请联系管理员.");
                break;
        }
    }

    public function add_chamber_guild($params){
        $params['ch_id']='';
        switch ($params['status']){
            case '1':
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->add_stock_info($user_id);
                break;
            case '2':
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'商会管理层'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->DAO->update_guild($user_id,$params['middle_guild']);
                $this->add_stock_info($user_id);
                break;
            case '3':
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'商会管理层'名称");
                }
                if(!$params['min_guild']){
                    $this->error_msg("请选择'商会组长'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->DAO->update_guild($user_id,$params['min_guild'],$params['middle_guild']);
//                $this->add_stock_info($user_id);
                break;
            default:
                $this->error_msg("添加失败,未知的商会类型1.");
                break;
        }
    }

    public function add_admin_guild($params){
        $params['ch_id']='';
        switch ($params['status']){
            case '1':
                $this->DAO->insert_account($params);
                break;
            case '2':
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'公会组'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                $this->DAO->update_guild($user_id,$params['middle_guild']);
                break;
            case '3':
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'公会组'名称");
                }
                if(!$params['min_guild']){
                    $this->error_msg("请选择'子公会组'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                $this->DAO->update_guild($user_id,$params['min_guild'],$params['middle_guild']);
                break;
            default:
                $this->error_msg("添加失败,未知的公会类型2.");
                break;
        }
    }

    public function add_chamber($info,$params){
        $params['ch_id']='';
        if($info['p1']=='0' && $info['p2']=='0'){
            if($params['status']=='1'){
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->DAO->update_guild($user_id,$info['id']);
                //增加商会代币库存
                $this->add_stock_info($user_id);
            }elseif($params['status']=='2'){
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'商会管理层'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->DAO->update_guild($user_id,$params['middle_guild']);
                //增加商会代币库存
                $this->add_stock_info($user_id);
            }elseif($params['status'] == '3'){
                if(!$params['min_guild']){
                    $this->error_msg("请选择'商会组长'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                //增加关联
                $this->DAO->insert_relation_tb($_POST,$user_id);
                //增加游戏分配记录
                $this->DAO->insert_business_game($user_id);
                $this->DAO->update_guild($user_id,$params['min_guild'],$info['id']);
                //增加商会代币库存
//                $this->add_stock_info($user_id);
            }else{
                $this->error_msg("添加失败,未知的商会类型3.");
            }
        }elseif($info['p2']=='0' && $params['status']=='3'){
            $user_id = $this->DAO->insert_account($params);
            //增加关联
            $this->DAO->insert_relation_tb($_POST,$user_id);
            //增加游戏分配记录
            $this->DAO->insert_business_game($user_id);
            $this->DAO->update_guild($user_id,$info['id'],$info['p1']);
            //增加商会代币库存
//            $this->add_stock_info($user_id);
        }else{
            $this->error_msg("无添加权限,请联系管理员");
        }
}

    public function add_stock_info($user_id){
        $inside_dao = new business_inside_dao();
        $app_list = $inside_dao->get_app_list();
        $stock_dao = new business_stock_dao();
        foreach($app_list as $key=>$data){
            $service_list = $inside_dao->get_service_list($data['app_id']);
            foreach($service_list as $k=>$d){
                $stock_dao->insert_stock_info($user_id,$data,$d);
            }
        }
    }

    public function add_guild($info,$params){
        $params['ch_id']='';
        if($info['p1']=='0' && $info['p2']=='0'){
            if($params['status']=='1'){
                $user_id = $this->DAO->insert_account($params);
                $this->DAO->update_guild($user_id,$info['id']);
            }elseif($params['status']=='2'){
                if(!$params['middle_guild']){
                    $this->error_msg("请选择'公会组'名称");
                }
                $user_id = $this->DAO->insert_account($params);
                $this->DAO->update_guild($user_id,$params['middle_guild'],$info['id']);
            }else{
                $this->error_msg("添加失败,未知的公会类型4.");
            }
        }elseif($info['p2']=='0' && $params['status']=='1'){
            $user_id = $this->DAO->insert_account($params);
            $this->DAO->update_guild($user_id,$info['id'],$info['p1']);
        }else{
            $this->error_msg("无添加权限,请联系管理员");
        }
}

    public function do_account_password($id){
        if(!$_POST['password'] || !$_POST['re_pwd']){
            $this->error_msg("缺少必填项");
        }

        $this->DAO->update_account_pwd($_POST, $id);
        $this->succeed_msg();
    }

    public function do_account_menu($id){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }

        $menus = implode(",", $_POST['menu']);
        foreach ($_POST['menu'] as $k=>$v){
            $menu = $this->DAO->get_menu($v);
            if($menu['pid']!=0 && !in_array($menu['pid'], $_POST['menu'])){
                $menus.= ",".$menu['pid'];
            }
        }

        $user_modules = $this->DAO->get_user_modules($id);
        if(!$user_modules){
            $this->DAO->insert_user_permision($id, $menus);
        }else{
            $this->DAO->update_user_permision($id, $menus);
        }
        $this->succeed_msg();
    }

    public function do_account_group_menu($id){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }
        $menus = implode(",", $_POST['menu']);
        foreach ($_POST['menu'] as $k=>$v){
            $menu = $this->DAO->get_menu($v);
            if($menu['pid']!=0 && !in_array($menu['pid'], $_POST['menu'])){
                $menus.= ",".$menu['pid'];
            }
        }
//        $group = $this->DAO->get_group_list($id);
//        $group_id = "";
//        foreach($group as $key=>$data){
//            $group_id .= $data['id'].",";
//        }
//        $this->DAO->update_group_permision($id, $menus,rtrim($group_id,","));
        $this->DAO->update_group_permision($id,$menus);
        $this->succeed_msg();
    }

    public function do_account_app($id){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }
        $apps = implode(",", $_POST['app']);
        $user_info = $this->DAO->get_user_apps($id);
        $this->DAO->update_user_apps($id, $apps);
        $this->succeed_msg();
    }

    public function del_account($id){
        $user_info = $this->DAO->get_user_info($id);
        if(empty($user_info)){
            die("未查询到该账号信息,请联系管理员");
        }
        $this->assign("user_info", $user_info);
        $this->display("account_del_view.html");
    }

    public function do_del(){
        if(!$_POST['id']){
            $this->error_msg("缺少必填项");
        }
        $this->DAO->del_user($_POST['id']);
        $this->succeed_msg();
    }

    public function add_img($id){
        $info = $this->DAO->get_user_info($id);
        $this->assign("info",$info);
        $this->display("account_add_img.html");
    }

    public function do_add_img($id){
        if(!$_POST['guild_name']){
            $this->error_msg("公会名称不能为空");
        }
        if($_FILES['img']['tmp_name']){
            $_POST['img'] = $this->up_img('img',PRODUCT_IMG,array(),1,1,time(),0);
            $this->DAO->update_account_img($id,$_POST);
        }elseif($_POST['old_img']){
            $_POST['img'] = $_POST['old_img'];
            $this->DAO->update_account_img($id,$_POST);
        }else{
            $this->error_msg("请上传头像");
        }
        $this->succeed_msg();
    }

    public function batch_view(){
        if($_SESSION['group_id'] != 1){
            die("你的目前没有此权限,请联系管理员");
        }
        $info = $this->DAO->get_groups();
        $list = $this->DAO->get_list($_GET);
        $this->assign("params",$_GET);
        $this->assign("dataList", $info);
        $this->assign("list",$list);
        $this->display("account_batch_view.html");
    }

    public function batch_set(){
        $menu_dao = new menu_admin_dao();
        $menu_list = $menu_dao->get_cate_menu(0);
        foreach ($menu_list as $k=>$v){
            $sub_list = $menu_dao->get_cate_menu($v['id']);
            $menu_list[$k]['sub_list'] = $sub_list;
            foreach ($sub_list as $kk=>$vv){
                $child_list = $menu_dao->get_cate_menu($vv['id']);
                $menu_list[$k]['sub_list'][$kk]['child_list'] = $child_list;
            }
        }
        $this->assign("guild_id",$_GET['guild_id']);
        $this->assign("menu_list", $menu_list);
        $this->display("account_batch_set.html");
    }

    public function batch_save(){
        if(!$_GET['guild_id']){
            $this->error_msg("请选择用户");
        }
        $guild_id = explode(",",$_GET['guild_id']);
        foreach($guild_id as $key=>$data){
            $permissions = $this->DAO->get_user_modules($data);
            foreach($_POST['menu'] as $k=>$d){
                $module = in_array($d,explode(",",$permissions['module']));;
                if($module){
                    continue;
                }else{
                    $permissions['module'] = $permissions['module'].",".$d;
                }
            }
            $permissions['module'] = ltrim($permissions['module'],",");
            $this->DAO->update_permision($permissions);
        }
        $this->succeed_msg();
    }

    public function guild_menu(){
        echo  "开始时间：".time();
        $menu = [2,100,101,102,103];
        $guild_list = $this->DAO->get_guild();
        foreach($guild_list as $key=>$data){
            foreach ($menu as $k=>$d){
                $module = in_array($d,explode(",",$data['module']));
                if($module){
                    continue;
                }else{
                    $data['module'] = $data['module'].",".$d;
                }
            }
            $data['module'] = ltrim($data['module'],",");
        $this->DAO->update_permision($data);
        }
        die("结束时间：".time());
    }

    /*********商会权限管理*********/
    public function ccm_list_view(){
        if(!$_SESSION['usr_id']){
            header("location:login.php?act=login");
        }
        $params = $this->get_params($_POST,$_GET);
        //权限判断
        $this->user_permissions($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1') {
            $guild_list = $this->DAO->merchant_list();
            $list = $this->DAO->get_merchant_info($params,$this->page);
        }elseif($_SESSION['group_id'] == '15'){
            $guild_list = $this->DAO->get_chamber_guild_code($_SESSION['usr_id'],15);
            $list = $this->DAO->get_chamber_info($_SESSION['usr_id'],$this->page,$params,15);
        }elseif($_SESSION['group_id'] == '14'){
            $guild_list = $this->DAO->get_chamber_guild_code($_SESSION['usr_id'],14);
            $list = $this->DAO->get_chamber_info($_SESSION['usr_id'],$this->page,$params,14);
        }else{
            die("您没有该目录的权限，请联系管理员。");
        }
        foreach($list as $key=>$items){
            if(!empty($items['p1'])){
                $p1_info = $this->DAO->get_user_info($items['p1']);
                if($p1_info){
                    $list[$key]['p1_name']=$p1_info['account'];
                }
            }
            if(!empty($items['p2'])){
                $p2_info = $this->DAO->get_user_info($items['p2']);
                if($p2_info){
                    $list[$key]['p2_name']=$p2_info['account'];
                }
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $page = $this->pageshow($this->page, "ccm_account.php?act=list&");
        $this->assign("guild_list", $guild_list);
        $this->assign("user_info", $user_info);
        $this->assign("params", $params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->assign("user_id", $params["user_id"]);
        $this->display("chamber/account_list.html");
    }

    public function ccm_add_view(){
        $groups = $this->DAO->get_groups();
        $ch_list = $this->DAO->get_ch_list();
        $this->assign("ch_list", $ch_list);
        $this->assign("groups", $groups);
        $this->display("chamber/account_add.html");
    }

    public function ccm_do_add(){
        if(!$_POST['account'] || !$_POST['real_name'] || !$_POST['usr_pwd']){
            $this->error_msg("缺少必填项");
        }
        if($this->DAO->verify_account($_POST)){
            $this->error_msg("账号已被使用");
        }
        $_POST['usr_pwd'] = md5($_POST['usr_pwd']);
        $_POST['user_code']='';
        $_POST['ch_id']='';
        $user_id = $this->DAO->insert_account($_POST);
        //增加关联
        $this->DAO->insert_relation_tb($_POST,$user_id);
        //增加游戏分配记录
        $this->DAO->insert_business_game($user_id);
        $this->succeed_msg();
    }

    public function recharge(){
        $this->open_debug();
        if($_SESSION['group_id'] == '1'){
            $merchant_list = $this->DAO->merchant_list();
        }else if($_SESSION['group_id']!='14'){
            $this->error_msg("你没有该目录的权限,需要开启请联系管理员.");
            exit();
        }
        $this->page_hash();
        $this->assign('merchant_list', $merchant_list);
        $this->display("chamber/add_money_view.html");
    }

    public function do_recharge(){
        $params = $_POST;
        if(!$params['page_hash'] || $params['page_hash']!=$_SESSION['page-hash']){
            $this->error_msg("非法的操作");
        }
        if(!$params){
            $this->error_msg("网络请求错误.");
        }
        if(!$params['user_id'] || empty($params['user_id'])){
            $this->error_msg("请选择充值用户.");
        }
        if(!$params['amount'] || empty($params['amount'])){
            $this->error_msg("请输入充值金额.");
        }
        if(!$_FILES['pay_img']['tmp_name']){
            $this->error_msg("请上传充值截图.");
        }
        $img = $this->up_img('pay_img',"images/ccm_pay_img",array(),1,1,time(),0);
        $this->DAO->insert_recharge_log($img,$params);
        $this->succeed_msg();
    }
    public function app_service_view($id){
        $game_service_list = $this->DAO->get_business_game($id);
        $game_service_list['game_list']?$game_list = explode(",",$game_service_list['game_list']):$game_list = array();
        $game_admin_dao = new game_admin_dao();
        $game_all = $game_admin_dao->get_all_app();
        foreach($game_all as $key=>$data){
            if($data['chamber_type'] == 1){
                unset($game_all[$key]);
            }
        }
        $this->assign("game_arr",$game_list);
        $this->assign("games",$game_all);
        $this->assign("user_id",$id);
        $this->assign("user_info",$game_service_list);
        $this->display("chamber/app_service_view.html");
    }
    public function service_show(){
        if(!$_POST['user_id']){
            $this->error_msg("数据丢失");
        }
        if ($_POST['game_list']){
            $game_list = $_POST['game_list'];
            $service_admin_dao = new service_admin_dao();
            $service_list = $service_admin_dao->get_services_by_game_id($game_list,$this->page,$_POST['user_id']);
        }else{
            $service_list = array();
        }
        if (!empty($service_list)){
            $game_service_list = $this->DAO->get_business_game($_POST['user_id']);
            $game_service_list['service_list']?$service_list_check = explode(",",$game_service_list['service_list']):$service_list_check = array();
        }else{
            $service_list_check = array();
        }
        $page = $this->pageshow_new($this->page,$service_admin_dao,"ccm_account.php?act=service_show&",10);
        $page->open_ajax("service_list_show");
        $data = array("page"=>$page->show(),"service_list"=>$service_list,"service_list_check"=>$service_list_check);
        $this->succeed_msg(json_encode($data,JSON_UNESCAPED_UNICODE));
    }
    public function service_save(){
        if ($_POST['user_key']){
            isset($_POST['game_list'])?$game_list = $_POST['game_list']:$game_list = array();
            isset($_POST['service_list'])?$service_list = $_POST['service_list']:$service_list = array();
            $user_id = $_POST['user_key'];
            $service_admin_dao = new service_admin_dao();
            $game_service_list = $this->DAO->get_business_game($user_id);
            if(!$game_service_list){
                $user_info = $this->DAO->get_user_info($user_id);
                if($user_info['group_id'] != '14' && $user_info['group_id'] != '15'){
                    $this->error_msg('数据丢失');
                }else{
                    $this->DAO->insert_business_game($user_id);
                }
            }
            $game_service_list['service_list']?$service_list_old = explode(",",$game_service_list['service_list']):$service_list_old = array();
            if (empty($game_list)){
                $game_list_new = array();
                $service_list_new = array();
                $service_list_page = array();
                $service_list_all = array();
            }else{
                $game_list_new = $game_list;
                if ($_POST['page_key']){
                    $page = $_POST['page_key'];
                    $service_list_all = $service_admin_dao->get_services_by_game_id_all(implode(",",$game_list),$user_id);
                    $service_res = $service_admin_dao->get_services_by_game_id(implode(",",$game_list),$page,$user_id);
                    $service_list_page = array();
                    foreach ($service_res as $val){
                        if (in_array($val['id'],$service_list)){
                            array_push($service_list_page,array("flag"=>$val['id'],"is_check"=>1,"service_type"=>$val['service_type'],"is_indservice"=>$val['is_indservice']));
                        }else{
                            array_push($service_list_page,array("flag"=>$val['id'],"is_check"=>0,"service_type"=>$val['service_type'],"is_indservice"=>$val['is_indservice']));
                        }
                    }
                    if (empty($service_list_old)){
                        $service_list_new = $service_list;
                    }else{
                        $service_list_new = $service_list_old;
                        foreach ($service_list_page as $value){
                            if (in_array($value['flag'],$service_list_old)){
                                if ($value['is_check']===0){
                                    //剔除这个数组元素
                                    array_splice($service_list_new,array_search($value['flag'],$service_list_new),1);
                                }
                            }else{
                                if ($value['is_check']===1){
                                    //添加这个数组元素
                                    array_push($service_list_new,$value['flag']);
                                }
                            }
                        }
                    }
                }else{
                    $service_list_new = array();
                    $service_list_page = array();
                    $service_list_all = array();
                }
            }
            //写入最终结果
            $service_list_all_id = array();
            foreach ($service_list_all as $v){
                array_push($service_list_all_id,$v['id']);
            }
            $service_list_del = array();
            foreach ($service_list_new as $v_key=>$v_new){
                if (!in_array($v_new,$service_list_all_id)){
                    array_push($service_list_del,$v_new);
                    unset($service_list_new[$v_key]);
                }
            }
            if (!empty($service_list_del)){
                $data_del = $service_admin_dao->get_services_by_services_id_all(implode(",",$service_list_del));
                foreach ($data_del as $v_data_del){
                    if ($v_data_del['service_type']==2){
                        array_push($service_list_page,array("flag"=>$v_data_del['id'],"is_check"=>0,"service_type"=>$v_data_del['service_type'],"is_indservice"=>$v_data_del['is_indservice']));
                    }
                }
            }
            $this->DAO->update_business_game(implode(",",$game_list_new),implode(",",$service_list_new),$user_id,$service_list_page);
            $this->succeed_msg("更新成功");
        }else{
            $this->error_msg("数据丢失");
        }
    }

    public function payment_method($user_id){
        $user_info = $this->DAO->get_relation_tb($user_id);
        $payment_list = explode(',',$user_info['payment_method']);
        $this->assign('payment_list',$payment_list);
        $this->assign("payment",$this->payment);
        $this->assign("user_id",$user_id);
        $this->display("chamber/payment_method.html");
    }

    public function do_payment(){
        if(!$_POST['user_id']){
            $this->error_msg("请选择用户");
        }
        $relation = $this->DAO->get_relation_tb($_POST['user_id']);
        $_POST['payment_method'] = implode($_POST['payment'],',');
        if($relation){
            $this->DAO->update_relation_tb($_POST);
        }else{
            $this->DAO->insert_relation($_POST);
        }
        $this->succeed_msg();
    }

    public function inside_add(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $groups = $this->DAO->get_groups();
        if($_SESSION['group_id'] == '1'){
            $chamber_list = $this->DAO->get_chamber_list();
        }else{
            $chamber_list = $this->DAO->get_chamber_list($_SESSION['usr_id']);
        }
        $ch_list = $this->DAO->get_ch_list();
        $this->assign("user_info",$user_info);
        $this->assign("chamber_list", $chamber_list);
        $this->assign("ch_list", $ch_list);
        $this->assign("groups", $groups);
        $this->display("chamber/account_add_inside.html");
    }

    public function do_add_inside(){
        if(!$_POST['account'] || !$_POST['real_name'] || !$_POST['usr_pwd']){
            $this->error_msg("缺少必填项");
        }
        if($this->DAO->verify_account($_POST)){
            $this->error_msg("账号已被使用");
        }
        $_POST['usr_pwd'] = md5($_POST['usr_pwd']);
        $_POST['user_code']='';
        $_POST['ch_id']='';
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(empty($user_info)){
            $this->error_msg("未查询到你的账号,请联系管理员");
        }
        $this->chamber_verify($user_info,$_POST);
        $this->succeed_msg();
    }

    public function get_chamber_min_guild(){
        $guild_id = $_POST['guild_id'];
        $data_list = $this->DAO->get_chamber_guild_list($guild_id);
        die(json_encode($data_list));
    }

    public function set_state($id){
        $info = $this->DAO->get_user_info($id);
        $this->assign("info",$info);
        $this->display("chamber/account_state_view.html");
    }

    public function do_state(){
        if(!$_POST['id']){
            $this->error_msg("用户ID错误");
        }
        $user_list = $this->DAO->get_user_list($_POST['id']);
        foreach($user_list as $key=>$data){
            $_POST['user_id'] = $data['id'];
            $this->DAO->update_admin_relation($_POST);
        }
        $this->succeed_msg();
    }

    public function pay_mode($id){
        $admin_info = $this->DAO->get_admin_info($id);
        $this->assign("info", $admin_info);
        $this->display("account_pay_mode_edit.html");
    }

    public function do_pay_mode($id){
        $this->DAO->update_admin_financial_mode($_POST,$id);
        $this->succeed_msg();
    }
}