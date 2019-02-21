<?php
COMMON('baseCore');
DAO('system_password_dao');


class system_password extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new system_password_dao();
    }

    public function password() {
        $this->display("system_password.html");
        $_SESSION['msg']  = '';
    }

    //修改密码
    public function changePassword(){
        $oldPwd = $_POST['oldPwd'];
        $oldPwd = md5($oldPwd);
        $newPwd = $_POST['newPwd'];
        $user_info = $this->DAO->checkPwd($_SESSION["usr_id"]);//判断输入的旧密码是否正确
        if (strtolower($oldPwd) != strtolower($user_info['usr_pwd'])) {
            echo json_encode($this->error_msg("旧密码输入错误，请重新输入！"));
            return;
        }
        $this->DAO->updateAdminPwd(md5($newPwd),$_SESSION['usr_id']);
        //echo json_encode($this->error_msg("密码修改失败"));
        echo json_encode($this->succeed_msg("密码修改成功"));
    }
}
?>