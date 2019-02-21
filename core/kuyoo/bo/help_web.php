<?php
COMMON('baseCore', 'pageCore');
BO('baseKuyoo');
DAO('article_dao','common_dao');

class help_web  extends baseCore{
    public $DAO;
    public $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new article_dao();
        $this->user_id=$_SESSION['user_id'];
        $this->COMDAO=new common_dao();
        $notices=$this->COMDAO->get_mod_articles(14);
        $links=$this->COMDAO->get_friendly_links();
        $this->assign("notices", $notices);
        $this->assign("links", $links);
    }

    public function service_view(){
        $this->display("help/service.html");
    }

    public function help_view(){
        $this->display("help/help.html");
    }

    public function advise_view(){
        $this->display("help/advise.html");
    }

    public function link_view(){
        $this->display("help/link.html");
    }
}