<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/5 >
// --------------------------------------
COMMON('dao');
class shop_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    // -----------------------
    //  店铺列表
    // -----------------------

    public function get_shop_list($params, $page=1){
        $this->limit_sql = 'select * from shop where 1=1';
        is_numeric($params['s_id']) && $this->limit_sql .= ' and s_id='.$params['s_id'];
        is_numeric($params['user_id']) && $this->limit_sql .= ' and user_id='.$params['user_id'];
        is_numeric($params['s_status']) && $this->limit_sql .= ' and s_status='.$params['s_status'];
        if(trim($params['s_name'])){ 
            $this->limit_sql .= ' and s_name=\''.$params['s_name'].'\'';
        }
        $this->limit_sql .= ' order by s_sort desc';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function shop_add_do($params = array()){
        $this->sql = 'insert into shop(user_id, s_name, s_intro, s_icon, s_sort, s_status, order_num, open_time, close_time, create_time, s_qq, s_tel)values(?,?,?,?,?,?,?,?,?,?,?,?)';
        $this->params = array($params['user_id'], $params['s_name'], $params['s_intro'], $params['s_icon'], $params['s_sort'], $params['s_status'], $params['order_num'], $params['open_time'], $params['close_time'], time(), $params['s_qq'], $params['s_tel']);
        $this->doInsert();
    }

    public function shop_edit_do($params){
        $this->sql = 'update shop set s_name=?, s_intro=?, s_icon=?, s_sort=?, s_status=?, order_num=?, open_time=?, close_time=?, s_qq=?, s_tel=? where s_id=?';
        $this->params = array($params['s_name'], $params['s_intro'], $params['s_icon'], $params['s_sort'], $params['s_status'], $params['order_num'], $params['open_time'], $params['close_time'], $params['s_qq'], $params['s_tel'], $params['s_id']);
        $this->doExecute();
    }

    public function shop_lock_do($shop_id, $s_status){
        $this->sql = 'update shop set s_status=? where s_id=?';
        $this->params = array($s_status, $shop_id);
        $this->doExecute();
    }

    public function get_shop_info($shop_id){
        $this->sql = 'select * from shop where 1=1 and s_id=?';
        $this->params = array($shop_id);
        $this->doResult();
        return $this->result;
    }

    // ---------------------------------
    // 一用户账号，只能拥有1个店铺
    // 新增店铺成功后，给店铺管理员开通后台权限
    // ---------------------------------
    public function shop_admin_add_do($params=array()){
        if(!intval($params['user_id'])) return 0; // 参数有误
        $this->result = null;

        // 判断要升级为店铺后台管理员的普通用户是否存在
        $this->sql = 'select user_id,password,nick_name,user_type from user_info where user_id=?';
        $this->params = array((int)$params['user_id']);
        $this->doResult();
        $user_info = $this->result;
        if(!$user_info['user_id']){ return -1; } // 要升级为店铺后台管理员的普通用户不存在
        $this->result = null;

        // 二重判定是否已经是店铺后台管理员
        // $this->sql = 'select real_name,shop_id from admins where real_name=?';
        // $this->params = array($user_info['user_id']);
        // $this->doResult();
        // $admin_info = $this->result;
        // $this->result = null;

        // 判断该普通用户是否是店铺后台管理员，暂时只使用了user_type！
        if($user_info['user_type'] == 3){ return -2; } // 该用户已拥有店铺,一个账号只允许拥有一个店铺

        // 该普通用户首次升级为店铺后台管理员 - 开店，升级管理员，赋权限
        $this->shop_add_do($params); // 新增店铺
        $shop_id = $this->LAST_INSERT_ID;
        $this->LAST_INSERT_ID = null;

        $shop_info = $this->get_shop_info($shop_id);
        if(!$shop_info['user_id']){ return -3;} // 店铺信息新增失败

        // 写入user_info表,设置店铺管理员的用户信息 user_type 字段为商户
        $this->sql = 'update user_info set user_type=3 where user_id=?';
        $this->params = array($user_info['user_id']);
        $this->doExecute();

        // 写入admins表, 空密码则默认为123456
        $account = $user_info['nick_name']?:$user_info['mobile'];
        $this->sql = 'insert into admins(account, real_name, usr_pwd, usr_name, `group`, shop_id, last_login)values(?,?,?,?,?,?,?)';
        $this->params = array($account,$user_info['user_id'],$user_info['password']?:'e10adc3949ba59abbe56e057f20f883e',$account,'shop_admin',$shop_info['s_id'],time());
        $this->doInsert();
        if(!$this->LAST_INSERT_ID){ return -4;} // 普通用户升级为店铺后台管理员失败

        // 写入权限表
        $admin_id = $this->LAST_INSERT_ID;
        $this->sql = 'insert into admin_permissions(usr_id,module,permissions)values(?,?,?)';
        // $this->params = array($admin_id, '1,52,54,55', '54_0,54_1,54_2,54_3,54_4,54_5,55_0,55_1,55_2,55_3,55_4,55_5,58_0,58_1,58_2,58_3,58_4,58_5');// 本地
        $this->params = array($admin_id, '1,54,56,57,58', '56_0,56_1,56_2,56_3,56_4,56_5,57_0,57_1,57_2,57_3,57_4,57_5,58_0,58_1,58_2,58_3,58_4,58_5');
        $this->doInsert();
        if(!$this->LAST_INSERT_ID){ return -5;} // 为新店铺后台管理员开通权限失败

        return 11;
    }


    // -----------------------
    // 我的游戏
    // -----------------------

    // $page=0 不分页 | $g_status=1 筛选66173上架的可用游戏
    public function get_shop_game_list($params=array(), $page=1, $g_status=1){
        $select = 'g.id, g.game_name, g.game_icon, g.first_letter, sg.*';
        $sql = 'select '.$select.' FROM shop_game AS sg LEFT JOIN game AS g ON sg.game_id=g.id WHERE 1=1 AND sg.s_id=? AND g.is_del=0';
        if($g_status==1){ 
            $sql .= ' and g.status=1';
        }
        is_numeric($params['game_id']) && $sql .= ' and sg.game_id='.$params['game_id']; // 单游戏
        $params['first_letter'] && $sql .= ' and g.first_letter=\''.strtoupper($params['first_letter'])."'";
        isset($params['is_hot']) && $sql .= ' and sg.is_hot=1';
        is_numeric($params['sg_status']) && $sql .= ' and sg.sg_status='.$params['sg_status'];
        if($params['sg_sort'] == 'asc'){
            $sql .= ' order by sg.sg_sort';
        }else{
            $sql .= ' order by sg.sg_status desc, sg.sg_sort desc, sg.is_hot desc,g.id desc'; // 店铺内部游戏下架后，店内排序自然无效了
        }
        $this->params = array($params['shop_id']);
        if($page){
            $this->limit_sql = $sql;
            $this->doLimitResultList($page);
        }else{
            $this->sql = $sql;
            $this->doResultList();
        }
        return $this->result;
    }

    public function get_shop_game_info($sg_id=0, $moreinfo=0){
        if($moreinfo){
            $select = 'g.id, g.game_name, g.game_icon, g.first_letter, sg.*';
            $sql = 'select '.$select.' FROM shop_game AS sg LEFT JOIN game AS g ON sg.game_id=g.id WHERE 1=1 AND sg.sg_id=? AND g.status=1 AND g.is_del=0';
        }else{
            $sql = 'select * from shop_game where 1=1 and sg_id=?';
        }
        $this->sql    = $sql;
        $this->params = array($sg_id);
        $this->doResult();
        return $this->result;
    }

    public function shop_game_add_do($params = array()){
        $this->sql = 'select sg_id from shop_game where 1=1 and s_id=? and game_id=?';
        $this->params = array($params['shop_id'], $params['game_id']);
        $this->doResult();
        if($this->result){ return -1; } // 已存在

        $this->sql = 'insert into shop_game(s_id, game_id, is_hot, sg_sort, sg_status)values(?,?,?,?,?)';
        $this->params = array($params['shop_id'],$params['game_id'],$params['is_hot'],$params['sg_sort'],$params['sg_status']);
        $this->doInsert();
    }

    public function shop_game_edit_do($params = array()){
        $this->sql = 'update shop_game set s_id=?, game_id=?, is_hot=?, sg_sort=?, sg_status=? where sg_id=?';
        $this->params = array($params['s_id'], $params['game_id'], $params['is_hot'], $params['sg_sort'], $params['sg_status'], $params['sg_id']);
        $this->doExecute();
    }

    // 店铺游戏渠道控制编辑
    public function shop_game_ch_edit_do($params = array()){
        $this->sql = 'update shop_game set';
        $values = array();
        for ($i=1; $i <= $params['chs_num']; $i++) { 
            $this->sql .= ' ch_'.$i.'_1=?,';
            $this->sql .= ' ch_'.$i.'_2=?,';
            $firstpay_val = in_array($i, $params['firstpay']) ? 1 : 0;
            $recharge_val = in_array($i, $params['recharge']) ? 1 : 0;
            $values[] = $firstpay_val;
            $values[] = $recharge_val;
        }
        $this->sql = rtrim($this->sql,',');
        $this->sql .= ' where sg_id=?';
        $values[] = $params['sg_id'];
        $this->params = $values;
        $this->doExecute();
    }


    // 店铺商品列表 - 某款66173上架游戏的所有上架商品[首充号+首充号续充]
    public function get_shop_product_list($params=array(), $page=1){
        if($params['is_shop']){
            // 店内特价商品
            $select = 'g.game_name, g.product_img, p.title, p.price, p.stock, sp.sp_type as type, sp.product_id as id, sp.*';
            $sql = 'select '.$select.' from shop_product sp left join products as p on p.id=sp.product_id left join game as g on g.id=p.game_id where 1=1 and p.type in(1,2) and g.is_del=0 and p.is_pub=1 and sp.s_id=? and sp.game_id=?';
            if($params['product_id'] && is_numeric($params['product_id'])){
                $sql .= ' and sp.product_id='.$params['product_id'];
                $page = 1;
            }
            if($params['sp_type']){ $sql .= ' and sp.sp_type='.$params['sp_type']; }
            $this->params = array($params['shop_id'], $params['game_id']);
        }else{
            // 66可用游戏可用商品列表
            $sql = 'select g.game_name, g.product_img, p.* from products as p left join game as g on p.game_id=g.id where 1=1 and p.type in(1,2) and g.is_del=0 and p.is_pub=1 and p.game_id=?';
            if($params['product_id'] && is_numeric($params['product_id'])){
                $sql .= ' and p.id='.$params['product_id'];
                $page = 1;
            }
            if($params['sp_type']){ $sql .= ' and p.type='.$params['sp_type']; }
            $this->params = array($params['game_id']);
        }
        $this->limit_sql = $sql;
        $this->doLimitResultList($page);
        return $this->result;
    }

    // 店铺某特价商品信息
    public function get_shop_product_info($params = array()){
        $this->sql = 'select * from shop_product where 1=1 and s_id=? and product_id=?';
        $this->params = array($params['shop_id'], $params['product_id']);
        $this->doResult();
        return $this->result;
    }

    // 店铺某特价商品渠道折扣编辑
    public function shop_product_ch_edit_do($params = array()){
        // 查看店铺是否已存在该特价商品
        if($params['sp_id']){
            $this->sql = 'update shop_product set';
            $values = array();
            for ($i=1; $i <= $params['chs_num']; $i++) { 
                $this->sql .= ' ch_'.$i.'=?,';
                $values[] = abs($params['ch_'.$i]);
            }
            $values[] = $params['shop_id'];
            $values[] = $params['product_id'];
            $this->sql = rtrim($this->sql,',');
            $this->sql .= ' where s_id=? and product_id=?';
            $this->params = $values;
            $this->doExecute();
        }else{
            $insert = 's_id, game_id, product_id, sp_type,';
            $fields = '?,?,?,?,';
            $values = array($params['shop_id'],$params['game_id'],$params['product_id'],$params['type']);
            for ($i=1; $i <= $params['chs_num']; $i++) { 
                $insert .= 'ch_'.$i.',';
                $fields .= '?,';
                $values[] = abs($params['ch_'.$i]);
            }
            $insert = rtrim($insert,',');
            $fields = rtrim($fields,',');
            $this->sql = 'insert into shop_product('.$insert.')values('.$fields.')';
            $this->params = $values;
            $this->doInsert();
        }
    }


    // -----------------------
    //  我的订单
    // -----------------------
    public function get_shop_order_list($params,$page=0){
        if($page){
            // 订单后台列表
            $sql = "select ord.*,pro.type,b.game_name,a.real_name from orders ord inner join products pro on ord.product_id=pro.id inner join game as b on ord.game_id=b.id left join admins as a on ord.service_id=a.id where 1=1 and ord.shop_id=?";
        }else{
            // 订单导出数据
            $sql = 'select ord.*,pro.title,pro.type,b.game_name,a.real_name from orders ord inner join products pro on ord.product_id=pro.id inner join game as b on ord.game_id=b.id inner join game_servs as gs on gs.id=ord.serv_id left join admins as a on ord.service_id=a.id where 1=1 and ord.shop_id=? ';
        }
        if($params['order_id']){
            $sql .= " and ord.order_id = '".$params['order_id']."'";
        }
        if($params['user_id']){
            $sql .= " and ord.buyer_id = '".$params['user_id']."'";
        }
        if($params['game_user']){
            $sql .= " and ord.game_user = '".$params['game_user']."'";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $sql .= " and ord.game_id =".$params['game_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $sql .= " and ord.serv_id =".$params['serv_id'];
        }
        if($params['game_channel'] && is_numeric($params['game_channel'])){
            $sql .= " and ord.game_channel =".$params['game_channel'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status']=='0'){
            if($params['status']=="-1"){
                $sql .= " and ord.is_del =2";
            }else{
                $sql .= " and ord.status =".$params['status'];
            }
        }
        if($params['buy_time'] && $params['buy_time2']){
            $sql .= " and ord.buy_time>=".$params['buy_time']." and ord.buy_time<=".$params['buy_time2'];
        }else if($params['time'] && !$params['time2']) {
            $sql .= " and ord.buy_time>=".$params['buy_time'];
        } else if(!$params['time'] && $params['time2']) {
            $sql .= " and ord.buy_time<=".$params['buy_time2'];
        }
        $sql = $sql." order by ord.id desc";

        if($page){
            $this->limit_sql = $sql;
            $this->params = array($params['shop_id']);
            $this->doLimitResultList($page);
        }else{
            $this->sql = $sql;
            $this->params = array($params['shop_id']);
            $this->doResultList();
        }
        return $this->result;
    }


    // -------------------------------
    // 折扣
    // -------------------------------

    // 获取指定游戏的折扣
    public function get_game_ch_discount($game_id){
        $data = memcache_get($this->mmc, 'game_ch_discount'.$game_id);
        if(!$data){
            $this->sql = 'select * from channel_discount where game_id=?';
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'game_ch_discount'.$game_id, $data, 1, 60);
        }
        return $data;
    }

    // 获取指定商品的折扣
    public function get_product_ch_discount($product_id){
        $data = memcache_get($this->mmc, 'product_ch_discount'.$product_id);
        if(!$data){
            $this->sql = 'select * from product_discounts where product_id=?';
            $this->params = array($product_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'product_ch_discount'.$product_id, $data, 1, 60);
        }
        return $data;
    }

    // 商品折扣[上架与否不重要]
    public function get_product_discount($product_id){
        $this->sql="select p.*,g.game_name,g.tags,g.product_img,pd.* from products p
        inner join game g on p.game_id=g.id LEFT join product_discounts as pd on p.id=pd.product_id where p.id=?";
        $this->params=array($product_id);
        $this->doResult();
        return $this->result;
    }

    // ---------------------------------

    public function get_master_game_list(){
        $this->sql="select * from game where is_del=0 and status=1";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel_list(){
        $this->sql="select * from channels";
        $this->doResultList();
        return $this->result;
    }

    // 获取某款上架的66173游戏的所有首充号和首充号续充上架商品
    public function get_master_product_list($game_id=0){
        $this->sql = 'select g.game_name, p.* from products as p left join game as g on p.game_id=g.id where 1=1 and p.type in(1,2) and g.is_del=0 and p.is_pub=1 and p.game_id=?';
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }
}