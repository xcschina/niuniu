<?php
COMMON('baseCore', 'pageCore');
DAO('article_dao');
class article_mobile  extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new article_dao();
        $this->user_id=$_SESSION['user_id'];
    }

    public function get_articles_list(){
        $params=$_GET;
        $parts_list=$this->DAO->get_parts_list();
        $articles_list=$this->DAO->get_articles_list($params,$this->page);
        foreach($articles_list as $key=>$data){
            $articles_list[$key]['add_time']= $this->fig_time(date("Y-m-d H:i:s",$data['add_time']));
        }
        $part_name="";
        if($params['part_id']){
            foreach($parts_list as $key=>$data){
                if($data['id']==$params['part_id']){
                    $part_name=$data['name'];
                    break;
                }
            }
        }
        $page = $this->pageshow($this->page, "/info/list".$params['part_id']."&");
        $this->assign("params",$params);
        $this->assign("title",$part_name);
        $this->assign("pageBar", $page->show());
        $this->assign("parts_list", $parts_list);
        $this->assign("articles_list", $articles_list);
        $this->display("articles.html");
    }

    public function  article_detail(){
        $regpic = $this->DAO->get_article_info(6407);
        $id=paramUtils::intByGET("id",false);
        $article_info = $this->DAO->get_article_info($id);
        if(!$article_info){
            die("鱼不可脱于渊，国之利器不可以示人");
        }
        $article_info['first_charge'] = "";//首充号
        $article_info['be_charged'] = "";//续充号
        if($article_info['game_id']){
            $game_info=$this->DAO->get_game($article_info['game_id']);
            $article_info['game_name']=$game_info['game_name'];
            $article_info['game_icon']=$game_info['game_icon'];
            $first_charge = $this->DAO->get_game_last_product($article_info['game_id'], 1);
            $be_charged = $this->DAO->get_game_last_product($article_info['game_id'], 2);
            $first_charge_ch = $be_charged_ch = $this->DAO->get_channels();
            $this->set_discount($first_charge, $first_charge_ch, 1);
            $this->set_discount($be_charged, $be_charged_ch, 1);
            if($first_charge_ch){
                $article_info['first_charge'] = $first_charge_ch['0']['discount'];
                unset($first_charge_ch);
            }
            if($be_charged_ch) {
                $article_info['be_charged'] = $be_charged_ch['0']['discount'];
                unset($be_charged_ch);
            }
        }
        $game = "";
        if (!empty($article_info['game_id']) && $article_info['game_id'] > 0) {
            $game = $this->DAO->get_game($article_info['game_id']);
        }
        $this->assign("game", $game);
        $this->assign("regpic", $regpic);
        $this->assign("detail",$article_info);
        $this->display("article_detail.html");
    }

    protected function set_discount($product, &$channels, $product_type=1){
        $agent_discount = 0;
        foreach($channels as $k=>$v){
            $discount = $product['ch_'.$v['id']];
            $g_discount = $product['chd_'.$v['id']];
            $discount = ($discount!=$g_discount && $discount>0)?$discount:$g_discount;
            if($discount==0){
                unset($channels[$k]);
                continue;
            }
            $channels[$k]['discount'] = $discount;
            //代理折扣
            if($agent_discount>0){
                $channels[$k]['discount'] = $agent_discount;
            }
        }
        usort($channels, function($a, $b) {
            $al = $a['discount'];
            $bl = $b['discount'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1;
        });
    }
}
?>