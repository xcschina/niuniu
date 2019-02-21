<?php
COMMON('dao');
class sell_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //游戏列表
    public function get_game_list(){
        $data = $this->mmc->get("game_list");
        if(!$data){
            $this->sql="select * from game where is_del=0 and status=1";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_list", $data, 1 ,60);
        }
        return $data;
    }

    //获取审核列表
    public function get_sell_list($params,$page){
        $this->limit_sql="select a.id,a.user_id,a.game_id,a.type,a.is_pub,a.add_time,a.title,b.game_name,c.serv_name,d.channel_name from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id where 1=1";
        if(empty($params['type'])){
            $this->limit_sql=$this->limit_sql." and a.type in (4,5,6) ";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and a.game_id =".$params['game_id'];
        }
        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql=$this->limit_sql." and a.type =".$params['type'];
        }

        if($params['is_pub']=="0" || is_numeric($params['is_pub'])){
            $this->limit_sql=$this->limit_sql." and a.is_pub =".$params['is_pub'];
        }
//        if($params['serv_id'] && is_numeric($params['serv_id'])){
//            $this->limit_sql=$this->limit_sql." and p.serv_id =".$params['serv_id'];
//        }
        if($params['user_id']){
            $this->limit_sql=$this->limit_sql." and a.user_id =".$params['user_id'];
        }else{
            $this->limit_sql=$this->limit_sql." and a.user_id>1 ";
        }
        $this->limit_sql=$this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    //商品附加信息
    public function get_products_info($id){
        $this->sql="select g.game_name,p.*,s.serv_name from products as p inner join game as g on g.id=p.game_id  left JOIN
                  game_servs as s on p.serv_id=s.id where p.id=".$id;
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_extra_info($id){
        $this->sql="select * from sell_extra where product_id=".$id;
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    //商品图片信息
    public function get_products_img($id,$is_){
        $this->sql="select * from product_imgs where product_id=".$id;
        $this->doResultList();
        $data = $this->result;
        return $data;
    }
    //更新审核状态
    public function update_is_pus($id,$is_pub){
        $this->sql="update products set is_pub=? where id=?";
        $this->params = array($is_pub, $id);
        $this->doExecute();
    }
    //审核日志
    public function audit_log($product, $operation){
        $this->sql = "insert into sell_audit_logs(user_id, product_id, `type`, create_time, status, operation_id, `desc`, end_time)values(?,?,?,?,?,?,?,?)";
        $this->params = array($product['user_id'],$product['id'],$product['type'],$product['add_time'],
            $operation['status'],$operation['operation_id'],$operation['desc'],$operation['end_time']);
        $this->doInsert();
    }

    public function get_review_logs($id){
        $this->sql="select logs.*,a.real_name from sell_audit_logs as logs  left join admins as a on logs.operation_id = a.id  where logs.product_id=?";
        $this->params = array($id);
        $this->doResultList();
        $data = $this->result;
        return $data;
    }
}
