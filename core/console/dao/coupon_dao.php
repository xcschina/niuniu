<?php
COMMON('dao');
class coupon_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_coupon_list($params,$page){
        $this->limit_sql="select a.* from coupon_tb as a inner join coupon_apply_type as b on a.id=b.coupon_id where 1=1";
        if($params['id'] && is_numeric($params['id'])){
            $this->limit_sql=$this->limit_sql." and a.id ='".$params['id']."'";
        }
        if($params['coupon_name']){
            $this->limit_sql=$this->limit_sql." and a.name like '%".$params['coupon_name']."%'";
        }
        if($params['coupon_type']){
            $this->limit_sql=$this->limit_sql." and a.type =".$params['coupon_type'];
        }
        if($params['send_type']){
            $this->limit_sql=$this->limit_sql." and a.send_type =".$params['send_type'];
        }
        if($params['review_status']){
            $this->limit_sql=$this->limit_sql." and a.review_status =".$params['review_status'];
        }
        if($params['issue_status']){
            $this->limit_sql=$this->limit_sql." and a.issue_status =".$params['issue_status'];
        }
        $this->limit_sql=$this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_count_total($coupon_id){
        $this->sql = "select count(0) as count from coupon_user_log_tb where coupon_id=? ";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result['count'];
    }

    public function get_count_receive($coupon_id){
        $this->sql = "select count(0) as count from coupon_user_log_tb where receive_time<>'' and coupon_id=? ";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result['count'];
    }

    public function get_coupon_info($id){
        $this->sql="select a.*, b.apply_type,b.channel_id,b.game_id,b.pay_type,b.goods_id from coupon_tb as a inner join coupon_apply_type as b on a.id=b.coupon_id where a.id=? ";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_logs($id){
        $this->sql="select * from coupon_user_log_tb where coupon_id = ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_log($params,$id,$page){
        $this->limit_sql="select * from coupon_user_log_tb where coupon_id =".$id;
        if($params['user_id'] && is_numeric($params['user_id'])){
            $this->limit_sql=$this->limit_sql." and user_id ='".$params['user_id']."'";
        }
        if($params['time_name']){
            if($params['time'] && $params['time2']){
                $this->limit_sql=$this->limit_sql .=  " and ".$params['time_name'].">=".$params['time']." and ".$params['time_name']."<=".$params['time2'];
            }else if($params['time'] && !$params['time2']) {
                $this->limit_sql=$this->limit_sql.=  " and ".$params['time_name'].">=".$params['time'];
            } else if(!$params['time'] && $params['time2']) {
                $this->limit_sql=$this->limit_sql.=  " and ".$params['time_name']."<=".$params['time2'];
            }
        }
        if($params['sort']){
            $this->limit_sql=$this->limit_sql." order by ".$params['sort']." desc";
        }else{
            $this->limit_sql=$this->limit_sql." order by id desc";
        }
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_log($id,$user_id,$type){
        $this->sql="select * from coupon_user_log_tb where user_id=? and coupon_type=? and coupon_id = ?";
        $this->params=array($user_id,$type,$id);
        $this->doResult();
        return $this->result;
    }

    public function get_goods_list($game_id){
        $this->sql="select * from products where is_pub=1 and game_id=? order by id desc";
        $this->params=array($game_id);
        $this->doResultList();
        return $this->result;
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

    //游戏列表
    public function get_channel_list(){
        $data = $this->mmc->get("channel_list");
        if(!$data){
            $this->sql="select * from channels";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channel_list", $data, 1 ,60);
        }
        return $data;
    }

    public function save_coupon($params){
        $this->sql="insert into coupon_tb(`name`,`discount_type`,`type`,`discount`,valid_type,start_time,end_time,total_amount,discount_amount,valid_days,review_status,total,send_type,content,operation_time) value(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['coupon_name'],$params['discount_type'],$params['coupon_type'],$params['discount'],$params['valid_type'],$params['start_time'],$params['end_time'],$params['total_amount'],$params['discount_amount'],$params['valid_days'],1,$params['total'],$params['send_type'],$params['content'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_coupon($params){
        $this->sql="update coupon_tb set `name`=?,`discount`=?,start_time=?,end_time=?,total_amount=?,discount_amount=?,valid_days=?,total=?,content=?,operation_time=? where id=?";
        $this->params=array($params['coupon_name'],$params['discount'],$params['start_time'],$params['end_time'],$params['total_amount'],$params['discount_amount'],$params['valid_days'],$params['total'],$params['content'],time(),$params['id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_review_status($params){
        $this->sql="update coupon_tb set review_status=?,reason=? where id=?";
        $this->params=array($params['review_status'],$params['reason'],$params['id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_issue_status($params,$issue_status){
        $this->sql="update coupon_tb set issue_status=?,issue_start_time=?,issue_end_time=? where id=?";
        $this->params=array($issue_status,$params['issue_start_time'],$params['issue_end_time'],$params['id']);
        $this->doInsert();
    }

    public function update_coupon_status($id,$status){
        $this->sql="update coupon_tb set issue_status=? where id=?";
        $this->params=array($status,$id);
        $this->doInsert();
    }

    public function save_user_log($coupon_id,$user_id,$coupon_type){
        $this->sql="insert into coupon_user_log_tb(coupon_id,user_id,coupon_type,add_time) value(?,?,?,?)";
        $this->params=array($coupon_id,$user_id,$coupon_type,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }


    public function count_coupon_logs($id){
        $this->sql = "select count(*) as count from coupon_user_log_tb where coupon_id = ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function save_coupon_apply_type($coupon_id,$apply_type,$channels,$game_id,$pay_type,$goods_id){
        $this->sql="insert into coupon_apply_type(coupon_id,apply_type,channel_id,game_id,pay_type,goods_id,operation_time) value(?,?,?,?,?,?,?)";
        $this->params=array($coupon_id,$apply_type,$channels,$game_id,$pay_type,$goods_id,time());
        $this->doInsert();
    }

    public function save_coupon_operation_log($coupon_id,$user_id,$desc){
        $this->sql="insert into coupon_operation_log(coupon_id,operation_id,`desc`,operation_time) value(?,?,?,?)";
        $this->params=array($coupon_id,$user_id,$desc,time());
        $this->doInsert();
    }

    public function update_coupon_apply_type($coupon_id,$apply_type,$channels,$game_id,$pay_type,$goods_id){
        $this->sql="update coupon_apply_type set coupon_id=?,apply_type=?,channel_id=?,game_id=?,pay_type=?,goods_id=?,operation_time=? where coupon_id=?";
        $this->params=array($coupon_id,$apply_type,$channels,$game_id,$pay_type,$goods_id,time(),$coupon_id);
        $this->doInsert();
    }

    public function get_massages_info($user_id,$params){
        $this->sql = "select * from messages where receiver_id=? and title=? and content=? and is_read=0";
        $this->params=array($user_id,$params['name'],$params['content']);
        $this->doResult();
        return $this->result;
    }

    public function add_massages_info($user_id,$params){
        $this->sql="insert into messages(receiver_id,title,content,add_time)values(?,?,?,?)";
        $this->params=array($user_id,$params['name'],$params['content'],strtotime("now"));
        $this->doInsert();
    }
}