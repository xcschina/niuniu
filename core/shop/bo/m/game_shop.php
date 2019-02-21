<?php
// -------------------------------------------
// 店铺系统 - 游戏 <zbc> <2016-04-26>
// -------------------------------------------
BO('m'.DS.'common_shop');
DAO('m'.DS.'game_shop_dao');
DAO('m'.DS.'index_shop_dao');

class game_shop extends common_shop {

    protected $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new game_shop_dao();
    }

    // 店铺游戏详情页 - 商品购买、礼包领取、游戏下载、游戏资讯
    public function shop_game_info_view($params=array()){
        $shop_id = $this->shop_check_id($params['shop_id'],'店铺不存在哟~~~');
        $game_id = $this->shop_check_id($params['game_id'],'游戏不存在哟~~~');
        $this->shop_check_exist($shop_id);
        $this->shop_game_check_exist($shop_id,$game_id);
        $this->set_session_back_url();
        $index_shop_dao = new index_shop_dao();
        $shop = $index_shop_dao->get_shop_info($shop_id);
        $shop_game = $this->DAO->get_shop_game_info($params,1);
        // 游戏下载,游戏资讯 暂无
        $this->assign('shop',$shop);
        $this->assign('game',$shop_game);
        $this->display(self::TPL.'/shop_game_info.html');
    }
    
    // 首充号验证
    public function check_character($params=array()){
        $from_shop_id = $params['id']?:'';
        if($params['character']){
            $bak['character'] = $params['character'];
            if(!empty($_COOKIE['cfpt'])){
                $times = intval($_COOKIE['cfpt']);
                if($times>50){
                    $bak['ret'] = 'often';
                    $this->assign('data',$bak);
                    $this->display(self::TPL.'/shop_check_character.html');
                    die;
                }else{
                    $times++;
                }
            }else{
                $times = 1;
            }
            setCookie('cfpt',$times,time()+60); 

            $res = $this->DAO->shop_check_character($params);
            if($res['id']){
                if($res['shop_id']){
                    // 店铺首充号
                    $url = 'http://shop.66173.cn/'.$res['shop_id'].'-'.$res['game_id'].'-'.$res['id'].'-recharge.html';
                }elseif($from_shop_id){
                    // 主站首充号 某个店铺内部进行的首充号验证
                    $url = 'http://shop.66173.cn/'.$from_shop_id.'-'.$res['game_id'].'-'.$res['id'].'-recharge.html';
                }else{
                    // 主站首充号
                    $url = 'http://shop.66173.cn';
                }
                $bak['ret'] = 'right';
                $bak['info']['url'] = $url; // 续充链接
                $bak['info']['serv_name'] = $res['serv_name'];
                $bak['info']['role_name'] = $res['role_name'];
                $bak['info']['game_user'] = $res['game_user'];
            }else{
                $bak['ret'] = 'wrong';
            }
            $this->assign('data',$bak);
        }
        $this->assign('from_shop',$from_shop_id);
        $this->display(self::TPL.'/shop_check_character.html');
    }
}