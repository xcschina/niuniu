<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('app_admin_dao');

class app_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new app_admin_dao();
    }

    public function app_list_view(){
        $params = $this->get_params($_POST,$_GET);
        $channel_list = $this->DAO->get_channel();
        $list = $this->DAO->get_app_list($this->page,$params);
        $applist = $this->DAO->get_app_name();
        $page = $this->pageshow($this->page, "app.php?act=list&");
        $this->assign("channel_list",$channel_list);
        $this->assign("app_list",$applist);
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("app_list.html");
    }

    public function callback(){
        $debug_info = array();
        $debug_info = $this->DAO->get_order_debug_info();
        if(isset($_GET['data']) && $_GET['type']){
            if($_GET['type'] == 1){
                $debug_info['web'] = $_GET['data'];
                $this->DAO->set_order_debug_info($debug_info);
            }
            if($_GET['type'] == 2){
                $debug_info['apple'] = $_GET['data'];
                $this->DAO->set_order_debug_info($debug_info);
            }
        }

        if($debug_info['web']){
            $file_path = PREFIX."/logs/order_debug_".date("Ymd",time()).".log";
            if(file_exists($file_path)){
                $fp = fopen($file_path,"r");
                $str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
                $str = str_replace("\r\n","<br />",$str);
                $msg = $str;
            }
        }

        if($debug_info['apple']){
            $file_path = PREFIX."/logs/apple_debug_".date("Ymd",time()).".log";
            if(file_exists($file_path)){
                $fp = fopen($file_path,"r");
                $str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
                $str = str_replace("\r\n","<br />",$str);
                $apple_msg = $str;
            }
        }
        $this->assign('msg',$msg);
        $this->assign('apple_msg',$apple_msg);
        $this->assign('info',$debug_info);
        $this->display("order_debug.html");
    }

    public function guild_list_view(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=='10' && empty($user_info['user_code'])){
            die("未能获取公会代码,请联系管理员.");
        }elseif($user_info['group_id']=='1'){
            $user_info['user_code']='nnwl';
        }elseif($user_info['group_id']!='10'){
            die("你没用公会产品的权限.");
        }
        if($user_info['id'] == '361'||$user_info['p1'] == '361'||$user_info['p2'] == '361'){
            $applist = $this->DAO->get_app_name_by_ids('5001','6024');
            $list = $this->DAO->get_moyu_lists($this->page);
            foreach($list as $i=>$data){
                $guild_app = $this->DAO->get_guild_app_url($user_info['user_code'],$data['app_id']);
                if($guild_app){
                    $list[$i]['url'] = $guild_app['down_url'];
                    $list[$i]['time'] = $guild_app['time'];
                    $list[$i]['status'] = $guild_app['status'];
                    if(!empty($guild_app['apk_size'])){
                        $list[$i]['apk_size'] = round($guild_app['apk_size']/1048576*100)/100 . ' MB';
                    }
                }
            }
        }elseif($user_info['id'] == '371'||$user_info['p1'] == '371'||$user_info['p2'] == '371'){
            $applist = $this->DAO->get_app_name_by_id('5001');
            $list = $this->DAO->get_moyu_list($this->page);
            foreach($list as $i=>$data){
                $guild_app = $this->DAO->get_guild_app_url($user_info['user_code'],$data['app_id']);
                if($guild_app){
                    $list[$i]['url'] = $guild_app['down_url'];
                    $list[$i]['time'] = $guild_app['time'];
                    $list[$i]['status'] = $guild_app['status'];
                    if(!empty($guild_app['apk_size'])){
                        $list[$i]['apk_size'] = round($guild_app['apk_size']/1048576*100)/100 . ' MB';
                    }
                }
            }
        }else{
            $applist = $this->DAO->get_guild_app_name();

            $list = $this->DAO->get_apps_list($this->page,$params);

            foreach($list as $i=>$data){
                if($data['app_id'] =='6023'){
                    if(!($user_info['id'] == '84'||$user_info['id'] == '681'||$user_info['p1'] == '681'||$user_info['p2'] == '681')){
                        unset($list[$i]);
                        continue;
                    }
                }
                $guild_app = $this->DAO->get_guild_app_url($user_info['user_code'],$data['app_id']);
                if($guild_app){
                    $list[$i]['url'] = $guild_app['down_url'];
                    $list[$i]['time'] = $guild_app['time'];
                    $list[$i]['status'] = $guild_app['status'];
                    if(!empty($guild_app['apk_size'])){
                        $list[$i]['apk_size'] = round($guild_app['apk_size']/1048576*100)/100 . ' MB';
                    }
                }
            }
        }
        $page = $this->pageshow($this->page, "app.php?act=guild_list&app_id=".$params['app_id']."&access_type=".$params['access_type']."&");
        $this->assign("params",$params);
        $this->assign("app_list",$applist);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("guild_app_list.html");
    }

    public function pack_prompt_view($app_id){
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(!$user_info["user_code"]) {
            die("该账号缺少必要参数,请联系管理员.");
        }else{
            $pack_log = $this->DAO->get_pack_log($app_id,$user_info["user_code"]);
            if(empty($pack_log)){
                $msg="该游戏还未打包,确定后即可立即打包.";
            }else{
                $msg="该游戏已有打包地址了,还需要重新打包?";
            }
        }

        $this->assign("msg", $msg);
        $this->assign("app_id", $app_id);
        $this->display("pack_prompt_view.html");
    }

    public function add_pack($app_id){
        if(!$_SESSION['usr_id'] || !$app_id){
            $this->error_msg('未能获取用户信息,请重新登录.');
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=='10' && empty($user_info['user_code'])){
            $this->error_msg('未能获取公会代码,请联系管理员.');
        }elseif($user_info['group_id']=='1'){
            $user_info['user_code']='nnwl';
        }elseif($user_info['group_id']!='10'){
            $this->error_msg('你没有自助打包的权限.请联系管理员.');
        }

        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info || empty($app_info['apk_url'])){
            $this->error_msg('该包还未上传');
        }
        $pack_log = $this->DAO->query_pack_log($app_id,$user_info['user_code'],1);
        if(!$pack_log){
            $this->DAO->insert_pack_log($user_info['user_code'],$app_info);
        }else{
            $this->error_msg('该包已经再队列中,无需重复提交.');
        }
        $apk_num = $this->DAO->get_ready_apk_num();
        $this->succeed_msg('已提交打包申请,目前已在队列中,还需要等待'.$apk_num['num'].'包');
    }



    public function do_pack_app($app_id){
        $data = array("error"=>'1','msg'=>'网络请求错误,请重新登录.');
        if(!$_SESSION['usr_id'] || !$app_id){
            $data['msg'] = '未能获取用户信息,请重新登录.';
            die(json_encode($data));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=='10' && empty($user_info['user_code'])){
            $data['msg'] = '未能获取公会代码,请联系管理员.';
            die(json_encode($data));
        }elseif($user_info['group_id']=='1'){
            $user_info['user_code']='nnwl';
        }elseif($user_info['group_id']!='10'){
            $data['msg'] = '你没有自助打包的权限.请联系管理员';
            die(json_encode($data));
        }

        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info || empty($app_info['apk_url'])){
            $data['msg'] = '该包还未上传.';
            die(json_encode($data));
        }
        $params['apk_url'] = $app_info['apk_url'];
        $params['app_id'] = $app_id;
        $params['user_code'] = $user_info['user_code'];
        $params['id'] = $app_info['id'];
        $url = "http://106.75.78.43/api.php?act=do_pack";
        $result = $this->request($url,$params);
        if(!empty($result)){
            $data['msg'] = $result;
            die(json_encode($data));
        }
    }

    public function new_pack($app_id){
        $data = array("error"=>'1','msg'=>'网络请求错误,请重新登录.');
        if(!$_SESSION['usr_id'] || !$app_id){
            $data['msg']='未能获取用户信息,请重新登录.';
            die(json_encode($data));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=='10' && empty($user_info['user_code'])){
            $data['msg']='未能获取公会代码,请联系管理员.';
            die(json_encode($data));
        }elseif($user_info['group_id']=='1'){
            $user_info['user_code']='nnwl';
        }elseif($user_info['group_id']!='10'){
            $data['msg']='你没有自助打包的权限.请联系管理员';
            die(json_encode($data));
        }
        $this->DAO->get_pack_info($app_id,$params['user_code']);
        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info || empty($app_info['apk_url'])){
            $data['msg']='该包还未上传.';
            die(json_encode($data));
        }
        $params['apk_url'] = $app_info['apk_url'];
        $params['app_id'] = $app_id;
        $params['user_code'] = $user_info['user_code'];
        $params['id'] = $app_info['id'];
        //添加打包记录
        $guild_pack_info = $this->DAO->get_guild_pack_info($app_id,$user_info['user_code']);
        $url = substr($params['apk_url'], 4);
        $apk_url = str_replace(".apk","_".$params['user_code'].".apk",$url);
        if($guild_pack_info['status'] != 4){
            $data['msg']='已在打包中.';
            die(json_encode($data));
        }
        if(!$guild_pack_info){
            $pack_id = $this->DAO->insert_guild_pack_info($params['app_id'], $params['user_code'],$apk_url);
        }else{
            $pack_id = $this->DAO->update_guild_pack_record($params['app_id'], $params['user_code'],$apk_url,0);
        }
        $params['pack_id'] = $pack_id;
        $url = "http://106.75.78.43/api.php?act=do_pack";
        $result = $this->request($url,$params);
        if(!empty($result)){
            $data['msg'] = $result;
            die(json_encode($data));
        }
    }

    public function app_add_view(){
        $this->display("app_add.html");
    }

    public function app_edit_view($id){
        $info = $this->DAO->get($id);
        $channel = $this->DAO->get_ch_info();
        $app_list = $this->DAO->get_web_app_list();
        $this->assign("app_list", $app_list);
        $this->assign("info", $info);
        $this->assign("channel", $channel);
        $this->display("app_edit.html");
    }

    public function app_notice_edit_view($id){
        $info = $this->DAO->get($id);
        $this->assign("info", $info);
        $this->display("app_notice.html");
    }

    public function version_edit($id){
        $info = $this->DAO->get($id);
        $this->assign("info", $info);
        $this->display("app_update_view.html");
    }

    public function do_app_edit($id){
        if(!$_POST['app_id'] || !$_POST['app_key'] || !$_POST['app_name']){
            $this->error_msg("缺少必填项");
        }
        if(!$_FILES['app_icon']['tmp_name']){
            $_POST['app_icon'] = $_POST['old_img'];
        }else{
            $_POST['app_icon']=$this->up_img('app_icon',"images/66game",array(),1,1,$id,0);
        }
        if(!$_FILES['register_img']['tmp_name']){
            $_POST['register_img'] = $_POST['old_register_img'];
        }else{
            $_POST['register_img']=$this->up_img('register_img',PRODUCT_IMG,array(),1,1,$id,0);
        }
        if(strtotime($_POST['version_time'])>0 && !$_POST['version_url']){
            $this->error_msg("请填写下载URL");
        }
        if($_POST['status'] == 2){
            if(!$_POST['offline_time']){
                $this->error_msg("请选择下线时间");
            }
        }else{
            $_POST['offline_time'] = '';
        }
        if($_POST['apk_url']) {
            $this->verify_link($_POST);
        }
        $app_message = $this->DAO->get_app_message($_POST['app_id']);
        if($app_message){
            $this->DAO->update_app_message($_POST);
        }elseif($_POST['nnb_scale'] || $_POST['relation_id']){
            $this->DAO->insert_app_message($_POST);
        }
        $this->DAO->update_app($_POST, $id);
        $this->succeed_msg();
    }

    private function verify_link($params){
        $link = $params['apk_url'];
        if(strstr($link,"https://") || strstr($link,"http://")){
            $this->error_msg("源包地址不能包含该https://或者http://");
        }
        $links=explode("/",$link);
        if($links[0]!='apk' || !$links[1]){
            $this->error_msg("源包地址有误,标准如下:apk/xxx.apk");
        }
        if(!strstr($links[1],"apk")){
            $this->error_msg("源包地址必须是apk包.");
        }
        $url = "http://106.75.78.43/api.php?act=verify&apk=".$links[1];
        $request = $this->request($url);
        if(empty($request)){
            $this->error_msg("未找到该包,请检查该包是否已上传.");
        }
    }

    public function to_apk_pack(){
        $results = array("error" => '1', "msg" => '网络请求错误');
        $params = $_POST;
        if(!$_POST['app_id'] || !$_POST['apk_url'] ||!$_POST['ch_status']){
            $results['msg']='缺少必填项';
            die(json_encode($results));
        }
        $link = $params['apk_url'];
        if(strstr($link,"https://") || strstr($link,"http://")){
            $results['msg']='源包地址不能包含该https://或者http://';
            die(json_encode($results));
        }
        $links=explode("/",$link);
        if($links[0]!='apk' || !$links[1]){
            $results['msg']='源包地址有误,标准如下:apk/xxx.apk';
            die(json_encode($results));
        }
        if(!strstr($links[1],"apk")){
            $results['msg']='源包地址必须是apk包';
            die(json_encode($results));
        }
        $url = "http://106.75.78.43/api.php?act=verify&apk=".$links[1];
        $request = $this->request($url);
        if(empty($request)){
            $results['msg']='未找到该包,请检查该包是否已上传.';
            die(json_encode($results));
        }
        $url = "http://106.75.78.43/api.php?act=do_all_pack";
        $result = $this->request($url,$params);
        if(!empty($result)){
            $results['msg'] = $result;
            die(json_encode($results));
        }
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

    public function do_app_notice_edit($id){
        if($_POST['notice_status']==1 && !$_POST['notice']){
            $this->error_msg("请填写公告");
        }
        $this->DAO->update_app_notice($_POST, $id);
        $this->succeed_msg();
    }

    public function do_app_add(){
        if(!$_POST['app_id'] || !$_POST['app_name']){
            $this->error_msg("缺少必填项");
        }
        if($this->DAO->check_app_id($_POST['app_id'])){
            $this->error_msg("APP ID已经被使用");
        }
        $app_key = md5(strtotime("now").rand(1,100000));

        $app_id = $this->DAO->insert_app($_POST,$app_key);

        if($_FILES['app_icon']['tmp_name']){
            $img = $this->up_img('app_icon',GAME_ICON,array(),1,1,$app_id,0);
            $this->DAO->update_app_icon($img, $app_id, $app_key);
        }
        $this->succeed_msg();
    }
    public function real_validate_view($id){
        $real_validate = $this->DAO->get_app_real_validate($id);
        $this->assign("real_validate",$real_validate['autonym']);
        $this->assign("id",$id);
        $this->display("app_real_validate.html");
    }
    public function real_validate_save($id){
        if (isset($_POST['autonym'])){
            $real_validate = $_POST['autonym'];
        }else{
            $this->error_msg("实名认证失败！");
        }
        $this->DAO->update_app_real_validate($id,$real_validate);
        $this->succeed_msg();
    }

    public function app_discount_edit($app_id){
        if ($app_id){
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1){
                $app_info = $this->DAO->get_app_name_by_id($app_id);
                $this->assign("info",$app_info[0]);
                $this->display("app_discount_edit_view.html");
            }else{
                die("你没有此权限，请联系管理员！");
            }
        }else{
            die("没有此游戏！");
        }
    }

    public function app_discount_save(){
        if ($_POST){
            $params = $_POST;
            $params['app_discount_new'] /= 10;
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if ($user_info['group_id']==1){
                if (strtoupper($params['code'])!=$_SESSION['c'])
                    $this->error_msg("验证码填写错误！");
                if (!preg_match("/(^1(\.0|\.00)?$)|(^0\.([1-9]|0[1-9]|[1-9][0-9])$)/",$params['app_discount_new']))
                    $this->error_msg("更改折扣非法！");
                if (!preg_match("/^\d{21}$/",$params['dd_id']))
                    $this->error_msg("钉钉编号非法！");
                $this->DAO->update_app_nd_discount($params['app_discount_new'],$params['app_id']);
                $result = $this->DAO->insert_nd_app_discount_log($params['app_id'],$params['dd_id'],$params['app_discount_new'],$user_info['id']);
                if ($result){
                    $this->succeed_msg();
                }else{
                    $this->error_msg("牛点折扣保存失败！");
                }
            }else{
                $this->error_msg("你没有此权限，请联系管理员！");
            }
        }else{
            $this->error_msg("没有相关数据！");
        }
    }

    public function web($id){
        $web = $this->DAO->get_app_web($id);
        $this->assign("is_web",$web['is_web']);
        $this->assign("id",$id);
        $this->display("app_web.html");
    }

    public function web_save($id){
        $this->DAO->update_app_web($id,$_POST['is_web']);
        $this->succeed_msg();
    }

    public function sdk_verify_rules_list(){
        $rules = $this->DAO->get_sdk_verify_rules($this->page);
        foreach ($rules AS $key=>$value){
            if ($value['type']==2){
                $rules[$key]['content'] = $this->time_exchange((int)$value['content']);
            }elseif ($value['type']==1){
                $rules[$key]['content'] = $value['content']."级";
            }
        }
        $page = $this->pageshow($this->page, "app.php?act=sdk_verify_rules&");
        $this->assign("page_bar",$page->show());
        $this->assign("datalist",$rules);
        $this->display("sdk_verify_rules_view.html");
    }

    public function sdk_rules_add(){
        $this->display("sdk_rules_add_view.html");
    }

    public function sdk_rules_save(){
        if ($_POST){
            if (!preg_match("/^[1-9][0-9]*$/",$_POST['rules_content'])){
                $this->error_msg("规则内容不符合！");
            }
            if ($_POST['rules_type']==2 && (int)$_POST['rules_content']>86400){
                $this->error_msg("时间不能超过24小时！");
            }
            $old_rules = $this->DAO->get_sdk_rules($_POST['rules_type']);
            foreach ($old_rules AS $value){
                if ($value['content']==$_POST['rules_content']){
                    $this->error_msg("该规则内容已经存在，请不要重复添加！");
                }
            }
            $res = $this->DAO->insert_sdk_verify_rules($_POST['rules_type'],$_POST['rules_content']);
            if ($res){
                $this->succeed_msg("新增成功！");
            }else{
                $this->error_msg("增加失败");
            }
        }else{
            $this->error_msg("数据错误！");
        }
    }

    public function app_verify_edit($app_id){
        if ($app_id){
            $level_res = $this->DAO->get_sdk_rules(1);
            foreach ($level_res as $key=>$value){
                $level_res[$key]['desc'] = $value['content']."级设定验证";
            }
            $time_res = $this->DAO->get_sdk_rules(2);
            foreach ($time_res as $k=>$val){
                $time_res[$k]['desc'] = "累计在线".$this->time_exchange((int)$val['content'])."验证";
            }
            $app_res = $this->DAO->get_app_name_by_id($app_id);
            $app_rules = array();
            if ($app_res[0]['verify_content']){
                $app_rules = explode(",",$app_res[0]['verify_content']);
            }
            $this->assign("level_arr",$level_res);
            $this->assign("time_arr",$time_res);
            $this->assign("app_arr",$app_rules);
            $this->assign("app",$app_res[0]);
            $this->display("sdk_verify_rules_edit.html");
        }else{
            die("无游戏数据！");
        }
    }

    public function app_rules_save(){
        if ($_POST){
            $params = $_POST;
            if ($params['verify_type']==0){
                $params['verify_content'] = "";
            }elseif ($params['verify_type']==1){
                $params['verify_content'] = implode(",",$params['level_verify_content']);
                if ($params['verify_content']==""){
                    $this->error_msg("等级不能为空！");
                }
            }elseif ($params['verify_type']==2){
                $params['verify_content'] = implode(",",$params['time_verify_content']);
                if ($params['verify_content']==""){
                    $this->error_msg("时间不能为空！");
                }
            }
            $this->DAO->update_app_rules($params['verify_type'],$params['verify_content'],$params['app_id']);
            $this->succeed_msg("编辑成功！");
        }else{
            $this->error_msg("数据错误！");
        }
    }
    //时间转换
    private function time_exchange($time){
        if ($time==86400){
            return "24小时";
        }
        $str = gmstrftime("%H:%M:%S",$time);
        $res = "";
        //时
        if (substr($str,0,2)=="00"){
        }elseif (substr($str,0,1)=="0"){
            $res .= substr($str,1,1)."小时";
        }else{
            $res .= substr($str,0,2)."小时";
        }
        //分
        if (substr($str,3,2)=="00"){
        }elseif (substr($str,3,1)=="0"){
            $res .= substr($str,4,1)."分钟";
        }else{
            $res .= substr($str,3,2)."分钟";
        }
        //秒
        if (substr($str,6,2)=="00"){
        }elseif (substr($str,6,1)=="0"){
            $res .= substr($str,7,1)."秒";
        }else{
            $res .= substr($str,6,2)."秒";
        }
        return $res;
    }

    public function app_verify_code_edit($appid){
        if ($appid){
            $app = $this->DAO->get_app_info($appid);
            $time = explode(",",$app['verifycode_time']);
            $app['start_time'] = date("Y-m-d",(int)$time[0]);
            $app['end_time'] = date("Y-m-d",(int)$time[1]);
            $this->assign("app",$app);
            $this->display("app_verify_code_edit_view.html");
        }else{
            die("无游戏记录");
        }
    }

    public function app_verify_code_save(){
        if ($_POST){
            $params = $_POST;
            $star_time = strtotime($params['start_time']);
            $end_time = strtotime($params['end_time']);
            $today = strtotime(date("Y-m-d",time()));
            if ($params['verifycode_type']==1){
                $params['verifycode_time'] = (string)$star_time.",".(string)$end_time;
                if ($star_time=="" || $end_time==""){
                    $this->error_msg("日期不能为空");
                }
                if ($today>$star_time){
                    $this->error_msg("日期不能早于当前时间");
                }
                if ($star_time==$end_time){
                    $this->error_msg("开始日期和结束日期不能相同");
                }
            }
            $this->DAO->update_app_verifycode_type($params['verifycode_type'],$params['verifycode_time'],$params['app_id']);
            $this->succeed_msg("编辑成功");
        }else{
            $this->error_msg("提交数据错误");
        }
    }

    public function export(){
        set_time_limit(0);
        $dataList =  $this->DAO->get_app_list_nolimit($_GET);
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
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->setTitle("游戏配置");
        $objActSheet->setCellValue("A1", "APPID");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "接入状态");
        $objActSheet->setCellValue("E1", "录入时间");
        $objActSheet->setCellValue("F1", "下线时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['ch_name']);
            if($info['access_type'] == 0){
                $objActSheet->setCellValue("D".$n,"接入中");
            }elseif($info['access_type'] == 1){
                $objActSheet->setCellValue("D".$n,"接入完成");
            }elseif($info['access_type'] == 2){
                $objActSheet->setCellValue("D".$n,"终止");
            }elseif($info['access_type'] == 3){
                $objActSheet->setCellValue("D".$n,"预接入");
            }elseif($info['access_type'] == 4){
                $objActSheet->setCellValue("D".$n,"对外运营");
            }
            $objActSheet->setCellValue("E".$n, date('Y-m-d H:i:s',$info['add_time']));
            if($info['offline_time']){
                $objActSheet->setCellValue("F".$n, date('Y-m-d H:i:s',$info['offline_time']));
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏配置-".$str_now.'.xls');
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

    public function apple_list($app_id){
        $params = $_POST;
        $app_info = $this->DAO->get_app_info($app_id);
        $apple_list = $this->DAO->get_apple_list($app_id);
        $data_list = $this->DAO->get_apple_data($app_id,$params['apple_id']);
        $this->assign("params",$params);
        $this->assign("app_info",$app_info);
        $this->assign("apple_list",$apple_list);
        $this->assign("data_list",$data_list);
        $this->display("apple_list.html");
    }

    public function do_add_apple(){
        $result = array('code'=>0,'msg'=>'网络出错');
        if(!$_POST['game_name'] || !$_POST['apple_id'] || !$_POST['channel']){
            $result['msg'] = '缺少必填参数！';
            die(json_encode($result));
        }
        if(preg_match("/[\x7f-\xff]/", $_POST['apple_id'])){
            $result['msg'] = '苹果ID不能包含中文';
            die(json_encode($result));
        }
        if($_POST['status'] == 2 && !$_POST['offline_time']){
            $result['msg'] = '下线时间不能为空';
            die(json_encode($result));
        }
        $apple_info = $this->DAO->get_apple_info($_POST['apple_id']);
        if($apple_info){
            $result['msg'] = '该苹果id已存在，请重新输入';
            die(json_encode($result));
        }else{
            $this->DAO->insert_apple($_POST);
        }
        $result['code'] = 1;
        $result['msg'] = '添加成功';
        die(json_encode($result));
    }

    public function apple_edit(){
        $info = $this->DAO->get_apple($_POST['app_id']);
        if(!$info){
            $result = array('code'=>0,'msg'=>'查无此数');
        }else{
            $result = array('code'=>1,'data'=>$info);
        }
        die(json_encode($result));
    }

    public function upload_img(){
        $file = $_FILES['file'];
        if($file["error"]==0) {
            //判断文件是否合法
            $types = ["jpg", "jpeg", "png", "gif"];
            $type = explode("/", $file["type"])[1];
            if (in_array($type, $types)) {
                $img = $this->up_img('file',GAME_ICON);
                $result = array("err" => 0, "msg" => "图片上传成功",'img' => $img);
            }else{
                $result = array("err" => 1, "msg" => "图片格式错误");
            }
            die(json_encode($result));
        }else{
            $result = array("err" => 1, "msg" => "图片太大，上传失败啦");
            die(json_encode($result));
        }
    }

    public function do_edit_apple(){
        $result = array('code'=>0,'msg'=>'网络出错');
        $params = $_POST;
        if(!$params['apple_id'] || !$params['game_name'] || !$params['channel']){
            $result['msg'] = '缺少必填项！';
            die(json_encode($result));
        }
        if (preg_match("/[\x7f-\xff]/", $params['apple_id'])) {
            $result['msg'] = '苹果ID不能包含中文！';
            die(json_encode($result));
        }
        if(strtotime($params['version_time'])>0 && !$params['version_url']){
            $result['msg'] = '请填写更新地址';
            die(json_encode($result));
        }
        if($params['version_url']){
            $params['version_url'] = trim($params['version_url']);
//            $array = get_headers($params['version_url'],1);
//            if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
//                $result['msg'] = '请填写正确的更新地址';
//                die(json_encode($result));
//            }
//            if($_SERVER['HTTP_REFERER'] == "" ){
//                header("Location:".$params['version_url']); exit;
//            }
        }
        $info = $this->DAO->get_apple_id($params);
        if($info){
            $result['msg'] = '该苹果id已存在，请重新输入';
            die(json_encode($result));
        }
        $this->DAO->update_apple($params);
        $result['code'] = 1;
        $result['msg'] = '更新成功';
        die(json_encode($result));
    }

    function do_edit_apple_notice(){
        $result = array('code'=>0,'msg'=>'网络出错');
        if($_POST['notice_status'] == 1 && !$_POST['notice']){
            $result['msg'] = '请填写公告';
            die(json_encode($result));
        }
        $this->DAO->update_apple_notice($_POST);
        $result['code'] = 1;
        $result['msg'] = '修改成功';
        die(json_encode($result));
    }
}