<?php
COMMON('baseCore', 'pageCore');
DAO('announcement_dao');
class announcement_mobile  extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new announcement_dao();
        $this->user_id=$_SESSION['user_id'];
    }


    //获取文章详情
    public function get_article_info($id){
        $article_info = $this->DAO->get_article_info($id);
        $this->assign("article_info",$article_info);
        $this->display("moyu/article.html");
    }

}