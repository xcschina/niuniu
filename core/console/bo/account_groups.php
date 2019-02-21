<?php
COMMON('baseCore');
DAO('account_groups_dao');

class account_groups extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new account_groups_dao();
    }

    public function groups() {
        $dataList = $this->DAO->get_groups_list();
        $this->assign("dataList", $dataList);
        $this->display("account_groups.html");
    }

    public function add() {
        $this->display("account_groups_add.html");
    }

    public function edit($id) {
        $info = $this->DAO->get_info($id);
        $this->assign("info", $info);
        $this->display("account_groups_edit.html");
    }

    public function insert(){
        $group = $_POST;
        if (!$group['groups_name']) {
            echo json_encode($this->error_msg("请把信息填写完整"));
            exit;
        } else {
            $this->DAO->insert_data($group);
            echo json_encode($this->succeed_msg("添加用户组成功","groups"));
        }
    }

    public function update(){
        $group = $_POST;

        if (!$group['groups_name']) {
            echo json_encode($this->error_msg("请把信息填写完整"));
            exit;
        } else {
            $this->DAO->update_data($group);
            echo json_encode($this->succeed_msg("用户组修改成功","groups"));
        }
    }
}
?>