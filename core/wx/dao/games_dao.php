<?php
COMMON('dao','randomUtils');
class games_dao extends Dao {

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

    public function get_wx_unionid_info($unionid){
//        $data = memcache_delete($this->mmc, 'usr_wx_unionid_'.$unionid);
        $data = memcache_get($this->mmc, 'usr_wx_unionid_'.$unionid);
        if(!$data){
            $wx_unionid_md5 = md5((string)$unionid."usr_wx_unionid");
            $user_id = $this->redis->get($wx_unionid_md5);
            if(!$user_id){
                $this->sql = "select * from user_info where unionid=?";
                $this->params = array($unionid);
                $this->doResult();
                $data = $this->result;
                if($data){
                    //写入redis
                    $user_info = array("user_id"=>$data['user_id'],"nick_name"=>$data['nick_name'],"login_name"=>$data['login_name'],
                        "user_name"=>$data['user_name'],"id_number"=>$data['id_number'],"password"=>$data['password'],"mobile"=>$data['mobile'],
                        "age"=>$data['age'],"login_type"=>$data['login_type'],"token"=>$data['token']);
                    $user_msg = array("user_id"=>$data['user_id'],"m_verified"=>$data['m_verified'],"reg_time"=>$data['reg_time'],"wx_id"=>$data['wx_id'],"hn_wx_id"=>$data['hn_wx_id'],"unionid"=>$data['unionid']);
                    $this->redis->set($wx_unionid_md5,$data['user_id']);
                    $this->redis->hMset(md5((string)$data['user_id']."user_login"),$user_info);
                    $this->redis->hMset(md5((string)$data['user_id']."user_info"),$user_msg);
                    if ($data['login_name'] && $data['login_name']!=$data['unionid']){
                        $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                    }
                    if ($data['mobile'] && $data['mobile']!=$data['unionid']){
                        $this->redis->set(md5((string)$data['mobile']."user_login"),$data['user_id']);
                    }
                    $data = array_merge($user_info,$user_msg);
                }else{
                    $data = array();
                }
            }else{
                $data = $this->redis->hGetAll(md5((string)$user_id."user_login"));
                if (!$data){
                    $this->sql = "select * from user_info where unionid=?";
                    $this->params = array($unionid);
                    $this->doResult();
                    $data = $this->result;
                    if ($data){
                        //写入redis
                        $user_info = array("user_id"=>$data['user_id'],"nick_name"=>$data['nick_name'],"login_name"=>$data['login_name'],
                            "user_name"=>$data['user_name'],"id_number"=>$data['id_number'],"password"=>$data['password'],"mobile"=>$data['mobile'],
                            "age"=>$data['age'],"login_type"=>$data['login_type'],"token"=>$data['token']);
                        $user_msg = array("user_id"=>$data['user_id'],"m_verified"=>$data['m_verified'],"reg_time"=>$data['reg_time'],"wx_id"=>$data['wx_id'],"hn_wx_id"=>$data['hn_wx_id'],"unionid"=>$data['unionid']);
                        $this->redis->hMset(md5((string)$data['user_id']."user_login"),$user_info);
                        $this->redis->hMset(md5((string)$data['user_id']."user_info"),$user_msg);
                        if ($data['login_name'] && $data['login_name']!=$data['unionid']){
                            $this->redis->set(md5((string)$data['login_name']."user_login"),$data['user_id']);
                        }
                        if ($data['mobile'] && $data['mobile']!=$data['unionid']){
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
            memcache_set($this->mmc,'usr_wx_unionid_'.$unionid, $data);
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

    public function get_game_info($app_id){
//        $data = memcache_delete($this->mmc, 'h5_game_info'.$app_id);
        $data = memcache_get($this->mmc, 'h5_game_info'.$app_id);
        if(!$data){
            $this->sql = "select * from `niuniu`.apps where app_id=? ";
//            $this->sql = "select * from `niuniu`.apps where app_id=? and app_type=3";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,'h5_game_info'.$app_id, $data);
        }
        return $data;
    }

    public function get_user_pid($pid){
//        $data = memcache_delete($this->mmc, 'h5_game_info'.$pid);
        $data = memcache_get($this->mmc, 'h5_game_info'.$pid);
        if(!$data){
            $this->sql = "select * from `niuniu`.app_ios_pack where apple_id=? ";
            $this->params = array($pid);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,'h5_game_info'.$pid, $data);
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

    public function insert_user_info($params){
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
        $user_info = array("user_id"=>$user_id,"guid"=>$params['token'],"reg_time"=>strtotime("now"),"reg_ip"=>$params['reg_ip'],"m_verified"=>$params['m_verified'],"wx_id"=>$params['wx_id'],"hn_wx_id"=>"","reg_from"=>$params['reg_from'],"user_type"=>$params['user_type'],"from_app_id"=>$params['from_app_id'],"channel"=>$params['channel']);
        $this->redis->set($login_name_md5,$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function insert_unionid_user_info($params){
        if ($this->redis->exists("incrhash_user") == 1){
            $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
        }else{
            $this->sql = "SELECT user_id FROM user_info ORDER BY user_id DESC LIMIT 1";
            $this->doResult();
            $user_info_id = $this->result;
            if($this->redis->exists("incrhash_user") == 1){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }elseif($user_info_id['user_id']){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",(int)$user_info_id['user_id']+1000);
            }else{
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }
        }
        $login_name = 'un'.(string)$user_id;
        $user_id_md5 = md5((string)$user_id."user_login");
        $login_name_md5 = md5((string)$login_name."user_login");
        $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>"","mobile"=>"","login_type"=>0,"token"=>$params['token'],"nick_name"=>$params['nick_name'],"id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$params['token'],"reg_time"=>strtotime("now"),"reg_ip"=>$params['reg_ip'],"m_verified"=>$params['m_verified'],"wx_id"=>"","hn_wx_id"=>"","reg_from"=>$params['reg_from'],"user_type"=>$params['user_type'],"from_app_id"=>$params['from_app_id'],"unionid"=>$params['unionid']);
        $this->redis->set($login_name_md5,$user_id);
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
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function get_wx_app_info($pid){
        $this->sql = "select * from `niuniu`.apps where app_id=? ";
        $this->params = array($pid);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function add_wx_user_log($session_key,$openid,$unionid,$wx_info){
        $this->sql = "insert into  `niuniu`.wx_app_login_tb(app_id,js_code,grant_type,open_id,session_key,unionid,add_time) values (?,?,?,?,?,?,?)";
        $this->params=array($wx_info['appid'],$wx_info['js_code'],$wx_info['grant_type'],$openid,$session_key,$unionid,time());
        $this->doInsert();
    }

    public function get_money_info($app_id,$money_id){
        $this->sql = "select b.app_name,a.* from `niuniu`.app_goods as a inner join `niuniu`.apps as b on a.app_id=b.app_id where a.app_id=? and a.good_code=? and a.status=1 order by good_price";
        $this->params = array($app_id,$money_id);
        $this->doResult();
        return $this->result;
    }

    public function create_order($order){
        $this->sql = "insert into `niuniu`.orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount, 
                                        pay_money,discount,pay_price,status, buy_time, ip, serv_id, channel,payExpandData,serv_name)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_user_unionid($user_id){
        $this->sql = "select * from user_info where user_id=? ";
        $this->params = array($user_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_wx_user_openid($unionid,$wx_app_id){
        $this->sql = "select * from `niuniu`.wx_app_login_tb where unionid=? and app_id=? ";
        $this->params = array($unionid,$wx_app_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

}