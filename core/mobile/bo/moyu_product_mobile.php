<?php
COMMON('baseCore', 'pageCore', 'uploadHelper');
DAO('moyu_product_dao', 'trading_dao');

class moyu_product_mobile extends baseCore{
    public $DAO;
    public $os_channel;

    public function __construct(){
        parent::__construct();
        $this->DAO        = new moyu_product_dao();
        $this->os_channel = array(
            3 => 'iOS',
            1 => '安卓'
        );
    }

    //获取游戏对应商品列表
    public function get_product_list($id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)){
            $user_id = '';
        }
        $product_list = $this->DAO->get_product_list($id, $user_id);
        $flag         = '';
        if($product_list){
            foreach($product_list as $key => $val){
                $imgs = $this->DAO->get_product_imgs($val['id']);
                if($val['end_time'] < time()){
                    $this->DAO->update_product($val['id']);
                    unset($product_list[$key]);
                }
                if($imgs){
                    $products[$key]['img'] = $imgs['img_url'];
                }
            }
        }
        if(count($product_list) >= 10){
            $flag = 1;
        } elseif(count($product_list) == 0){
            $flag = 0;
        } else{
            $flag = 2;
        }
        $game_name = $this->DAO->get_game_name($id)['game_name'];
        $channel   = $this->DAO->get_channel();
        $this->assign("channel", $channel);//系统
        $this->assign("flag", $flag);//系统
        $this->assign("game_name", $game_name);//系统
        $this->assign("game_id", $id);//系统
        $this->assign("product_list", $product_list);
        $this->display("moyu/product_list.html");
    }


    //获取游戏商品具体信息
    public function get_product_info($product_id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'], '/');
        $product_info               = $this->DAO->get_product_info($product_id);
        $img_url = $product_info['img_url'];
        $img_arr = explode(',',$img_url);
//        //处理卖家信息
        $sell_user_id = $product_info['user_id'];//发布者
        if($sell_user_id > 1){
            $user_certification_info = $this->DAO->get_account_info($sell_user_id);
        }
        $certification_flag = '';//1-系统 2-用户已认证 3-用户未认证
        $sell_amout         = $this->DAO->get_sell_count_by_user_id($sell_user_id)['num'];//销售数量
        $this->DAO->update_product_see_num($product_id);
        if($sell_user_id == 1){
            $certification_flag = 1;
        } elseif($sell_user_id > 1){
            $num = $this->DAO->get_user_certification($sell_user_id)['num'];
            if($num == 1){
                $certification_flag = 2;
            } else{
                $certification_flag = 3;
            }
        }
        if($product_info['is_pub'] == 1 && $product_info['end_time'] < time()){
            $this->DAO->update_product($product_id);
            $product_info['is_pub'] = 0;
        }
        $imgs                 = $this->DAO->get_product_imgs($product_info['id']);
        $params['user_id']    = $_SESSION['user_id'];
        $params['product_id'] = $product_id;
        $info                 = $this->DAO->get_user_colllectinfo_by_product_id($params);
        if($info['id']){
            $flag = 1;
        } else{
            $flag = 0;
        }
        if(empty($_SESSION['user_id'])){
            $flag = 0;
        }
        if(!empty($product_list[0]['game_name'])){
            $game_name = $product_list[0]['game_name'];
        } else{
            $game_name = '暂无数据';
        }
        $this->wx_share();
        $this->assign("user_id", $sell_user_id);
        $this->assign("login_id", $_SESSION['user_id']);
        $this->assign("img_arr", $img_arr);
        $this->assign("product_id", $product_id);
        $this->assign("user_certification_info", $user_certification_info);
        $this->assign("certification_flag", $certification_flag);//用户认证
        $this->assign("sell_amout", $sell_amout);//销售数量
        $this->assign("game_name", $game_name);//系统
        $this->assign("product_info", $product_info);
        $this->assign("imgs", $imgs);
        $this->assign("flag", $flag);

        $this->display("moyu/goods_detail.html");
    }

    public function wx_share(){
        $R_DAO = new moyu_index_dao();
        $ret   = $R_DAO->get_wx_access_token();
        if(!$ret){
            COMMON('weixin.class');
            $ret = wxcommon::getToken();
            $R_DAO->set_wx_access_token($ret);
        }
        $ACCESS_TOKEN = $ret['access_token'];
        $jsapi_data   = $R_DAO->get_wx_access_jsapi_data($ACCESS_TOKEN);
        if(!$jsapi_data){
            $url        = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $ACCESS_TOKEN . '&type=jsapi';
            $content    = file_get_contents($url);
            $jsapi_data = json_decode($content, true);
            $R_DAO->set_wx_access_jsapi_data($ACCESS_TOKEN, $jsapi_data);
        }
        $guid      = $this->create_guids();
        $time      = time();
        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $share_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $sign      = "jsapi_ticket=" . $jsapi_data['ticket'] . "&noncestr=" . $guid . "&timestamp=" . $time . "&url=" . $share_url;
        $signature = sha1($sign);
        $this->assign("noncestr", $guid);
        $this->assign("timestamp", $time);
        $this->assign("signature", $signature);
    }

    public function create_guids(){
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid   = substr($charid, 0, 32);
        return $uuid;
    }

    //收藏
    public function update_product_collect($product_id){
        $result  = array('code' => 0, 'msg' => '网络异常');
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)){
            $result['msg'] = '请先登录再收藏';
            $result['url'] = '/Login';
            die('0' . base64_encode(json_encode($result)));
        }
        //判断用户收藏次数
        $count = $this->DAO->get_product_colllect_count_by_user_id($user_id);
        if($count['num'] > 20){
            $result['msg'] = '用户收藏次数不能超过二十';
            die('0' . base64_encode(json_encode($result)));
        }
        $params['user_id']    = $user_id;
        $params['product_id'] = $product_id;
        $info                 = $this->DAO->get_user_colllectinfo_by_product_id($params);
        if($info['id'] && $info['is_deleted'] == 2){
            $result['msg']  = '该用户已经收藏过了';
            $result['code'] = 0;
            die('0' . base64_encode(json_encode($result)));
        }
        if(empty($info)){
            $id = $this->DAO->insert_product_collect($params);
            if(empty($id)){
                $result['msg']  = '收藏失败';
                $result['code'] = 0;
                die('0' . base64_encode(json_encode($result)));
            }
        } else{
            $this->DAO->update_user_colllect_by_id($info['id']);//有旧记录则更新
        }
        $this->DAO->del_session('my_collect_', $_SESSION['user_id'], 1);
        $this->DAO->del_session('my_collect_', $_SESSION['user_id']);
        $info           = $this->DAO->get_user_colllectinfo_by_product_id($params);
        $result['msg']  = '收藏成功';
        $result['code'] = 1;
        die('0' . base64_encode(json_encode($result)));
    }

    //取消收藏
    public function cancle_product_collect($product_id){

        $user_id = $_SESSION['user_id'];
        if(empty($user_id)){
            $result['msg']  = '请先登录再取消收藏';
            $result['code'] = 0;
            $this->redirect('login.php?act=login');
            die('0' . base64_encode(json_encode($result)));
        }
        $params['user_id']    = $user_id;
        $params['product_id'] = $product_id;
        $this->DAO->delete_product_collect($params);
        $reuslt = $this->DAO->get_user_colllectinfo_by_product_id($params);
        if($reuslt['is_deleted'] == 2){
            $result['msg']  = '取消收藏失败';
            $result['code'] = 0;
            die('0' . base64_encode(json_encode($result)));
        } else{
            $this->DAO->del_session('my_collect_', $_SESSION['user_id'], 1);
            $this->DAO->del_session('my_collect_', $_SESSION['user_id']);
            $result['msg']  = '取消收藏成功';
            $result['code'] = 1;
            die('0' . base64_encode(json_encode($result)));
        }
    }

    //获取搜索的具体商品列表
    public function get_real_product(){
        $result  = array();
        $params  = $_POST;
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)){
            $user_id = 1;
        }
        $result['ch_id']   = $params['ch_id'];
        $result['serv_id'] = $params['serv_id'];
        if($_POST['game_id'] == '1829'){
            $channel_info = $this->DAO->get_channel_info($params['ch_id']);
            if($params['platform'] != $channel_info['platform'] && $params['platform']){
                $params['ch_id']     = $result['ch_id'] = '';
                $params['serv_id']   = $result['serv_id'] = '';
                $result['serv_type'] = 1;
            }
            $channel            = $this->DAO->get_channel($params['platform']);
            $result['channels'] = $channel;
        }
        $products = $this->DAO->get_moyu_products($params, $user_id);
        if($products){
            foreach($products as $key => $val){
                $imgs = $this->DAO->get_product_imgs($val['id']);
                if($imgs){
                    $products[$key]['img'] = $imgs['img_url'];
                }
            }
            $result['msg']  = '搜索成功';
            $result['code'] = 1;
            $result['data'] = $products;
            die('0' . base64_encode(json_encode($result)));
        } else{
            $result['msg']  = '暂无数据';
            $result['code'] = 0;
            $result['data'] = '';
            die('0' . base64_encode(json_encode($result)));
        }
    }

    //获取渠道
    public function get_channel(){
        $channel_list = $this->DAO->get_channel();
        $result       = array();
        if($channel_list){
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $channel_list;
        } else{
            $result['msg']  = '暂无数据';
            $result['code'] = 0;
        }
        die('0' . base64_encode(json_encode($result)));
    }

    public function get_game_servs_list(){
        $result = array('code' => 0, 'msg' => '网络错误');
        if(empty($_POST['id'])){
            $result['msg'] = '请输入渠道id';
            die('0' . base64_encode(json_encode($result)));
        }
        if(empty($_POST['game_id'])){
            $result['msg'] = '请输入游戏id';
            die('0' . base64_encode(json_encode($result)));
        }
        $serv_name       = $_POST['serv_name'];
        $game_servs_list = $this->DAO->get_ch_game_servs($_POST['id'], $_POST['game_id'], $serv_name);
//        if(empty($game_servs_list)){
//            $game_servs_list = array();
//        }


        array_push($game_servs_list, array('id' => '0', 'serv_name' => '全区服'));

        if($game_servs_list){
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $game_servs_list;
        } else{
            $result['msg']  = '暂无数据';
            $result['code'] = 0;
        }
        die('0' . base64_encode(json_encode($result)));
    }

    public function get_serv_name($ch_id, $serv_name, $game_id){
        $result = array('code' => 0, 'msg' => '网络错误');
        if(empty($serv_name)){
            $result['msg'] = '请输入名字';
            die('0' . base64_encode(json_encode($result)));
        }
        $server_name = $this->DAO->get_specified_ch_servs($ch_id, $serv_name, $game_id);
        if(empty($server_name)){
            $result['msg']  = '暂无数据';
            $result['code'] = 0;

        } else{
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $server_name;
        }
        die('0' . base64_encode(json_encode($result)));
    }

    //获取游戏列表
    public function get_game_list(){
        $game_list = $this->DAO->get_game_list();
        if(empty($game_list)){
            $result['msg']  = '暂无数据';
            $result['code'] = 0;

        } else{
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $game_list;
        }
        die('0' . base64_encode(json_encode($result)));

    }

    //发布商品
    public function publish_product(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = '请求出错,请刷新页面重新提交。';
            die('0' . base64_encode(json_encode($result)));
        }
        $is_msg = $this->product_verify($params);
        if($is_msg){
            $result['msg'] = $is_msg;
            die('0' . base64_encode(json_encode($result)));
        }
        $params['user_id'] = $_SESSION['user_id'];
        if(empty($params['user_id'])){
            $result['code'] = 3;
            $result['msg']  = '缺少用户信息';
            die('0' . base64_encode(json_encode($result)));
        }
        //参数验证
        if(!$_FILES){
            $result['msg'] = '请上传图片';
            die('0' . base64_encode(json_encode($result)));
        }
        $img_array = array();
        foreach($_FILES as $key => $data){
            if($_FILES[$key]['tmp_name']){
                $img_url = $this->up_img($key, "images/product_img");
                array_push($img_array, $img_url);
            }
        }

