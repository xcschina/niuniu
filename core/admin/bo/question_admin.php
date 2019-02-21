<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('question_dao');

class question_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new question_dao();
    }

    public function question_list_view(){
        $params = $this->get_params($_POST,$_GET);
        $question = $this->DAO->get_question_list($this->page,$params);
        $page = $this->pageshow($this->page, "question.php?act=list&");
        $this->assign('page_bar',$page->show());
        $this->assign('params',$params);
        $this->assign('question',$question);
        $this->display('question_list.html');
    }

    public function add_view(){
        $this->display("question_add.html");
    }

    public function do_add(){
        $params = $_POST;
        if(!$params['title']){
            $this->error_msg("问题名称不能为空！");
        }
        if(!$params['content']){
            $this->error_msg("问题详情不能为空！");
        }
        if($params['is_show'] == '0'){
            $num = $this->DAO->get_show();
            if($num['num'] >= 4){
                $this->error_msg("首页显示已有4个，无需再添加");
            }
        }
        $this->DAO->insert_question($params);
        $this->succeed_msg();
    }

    public function edit_view($id){
        $info = $this->DAO->get_question_info($id);
        $this->assign('info',$info);
        $this->display('question_edit.html');
    }

    public function do_edit(){
        $params = $_POST;
        if(!$params['title']){
            $this->error_msg("问题名称不能为空！");
        }
        if(!$params['content']){
            $this->error_msg("问题详情不能为空！");
        }
        if($params['is_show'] == '0'){
            $num = $this->DAO->get_show_num($params['id']);
            if($num['num'] >= 4){
                $this->error_msg("首页显示已有4个，无需再添加");
            }
        }
        $this->DAO->update_question($params);
        $this->succeed_msg();
    }

    public function del_view($id){
        $this->assign('id',$id);
        $this->display('question_del.html');
    }

    public function do_del($id){
        $info = $this->DAO->get_question_info($id);
        if(!$info){
            $this->error_msg('查无此常见问题');
        }
        $this->DAO->delete_question($id);
        $this->succeed_msg();
    }

}