<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper');
DAO('auto_pack_admin_dao');

class auto_pack_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new auto_pack_admin_dao();
    }

    public function param_verify($app_id, $data){
        if (!$_SESSION['usr_id'] || !$app_id) {
            $data['msg'] = '未能获取用户信息,请重新登录.';
            die(json_encode($data));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if ($user_info['group_id'] == '10' && empty($user_info['user_code'])) {
            $data['msg'] = '未能获取公会代码,请联系管理员。';
            die(json_encode($data));
        } elseif ($user_info['group_id'] == '1') {
            $user_info['user_code'] = 'nnwl';
        } elseif($user_info['group_id'] != '10'){
            $data['msg'] = '你没有自助打包的权限.请联系管理员';
            die(json_encode($data));
        }
        if (preg_match("/[\x7f-\xff]/", $user_info['user_code'] )) {
            $data['msg'] = '公会参数异常,请联系管理员。';
            die(json_encode($data));
        }
        $_SESSION['user_code'] = $user_info['user_code'];
        //是否正在打包
        $pack_info = $this->DAO->get_pack_in_list($app_id, $user_info['user_code'],0);
        if ($pack_info) {
            $data['msg'] = '已提交申请，无需重复打包。';
            die(json_encode($data));
        }
    }

    public function do_pack_app($app_id){
        $data = array("error" => '1', 'msg' => '网络请求错误,请重新登录.');
        //参数验证
        $this->param_verify($app_id, $data);
        $app_info = $this->DAO->get_app_info($app_id);
        if (!$app_info || empty($app_info['apk_url'])) {
            $data['msg'] = '源包异常，请联系管理员。';
            die(json_encode($data));
        }
        $url = substr($app_info['apk_url'], 4);
        $apk_url = str_replace(".apk", "_" . $_SESSION['user_code'] . ".apk", $url);
        $pack_data = array(
            'apk_url' => $apk_url,
            'app_id' => $app_id,
            'user_code' => $_SESSION['user_code'],
            'id' => $app_info['id']
        );

        $pack_log = $this->DAO->get_pack_record($app_id, $_SESSION['user_code']);
        if(!$pack_log){
            $this->DAO->add_pack_record($pack_data);
        }else{
            $this->DAO-> update_pack_record($pack_log['id'],0);
        }
        $app_pack_info = $this->DAO->get_app_pack_info($app_id);
        if($app_pack_info){
            if($app_pack_info['channels']){
                $old_ch = explode(',',$app_pack_info['channels']);
                if(!in_array($_SESSION['user_code'],$old_ch)){
                    array_push($old_ch,$_SESSION['user_code']);
                }
                $new_ch = implode(',',$old_ch);
                $this->DAO->update_app_pack_info($app_pack_info['id'],$new_ch);
            }else{
                $this->DAO->update_app_pack_info($app_pack_info['id'],$_SESSION['user_code']);
            }
        }else{
            $this->DAO->insert_app_pack_info($url,$app_id,$_SESSION['user_code']);
        }

        $data = array("error" => '0', 'msg' => '已成功提交打包申请，正在快马加鞭的打包中。');
        die(json_encode($data));
    }

    public function auto_pack(){
        $upload_pack = $this->DAO->get_pack_lsit(3);
        if ($upload_pack) {
            die('有包在上传中');
        }
        $do_pack = $this->DAO->get_pack_lsit(1);
        if($do_pack){
            die('服务器正在打包中');
        }
        $pack_list = $this->DAO->get_pack_lsit(0);
        if(!$pack_list){
            die('全部都打完了');
        }
        if(!$pack_list['guild_code'] || !$pack_list['app_id'] ||!$pack_list['down_url']){
            $this->DAO->update_pack_log($pack_list['id'],10);
            die("参数错误");
        }
        $app_pack_info = $this->DAO->get_app_info($pack_list['app_id']);
        if(!$app_pack_info || !$app_pack_info['apk_url']){
            $this->DAO->update_pack_log($pack_list['id'],11);
            die("参数错误");
        }
        $params = array(
            'apk_url'=>$app_pack_info['apk_url'],
            'pack_ch'=>$pack_list['guild_code'],
            'pack_id'=>$pack_list['id']
        );
        $pack_info = $this->go_apk_pack($params);
        //更打包记录,上传cdn
        if($pack_info === 'success'){
            $url = substr($app_pack_info['apk_url'], 4);
            $apk_url = str_replace(".apk","_".$pack_list['guild_code'].".apk",$url);
            $apk_size = filesize(PREFIX . DS . "htdocs" . DS . 'apk' . DS . $apk_url);
            $this->DAO->update_pack_log($pack_list['id'],3);
            $this->DAO->update_pack_size($pack_list['id'],$apk_size);
            $this->push_ftp($pack_list['app_id'],$pack_list['down_url']);
            $this->DAO->update_pack_log($pack_list['id'],4);
            //预加载
            if($pack_list['pack_num'] == 1){
                $cdn_url = 'http://apk.66173.cn/'.$pack_list['app_id'].'/'.$pack_list['down_url'];
                $ressult = $this->update_preload($cdn_url);
                if($ressult == 1){
                    $this->DAO->update_pack_log($pack_list['id'],5);
                }
            }else{
                $cdn_url = 'http://apk.66173.cn/'.$pack_list['app_id'].'/'.$pack_list['down_url'];
                $ressult = $this->update_refresh($cdn_url);
                if($ressult == 1){
                    $this->DAO->update_pack_log($pack_list['id'],5);
                }
            }
        }
    }

    public function update_preload($cdn_url){
        $token = $this->get_cdn_token();
        $url='https://api3.verycloud.cn/API/cdn/preload';
        $data = array(
            'token' => $token,
            'urls' => $cdn_url
        );
        $result = $this->request($url,$data);
        $result = json_decode($result);
        if($result->code == 1 && $result->message == "操作完成"){
            return  1 ;
        }
        return 0;
    }

    public function update_refresh($cdn_url){
        $token = $this->get_cdn_token();
        $url='https://api3.verycloud.cn/API/cdn/refresh';
        $data = array(
            'token' => $token,
            'type' => 'file',
            'urls' => $cdn_url
        );
        $result = $this->request($url,$data);
        $result = json_decode($result);
        if($result->code == 1 && $result->message == "操作完成"){
            return  1 ;
        }
        return 0;
    }

    public function get_cdn_token(){
        require_once (COMMON.'/cdn/api/Token.php');
        $token = new \demo\api\Token();
        return  $token->token();
    }

    public function push_ftp($appid,$apk_name){
        $conn = ftp_connect('eaaf45b36c07e7713dc935.backend.tan14.net') or die('conld not connect');
        $login_result = ftp_login($conn, 'eaaf45b36c07e7713dc935', 'erK3Jm3X');
        ftp_pasv($conn, true);
        ftp_mkdir($conn, $appid);
        //ftp_cdup($conn);
        ftp_chdir($conn, $appid);
        //上传文件，ftp_put()函数能很好的胜任，它需要你指定一个本地文件名，上传后的文件名以及传输的类型。比方说：如果你想上传 "abc.txt"这个文件，上传后命名为"xyz.txt"，命令应该是这样：
        ftp_put($conn, $apk_name, PREFIX.DS.'htdocs'.DS.'apk'.DS.$apk_name, FTP_BINARY);
        ftp_close($conn);
    }



    private function go_apk_pack($data){
        ob_start(); //打开输出缓冲区
        ob_end_flush();
        ob_implicit_flush(1); //立即输出
        $this->DAO->update_pack_log($data['pack_id'],1);//准备打包
        $cmd = "java -jar /data/wwwroot/androidpack/AndroidPack.jar "
            ."/data/wwwroot/niuniu/htdocs/apk "
            ."/data/wwwroot/niuniu/htdocs/".$data['apk_url']." "
            ."'".$data['pack_ch']."' "
            ."/data/wwwroot/niuniu/htdocs/wjx.keystore "
            ."12152205 ";
        echo $cmd;
        echo "<br/>";
        try {
            system($cmd,$err);
            $this->DAO->update_pack_log($data['pack_id'],2);//打包完成
            return "success";
            //echo $err;
        } catch (Exception $e) {
            echo "发生错误:";
            print $e->getMessage();
        }
    }
}