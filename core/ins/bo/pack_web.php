<?php
COMMON('sdkCore');
DAO('pack_web_dao');

class pack_web extends sdkCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new pack_web_dao();
    }

    public function charge_pack(){
        $game_list = $this->DAO->get_upload_game_list();
        if(!$game_list){
            die("执行完毕");
        }
        foreach($game_list as $k=>$data){
            $diff_time = time()-$data['time'];
            if($diff_time > 1200){
                $size = $this->get_pick_size($data);
                if($size == (int)$data['apk_size']){
                   $this->DAO->update_guild_pack_record($data['id'],4);
                   echo $data['id'];
                   echo '</br>';
                   $this->err_log(var_export($data,1),"pack_web");

                }
            }
        }
    }

    public function pack_charge(){
        $this->open_debug();
        $game_list = $this->DAO->get_upload_game_list();
        if(!$game_list){
            die("执行完毕");
        }
        foreach($game_list as $k=>$data){
            $diff_time = time()-$data['time'];
            if($diff_time > 600){
                $size = $this->get_pick_size($data);
                if($size == (int)$data['apk_size']){
                    $this->DAO->update_guild_pack_record($data['id'],4);
                    if($data['pack_num'] == 1){
                        $cdn_url = 'http://apk.66173.cn/'.$data['app_id'].'/'.$data['down_url'];
                        $ressult = $this->update_preload($cdn_url);
                        if($ressult == 1){
                            $this->DAO->update_guild_pack_record($data['id'],5);
                        }
                    }else{
                        $cdn_url = 'http://apk.66173.cn/'.$data['app_id'].'/'.$data['down_url'];
                        $ressult = $this->update_refresh($cdn_url);
                        if($ressult == 1){
                            $this->DAO->update_guild_pack_record($data['id'],5);
                        }
                    }
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

    public function get_pick_size($params) {
        $conn = ftp_connect('eaaf45b36c07e7713dc935.backend.tan14.net');
        $login_result = ftp_login($conn, 'eaaf45b36c07e7713dc935', 'erK3Jm3X');
        ftp_pasv($conn, true);
        //ftp_cdup($conn);
        ftp_chdir($conn, $params['app_id']);
        //上传文件，ftp_put()函数能很好的胜任，它需要你指定一个本地文件名，上传后的文件名以及传输的类型。比方说：如果你想上传 "abc.txt"这个文件，上传后命名为"xyz.txt"，命令应该是这样：
        $res = ftp_size($conn, $params['down_url']);

        if ($res != -1) {
            ftp_close($conn);
            return $res;
        } else {
            ftp_close($conn);
            return 0;
        }
    }

}
