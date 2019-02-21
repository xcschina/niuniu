<?php
COMMON('baseCore');
DAO('login_dao','system_setting_dao');

class login_web extends baseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO=new login_dao();
    }

    public function login_view(){
        $this->display('login_view.html');
    }

    public function do_login(){
        $this->init_session();
        $this->open_debug();
        if($this->user_pwd_check()){
            header("location:index.php");
        }else{
            header("location:login.php?act=login");
        }
    }

    public function do_logout(){
        $this->DAO->update_user_active($_SESSION['usr_id'], 0);
        unset($_SESSION['usr_name']);
        unset($_SESSION['usr_id']);
        unset($_SESSION['real_name']);
        unset($_SESSION['group']);
        unset($_SESSION['menu_list']);
        unset($_SESSION['perm_arr']);
        header("location:login.php?act=login");
    }

    public function init_session(){
        unset($_SESSION['login_error_msg']);
    }

    //用户名密码验证
    public function user_pwd_check(){
        $account = $_POST["account"];//账号
        $password = $_POST["user_pwd"]; //密码
        $md5_pwd = md5($password);
        $user_info=$this->DAO->get_user_by_account($account);

        //如果加密后的密码和数据库里的相同 则返回true
        if(strtolower($md5_pwd)!= strtolower($user_info['usr_pwd'])){
            if($user_info){
                $_SESSION['login_error_msg']="密码错误，请重新输入";
                $this->DAO->do_login_log($account, $password, '密码错误', $this->client_ip(), $_SERVER['HTTP_USER_AGENT'],$user_info['id']);
                return false;
            }else{
                $_SESSION['login_error_msg']="用户名不存在，请重新输入";
                $this->DAO->do_login_log($account, $password, '用户名错误', $this->client_ip(), $_SERVER['HTTP_USER_AGENT']);
                return false;
            }
        }else{
            $systemDao=new system_setting_dao();
            $menu_arr="";
            $perm_arr="";
            if($user_info['id']){
                $perm_info=$systemDao->get_permissions_info($user_info['id']);
                $menu_arr =explode(',',$perm_info['module']);
                $perm_arr=explode(",",$perm_info['permissions']);
            }
            //获取菜单列表
            $m_list=$systemDao->get_module_list();
            foreach($m_list as $key=>$data){
                if(!in_array($data['id'],$menu_arr)){
                    unset($m_list[$key]);
                    continue;
                }
                $p_menu_list=$systemDao->get_menu_list($data['id']);
                foreach($p_menu_list as $i=>$list){
                    if(!in_array($list['id'],$menu_arr)){
                        unset($p_menu_list[$i]);
                        continue;
                    }
                    $menu_list=$systemDao->get_menu_list($list['id']);
                    foreach($menu_list as $j=>$clist){
                        if(!in_array($clist['id'],$menu_arr)){
                            unset($menu_list[$j]);
                            continue;
                        }
                    }
                    $p_menu_list[$i]['c_menu']=$menu_list;
                }
                $m_list[$key]['p_menu']=$p_menu_list;
            }

            //保存用户名到session
            $_SESSION["usr_name"] = $user_info['usr_name'];
            $_SESSION["usr_id"] = $user_info['id'];
            $_SESSION["real_name"] = $user_info['real_name'];
            $_SESSION["group"] = $user_info['group'];
            $_SESSION['last_ip']=$user_info['last_ip'];
            $_SESSION['menu_list']=$m_list;//可见菜单
            $_SESSION['perm_arr']=$perm_arr;//菜单权限
            $_SESSION['shop_id'] = $user_info['shop_id'];//店铺信息
           // $this->DAO->login_log($user_info['id'],$this->client_ip());
            //$this->DAO->do_login_log($account, $password, '登录成功', $this->client_ip(), $_SERVER['HTTP_USER_AGENT'],$user_info['id']);
            $this->DAO->update_user_active($user_info['id'], strtotime("now"));
            return true;
        }
    }
}
?>