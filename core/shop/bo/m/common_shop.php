<?php
// -------------------------------------------
// 店铺系统 - 公共 <zbc> <2016-04-26>
// -------------------------------------------
COMMON('baseCore');
DAO('m'.DS.'common_shop_dao');
DAO('m'.DS.'index_shop_dao');
DAO('m'.DS.'game_shop_dao');
DAO('m'.DS.'product_shop_dao');

class common_shop extends baseCore {

    const TPL = 'm';
    protected $shop_id;
    protected $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->COMDAO = new common_shop_dao();
        $this->assign('TPL',self::TPL);
    }

    // @override
    public function check_usr_login(){
        if(!isset($_SESSION['user_id']) || !$_SESSION['user_id']){
            $this->redirect("index.php?act=user_login_view");
            exit;
        }
    }

    public function set_session_back_url(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
    }

    public function get_session_back_url(){
        return $_SESSION['login_back_url']?:'';
    }

    // 店铺错误页面
    public function redirect_error($url='', $err='发生错误'){
        $url = $url ?:'http://'.SITEURL;
        $this->assign('url',$url);
        $this->assign('err',$err);
        $this->display(self::TPL.'/shop_error.html'); 
        die;
    }

    // 验证id是否存在
    public function shop_check_id($id, $err='参数不存在哦'){
        $id = $id?:0;
        if(!$id){
            $this->redirect_error('',$err);
        }else{
            return $id;
        }
    }

    public function shop_check_exist($shop_id){
        $index_shop_dao = new index_shop_dao();
        $res = $index_shop_dao->get_shop_info($shop_id);
        if(!$res){
            $this->redirect_error('','店铺不存在哟~~');
        }else{
            $this->assign('shop_id',$shop_id);
        }
    }

    public function shop_game_check_exist($shop_id, $game_id){
        $game_shop_dao = new game_shop_dao();
        $params['shop_id'] = $shop_id;
        $params['game_id'] = $game_id;
        $res = $game_shop_dao->get_shop_game_info($params);
        if(!$res){
            $this->redirect_error("http://shop.66173.cn/{$shop_id}.html",'本店铺暂时没有这款游戏哟~~');
        }
    }

    public function shop_product_check_exist($shop_id, $game_id, $product_id){
        $product_shop_dao = new product_shop_dao();
        $params['shop_id']    = $shop_id;
        $params['game_id']    = $game_id;
        $params['product_id'] = $product_id;
        $res = $product_shop_dao->get_shop_product_info($params,1);
        if(!$res){
            $this->redirect_error("http://shop.66173.cn/{$shop_id}-{$game_id}.html",'本店铺暂时没有这款商品哟~~');
        }
    }

    public function shop_product_final_ch_discounts($shop_id, $game_id, $product_id){
        $par['params']['shop_id']    = $shop_id;
        $par['params']['game_id']    = $game_id;
        $par['params']['product_id'] = $product_id;
        $chs_discount = $this->shop_product_priority_discount($par);
        $chs = array();
        foreach ($chs_discount as $k => $v) {
            if($v['platform'] == 1){
                $chs['android'][$k] = $v;
            }elseif($v['platform'] == 2){
                $chs['ios'][$k] = $v;
            }
        }
        return $chs;
    }

    /**
     * 店铺商品折扣优先级计算
     *************************************************
     * 鉴于折扣业务稍复杂且无文档参阅，新人接手可能费时费力
     * 在此用傻瓜代码便于继任者理解折扣业务
     * 如您已经清楚业务，可以使用自定义新方法加以优化
     *************************************************
     * 优先级：游戏折扣 < 商品折扣 < 店铺特价商品折扣
     * 必须参数：
     * $par['params']['shop_id'] = $shop_id;
     * $par['params']['game_id'] = $game_id;
     * $par['params']['product_id'] = $product_id;
     * @author <zbc> <2016-09>
     */
    public function shop_product_priority_discount($par=array()){
        $game_shop_dao = new game_shop_dao();
        $shop_game_switch = $game_shop_dao->get_shop_game_info($par['params']);

        // 获取店铺特价商品折扣
        $product_id = $par['params']['product_id'];
        $is_shop = false;
        if($product_id){
            $product_shop_dao  = new product_shop_dao();
            $shop_product_info = $product_shop_dao->get_shop_product_info($par['params']);
            $is_shop = true;
        }

        // 获取主站商品折扣
        $product_info = $this->COMDAO->get_product_discount($product_id);
        if($product_info){
            $ptype   = $product_info['type'];
            $game_id = $product_info['game_id'];
        }

        // 获取主站游戏折扣
        $game_chs = $this->COMDAO->get_game_ch_discount($game_id);

        // 获取主站所有渠道信息
        $chs = $this->COMDAO->get_channels();

        // 优先级计算
        foreach ($chs as $k => $v) {
            // 店铺游戏渠道开关判断 - 1屏蔽
            if(!$shop_game_switch['ch_'.$v['id'].'_'.$ptype]){
                $g = floatval($game_chs['ch_'.$v['id'].'_'.$ptype]);
                $p = floatval($product_info['ch_'.$v['id']]);
                if($is_shop && $shop_product_info){
                    $s = floatval($shop_product_info['ch_'.$v['id']]);
                }
                $priority_disc = $s?:($p?:($g?:0));
                if(!$priority_disc){
                    unset($chs[$k]); // 0 页面不显示
                }else{
                    $chs[$k]['priority_discount'] = $priority_disc;
                }
            }else{
                unset($chs[$k]); // 删除被屏蔽的渠道
            }
        }
        return $chs;
    }

}