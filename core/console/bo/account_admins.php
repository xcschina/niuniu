<?php
COMMON('baseCore');
DAO('account_admins_dao');

class account_admins extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new account_admins_dao();
    }

    public function admins() {
        $params=$_POST;
        $dataList = $this->DAO->get_admin_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $groupList = $this->DAO->get_group_list();
        $this->assign("groupList", $groupList);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("account_admins.html");
    }

    public function add() {
        $groupList = $this->DAO->get_group_list();
        $this->assign("groupList", $groupList);
        $this->display("account_add.html");
    }

    public function edit($id) {
        $info = $this->DAO->get_info($id);
        $groupList = $this->DAO->get_group_list();
        $this->assign("groupList", $groupList);
        $this->assign("info", $info);
        $this->display("account_edit.html");
    }

    public function insert(){
        $admin = $_POST;
        if (!$admin['usr_name'] || !$admin['usr_pwd'] || !$admin['real_name']||!$admin['account']) {
            echo json_encode($this->error_msg("请把信息填写完整"));
            exit;
        } else {
            $s_admin=$this->DAO->get_admin_by_account($admin['account']);
            if($s_admin){
                echo json_encode($this->error_msg("登陆账号已存在"));
                exit;
            }
            $this->DAO->insert_data($admin);
            echo json_encode($this->succeed_msg("用户添加成功","admins"));
        }
    }

    public function update(){
        $admin = $_POST;
        if (!$admin['usr_name'] || !$admin['real_name']||!$admin['account']) {
            echo json_encode($this->error_msg("请把信息填写完整"));
            exit;
        } else {
            $s_admin=$this->DAO->get_admin_by_account($admin['account']);
            if($s_admin && $admin['id']!=$s_admin['id']){
                echo json_encode($this->error_msg("登陆账号已存在"));
                exit;
            }
            $this->DAO->update_data($admin);
            echo json_encode($this->succeed_msg("用户修改成功","admins"));
        }
    }
}
?>