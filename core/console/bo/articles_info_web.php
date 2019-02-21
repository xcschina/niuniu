<?php
COMMON('baseCore','uploadHelper','baidu');
DAO('articles_info_dao');

class articles_info_web extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new articles_info_dao();
    }

    public function get_parts_list(){
        ini_set("display_errors","off");
        $params=$_POST;
        $dataList=$this->DAO->get_parts_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("parts_list.html");
    }

    public function part_add_view(){
        $this->display("parts_add.html");
    }

    public function do_part_add(){
        $name=$_POST['name'];
        if(!$name){
            die(json_encode($this->error_msg("请输入模块名称")));
        }
        $info=$this->DAO->get_part_by_name($name);
        if($info){
            die(json_encode($this->error_msg("模块已经存在")));
        }
        $this->DAO->add_part($name);
        echo json_encode($this->succeed_msg("模块添加成功"));
    }

    public function part_edit_view($id){
        $info=$this->DAO->get_part_by_id($id);
        $this->assign("info",$info);
        $this->display("parts_edit.html");
    }


    public function do_part_edit(){
        $params=$_POST;
        if(!$params['name']){
            die(json_encode($this->error_msg("请输入模块名称")));
        }
        $info=$this->DAO->get_part_by_name($params['name']);
        if($info && $params['id']!=$info['id']){
            die(json_encode($this->error_msg("模块已经存在")));
        }
        $this->DAO->upd_part($params);
        echo json_encode($this->succeed_msg("模块修改成功"));
    }

    public function get_articles_list(){
        $params=$_POST;
        $parts_list=$this->DAO->parts_list();
        $dataList=$this->DAO->get_articles_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("parts_list", $parts_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("articles_info_list.html");
    }


    public function articles_add_view(){
        $games = $this->DAO->get_game_list();
        $parts_list=$this->DAO->parts_list();
        $this->assign("parts_list", $parts_list);
        $this->assign("games", $games);
        $this->display("articles_info_add.html");
    }

    public function do_articles_add(){
        $params=$_POST;
        if(!$params['part_id']){
            die(json_encode($this->error_msg("请选择模块")));
        }
        if(!$params['title']){
            die(json_encode($this->error_msg("请输入标题")));
        }
        if(!$params['intro']){
            die(json_encode($this->error_msg("请输入文章内容")));
        }
        if(!$_FILES['img']['tmp_name']){
            $params['img']="";
        }else{
            $params['img']=$this->up_img('img',INTRO_IMG);
        }
//        if($params['img'] && !$params['go_url']){
//            echo json_encode($this->error_msg("请输入图片跳转地址"));
//            exit();
//        }

        $id = $this->DAO->add_article($params);
        $baidu = new baidu();
        $baidu->ping_topic("http://www.66173.cn/info/".$id);
        echo json_encode($this->succeed_msg("文章添加成功"));
    }

    public function article_edit_view($id){
        $games = $this->DAO->get_game_list();
        $parts_list=$this->DAO->parts_list();
        $info= $this->DAO->get_article_info($id);
        $this->assign("parts_list", $parts_list);
        $this->assign("article",$info);
        $this->assign("games", $games);
        $this->display("articles_info_edit.html");
    }

    public function  do_articles_edit(){
        $params=$_POST;
        if(!$params['part_id']){
            die(json_encode($this->error_msg("请选择模块")));
        }
        if(!$params['title']){
            die(json_encode($this->error_msg("请输入标题")));
        }
        if(!$params['intro']){
            die(json_encode($this->error_msg("请输入文章内容")));
        }

        if(!$_FILES['img']['tmp_name']){
            $params['img']=$params['old_img'];
        }else{
            $params['img']=$this->up_img('img',INTRO_IMG);
        }

//        if($params['img'] && !$params['go_url']){
//            echo json_encode($this->error_msg("请输入图片跳转地址"));
//            exit();
//        }

        $this->DAO->edit_article($params);
        echo json_encode($this->succeed_msg("文章编辑成功"));
    }
}
?>