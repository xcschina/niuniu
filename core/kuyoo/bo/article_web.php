<?php
COMMON('baseCore', 'pageCore');
BO('baseKuyoo');
DAO('article_dao','common_dao','index_dao');
class article_web extends baseKuyoo{
    public $DAO;
    public $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new article_dao();
        $data['questions'] = $this->DAO->get_mod_articles(21);
        $this->assign('data',$data);
        $this->COMDAO=new common_dao();
        $games = $this->COMDAO->get_hot_games();
        $this->assign("games", $games);
    }

    public function get_articles_list($id){
        $params=$_GET;
        $this->assign("canonical", "http://shouyou.kuyoo.com/info/list".$id);
        $part_name     =  $this->get_part_name($id);
        $article_list  =  $this->DAO->get_articles_list($params,$this->page);
        $page  =  $this->pageshow($this->page, 'info/list'.$id.'?');
        if($id==13){
            $this->assign("web_des", '酷游(shouyou.kuyoo.com)是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。酷游提供最专业的游戏新闻资讯，最潮流的玩法，最完善的游戏攻略，最齐全的新游玩法推荐。');
        }
        $this->assign("title",$part_name);
        $this->assign("page_show", $page->show(9));
        $this->assign("article_list", $article_list);
        $this->assign("pid",$params['part_id']);
        if($params['part_id']>7){
            $index_dao = new index_dao();
            $hot_games = $index_dao->get_hot_games();
            $new_game_news = $this->DAO->get_articles_list(array('part_id'=>13));

            $this->assign("hot_games", $hot_games);
            $this->assign("new_game_news", $new_game_news);
            $this->display("article_game_list.html");
        }else{
            $this->display("article_list.html");
        }
    }

    public function get_article($id){
        $allow_articles = array('4363','4366','4369','4371','4372');
        if(!in_array($id, $allow_articles)){
            $this->redirect(''); die;
        }
        $this->assign("canonical", "http://shouyou.kuyoo.com/info/".$id);
        $article = $this->DAO->get_article_info($id);
        $article['ot'] = $this->fig_time(date("Y-m-d H:i:s",$article['add_time']));
        $article['part_name'] = $this->get_part_name($article['part_id']);
        $game_info = array();
        $game_news = array();

        if($article['game_id']){
            $game_info = $this->DAO->get_game_info($article['game_id']);
            $web_key = $game_info['game_name']."，".$game_info['game_name']."最新资讯，".$game_info['game_name']."游戏资讯，酷游手游新闻资讯";
            $web_des = "酷游游戏新闻提供".$game_info['game_name']."游戏攻略、".$game_info['game_name']."游戏公告、".$game_info['game_name']."游戏测评、".$game_info['game_name']."游戏资讯，获取".$game_info['game_name']."相关资讯就上酷游。(shouyou.kuyoo.com)是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。";
        }else{
            $web_des = $article['title']."，酷游手游新闻资讯";
            $web_key = "";
        }

        if($article['tags']){
            $article['tags'] = explode("，", $article['tags']);
        }

        if($game_info){
            $game_strategy = $this->DAO->get_game_strategy($article['game_id'],18);
        }

        $this->assign("article",$article);
        $this->assign("web_key",$web_key);
        $this->assign("web_des",$web_des);
        $this->assign("game",$game_info);
        $this->assign("game_news",$game_news);
        $this->assign("game_strategys",$game_strategy);
        $this->assign("title",$article['title']);
        $this->assign("pid",$article['part_id']);
        $this->assign("sid",$article['id']);
        $this->assign("show_zx",array(16)); // 显示游戏资讯子栏目集合);

        if($article['game_id']){
            $this->display("article_game.html");
        }else{
            $this->display("article.html");
        }
    }

    private function get_part_name($part_id = 0){
        $part_name  = "";
        $parts_list = $this->DAO->get_parts_list();
        if($part_id){
            foreach($parts_list as $data){
                if($data['id'] == $part_id){
                    $part_name = $data['name'];
                    break;
                }
            }
        }
        return $part_name;
    }



}
?>
