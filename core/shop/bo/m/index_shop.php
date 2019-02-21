<?php
// --------------------------------------
//  店铺系统 v1.0 <zbc> < 2016/4/14 >
// --------------------------------------
COMMON('pageCore');
BO('m'.DS.'common_shop');
DAO('m'.DS.'index_shop_dao');
DAO('m'.DS.'order_shop_dao');
DAO('m'.DS.'game_shop_dao');

class index_shop extends common_shop{

    protected $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new index_shop_dao();
    }

    // 店铺列表页
    public function shop_list_view($params=array()){
        $PERPAGE = 6;
        if($params['pageNow']){
            $pars['limit'] = ($PERPAGE*(int)$params['pageNow']).','.$PERPAGE;
            $shops = $this->DAO->get_shop_list($pars);
            die(json_encode($shops));
        }else{
            $pageNow = 0;
            $pars['limit'] = $pageNow.','.$PERPAGE;
            $shops = $this->DAO->get_shop_list($pars);
            $this->assign('shops',$shops);
            $this->display(self::TPL.'/shop_list.html');
        }
    }

    // 店铺详情页
    public function shop_info_view($params=array()){
        $shop_id = $this->shop_check_id($params['shop_id'],'店铺不存在哟~~~');
        $this->shop_check_exist($shop_id);
        $this->set_session_back_url();
        $shop = $this->DAO->get_shop_info($shop_id); 

        // 店铺热门游戏列表 top15
        $pars['shop_id'] = $shop_id;
        $pars['limit']   = 15;
        $pars['is_hot']  = 1;
        $game_shop_dao  = new game_shop_dao();
        $shop_hot_games = $game_shop_dao->get_shop_game_list($pars);

        $this->assign('shop',$shop);
        $this->assign('shop_games',$shop_games);
        $this->assign('shop_hot_games',$shop_hot_games);
        $this->display(self::TPL.'/shop_info.html');
    }

    // 店铺客服
    public function shop_service_view($params=array()){
        $shop_id = $params['shop_id'];
        if($shop_id){
            $this->shop_check_exist($shop_id);
            $shop_info = $this->DAO->get_shop_info($shop_id);
            if($shop_info){
                $shop_info = $this->DAO->shop_qq_decode($shop_info);
                $data['shop']['s_qq']   = $shop_info['s_qq'];
                $data['shop']['s_tel']  = $shop_info['s_tel'];
                $data['shop']['s_name'] = $shop_info['s_name'];
            }else{
                $this->redirect_error('service.html','本店客服部正在筹备中...');
            }
        }else{
            $data['master']['qq'] = $this->DAO->get_master_service();
            $master_setting = $this->DAO->get_master_setting();
            $data['master']['tel'] = $master_setting['tel'];
        }
        $this->assign('data',$data);
        $this->assign('shop_id',$shop_id);
        $this->display(self::TPL.'/shop_service.html');
    }


}
