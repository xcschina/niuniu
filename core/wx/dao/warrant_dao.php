<?php
COMMON('dao','randomUtils');
class warrant_dao extends Dao {

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

    public function get_hm_wx_usr_info($openid){
//        $data = memcache_delete($this->mmc, 'usr_hn_wx_'.$openid);
        $data = memcache_get($this->mmc, 'usr_hn_wx_'.$openid);
        if(!$data){
            $hn_wx_id_md5 = md5((string)$openid."user_login_hn_wx");
            $user_id = $this->redis->get($hn_wx_id_md5);
            if (!$user_id){
                $this->sql = "select * from user_info where hn_wx_id=?";
                $this->params = array($openid);
                $this->doResult();
                $data = $this->result;
                if ($data){
                    //写入redis
                    $user_info = array("user_id"=>$data['user_id'],"nick_name"=>$data['nick_name'],"login_name"=>$data['login_name'],
                        "user_name"=>$data['user_name'],"id_number"=>$data['id_number'],"password"=>$data['password'],"mobile"=>$data['mobile'],
                        "age"=>$data['age'],"login_type"=>$data['login_type'],"token"=>$data['token']);
                    $user_msg = array("user_id"=>$data['user_id'],"m_verified"=>$data['m_verified'],"reg_time"=>$data['reg_time'],"wx_id"=>$data['wx_id'],"hn_wx_id"=>$data['hn_wx_id']);
                    $this->redis->set($hn_wx_id_md5,$data['user_id']);
                    $this->redis->hMset(md5((string)$data['user_id']."user_login"),$user_info);
                    $this->redis->hMset(md5((string)$data['user_id']."user_info"),$user_msg);
                    if ($data['login_name'] && $data['login_name']!=$data['hn_wx_id']){
                        $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                    }
                    if ($data['mobile'] && $data['mobile']!=$data['hn_wx_id']){
                        $this->redis->set(md5((string)$data['mobile']."user_login"),$data['user_id']);
                    }
                    $data = array_merge($user_info,$user_msg);
                }else{
                    $data = array();
                }
            }else{
                $data = $this->redis->hGetAll(md5((string)$user_id."user_login"));
                if (!$data){
                    $this->sql = "select * from user_info where hn_wx_id=?";
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
                        if ($data['login_name'] && $data['login_name']!=$data['hn_wx_id']){
                            $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                        }
                        if ($data['mobile'] && $data['mobile']!=$data['hn_wx_id']){
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
            memcache_set($this->mmc,'usr_hn_wx_'.$openid, $data);
        }
        return $data;
    }


    public function get_app_info($app_id){
//        $data = memcache_get($this->mmc, 'h5_app_info'.$app_id);
        if(!$data){
            $this->sql = "select * from `niuniu`.apps where app_id=? ";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,'h5_app_info'.$app_id, $data);
        }
        return $data;
    }


    public function update_usr_token($user_id,$token){
        $user_id_md5 = md5((string)$user_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_info = array_merge($user_info,array("token"=>$token));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function set_user_info($user_id){
        $user_id_md5 = md5((string)$user_id."user_login");
        $data = $this->redis->hGetAll($user_id_md5);
        if (!$data){
            $this->sql = "select * from user_info where user_id=?";
            $this->params = array($user_id);
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
                $data = array_merge($user_info,$user_msg);
            }else{
                $data = array();
            }
        }else{
            $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
            $data = array_merge($data,$user_msg);
        }
        memcache_set($this->mmc,'user_info_'.$user_id, $data);
        $bac = memcache_get($this->mmc, 'user_info_'.$user_id);
        return $bac;
    }

    public function h5_insert_user_info($params){
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
        $login_name = $params['chr'].(string)$user_id;
        $user_id_md5 = md5((string)$user_id."user_login");
        $login_name_md5 = md5((string)$login_name."user_login");
        $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>"","mobile"=>"","login_type"=>0,"token"=>$params['token'],"nick_name"=>$params['nick_name'],"id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$params['token'],"reg_time"=>strtotime("now"),"reg_ip"=>$params['reg_ip'],"m_verified"=>$params['m_verified'],"wx_id"=>$params['wx_id'],"hn_wx_id"=>"","reg_from"=>$params['reg_from'],"user_type"=>$params['user_type'],"from_app_id"=>$params['from_app_id'],"channel"=>$params['channel']);
        $this->redis->set($login_name_md5,$user_id);
        $this->redis->set(md5((string)$params['wx_id']."user_login_wx"),$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function insert_hn_user_info($params){
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
        $login_name = 'h'.(string)$user_id;
        $user_id_md5 = md5((string)$user_id."user_login");
        $login_name_md5 = md5((string)$login_name."user_login");
        $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>"","mobile"=>"","login_type"=>0,"token"=>$params['token'],"nick_name"=>$params['nick_name'],"id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$params['token'],"reg_time"=>strtotime("now"),"reg_ip"=>$params['reg_ip'],"m_verified"=>$params['m_verified'],"wx_id"=>"","hn_wx_id"=>$params['wx_id'],"reg_from"=>$params['reg_from'],"user_type"=>$params['user_type'],"from_app_id"=>$params['from_app_id'],"channel"=>$params['channel']);
        $this->redis->set($login_name_md5,$user_id);
        $this->redis->set(md5((string)$params['wx_id']."user_login_hn_wx"),$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }
}