//        $img = $params['img'];//img数组
//        $img_array = array();
//        for($i = 0; $i < count($img); $i++){
//            $img = 'img' . $i;
//            if($_FILES[$img]['tmp_name']){
//                $img_url = $this->up_img('img', PRODUCT_IMG, array(), 1, 1, $img . time());
//                array_push($img_url);
//            }
//
//        }
        $img_array      = implode(",", $img_array);
        $data           = array(
            'qq' => $params['userQQ'],
            'title' => $params['goodsNum'].'魔石'.'(1元='.intval($params['goodsNum']) / intval($params['goodsprice']).'魔石)',
            'mobile' => $params['userPhone'],
            'proportion' => intval($params['goodsNum']) / intval($params['goodsprice']),
            'intro' => $params[''],
            'game_id' => $params['game_id'],
            'channel_id' => $params['ch_id'],
            'serv_id' => $params['serv_id'],
            'stock' => $params['goodsStock'],
            'price' => $params['goodsprice'],
            'user_id' => $params['user_id'],
            'num' => $params['goodsNum'],
        );
        $id             = $this->DAO->add_product($data);
        $product_img_id = $this->DAO->add_product_img($id, $img_array);
        if($id && $product_img_id){
            $result['code'] = 1;
            $result['msg']  = '发布商品成功';
            $result['url']  = 'moyu_product.php?act=pusblish_success&id=' . $id;
            unset($_SESSION['page-hash']);
            die('0' . base64_encode(json_encode($result)));
        }
        $result['msg'] = '发布商品出错';
        die('0' . base64_encode(json_encode($result)));

    }

    public function product_verify($params,$type=''){
        //先粗略的判断
        if(empty($params['goodsNum'])){
            return '魔石数量不能为空';
        }
        if(empty($params['goodsprice'])){
            return '商品价格不能为空';
        }
        if(empty($params['goodsStock'])){
            return '商品库存不能为空';
        }
        if(empty($params['userQQ'])){
            return 'QQ号不能为空';
        }
        if(empty($params['userPhone'])){
            return '联系手机不能为空';
        }elseif(!$this->is_mobile($params['userPhone'])){
            return "联系手机格式不正确";
        }
        if(!$type){
            if(empty($params['game_id'])){
                return '游戏信息异常';
            }
            if(empty($params['ch_id'])){
                return '游戏渠道信息异常';
            }
            if(empty($params['serv_id'])){
                return '游戏区服信息异常';
            }
        }
        return;
    }

    //取消，下架，删除 商品
    public function update_products_status(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        $params = $_POST;
        if(!$params['id']){
            $result['msg'] = '请上传必要的参数';
            die('0' . base64_encode(json_encode($result)));
        }
        if($params['is_pub'] == null){
            $result['msg'] = '请上传必要的参数';
            die('0' . base64_encode(json_encode($result)));
        }

        if($params['is_pub'] == 1){
            $this->DAO->update_products_end_time($params);
        }
        if($params['is_pub'] == 5){
            $params['is_pub'] = 6;
            $this->DAO->update_products_status($params);
            $result['code'] = 1;
            $result['msg']  = '取消发布商品成功';
        } elseif($params['is_pub'] == 1){
            $params['is_pub'] = 0;
            $this->DAO->update_products_status($params);
            $result['code'] = 1;
            $result['msg']  = '下架商品成功';
        } elseif($params['is_pub'] == 0 || $params['is_pub'] == 3 || $params['is_pub'] == 6){
            $params['is_pub'] = 4;
            $this->DAO->update_products_status($params);
            $result['code'] = 1;
            $result['msg']  = '删除商品成功';
        }
        die('0' . base64_encode(json_encode($result)));
    }

    //获取卖家商品信息
    public function get_publish_product_info(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        $params = $_POST;
        if(!$params['id']){
            $result['msg'] = '';
        }
        $data = $this->DAO->get_publish_product_info($params['id']);
        if($data){
            $result['code'] = 1;
            $result['msg']  = '查询成功';
        } else{
            $result['msg'] = '暂无数据';
        }
        die('0' . base64_encode(json_encode($result)));
    }

    //修改商品信息
    public function edit_products(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        $params = $_POST;
        var_dump($_FILES);
        var_dump($params);
        if(!$_FILES && !$params['old_img']){
            $result['msg'] = '请上传图片';
            die('0' . base64_encode(json_encode($result)));
        }else{
            $img_url = '';
            foreach ($_FILES as $key=>$data){
                if($_FILES[$key]['tmp_name']){
                    $img_url .= ','.$this->up_img($key, "images/product_img");
                }
            }
        }
        var_dump($img_url);
        if(!$params['pagehash'] || $params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = '请求出错,请刷新页面重新提交。';
            die('0' . base64_encode(json_encode($result)));
        }
        $is_msg = $this->product_verify($params,1);
        if($is_msg){
            $result['msg'] = $is_msg;
            die('0' . base64_encode(json_encode($result)));
        }
        $info = $this->DAO->get_product_info($params['order_id']);
        if(!$info || !$params['order_id']){
            $result['msg'] = '查无此订单';
            die('0' . base64_encode(json_encode($result)));
        }
        if($info['is_pub'] != 3 && $info['is_pub'] != 6 && $info['is_pub'] !=0){
            $result['msg'] = '该商品无需修改信息';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_FILES && !$params['old_img']){
            $result['msg'] = '请上传图片';
            die('0' . base64_encode(json_encode($result)));
        }else{
            $img_url = $params['old_img'];
            foreach ($_FILES as $key=>$data){
                if($_FILES[$key]['tmp_name']){
                    $img_url .= ','.$this->up_img($key, "images/product_img");
                }
            }
        }
        $params['proportion'] = intval($params['goodsNum']/$params['goodsprice']);
        $params['title'] = $params['goodsNum'].'魔石'.'(1元='.intval($params['goodsNum']) / intval($params['goodsprice']).'魔石)';
        $this->DAO->edit_product($params);
        $this->DAO->update_product_img($params['order_id'],trim(',',$img_url));
        $result['code'] = 1;
        $result['msg'] = '修改成功';
        die('0' . base64_encode(json_encode($result)));
    }

    //添加商品图片
    public function add_prducts_img(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        $params = $_POST;
        if($_FILES['img']['tmp_name']){
            $img1           = $this->up_img('img', PRODUCT_IMG, array(), 1, 1, 'img' . time());
            $img_url        = $img1;
            $product_img_id = $this->DAO->add_product_img($params['id'], $img_url);
            if(!$product_img_id){
                $result['msg'] = '上传图片出错';
                die('0' . base64_encode(json_encode($result)));
            }
        }
        $result['msg'] = '图片上传成功';
        die('0' . base64_encode(json_encode($result)));
    }

    //删除商品图片
    public function delete_prducts_img(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params = $_POST;
        $this->DAO->delete_products_imgs($params['id']);
        $is_del = $this->DAO->get_products_imgs_is_del($params['id'])['is_del'];
        if($is_del == 2){
            $result['code'] = 1;
            $result['msg']  = '删除成功';
        } else{
            $result['msg'] = '删除失败';
        }
        die('0' . base64_encode(json_encode($result)));
    }

    //获取商品图片
    public function get_products_imgs(){
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $params   = $_POST;
        $img_list = $this->DAO->get_product_imgs($params['product_id']);
        if($img_list){
            $result['code'] = 1;
            $result['msg']  = '查询成功';
            $result['data'] = $img_list;
        } else{
            $result['msg'] = '暂无数据';
        }
        die('0' . base64_encode(json_encode($result)));
    }

    public function publish_product_view(){
        $game_list = $this->DAO->get_game_list();
        $this->assign('game_list', $game_list);
        $this->display('moyu/publish_view.html');
    }

    //渠道
    public function publish_channel_view($game_id){
        $channel_list = $this->DAO->get_channel();
        $this->assign('game_id', $game_id);
        $this->assign('channel_list', $channel_list);
        $this->display('moyu/publish_channel_view.html');
    }

    //服务器
    public function publish_serv_view($ch_id, $serv_name, $game_id){
        $server_list = $this->DAO->get_specified_ch_servs($ch_id, $serv_name, $game_id);
        $this->assign('game_id', $game_id);
        $this->assign('ch_id', $ch_id);
        $this->assign('server_list', $server_list);
        $this->display('moyu/product_serv_view.html');
    }

    public function product_category_view($ch_id, $serv_id, $game_id){
        $this->assign('game_id', $game_id);
        $this->assign('serv_id', $serv_id);
        $this->assign('ch_id', $ch_id);
        $this->display('moyu/product_category.html');
    }

    public function add_product_view($ch_id, $serv_id, $game_id){
        $this->assign('game_id', $game_id);
        $this->assign('serv_id', $serv_id);
        $this->assign('ch_id', $ch_id);
        $this->page_hash();
        $this->display('moyu/add_pusblish_product_view.html');
    }

    //发布商品成功
    public function pusblish_success($id){
        $this->assign('id', $id);
        $this->display('moyu/pusblish_success.html');
    }

    //发货商品页面
    public function deliver_product_view($id){
        $this->page_hash();
        $this->assign('id', $id);
        $this->display('moyu/deliver_product_view.html');
    }

    //修改商品信息
    public function edit_product_view($id){
        $info = $this->DAO->get_product_info($id);
        $img = $this->DAO->get_product_imgs($id);
        $img_list = explode(',',$img['img_url']);
        $this->page_hash();
        $this->assign("info",$info);
        $this->assign("img_list",$img_list);
        $this->assign("type",1);
        $this->display("moyu/add_pusblish_product_view.html");
    }

    //获取用户收藏列表
    public function get_user_collect_list(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id'] == null){
            $result['msg']  = '请先登录';
            $result['code'] = 2;
            $result['url']  = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        //收藏
        $data = $this->DAO->get_user_collect_list($_SESSION['user_id']);
        $data = $this->get_info($data);
        if($data){
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $data;
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 1;
        $result['msg'] = '暂无数据';
        $result['data'] = [];
        die('0' . base64_encode(json_encode($result)));
    }

    public function get_user_invalid_collect_list(){
        $result = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id'] == null){
            $result['msg']  = '请先登录';
            $result['code'] = 2;
            $result['url']  = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        //失效
        $data = $this->DAO->get_user_collect_list($_SESSION['user_id'],1);
        $data = $this->get_info($data);
        if($data){
            $result['msg']  = '获取数据成功';
            $result['code'] = 1;
            $result['data'] = $data;
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 1;
        $result['msg'] = '暂无数据';
        $result['data'] = [];
        die('0' . base64_encode(json_encode($result)));
    }
    public function get_info($data_list){
        foreach($data_list as $key => $data){
            $game                            = $this->DAO->get_game_info($data['game_id']);
            $service                         = $this->DAO->get_service_info($data['serv_id']);
            $channel                         = $this->DAO->get_channel_infomation($data['channel_id']);
            $data_list[$key]['game_name']    = $game['game_name'];
            $data_list[$key]['serv_name']    = $service['serv_name'];
            $data_list[$key]['channel_name'] = $channel['channel_name'];
        }
        return $data_list;
    }



}