<?php
COMMON('baseCore', 'pageCore');
DAO('article_dao');

class trade_guide_mobile extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new article_dao();
    }

    //首页
    public function index_view(){
        $buy_article_list = $this->DAO->get_articles_by_type(31);
        $sell_article_list = $this->DAO->get_articles_by_type(32);
        $this->assign('buy_article_list',$buy_article_list);
        $this->assign('sell_article_list',$sell_article_list);
        $this->display('moyu/trade_guide.html');
    }
    public function article_type($type){
        $article_list = $this->DAO->get_articles_by_type($type);
        $name = $this->DAO->get_part_name($type)['name'];
        $this->assign('article_list',$article_list);
        $this->assign('type',$type);
        $this->assign('name',$name);
        $this->display('moyu/trade_type_list.html');
    }
    public function get_article_info($id){
        $article = $this->DAO->get_article_info($id);
        $this->assign('info',$article);
        $this->display('moyu/trade_guide_detail.html');
    }
    public function get_article_data(){
        $result = array('code'=>0,'msg'=>'网络出错！');
        $params              = $_POST;
        $data = $this->DAO->get_more_articles_list($params);
        if(!$data){
            $result['msg'] = '暂无数据';
            $result['flag'] = '0';
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 1 ;
        $result['flag'] = 1 ;
        $result['msg'] = '查询成功';
        $result['data'] = $data;
        die('0' . base64_encode(json_encode($result)));
    }
    public function get_all_type(){
        $params['type'] = '';
        $data = $this->DAO->get_more_articles_list($params);
        $this->assign('article_list',$data);
        $this->display('moyu/trade_all_type_list.html');
    }


}