<?php
COMMON('adminBaseCore','pageCore','uploadHelper','imageCore');
DAO('account_admin_dao','menu_admin_dao','app_admin_dao');

class guild_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new guild_admin_dao();
    }

    public function guild_list(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['nnb_lock']){
            die("你的账号已经被冻结,请联系上级会长.");
        }
        if($user_info['group_id'] == '10') {
            $list = $this->DAO->get_account_by_group($_SESSION['usr_id'],$_GET,$this->page);
            $user_info = $this->get_user_sum($user_info);
            $guild_code="";
            $guild_list = $this->DAO->get_son_guild_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_code.="'".$item['user_code']."',";
                    }
                }
                $_SESSION['guild_code'] = substr($guild_code, 0, strlen($guild_code) - 1);
            }
        }elseif($user_info['group_id'] == '1'){
            $list = $this->DAO->get_account_list($_GET,$this->page);
            $user_info = $this->get_user_sum($user_info);
            $guild_list = $this->DAO->get_guild_list();
        }else{
            die("你没有该目录的权限,需要开启请联系管理员");
        }
        foreach($list as $key=>$guild){
            $list[$key]['son_sum'] = 0;
            $son_sum = $this->DAO->get_son_count($guild['id']);
            if(is_numeric($son_sum['sum']) && $son_sum['sum'] > 0 ){
                $list[$key]['son_sum'] = $son_sum['sum'];
            }
        }
        $page = $this->pageshow($this->page, "guild.php?act=guild_list&guild=".$_GET['guild']."&type=".$_GET['type']."&");
        $this->assign("params", $_GET);
        $this->assign("user_info", $user_info);
        $this->assign("guild_list", $guild_list);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("guild_list.html");
    }

    public function recharge_list($parent_id, $son_id, $type){
        $parent_info = $this->DAO->get_user_info($parent_id);
        if(!$parent_info || empty($parent_info['nnb'])){
            $this->display("no_amount_view.html");
            exit();
        }
        if($type == '1'){
            $son_info = $this->DAO->get_user_info($son_id);
        }elseif($type == '2'){
            $son_info = $this->DAO->get_user_data($son_id);
        }
        $this->page_hash();
        $this->assign('type', $type);
        $this->assign('parent_info', $parent_info);
        $this->assign('son_info', $son_info);
        $this->display("guild_recharge_view.html");
    }

    public function frozen_view($parent_id, $son_id, $type){
        $son_info = $this->DAO->get_user_info($son_id);
        if(!$son_info){
            $this->error_msg("未查询到子账号的信息.");
        }
        $this->page_hash();
        $this->assign('type', $type);
        $this->assign('parent_id', $parent_id);
        $this->assign('son_info', $son_info);
        $this->display("frozen_view.html");
    }

    public function revoke_view($parent_id, $son_id,$type){
        $son_info = $this->DAO->get_user_info($son_id);
        if(!$son_info){
            $this->error_msg("未查询到子账号的信息.");
        }elseif(empty($son_info['nnb'])){
            $this->error_msg("子账号无牛币.");
        }
        $parent_info = $this->DAO->get_user_info($parent_id);
        $this->page_hash();
        $this->assign('type',$type);
        $this->assign('parent_info', $parent_info);
        $this->assign('son_info', $son_info);
        $this->display("revoke_view.html");
    }

    public function revoke_log($id){
        $log_info = $this->DAO->get_pay_log_info($id);
        $guild_info = $this->DAO->get_user_info($log_info['guild_id']);
        if(!$log_info){
            die('未查询到该条记录.');
        }
        if(!$guild_info){
            die('未查询到需测试公会的信息.');
        }elseif($guild_info['is_del']=='1'){
            die('该公会已被删除,无法进行撤回操作.');
        }
        if($log_info['is_revoke']=='1'){
            die('该记录已撤回过了,请不要重复操作.');
        }
        $this->page_hash();
        $this->assign('guild_info', $guild_info);
        $this->assign('info', $log_info);
        $this->display("revoke_log_view.html");
    }

    public function do_recharge(){
        $params = $_POST;
        $this->form_verify($params);
        if($params['type'] == '1'){
            $this->guild_recharge_verify($params);
        }elseif($params['type'] == '2'){
            $this->user_recharge_verify($params);
        }else{
            $this->error_msg("错误的类型.");
        }
        $this->succeed_msg("充值成功.");
    }

    public function do_revoke(){
        $params = $_POST;
        $this->form_verify($params);
        if($params['type'] == '9'){
            $this->revoke_verify($params);
        }elseif($params['type'] == '10'){
            if(!$params['id']){
                $this->error_msg("请求出错,请刷新后重新撤回.");
            }
            $info = $this->DAO->get_pay_log_info($params['id']);
            if($info['add_time']+86400 < time()){
                $this->error_msg("你已过了撤回时间,无法进行撤回");
            }
            $this->revoke_verify($params);
        }else{
            $this->error_msg("错误的类型.");
        }
        $this->succeed_msg("撤回成功.");
    }

    public function do_frozen(){
        $params = $_POST;
        if(!$params['page_hash'] || $params['page_hash']!=$_SESSION['page-hash']){
            $this->error_msg("非法的操作");
        }
        if(!$params['parent_id'] || !$params['son_id'] || !$params['type']){
            $this->error_msg("缺少必要参数");
        }
        $parent_info = $this->DAO->get_user_info($params['parent_id']);
        if(!$parent_info){
            $this->error_msg("未能获取您的信息,请联系管理员.");
        }
        $md5_pwd = md5($params['pay_pwd']);
        if(strtolower($md5_pwd)!= strtolower($parent_info['pay_pwd'])){
            $this->error_msg("支付密码密码错误，请重新输入");
        }
        if($parent_info['group_id']==10){
            $is_son = $this->DAO->in_this_guild($params['son_id'],$params['parent_id']);
            if(!$is_son){
                $this->error_msg("你公会底下无该子账号,无法操作.");
            }
        }elseif($parent_info['group_id']!=1){
            $this->error_msg("你不是公会成员,没有充值的权限.");
        }
        if($params['type']=='2'){
            $params['type']=0;
        }
        $this->DAO->guild_lock($params['son_id'],$params['type']);
        $this->succeed_msg();
    }

    public function nnb_recharge(){
        if($_SESSION['group_id'] == '10') {
            $guild_ids="";
            $guild_list = $this->DAO->get_guild_all_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['id'])){
                        $guild_ids.="'".$item['id']."',";
                    }
                }
                $id_arr = substr($guild_ids, 0, strlen($guild_ids) - 1);
            }
            $recharge_list = $this->DAO->get_recharge_list($this->page,$_GET,$id_arr);
        }elseif($_SESSION['group_id'] == '1'){
            $recharge_list = $this->DAO->get_recharge_list($this->page,$_GET);
        }else{
            $this->error_msg("你没有该目录的权限,需要开启请联系管理员.");
            exit();
        }
        foreach ($recharge_list as $key=>$data) {
            $info = $this->DAO->get_user_info($data['user_id']);
            $recharge_list[$key]['real_name'] = $info['real_name'];
            $recharge_list[$key]['account'] = $info['account'];
        }
        $page = $this->pageshow($this->page, "guild.php?act=nnb_recharge&start_time=".$_GET['start_time']."&end_time=".$_GET['end_time']."&status=".$_GET['status']."&");
        $this->assign("page_bar", $page->show());
        $this->assign("params", $_GET);
        $this->assign('list', $recharge_list);
        $this->display("nnb_recharge_view.html");
    }

    public function reason_view($id){
        $recharge_record = $this->DAO->get_recharge_record($id);
        $info = $this->DAO->get_user_info($recharge_record['user_id']);
        $recharge_record['real_name'] = $info['real_name'];
        $this->page_hash();
        $this->assign('info', $recharge_record);
        $this->display("review_view.html");
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
            $son_info['nnb'] = $son_info['nnb'] + $recharge_record['amount'];
            $this->DAO->guild_lock($son_info['id'],1);
            $this->DAO->update_guild_nnb($son_info['nnb'],$son_info['id']);
            $this->DAO->guild_lock($son_info['id'],0);
            $data['son_id'] = $son_info['id'];
            $data['amount'] = $recharge_record['amount'];
            $data['parent_id'] = $_SESSION['usr_id'];
            $data['remarks'] = $_SESSION['remarks'];
            $this->DAO->insert_guild_nnb_log($this->create_guid(),$data,1);
        }
        $this->succeed_msg();
    }

    public function add_nnb(){
        if($_SESSION['group_id'] == '10') {
            $guild_code="";
            $guild_list = $this->DAO->get_guild_all_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_code.="'".$item['user_code']."',";
                    }
                }
                $_SESSION['guild_code'] = substr($guild_code, 0, strlen($guild_code) - 1);
            }
        }elseif($_SESSION['group_id'] == '1'){
            $guild_list = $this->DAO->get_guilds_list();
        }else{
            $this->error_msg("你没有该目录的权限,需要开启请联系管理员.");
            exit();
        }
        $this->page_hash();
        $this->assign('guild', $guild_list);
        $this->display("add_nnb_view.html");
    }

    public function do_add_nnb(){
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
        $img = $this->up_img('pay_img',"images/pay_img",array(),1,1,time(),0);
        $this->DAO->insert_recharge_record($img,$params);
        $this->succeed_msg();
    }

    private function form_verify($data){
        if(!$data){
            $this->error_msg("网络请求错误");
        }
        if(!$data['parent_id']||!$data['son_id']||!$data['pay_pwd']||!$data['verifycode']||!$data['type']){
            $this->error_msg("缺少必要参数");
        }
        if(!$data['page_hash'] || $data['page_hash']!=$_SESSION['page-hash']){
            $this->error_msg("非法的操作");
        }
        if(!$data['verifycode'] || strtoupper($data['verifycode'])!=$_SESSION['c']){
            $this->error_msg("验证码错误");
        }
        if(!$data['amount'] || empty($data['amount'])){
            $this->error_msg("请输入大于1的正整数.");
        }elseif(is_numeric($data['amount']) && !($data['amount'] > 0)){
            $this->error_msg("金额必须大于0");
        }
        if(strlen($data['pay_pwd']) < 6){
            $this->error_msg("密码错误");
        }
    }

    private function guild_recharge_verify($data){
        $parent_info = $this->DAO->get_user_info($data['parent_id']);
        if(!$parent_info|| empty($parent_info['nnb'])){
            $this->error_msg("余额不足");
        }elseif($data['amount'] > $parent_info['nnb']){
            $this->error_msg("充金额不能大于您当前的余额.");
        }
        $md5_pwd = md5($data['pay_pwd']);
        $parent_info = $this->DAO->get_user_info($data['parent_id']);
        if(!$parent_info){
            $this->error_msg("未能获取您的信息,请联系管理员.");
        }
        if(strtolower($md5_pwd)!= strtolower($parent_info['pay_pwd'])){
            $this->error_msg("支付密码密码错误，请重新输入");
        }
        if($parent_info['group_id']==10){
            $is_son = $this->DAO->in_this_guild($data['son_id'],$data['parent_id']);
            if(!$is_son){
                $this->error_msg("你公会底下无该子账号,无法进行充值.");
            }
        }elseif($parent_info['group_id']!=1){
            $this->error_msg("你不是公会成员,没有充值的权限.");
        }
        $son_info = $this->DAO->get_user_info($data['son_id']);
        if(!$son_info){
            $this->error_msg("未能获取到子账号信息.");
        }elseif($son_info['group_id']!=10){
            $this->error_msg("子账号不是公会账号,不能进行充值");
        }
        $parent_info['nnb'] = $parent_info['nnb'] - $data['amount'];
        $son_info['nnb'] = $son_info['nnb'] + $data['amount'];
        //账号锁定
        $this->DAO->guild_lock($data['parent_id'],1);
        $this->DAO->guild_lock($data['son_id'],1);
        $this->DAO->update_guild_nnb($parent_info['nnb'],$data['parent_id']);
        $this->DAO->update_guild_nnb($son_info['nnb'],$data['son_id']);
        $this->DAO->guild_lock($data['parent_id'],0);
        $this->DAO->guild_lock($data['son_id'],0);
        $this->DAO->insert_guild_nnb_log($this->create_guid(),$data,2);
    }

    private function user_recharge_verify($data){
        $parent_info = $this->DAO->get_user_info($data['parent_id']);
        if(!$parent_info){
            $this->error_msg("未能获取您的信息,请联系管理员.");
        }elseif(empty($parent_info['nnb'])){
            $this->error_msg("余额不足");
        }elseif($data['amount'] > $parent_info['nnb']){
            $this->error_msg("充金额不能大于您当前的余额.");
        }
        $md5_pwd = md5($data['pay_pwd']);
        if(strtolower($md5_pwd)!= strtolower($parent_info['pay_pwd'])){
            $this->error_msg("支付密码密码错误，请重新输入");
        }
        if($parent_info['group_id']==10){
            $codes = "";
            $guild_all_son = $this->DAO->get_guild_all_son($data['parent_id']);
            foreach($guild_all_son as $item){
                if(!empty($item['user_code'])){
                    $codes.="'".$item['user_code']."',";
                }
            }
            $user_code = substr($codes, 0, strlen($codes) - 1);
            $is_user = $this->DAO->user_in_guild($data['son_id'],$user_code);
            if(!$is_user){
                $this->error_msg("你公会底下无该用户,无法进行充值.");
            }
        }elseif($parent_info['group_id']!=1){
            $this->error_msg("你不是公会成员,没有充值的权限.");
        }
        $son_info = $this->DAO->get_user_data($data['son_id']);
        if(!$son_info){
            $this->error_msg("未查询到该用户,请确定充值的用户id.");
        }
        $parent_info['nnb'] = $parent_info['nnb'] - $data['amount'];
        $son_info['nnb'] = $son_info['nnb'] + $data['amount'];
        //账号锁定
        $this->DAO->user_lock($data['son_id'],1);
        $this->DAO->guild_lock($data['parent_id'],1);
        $this->DAO->update_guild_nnb($parent_info['nnb'],$data['parent_id']);
        $this->DAO->update_user_nnb($son_info['nnb'],$data['son_id']);
        $this->DAO->guild_lock($data['parent_id'],0);
        $this->DAO->user_lock($data['son_id'],0);
        $this->DAO->insert_user_nnb_log($this->create_guid(),$data,2);
    }

    private function revoke_verify($data){
        if(!$data['remarks']){
            $this->error_msg("必须填写理由");
        }
        $son_info = $this->DAO->get_user_info($data['son_id']);
        if(!$son_info|| empty($son_info['nnb'])){
            $this->error_msg("子账号无可撤回的牛币");
        }elseif($data['amount'] > $son_info['nnb']){
            $this->error_msg("充金额不能大于您当前的余额.");
        }elseif($son_info['group_id']!=10){
            $this->error_msg("该子账号不是公会权限,无法进行撤回操作");
        }
        $md5_pwd = md5($data['pay_pwd']);
        $parent_info = $this->DAO->get_user_info($data['parent_id']);
        if(!$parent_info){
            $this->error_msg("未能获取您的信息,请联系管理员.");
        }
        if(strtolower($md5_pwd)!= strtolower($parent_info['pay_pwd'])){
            $this->error_msg("支付密码密码错误，请重新输入");
        }
        if($parent_info['group_id']==10){
            $is_son = $this->DAO->in_this_guild($data['son_id'],$data['parent_id']);
            if(!$is_son){
                $this->error_msg("该子账号不在你的公会系统下,无法进行撤回.");
            }
        }elseif($parent_info['group_id']!=1){
            $this->error_msg("你不是公会成员,没有撤回的权限.");
        }
        $parent_info['nnb'] = $parent_info['nnb'] + $data['amount'];
        $son_info['nnb'] = $son_info['nnb'] - $data['amount'];
        //账号锁定
        $this->DAO->guild_lock($data['parent_id'],1);
        $this->DAO->guild_lock($data['son_id'],1);
        $this->DAO->update_guild_nnb($parent_info['nnb'],$data['parent_id']);
        $this->DAO->update_guild_nnb($son_info['nnb'],$data['son_id']);
        $this->DAO->guild_lock($data['parent_id'],0);
        $this->DAO->guild_lock($data['son_id'],0);
        $log_id = $this->DAO->insert_guild_nnb_log($this->create_guid(),$data,$data['type']);
        if($data['type']=='10'){
            $this->DAO->update_guild_nnb_log($log_id,$data['id']);
        }
    }

    public function get_user_sum($info){
        if($info['group_id']=="10"){
            if(empty($info['p1']) && empty($info['p2'])){
                $son_sum = $this->DAO->get_son_count($info['id']);
                if(is_numeric($son_sum['sum']) && $son_sum['sum'] > 0 ){
                    $info['son_sum'] = $son_sum['sum'];
                }else{
                    $info['son_sum'] = 0;
                }
                $lower_sum = $this->DAO->get_lower_count($info['id']);
                if(is_numeric($lower_sum['sum']) && $lower_sum['sum'] > 0 ){
                    $info['lower_sum'] = $lower_sum['sum'];
                }else{
                    $info['lower_sum'] = 0;
                }
            }elseif(!empty($info['p1']) && empty($info['p2'])){
                $son_sum = $this->DAO->get_son_count($info['id']);
                if(is_numeric($son_sum['sum']) && $son_sum['sum'] > 0 ){
                    $info['son_sum'] = $son_sum['sum'];
                }else{
                    $info['son_sum'] = 0;
                }
            }
        }elseif($info['group_id']=="1"){
            $b_guild = $this->DAO->get_guild_count(1);
            $m_guild = $this->DAO->get_guild_count(2);
            $s_guild = $this->DAO->get_guild_count(3);
            $info['b_guild']=!$b_guild['sum']?0:$b_guild['sum'];
            $info['m_guild']=!$m_guild['sum']?0:$m_guild['sum'];
            $info['s_guild']=!$s_guild['sum']?0:$s_guild['sum'];
        }
        return $info;
    }

    public function account_pwd_view($id){
        $info = $this->DAO->get_user_info($id);
        $this->assign("info", $info);
        $this->display("pay_pwd_edit.html");
    }

    public function user_list(){
        $params = $_GET;
        $guild_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($guild_info['nnb_lock']){
            die("你的账号已经被冻结,请联系上级会长.");
        }
        if($guild_info['group_id'] == '10') {
            $guild_code="";
            $guild_list = $this->DAO->get_guild_all_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_code.="'".$item['user_code']."',";
                    }
                }
                $params['guild_code'] = substr($guild_code, 0, strlen($guild_code) - 1);
            }
            $user_list = $this->DAO->get_user_list($this->page,$params);
        }elseif($_SESSION['group_id'] == '1'){
            $user_list = $this->DAO->get_user_list($this->page,$params);
        }else{
            die("你没有该目录的权限,需要开启请联系管理员.");
        }
        $page = $this->pageshow($this->page, "guild.php?act=user_list&user_id=".$params['user_id']."&");
        $this->assign("page_bar", $page->show());
        $this->assign("user_list", $user_list);
        $this->assign("params", $params);
        $this->assign("guild_info", $guild_info);
        $this->display("user_list.html");
    }

    public function pay_list(){
        $params = $this->get_params($_POST,$_GET);
        if($_SESSION['group_id']=='1'){
            $pay_log = $this->DAO->get_pay_log("",$params,$this->page);
        }else{
            $pay_log = $this->DAO->get_pay_log($_SESSION['usr_id'],$params,$this->page);
        }
        foreach($pay_log as $key=>$data){
            $user_info = $this->DAO->get_user_data($data['user_id']);
            $guild_info = $this->DAO->get_user_info($data['guild_id']);
            $parent_info = $this->DAO->get_user_info($data['parent_id']);
            $pay_log[$key]['user_name'] = $user_info['nick_name'];
            $pay_log[$key]['guild_name'] = $guild_info['real_name'];
            $pay_log[$key]['parent_name'] = $parent_info['real_name'];
//            if($data['parent_id']==$_SESSION['usr_id'] and !empty($data['guild_id']) and $data['is_revoke']=='0' and ($data['do'] =='2' or $data['do'] =='1') and ($data['add_time']+ 86400>time())){
//                $pay_log[$key]['can_revoke']="1";
//            }
        }
        $page = $this->pageshow($this->page, "guild.php?act=pay_list&");
        $app_dao = new app_admin_dao();
        $this->assign("guild_list",$app_dao->get_guild_list());
        $this->assign('params',$params);
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $pay_log);
        $this->display("guild_pay_list.html");
    }

    public function do_account_password($id){
        if(!$_POST['password'] || !$_POST['re_pwd']){
            $this->error_msg("缺少必填项");
        }
        $this->DAO->update_pay_pwd($_POST, $id);
        $this->succeed_msg();
    }

    public function export(){
        set_time_limit(0);
        if($_SESSION['group_id']=='1'){
            $dataList = $this->DAO->get_export_log("",$_GET);
        }else{
            $dataList = $this->DAO->get_export_log($_SESSION['usr_id'],$_GET);
        }
        foreach($dataList as $key=>$data){
            $user_info = $this->DAO->get_user_data($data['user_id']);
            $guild_info = $this->DAO->get_user_info($data['guild_id']);
            $parent_info = $this->DAO->get_user_info($data['parent_id']);
            $dataList[$key]['user_name'] = $user_info['nick_name'];
            $dataList[$key]['guild_name'] = $guild_info['real_name'];
            $dataList[$key]['parent_name'] = $parent_info['real_name'];
        }
        if($dataList){
            $this->master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("牛币操作记录");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "操作人id");
        $objActSheet->setCellValue("C1", "操作人名称");
        $objActSheet->setCellValue("D1", "动作");
        $objActSheet->setCellValue("E1", "金额");
        $objActSheet->setCellValue("F1", "公会ID");
        $objActSheet->setCellValue("G1", "公会昵称");
        $objActSheet->setCellValue("H1", "用户ID");
        $objActSheet->setCellValue("I1", "用户昵称");
        $objActSheet->setCellValue("K1", "备注");
        $objActSheet->setCellValue("L1", "操作时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, $info['parent_id']);
            $objActSheet->setCellValue("C".$n, $info['parent_name']);
            if($info['do'] == 1){
                $objActSheet->setCellValue("D".$n, "充值");
            }elseif($info['do'] == 2){
                $objActSheet->setCellValue("D".$n, "转币");
            }elseif($info['do'] == 9){
                $objActSheet->setCellValue("D".$n, "撤回");
            }
            $objActSheet->setCellValue("E".$n, $info['amount']);
            $objActSheet->setCellValue("F".$n, $info['guild_id']);
            $objActSheet->setCellValue("G".$n, $info['guild_name']);
            $objActSheet->setCellValue("H".$n, $info['user_id']);
            $objActSheet->setCellValue("I".$n, $info['user_name']);
            $objActSheet->setCellValue("K".$n, $info['remarks']);
            $objActSheet->setCellValue("L".$n, date('Y-m-d H:i:s',$info['add_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","牛币操作记录-".$str_now.'.xls');
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

    public function nnb_export(){
        set_time_limit(0);
        if($_SESSION['group_id'] == '10') {
            $guild_ids="";
            $guild_list = $this->DAO->get_guild_all_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['id'])){
                        $guild_ids.="'".$item['id']."',";
                    }
                }
                $id_arr = substr($guild_ids, 0, strlen($guild_ids) - 1);
            }
            $dataList = $this->DAO->get_export_recharge_list($_GET,$id_arr);
        }elseif($_SESSION['group_id'] == '1'){
            $dataList = $this->DAO->get_export_recharge_list($_GET);
        }
        foreach ($dataList as $key=>$data) {
            $info = $this->DAO->get_user_info($data['user_id']);
            $dataList[$key]['real_name'] = $info['real_name'];
            $dataList[$key]['account'] = $info['account'];
        }
        if($dataList){
            $this->nnb_master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function nnb_master_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("牛币充值");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "申请人");
        $objActSheet->setCellValue("C1", "申请账号");
        $objActSheet->setCellValue("D1", "金额");
        $objActSheet->setCellValue("E1", "截图");
        $objActSheet->setCellValue("F1", "备注");
        $objActSheet->setCellValue("G1", "理由");
        $objActSheet->setCellValue("H1", "时间");
        $objActSheet->setCellValue("I1", "操作");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, $info['real_name']);
            $objActSheet->setCellValue("C".$n, $info['account']);
            $objActSheet->setCellValue("D".$n, $info['amount']);
            $objActSheet->setCellValue("E".$n, 'http://static.66173.cn'.$info['img']);
            $objActSheet->setCellValue("F".$n, $info['remarks']);
            $objActSheet->setCellValue("G".$n, $info['reason']);
            $objActSheet->setCellValue("H".$n, date('Y-m-d H:i:s',$info['update_time']));
            if($info['status'] == 1){
                $objActSheet->setCellValue("I".$n, "待审核");
            }elseif($info['status'] == 2){
                $objActSheet->setCellValue("I".$n, "审核通过");
            }elseif($info['status'] == 3){
                $objActSheet->setCellValue("I".$n, "审核不通过");
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","牛币充值-".$str_now.'.xls');
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

    public function pack_list(){
        if($_SESSION['group_id'] != 1){
            die("你没有该目录的权限,需要开启请联系管理员.");
        }
        if($_POST){
            $_SESSION['pack_list'] = $params = $_POST;
        }else{
            $params = $_SESSION['pack_list'];
        }
        $list = $this->DAO->get_pack_list($this->page,$params);
        $guild_list = $this->DAO->get_guild_list();
        $app_list = $this->DAO->get_app_name();
        $page = $this->pageshow($this->page,"guild.php?act=pack_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("guild_list",$guild_list);
        $this->assign("app_list",$app_list);
        $this->assign("params",$params);
        $this->assign("list",$list);
        $this->display("guild_pack_list.html");
    }

    public function pack_status($id){
        $this->assign("id",$id);
        $this->display("guild_pack_status.html");
    }

    public function status_save(){
        $this->DAO->update_pack_status($_GET['id']);
        $this->succeed_msg();
    }

    public function pack_validate(){
        $this->assign("id",$_GET['id']);
        $this->display("guild_pack_validate.html");
    }

    public function validate_save(){
        $info = $this->DAO->get_guild_app_info($_GET['id']);
        $conn = ftp_connect('eaaf45b36c07e7713dc935.backend.tan14.net');
        $login_result = ftp_login($conn, 'eaaf45b36c07e7713dc935', 'erK3Jm3X');
        ftp_pasv($conn, true);
        ftp_chdir($conn, $info['app_id']);
        //上传文件，ftp_put()函数能很好的胜任，它需要你指定一个本地文件名，上传后的文件名以及传输的类型。比方说：如果你想上传 "abc.txt"这个文件，上传后命名为"xyz.txt"，命令应该是这样：
        $res = ftp_size($conn, $info['down_url']);
        ftp_close($conn);
        if ($res == $info['apk_size']) {
            $this->DAO->update_guild_app_type($_GET['id']);
            $this->succeed_msg("验证成功");
        } else {
            $this->error_msg("验证失败,包大小为".$res);
        }

    }

    public function user_revoke_log_view(){
        //权限控制
        $start_time = date("Y-m-d");
        $end_time = date("Y-m-d",time()+24*3600);
        if ($_POST['start_time']){
            $start_time = $_POST['start_time'];
        }
        if ($_POST['end_time']){
            $end_time = $_POST['end_time'];
        }
        $search_condition_order = 0;
        if (isset($_POST['search_condition'])){
            if ($_POST['search_condition']==1){
                $search_condition_order =1;
            }
        }
        $search_info = '';
        if ($_POST['search_info']){
            $search_info = $_POST['search_info'];
        }
        $revoke_log = $this->DAO->get_user_revoke_log($start_time,$end_time,$search_condition_order,$search_info,$this->page);
        $page = $this->pageshow($this->page,"guild.php?act=user_revoke_log_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time);
        $this->assign("search_condition_order",$search_condition_order);
        $this->assign("search_info",$search_info);
        $this->assign("revoke_log",$revoke_log);
        $this->display("user_revoke_log_list.html");
    }

    public function user_revoke_view(){
        //权限控制
        $this->page_hash();
        $this->display("user_revoke_list.html");
    }

    public function user_search_info(){
        if (isset($_POST['user_account'])){
            $user_account = $_POST['user_account'];
            if (!$user_account) $this->error_msg("用户账号ID不能为空！");
            if (!preg_match('/^[1-9][0-9]*$/',$user_account)) $this->error_msg("用户账号ID不合法！");
            $user_info = $this->DAO->get_user_revoke_info($user_account);
            if (empty($user_info)) $this->error_msg("该用户不存在！");
            $child_user = array();
            $sub_user = array();
            $sup_user = array();
            if ($user_info['channel']){
                //查找对应上级公会
                $channel_super = $this->DAO->get_user_super_info($user_info['channel']);
                $child_user = $channel_super;
                //查找上级及上上级
                if ($channel_super['p1']!=0){
                    $sub_user = $this->DAO->get_user_info($channel_super['p1']);
                    if ($channel_super['p2']!=0){
                        $sup_user = $this->DAO->get_user_info($channel_super['p2']);
                    }
                }
            }
            $user_info['child_user'] = $child_user;
            $user_info['sub_user'] = $sub_user;
            $user_info['sup_user'] = $sup_user;
            $this->succeed_msg($user_info);
        }else{
            $this->error_msg("查询异常！");
        }
    }

    public function user_revoke_do(){
        if (isset($_POST)){
            //权限控制
            if($_POST['pagehash'] != $_SESSION['page-hash']){
                $this->error_msg("hash错误，请刷新后重新登录。");
            }
            if (!$_SESSION['usr_id']) $this->error_msg("登录账号出现异常！");
            $code = $_POST['code'];
            if ($_SESSION['c']!=strtoupper($code)) $this->error_msg("验证码填写错误！");
            $user_account = $_POST['user_account_hidden'];
            if (!$user_account) $this->error_msg("用户账号ID不能为空！");
            if (!preg_match('/^[1-9][0-9]*$/',$user_account)) $this->error_msg("用户账号ID不合法！");
            $arrive_account = $_POST['arrive_account'];
            if (!$arrive_account) $this->error_msg("到账账户不能为空！");
            $arrive_money = $this->DAO->get_user_info($arrive_account);
            $nnb_number = $_POST['nnb_number'];
            if (!$nnb_number) $this->error_msg("撤币不能为空！");
            if (!preg_match('/^[1-9][0-9]*$/',$nnb_number)) $this->error_msg("撤币数量不合法！");
            $user_nnb = $this->DAO->get_user_revoke_info($user_account);
            if ($user_nnb['nnb']-(int)$nnb_number<0) $this->error_msg("撤币数量不能大于用户拥有币！");
            $pay_pwd = $_POST['pay_pwd'];
            if (!$pay_pwd) $this->error_msg("支付密码不能为空！");
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['pay_pwd']!=md5($pay_pwd)) $this->error_msg("支付密码错误！");
            $dd_id = $_POST['dd_id'];
            if (!$dd_id) $this->error_msg("钉钉审批编号不能为空！");
            if (!preg_match('/^[0-9]{21}$/',$dd_id)) $this->error_msg("钉钉审批编号不符合规则！");
            $revoke_reason = $_POST['revoke_reason'];
            if (!$revoke_reason) $this->error_msg("撤币理由不能为空！");
            $this->DAO->update_user_nnb_lock(1,$user_account);
            $this->DAO->update_user_nnb_revoke($nnb_number,$user_account);
            $this->DAO->update_user_nnb_lock(0,$user_account);
            $this->DAO->update_arrive_nnb_lock(1,$arrive_account);
            $this->DAO->update_arrive_nnb($nnb_number,$arrive_account);
            $this->DAO->update_arrive_nnb_lock(0,$arrive_account);
            $time = time();
            $order_id = "NN".$time.mt_rand(10,99);
            $revoke_log = array();
            $revoke_log['order_id'] = $order_id;
            $revoke_log['arrive_id'] = $arrive_account;
            $revoke_log['nnb_revoke'] = $nnb_number;
            $revoke_log['user_id_revoke'] = $user_account;
            $revoke_log['operation_id'] = $_SESSION['usr_id'];
            $revoke_log['time_revoke'] = $time;
            $revoke_log['dd_id'] = $dd_id;
            $revoke_log['revoke_reason'] = $revoke_reason;
            $revoke_log['user_money'] = $user_nnb['nnb'];
            $revoke_log['arrive_money'] = $arrive_money['nnb'];
            $this->DAO->insert_revoke_log($revoke_log);
            $this->succeed_msg("撤币成功！");
        }else{
            $this->error_msg("撤币异常！");
        }
    }

    public function create_verifi(){
        $img = new Image();
        $img->verifyCodeImg();
    }

    public function user_revoke_msg($id){
        $user_revoke_log = $this->DAO->get_revoke_log_details($id);
        $user_revoke_log['user_new_money'] = (int)$user_revoke_log['user_money']-(int)$user_revoke_log['nnb_revoke'];
        $user_revoke_log['arrive_new_money'] = (int)$user_revoke_log['arrive_money']+(int)$user_revoke_log['nnb_revoke'];
        $this->assign("user_revoke_log",$user_revoke_log);
        $this->display("user_revoke_details.html");
    }

    private function guild_data_time($params){
        if (isset($_GET['time'])){
            $time = $_GET['time'];
        }else{
            $time = 'today';
        }
        switch ($time){
            case 'today':
                $start_time = date('Y-m-d');
                $end_time = date('Y-m-d',time()+86400);
                break;
            case 'last_week':
                $time_temp = date('w')=='0'?6*86400:((int)date('w')-1)*86400;
                $start_time = date('Y-m-d',time()-$time_temp-7*86400);
                $end_time = date('Y-m-d',time()-$time_temp);
                break;
            case 'this_month':
                $start_time = date('Y-m-01');
                $end_time = date('Y-m-d',time()+86400);
                break;
            case 'last_month':
                $start_time = date('Y-m-d',time()-((int)date('d')-1)*86400-((int)date('t',time()-((int)date('d'))*86400))*86400);
                $end_time = date('Y-m-d',time()-((int)date('d')-1)*86400);
                break;
            case 'last_three_month':
                if (date('m')=='01'||date('m')=='02'||date('m')=='03'){
                    //待更改
                    //$start_time = date('Y-m-d',time()-((int)date('d')-1)*86400-92*86400);
                }else{
                    $start_time = date('Y-m-01',strtotime(date('Y',strtotime(date('Y-m-d'))).'-'.(date('m',strtotime(date('Y-m-d')))-3).'-01'));
                }
                $end_time = date('Y-m-d',time()-((int)date('d')-1)*86400);
                break;
            default :
                $start_time = $params['start_time'];
                $end_time = $params['end_time'];
                break;
        }
        return array("time"=>$time,"start_time"=>$start_time,"end_time"=>$end_time);
    }
    public function guild_data_sup_count_list(){
        if($_POST){
            $_SESSION['guild_data_sup'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['guild_data_sup']);
        }else{
            $params = $_SESSION['guild_data_sup'];
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if ($user_info['group_id']==1||($user_info['group_id']==10 && $user_info['p1']==0)){
            $time_arr = $this->guild_data_time($params);
                if ($user_info['group_id']!=1){
                    $guild_data = $this->DAO->get_sup_pay_money($this->page,$time_arr['start_time'],$time_arr['end_time'],$user_info['id']);
                }else{
                    $guild_data = $this->DAO->get_sup_pay_money($this->page,$time_arr['start_time'],$time_arr['end_time']);
                }
                foreach ($guild_data as $key=>$value){
                    if (!$guild_data[$key]['money']){
                        $guild_data[$key]['money']=0;
                    }
                }
            $page = $this->pageshow($this->page, "guild.php?act=guild_data_sup_count&time={$time_arr['time']}&");
            $this->assign("time",$time_arr['time']);
            $this->assign("start_time",$time_arr['start_time']);
            $this->assign("end_time",$time_arr['end_time']);
            $this->assign("datalist",$guild_data);
            $this->assign("page_bar",$page->show());
            $this->display("guild_data_sup_view.html");
        }elseif ($user_info['group_id']==10 && $user_info['p2']==0){
            $this->guild_data_sub_count_list($user_info['id']);
        }elseif ($user_info['group_id']==10){
            $this->guild_data_child_count_list($user_info['id']);
        }else{
            $this->error_msg("你没有权限看此数据，需要请联系管理员！");
        }
    }

    public function guild_data_sub_count_list($id){
        if ($id){
            if($_POST){
                $_SESSION['guild_data_sub'] = $params = $_POST;
            }elseif(!$_GET['page']){
                unset($_SESSION['guild_data_sub']);
            }else{
                $params = $_SESSION['guild_data_sub'];
            }
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1||($user_info['group_id']==10&&$user_info['p1']==0)||($user_info['group_id']==10&&$user_info['p2']==0)){
                $time_arr = $this->guild_data_time($params);
                if ($user_info['group_id']==1||($user_info['group_id']==10&&$user_info['p1']==0)){
                    $guild_data = $this->DAO->get_sub_pay_money($this->page,$id,$time_arr['start_time'],$time_arr['end_time']);
                }else{
                    $guild_data = $this->DAO->get_sub_pay_money($this->page,$user_info['p1'],$time_arr['start_time'],$time_arr['end_time'],$user_info['id']);
                }
                foreach ($guild_data as $key=>$value){
                    if (!$guild_data[$key]['money']){
                        $guild_data[$key]['money']=0;
                    }
                }
                $page = $this->pageshow($this->page, "guild.php?act=guild_data_sub_count&id={$id}&time={$time_arr['time']}&");
                $this->assign("time",$time_arr['time']);
                $this->assign("start_time",$time_arr['start_time']);
                $this->assign("end_time",$time_arr['end_time']);
                $this->assign("datalist",$guild_data);
                $this->assign("page_bar",$page->show());
                $this->assign("id",$id);
                $this->assign("group_id",$user_info['group_id']);
                $this->assign("p1",$user_info['p1']);
                $this->display("guild_data_sub_view.html");
            }else{
                $this->error_msg("你没有权限看此数据，需要请联系管理员！");
            }
        }else{
            $this->error_msg("下级公会获取数据失败！");
        }
    }

    public function guild_data_child_count_list($id){
        if ($id){
            if($_POST){
                $_SESSION['guild_data_child'] = $params = $_POST;
            }elseif(!$_GET['page']){
                unset($_SESSION['guild_data_child']);
            }else{
                $params = $_SESSION['guild_data_child'];
            }
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1|| $user_info['group_id']==10) {
                $time_arr = $this->guild_data_time($params);
                if ($user_info['p2']!=0&&$user_info['group_id']==10){
                    $guild_data = $this->DAO->get_child_pay_money($this->page, $user_info['p1'], $time_arr['start_time'], $time_arr['end_time'],$user_info['id']);
                }else{
                    $guild_data = $this->DAO->get_child_pay_money($this->page, $id, $time_arr['start_time'], $time_arr['end_time']);
                }
                foreach ($guild_data as $key => $value) {
                    if (!$guild_data[$key]['money']) {
                        $guild_data[$key]['money'] = 0;
                    }
                }
                $page = $this->pageshow($this->page, "guild.php?act=guild_data_child_count&id={$id}&time={$time_arr['time']}&");
                $this->assign("time", $time_arr['time']);
                $this->assign("start_time",$time_arr['start_time']);
                $this->assign("end_time",$time_arr['end_time']);
                $this->assign("datalist", $guild_data);
                $this->assign("page_bar", $page->show());
                $this->assign("id", $id);
                $this->assign("group_id",$user_info['group_id']);
                $this->assign("p1",$user_info['p1']);
                $this->assign("p2",$user_info['p2']);
                $this->display("guild_data_child_view.html");
            }else{
                $this->error_msg("你没有权限看此数据，需要请联系管理员！");
            }
        }else{
            $this->error_msg("子公会获取数据失败！");
        }
    }

    public function apps_data_list(){
        if($_POST){
            $_SESSION['apps_data'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['apps_data']);
        }else{
            $params = $_SESSION['apps_data'];
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if ($user_info['group_id']==1||$user_info['group_id']==10){
            $time_arr = $this->guild_data_time($params);
            if ($user_info['group_id']==10){
                $appid_list = trim(preg_replace("/5001,*/","",$user_info['apps']),",");
                $apps_data = $this->DAO->get_apps_pay_money($this->page,$time_arr['start_time'],$time_arr['end_time'],$appid_list);
            }else{
                $apps_data = $this->DAO->get_apps_pay_money($this->page,$time_arr['start_time'],$time_arr['end_time']);
            }
            foreach ($apps_data as $key=>$value){
                if (!$apps_data[$key]['channel_no']){
                    $apps_data[$key]['channel_no'] = 0;
                }
                if (!$apps_data[$key]['money_all']){
                    $apps_data[$key]['money_all']=0;
                }
            }
            $page = $this->pageshow($this->page, "guild.php?act=apps_data&time={$time_arr['time']}&");
            $this->assign("time",$time_arr['time']);
            $this->assign("start_time",$time_arr['start_time']);
            $this->assign("end_time",$time_arr['end_time']);
            $this->assign("datalist",$apps_data);
            $this->assign("page_bar",$page->show());
            $this->display("apps_data_view.html");
        }else{
            $this->error_msg("你没有权限看此数据，需要请联系管理员！");
        }
    }

    public function app_channel_data($app_id){
        if ($app_id){
            if($_POST){
                $_SESSION['apps_data'] = $params = $_POST;
            }elseif(!$_GET['page']){
                unset($_SESSION['apps_data']);
            }else{
                $params = $_SESSION['apps_data'];
            }
            $time_arr = $this->guild_data_time($params);
            $apps_data = $this->DAO->get_app_channel_pay_money($this->page,$app_id,$time_arr['start_time'],$time_arr['end_time']);
            foreach ($apps_data as $key=>$value){
                if (!$apps_data[$key]['money']){
                    $apps_data[$key]['money']=0;
                }
            }
            $page = $this->pageshow($this->page, "guild.php?act=app_channel_data&app_id={$app_id}&time={$time_arr['time']}&");
            $this->assign("time",$time_arr['time']);
            $this->assign("start_time",$time_arr['start_time']);
            $this->assign("end_time",$time_arr['end_time']);
            $this->assign("datalist",$apps_data);
            $this->assign("app_id",$app_id);
            $this->assign("page_bar",$page->show());
            $this->display("apps_data_channel_view.html");
        }else{
            $this->error_msg("游戏获取数据失败！");
        }
    }

    public function nd_user_info(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            die("账号异常，请检查！");
        }
        if($_SESSION['usr_id'] == '371' || $user_info['p1'] == '371'){
            die("你没有权限，请联系管理员！！！");
        }

        if ($user_info['group_id']==1||$user_info['group_id']==10){
            $app_list = $this->DAO->get_app_name_all();
            if($_POST){
                $_SESSION['nd_user_info'] = $params = $_POST;
            }elseif(!$_GET['page']){
                unset($_SESSION['nd_user_info']);
            }else{
                $params = $_SESSION['nd_user_info'];
            }
            if ($user_info['group_id']==10){
                $params['guild_channels'] = $this->user_guild_permition($user_info['id']);
            }
            $nd_app_user = $this->DAO->get_nd_app_user($this->page,$params);
        }else{
            die("你没有权限，请联系管理员！");
        }
        $page = $this->pageshow($this->page, "guild.php?act=nd_user_info&");
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->assign("datalist",$nd_app_user);
        $this->assign("app_list",$app_list);
        $this->display("nd_user_info_view.html");
    }

    public function nd_user_charge(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            die("账号异常，请检查！");
        }
        if ($user_info['group_id']==1||$user_info['group_id']==10){
            $app_list = $this->DAO->get_app_name_all();
        }else{
            die("你没有权限，请联系管理员！");
        }
        $this->page_hash();
        $this->assign("nnb_current",$user_info['nnb']);
        $this->assign("app_list",$app_list);
        $this->display("nd_user_charge.html");
    }

    public function nd_user_charge_save(){
        $params = $_POST;
        if (!$params['code'] || strtoupper($params['code'])!= $_SESSION['c']){
            $this->error_msg("验证码输入错误。");
        }
        if (!preg_match("/^[1-9][0-9]*$/",$params['nd_user_id_hidden'])){
            $this->error_msg("用户id输入错误。");
        }
        if (!$params['nd_app_id']){
            $this->error_msg("请选择游戏。");
        }
        if (!preg_match("/^[1-9][0-9]*$/",$params['nd_no'])||!$params['nnb_no']||!$params['app_discount']){
            $this->error_msg("牛点数量错误。");
        }
        if (round((int)$params['nd_no']*(float)$params['app_discount'],2)!=$params['nnb_no']){
            $this->error_msg("牛点折扣计算错误。");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            $this->error_msg("账号异常，请检查！");
        }
        if (!$params['charge_pwd'] || md5($params['charge_pwd'])!=$user_info['pay_pwd']){
            $this->error_msg("充值密码错误。");
        }
        if ($user_info['nnb']-(int)$params['nnb_no']<0){
            $this->error_msg("账号牛币余额不足。");
        }
        $nd_user_info = $this->DAO->get_nd_user($params['nd_user_id_hidden'],$params['nd_app_id']);
        if ($nd_user_info['nd_lock']==1){
            $this->error_msg("该用户对应游戏牛点已被冻结，请联系管理员。");
        }
        if(!$params['pagehash'] || $params['pagehash']!= $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        unset($_SESSION['page-hash']);
        $orders_id = "NN".time().mt_rand(1000,9999);
        $id_log = $this->DAO->insert_nd_user_charge_log($orders_id,$params['nd_user_id_hidden'],$params['nd_app_id'],$params['nnb_no'],
            $params['nd_no'],$params['app_discount'],0,$user_info['id']);
        $nnb_new = round($user_info['nnb']-(float)$params['nnb_no'],2);
        $this->DAO->guild_lock($user_info['id'],1);
        $this->DAO->update_guild_nnb($nnb_new,$user_info['id']);
        $this->DAO->guild_lock($user_info['id'],0);
        $this->DAO->update_nd_user_charge_log(1,$id_log);
        if ($nd_user_info){
            $nd_new = $nd_user_info['nd_num'] + (int)$params['nd_no'] ;
            $this->DAO->nd_user_lock(1,$params['nd_app_id'],$params['nd_user_id_hidden']);
            $this->DAO->update_nd_user($nd_new,$params['nd_app_id'],$params['nd_user_id_hidden']);
            $this->DAO->nd_user_lock(0,$params['nd_app_id'],$params['nd_user_id_hidden']);
        }else{
            $this->DAO->insert_nd_user($params['nd_app_id'],$params['nd_user_id_hidden'],$params['nd_no']);
        }
        $this->DAO->update_nd_user_charge_log(2,$id_log);
        $this->succeed_msg("充值成功。");
    }

    public function nd_user_verify(){
        if (isset($_POST['nd_user_id'])){
            $nd_user_id = $_POST['nd_user_id'];
            if (!preg_match("/^[1-9][0-9]*$/",$nd_user_id))
                $this->error_msg("用户id非法！");
            $nd_user_info = $this->DAO->get_user_data($nd_user_id);
            if (!$nd_user_info)
                $this->error_msg("不存在该用户！");
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==10){
                $guild_channels = $this->user_guild_permition($user_info['id']);
                if (strpos($guild_channels,"'".$nd_user_info['channel']."'")===false)
                    $this->error_msg("该用户不属于你的公会！");
            }
            $this->succeed_msg($nd_user_info);
        }else{
            $this->error_msg("没有数据");
        }
    }

    public function nd_user_frozen(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            die("账号异常，请检查！");
        }
        if ($user_info['group_id']==1){
            $params = $_GET;
            $this->assign("params",$params);
            $this->display("nd_user_frozen_view.html");
        }else{
            die("你没有权限，请联系管理员！");
        }
    }

    public function nd_user_frozen_save(){
        if ($_POST){
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if (!$user_info){
                $this->error_msg("账号异常，请检查！");
            }
            if ($user_info['group_id']==1){
                $orders_id = "NN".time().mt_rand(1000,9999);
                if ($_POST['is_frozen']==0){
                    $this->DAO->update_nd_user_frozen(1,$_POST['app_id'],$_POST['user_id']);
                    $this->DAO->insert_nd_user_frozen_log($orders_id,$_POST['user_id'],$_POST['app_id'],2,$user_info['id']);
                }elseif($_POST['is_frozen']==1){
                    $this->DAO->update_nd_user_frozen(0,$_POST['app_id'],$_POST['user_id']);
                    $this->DAO->insert_nd_user_frozen_log($orders_id,$_POST['user_id'],$_POST['app_id'],4,$user_info['id']);
                }else{
                    $this->error_msg("该用户对应游戏状态异常！");
                }
                $this->succeed_msg("操作成功！");
            }else{
                $this->error_msg("你没有权限，请联系管理员！");
            }
        }else{
            $this->error_msg("没有数据");
        }
    }

    public function nd_user_revoke(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if ($user_info['group_id']==1){
            $params = $_GET;
            $user_name = $this->DAO->get_user_data($params['user_id']);
            $params['nick_name'] = $user_name['nick_name'];
            $this->assign("params",$params);
            $this->page_hash();
            $this->display("nd_user_revoke_view.html");
        }else{
            die("你没有权限，请联系管理员！");
        }
    }

    public function nd_user_revoke_save(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            $this->error_msg("账号异常，请检查！");
        }
        if ($user_info['group_id']==1){
            $params = $_POST;
            if (!$params['code'] || strtoupper($params['code'])!= $_SESSION['c']){
                $this->error_msg("验证码输入错误。");
            }
            if (!preg_match("/^[1-9][0-9]*$/",$params['nd_no'])){
                $this->error_msg("牛点数量错误。");
            }
            if (!$params['nd_revoke_reason']){
                $this->error_msg("撤销理由不能为空。");
            }
            if (!$params['charge_pwd'] || md5($params['charge_pwd'])!=$user_info['pay_pwd']){
                $this->error_msg("充值密码错误。");
            }
            $nd_user_info = $this->DAO->get_nd_user($params['nd_user_id'],$params['nd_app_id']);
            if ($nd_user_info['nd_lock']==1){
                $this->error_msg("该用户对应游戏牛点已被冻结，请联系管理员。");
            }
            $nd_new = $nd_user_info['nd_num']-(int)$params['nd_no'];
            if ($nd_new<0){
                $this->error_msg("牛点数量不足，请输入正确数量。");
            }
            if(!$params['pagehash'] || $params['pagehash']!= $_SESSION['page-hash']){
                $this->error_msg("hash错误，请刷新后重新登录。");
            }
            unset($_SESSION['page-hash']);
            //后期增加选择撤销人员
            $app_obj = new app_admin_dao();
            $app_info = $app_obj->get_app_info($params['nd_app_id']);
            $orders_id = "NN".time().mt_rand(1000,9999);
            $id_log = $this->DAO->insert_nd_user_revoke_log($orders_id,$params['nd_user_id'],$params['nd_app_id'],round((int)$params['nd_no'] * $app_info['nd_discount'],2),
                $params['nd_no'],$app_info['nd_discount'],0,$user_info['id'],$params['nd_revoke_reason']);
            $nnb_new = round((int)$params['nd_no'] * $app_info['nd_discount'],2) + $user_info['nnb'];
            $this->DAO->guild_lock($user_info['id'],1);
            $this->DAO->update_guild_nnb($nnb_new,$user_info['id']);
            $this->DAO->guild_lock($user_info['id'],0);
            $this->DAO->update_nd_user_revoke_log(1,$id_log);
            $this->DAO->nd_user_lock(1,$params['nd_app_id'],$params['nd_user_id']);
            $this->DAO->update_nd_user($nd_new,$params['nd_app_id'],$params['nd_user_id']);
            $this->DAO->nd_user_lock(0,$params['nd_app_id'],$params['nd_user_id']);
            $this->DAO->update_nd_user_revoke_log(2,$id_log);
            $this->succeed_msg("撤销成功。");
        }else{
            die("你没有权限，请联系管理员！");
        }
    }

    public function nd_operation_log(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            $this->error_msg("账号异常，请检查！");
        }
        if ($user_info['group_id']==1 || $user_info['group_id']==10){
            $app_list = $this->DAO->get_app_name_all();
            if($_POST){
                $_SESSION['nd_operation_log'] = $params = $_POST;
            }elseif(!$_GET['page']){
                unset($_SESSION['nd_operation_log']);
            }else{
                $params = $_SESSION['nd_operation_log'];
            }
            if ($user_info['group_id']==10){
                $params['guild_channels'] = $this->user_guild_permition($user_info['id']);
            }
            $operation_data = $this->DAO->get_nd_operation_log($this->page,$params);
            $page = $this->pageshow($this->page, "guild.php?act=nd_operation_log&");
            $this->assign("params",$params);
            $this->assign("datalist",$operation_data);
            $this->assign("page_bar", $page->show());
            $this->assign("app_list",$app_list);
            $this->display("nd_user_operation_log.html");
        }else{
            die("你没有权限，请联系管理员！");
        }
    }

    public function export_nd_log(){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if (!$user_info){
            die("账号异常，请检查！");
        }
        if ($user_info['group_id']==1 || $user_info['group_id']==10){
            $params = $_GET;
            if ($user_info['group_id']==10){
                $params['guild_channels'] = $this->user_guild_permition($user_info['id']);
            }
            set_time_limit(0);
            $dataList = $this->DAO->get_export_nd_operation_log($params);
            if($dataList){
                $this->nd_excel_out($dataList);
            }else{
                echo "没有数据需要导出！";
            }
        }else{
            die("你没有权限，请联系管理员！");
        }
    }

    private function nd_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(35);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->setTitle("牛点操作记录");
        $objActSheet->setCellValue("A1", "订单号");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "用户id");
        $objActSheet->setCellValue("D1", "操作类型");
        $objActSheet->setCellValue("E1", "牛币数");
        $objActSheet->setCellValue("F1", "牛点数");
        $objActSheet->setCellValue("G1", "折扣");
        $objActSheet->setCellValue("H1", "操作人id");
        $objActSheet->setCellValue("I1", "备注");
        $objActSheet->setCellValue("J1", "操作时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit('A'.$n,$info['orders'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("B".$n, "[".$info['app_id']."]".$info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['user_id']);
            if($info['do_type'] == 1){
                $objActSheet->setCellValue("D".$n, "充值");
            }elseif($info['do_type'] == 2){
                $objActSheet->setCellValue("D".$n, "冻结");
            }elseif($info['do_type'] == 3){
                $objActSheet->setCellValue("D".$n, "撤点");
            }elseif ($info['do_type'] == 4){
                $objActSheet->setCellValue("D".$n, "解冻");
            }else{
                $objActSheet->setCellValue("D".$n, "其他");
            }
            $objActSheet->setCellValue("E".$n, $info['nnb_num']);
            $objActSheet->setCellValue("F".$n, $info['nd_num']);
            $objActSheet->setCellValue("G".$n, $info['nd_discount']);
            $objActSheet->setCellValue("H".$n, $info['guild_id']);
            $objActSheet->setCellValue("I".$n, $info['remarks']);
            $objActSheet->setCellValue("J".$n, date('Y-m-d H:i:s',$info['add_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","牛点操作记录-".$str_now.'.xls');
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
    //公会不同权限
    private function user_guild_permition($id){
        $guild_info = $this->DAO->get_guild_all_son($id);
        $guild_channels = "";
        foreach ($guild_info AS $value){
            $guild_channels.="'".$value['user_code']."',";
        }
        return trim($guild_channels,",");
    }
}