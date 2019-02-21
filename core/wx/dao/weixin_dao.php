<?php
COMMON('dao');
class weixin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_wx_usr_info($openid){
        $data = memcache_delete($this->mmc, 'usr_wx_'.$openid);
        $data = memcache_get($this->mmc, 'usr_wx_'.$openid);
        if(!$data){
            $wx_id_md5 = md5((string)$openid."user_login_wx");
            $user_id = $this->redis->get($wx_id_md5);
            if (!$user_id){
                $this->sql = "select * from user_info where wx_id=?";
                $this->params = array($openid);
                $this->doResult();
                $data = $this->result;
                if ($data){
                    //写入redis
                    $user_info = array("user_id"=>$data['user_id'],"nick_name"=>$data['nick_name'],"login_name"=>$data['login_name'],
                        "user_name"=>$data['user_name'],"id_number"=>$data['id_number'],"password"=>$data['password'],"mobile"=>$data['mobile'],
                        "age"=>$data['age'],"login_type"=>$data['login_type'],"token"=>$data['token']);
                    $user_msg = array("user_id"=>$data['user_id'],"m_verified"=>$data['m_verified'],"reg_time"=>$data['reg_time'],"wx_id"=>$data['wx_id'],"hn_wx_id"=>$data['hn_wx_id']);
                    $this->redis->set($wx_id_md5,$data['user_id']);
                    $this->redis->hMset(md5((string)$data['user_id']."user_login"),$user_info);
                    $this->redis->hMset(md5((string)$data['user_id']."user_info"),$user_msg);
                    if ($data['login_name'] && $data['login_name']!=$data['wx_id']){
                        $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                    }
                    if ($data['mobile'] && $data['mobile']!=$data['wx_id']){
                        $this->redis->set(md5((string)$data['mobile']."user_login"),$data['user_id']);
                    }
                    $data = array_merge($user_info,$user_msg);
                }else{
                    $data = array();
                }
            }else{
                $data = $this->redis->hGetAll(md5((string)$user_id."user_login"));
                if (!$data){
                    $this->sql = "select * from user_info where wx_id=?";
                    $this->params = array($openid);
                    $this->doResult();
                    $data = $this->result;
                    if ($data){
                        //写入redis
                        $user_info = array("user_id"=>$data['user_id'],"nick_name"=>$data['nick_name'],"login_name"=>$data['login_name'],
                            "user_name"=>$data['user_name'],"id_number"=>$data['id_number'],"password"=>$data['password'],"mobile"=>$data['mobile'],
                            "age"=>$data['age'],"login_type"=>$data['login_type'],"token"=>$data['token']);
                        $user_msg = array("user_id"=>$data['user_id'],"m_verified"=>$data['m_verified'],"reg_time"=>$data['reg_time'],"wx_id"=>$data['wx_id'],"hn_wx_id"=>$data['hn_wx_id']);
                        $this->redis->hMset(md5((string)$data['user_id']."user_login"),$user_info);
                        $this->redis->hMset(md5((string)$data['user_id']."user_info"),$user_msg);
                        if ($data['login_name'] && $data['login_name']!=$data['wx_id']){
                            $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                        }
                        if ($data['mobile'] && $data['mobile']!=$data['wx_id']){
                            $this->redis->set(md5((string)$data['mobile']."user_login"),$data['user_id']);
                        }
                        $data = array_merge($user_info,$user_msg);
                    }else{
                        $data = array();
                    }
                }else{
                    $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
                    $data = array_merge($data,$user_msg);
                }
            }
            memcache_set($this->mmc,'usr_wx_'.$openid, $data);
        }
        return $data;
    }

    public function get_user_gifts($user_id){
        $data = memcache_get($this->mmc, 'usr_gifts'.$user_id);
        if(!$data){
            $this->sql = "select a.*,b.code,c.title,b.batch_id from weixin_gifts as a INNER join game_gifts as b on a.gift_id=b.id
                      INNER join game_gift_info as c on b.batch_id=c.id where a.user_id=? order by a.id desc";
            $this->params = array($user_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'usr_gifts'.$user_id, $data);
        }
        return $data;
    }

    public function get_user_by_mobile($mobile){
        $phone_id_md5 = md5((string)$mobile."user_login");
        $user_id = $this->redis->get($phone_id_md5);
        if (!$user_id){
            $this->sql = "select * from user_info where mobile=?";
            $this->params = array($mobile);
            $this->doResult();
            $user_info = $this->result;
            if ($user_info){
                $user_msg = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
                $user_info = array("user_id"=>$user_info['user_id'],"nick_name"=>$user_info['nick_name'],"login_name"=>$user_info['login_name'],
                    "user_name"=>$user_info['user_name'],"id_number"=>$user_info['id_number'],"password"=>$user_info['password'],
                    "mobile"=>$user_info['mobile'],"age"=>$user_info['age'],"login_type"=>$user_info['login_type'],"token"=>$user_info['token']);
                $this->redis->set(md5((string)$mobile."user_login"),$user_info['user_id']);
                if ($user_info['login_name'] && $user_info['login_name']!=$mobile){
                    $this->redis->set(md5((string)$user_info['login_name']."user_login"),$user_info['user_id']);
                }
                $this->redis->hMset(md5((string)$user_info['user_id']."user_login"),$user_info);
                $this->redis->hMset(md5((string)$user_info['user_id']."user_info"),$user_msg);
                $user_info = array_merge($user_info,$user_msg);
            }else{
                $user_info = array();
            }
        }else{
            $user_info = $this->redis->hGetAll(md5((string)$user_id."user_login"));
            if (!$user_info){
                $this->sql = "select * from user_info where mobile=?";
                $this->params = array($mobile);
                $this->doResult();
                $user_info = $this->result;
                if ($user_info){
                    $user_msg = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
                    $user_info = array("user_id"=>$user_info['user_id'],"nick_name"=>$user_info['nick_name'],"login_name"=>$user_info['login_name'],
                        "user_name"=>$user_info['user_name'],"id_number"=>$user_info['id_number'],"password"=>$user_info['password'],
                        "mobile"=>$user_info['mobile'],"age"=>$user_info['age'],"login_type"=>$user_info['login_type'],"token"=>$user_info['token']);
                    if ($user_info['login_name'] && $user_info['login_name']!=$mobile){
                        $this->redis->set(md5((string)$user_info['login_name']."user_login"),$user_info['user_id']);
                    }
                    $this->redis->hMset(md5((string)$user_info['user_id']."user_login"),$user_info);
                    $this->redis->hMset(md5((string)$user_info['user_id']."user_info"),$user_msg);
                    $user_info = array_merge($user_info,$user_msg);
                }else{
                    $user_info = array();
                }
            }else{
                $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
                $user_info = array_merge($user_info,$user_msg);
            }
        }
        return $user_info;
    }

    public function update_usr_wxid($wx_id, $user_id){
        $user_id_md5 = md5((string)$user_id."user_info");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT m_verified,reg_time,wx_id,hn_wx_id,user_id FROM user_info WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            $user_info = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
        }
        if ($user_info['wx_id']) $this->redis->delete(md5((string)$user_info['wx_id']."user_login_wx"));
        if ($wx_id) $this->redis->set(md5((string)$wx_id."user_login_wx"),$user_id);
        $user_info = array_merge($user_info,array("wx_id"=>$wx_id));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",md5((string)$user_id."user_login"));
        $this->update_wx_gift($wx_id, $user_id);
    }

    public function insert_wx_user($guid, $wx_info, $mobile, $password, $ip){
        if ($this->redis->exists("incrhash_user") == 1){
            $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
        }else{
            $this->sql = "SELECT user_id FROM user_info ORDER BY user_id DESC LIMIT 1";
            $this->doResult();
            $user_info_id = $this->result;
            if ($this->redis->exists("incrhash_user") == 1){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }elseif ($user_info_id['user_id']){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",(int)$user_info_id['user_id']+1000);
            }else{
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }
        }
        $login_name = 'n'.(string)$user_id;
        $user_id_md5 = md5((string)$user_id."user_login");
        $login_name_md5 = md5((string)$login_name."user_login");
        $phone_md5 = md5((string)$mobile."user_login");
        $wx_id_md5 = md5((string)$wx_info['openid']."user_login_wx");
        $user_login = array("user_id"=>$user_id,"login_name"=>"","user_name"=>"","password"=>md5($password),"mobile"=>$mobile,"login_type"=>0,"token"=>"","nick_name"=>$wx_info['nickname'],"id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$ip,"m_verified"=>1,"wx_id"=>$wx_info['openid'],"hn_wx_id"=>"","reg_from"=>3,"buy_mobile"=>$mobile);
        $this->redis->set($wx_id_md5,$user_id);
        $this->redis->set($phone_md5,$user_id);
        $this->redis->set($login_name_md5,$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        $this->update_wx_gift($wx_info['openid'], $user_id);
        return $user_id;
    }

    public function update_wx_gift($wx_id, $user_id){
        $this->sql = "update weixin_gifts set user_id=? where openid=?";
        $this->params = array($user_id, $wx_id);
        $this->doExecute();
        memcache_delete($this->mmc, 'usr_gifts'.$user_id);
    }
}