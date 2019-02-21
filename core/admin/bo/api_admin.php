<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('account_admin_dao','menu_admin_dao','app_admin_dao');

class api_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new api_admin_dao();
    }

    //包地址验证
    public function apk_verify($apk_link){
        if(!$apk_link) {
            echo 0;
            exit();
        }elseif(!is_file(PREFIX.DS."htdocs".DS.'apk'.DS.$apk_link)) {
            echo 0;
            exit();
        }
        echo 1;
        exit();
    }

    public function do_pack(){
        $cache = $this->DAO->get_cache();
        if($cache){
            die("服务器正在打包中,请稍后打包");
        }
        $params = $_POST;
        $pack_lsit = $this->DAO->get_pack_info($params['app_id']);
        if(!empty($pack_lsit['channels'])){
            $position = strpos($pack_lsit['channels'],$params['user_code']);
            if($position === FALSE){
                $params['new_pack_ch'] = $pack_lsit['channels'].",".$params['user_code'];
            }else{
                $params['new_pack_ch'] = $pack_lsit['channels'];
            }
        }
        $params['pack_ch'] = $params['user_code'];
        $url = substr($params['apk_url'], 4);
        $this->DAO->set_cache();
        //服务器打包
        $pack_info = $this->go_apk_pack($params);
        //更打包记录,上传cdn
        if($pack_info === 'success' && !$pack_lsit){
            $this->DAO->insert_pack_info($params);
            $guild_info = $this->DAO->get_guild_pack_info($params['app_id'], $params['user_code']);
            if(!$guild_info){
                $apk_url = str_replace(".apk","_".$params['user_code'].".apk",$url);
                $this->DAO->insert_guild_pack_info($params['app_id'], $params['user_code'],$apk_url);
                $this->push_ftp($params['app_id'],$apk_url);
            }else{
                $apk_url = str_replace(".apk","_".$params['user_code'].".apk",$url);
                $apk_size = filesize(PREFIX . DS . "htdocs" . DS . 'apk' . DS . $apk_url);
//                $this->DAO->update_guild_pack_info($params['app_id'], $params['user_code'],$apk_url);
                $this->DAO->update_guild_pack_record($params['app_id'], $params['user_code'],$apk_size,$apk_url,3);
                $this->push_ftp($params['app_id'],$apk_url);
                $this->err_log(var_export($params,1),'yyq_test');
                $this->DAO->update_guild_pack_record($params['app_id'], $params['user_code'],$apk_size,$apk_url,4);
            }
        }elseif($pack_info === 'success'){
            $this->DAO->update_pack_info($params, $params['id']);
            $guild_info = $this->DAO->get_guild_pack_info($params['app_id'], $params['user_code']);
            if(!$guild_info){
                $apk_url = str_replace(".apk","_".$params['user_code'].".apk",$url);
                $this->DAO->insert_guild_pack_info($params['app_id'], $params['user_code'],$apk_url);
                $this->push_ftp($params['app_id'],$apk_url);
            }else{
                $apk_url = str_replace(".apk","_".$params['user_code'].".apk",$url);
                $apk_size = filesize(PREFIX . DS . "htdocs" . DS . 'apk' . DS . $apk_url);
//                $this->DAO->update_guild_pack_info($params['app_id'], $params['user_code'],$apk_url);
                $this->DAO->update_guild_pack_record($params['app_id'], $params['user_code'],$apk_size,$apk_url,3);
                $this->push_ftp($params['app_id'],$apk_url);
                $this->err_log(var_export($params,1),'yyq_test');
                $this->DAO->update_guild_pack_record($params['app_id'], $params['user_code'],$apk_size,$apk_url,4);
            }
        }
        $this->DAO->del_cache();
    }


    public function get_apk_size($appid,$apk_name){
//        error_reporting(E_ALL);
//        $this->open_debug();
        $conn = ftp_connect('eaaf45b36c07e7713dc935.backend.tan14.net');
        $login_result = ftp_login($conn, 'eaaf45b36c07e7713dc935', 'erK3Jm3X');
        ftp_pasv($conn, true);
        ftp_mkdir($conn, $appid);
        //ftp_cdup($conn);
        ftp_chdir($conn, $appid);
        $res = ftp_size($conn, $apk_name);
        ftp_close($conn);
    }


    public function do_all_pack(){
        $cache = $this->DAO->get_cache();
        if($cache){
            die("服务器正在打包中,请稍后打包");
        }
        $params = $_POST;
        $pack_ch_arr = array();
        $app_channels = array();
        $pack_ch_list = $this->DAO->get_pack_ch_list($params['app_id']);
        if(!$pack_ch_list){
            die("该游戏还未关联渠道");
        }
        $app_info = $this->DAO->get_pack_info($params['app_id']);
        if(!empty($app_info['channels'])){
            $app_channels = explode(",", $app_info['channels']);
        }
        //分包状态判断
        if($params['ch_status']=='2'){
            $app_channels = array();
        }
        foreach ($pack_ch_list as $key => $data) {
            if (!empty($data['user_code']) && !in_array($data['user_code'], $app_channels)) {
                array_push($app_channels, $data['user_code']);
                array_push($pack_ch_arr, $data['user_code']);
            }
        }
        $params['pack_ch'] = implode(",", $pack_ch_arr);
        $params['new_pack_ch'] = implode(",", $app_channels);
        if (empty($params['pack_ch'])) {
            die("无新渠道需打包");
        }else{
            $this->DAO->set_cache();
            $url = substr($params['apk_url'], 4);
            $pack_info = $this->go_apk_pack($params);
            if($pack_info == 'success' && !$app_info){
                $this->DAO->insert_pack_info($params);
                foreach ($pack_ch_arr as $key=>$guild){
                    $guild_info = $this->DAO->get_guild_pack_info($params['app_id'], $guild);
                    if(!$guild_info){
                        $apk_url = str_replace(".apk","_".$guild.".apk",$url);
                        $this->DAO->insert_guild_pack_info($params['app_id'], $guild,$apk_url);
                        $this->push_ftp($params['app_id'],$apk_url);
                    }else{
                        $apk_url = str_replace(".apk","_".$guild.".apk",$url);
                        $this->DAO->update_guild_pack_info($params['app_id'], $guild,$apk_url);
                        $this->push_ftp($params['app_id'],$apk_url);
                    }
                }
            }elseif($pack_info == 'success'){
                $this->DAO->update_pack_info($params, $app_info['id']);
                foreach ($pack_ch_arr as $key=>$guild){
                    $guild_info = $this->DAO->get_guild_pack_info($params['app_id'], $guild);
                    if(!$guild_info){
                        $apk_url = str_replace(".apk","_".$guild.".apk",$url);
                        $this->DAO->insert_guild_pack_info($params['app_id'], $guild,$apk_url);
                        $this->push_ftp($params['app_id'],$apk_url);
                    }else{
                        $apk_url = str_replace(".apk","_".$guild.".apk",$url);
                        $this->DAO->update_guild_pack_info($params['app_id'], $guild,$apk_url);
                        $this->push_ftp($params['app_id'],$apk_url);
                    }
                }
            }
        }
        $this->DAO->del_cache();
    }

    public function push_ftp($appid,$apk_name){
//        error_reporting(E_ALL);
//        $this->open_debug();
        $conn = ftp_connect('eaaf45b36c07e7713dc935.backend.tan14.net') or die('not connect');
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
            $this->DAO->update_pack_status(2,$data['pack_id']);
            return "success";
            //echo $err;
        } catch (Exception $e) {
            echo "发生错误:";
            print $e->getMessage();
        }
    }
}