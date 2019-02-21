<?php
COMMON('dao');
class app_gifts_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function get_game_list(){
        $this->sql="select * from 66app_game_tb where is_del=0";
        $this->doResultList();
        return $this->result;
    }

    //礼包中心列表
    public function gift_info_list(){
        $this->sql="select a.*,b.game_name from 66app_gift_tb as a INNER JOIN 66app_game_tb as b on a.game_id=b.id where a.is_del=0";
        $this->doResultList();
        return $this->result;
    }


    //礼包中心表
    public function gift_info_lis($params,$page){
        $this->limit_sql="select a.*,b.game_name from 66app_gift_tb as a INNER JOIN 66app_game_tb as b on a.game_id=b.id where 1=1 ";
        if($params['title']){
            $this->limit_sql=$this->limit_sql." and a.title ='".$params['title']."'";
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and a.game_id =".$params['game_id'];
        }
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and a.channel_id =".$params['channel_id'];
        }
        $this->limit_sql=$this->limit_sql." order by a.is_top desc,a.lastupdate desc";

        $this->doLimitResultList($page);
        return $this->result;
    }

    public function gift_info($id,$page){
        $this->sql="select * from 66app_gift_tb where id=?";
        $this->params=array($id) ;
        $this->doResult();
        $data=$this->result;

        $this->limit_sql="select * from 66app_gifts where batch_id=? order by id asc";
        $this->doLimitResultList($page);
        $data['gift_codes']=$this->result;

        return $data;
    }

    public function get_gift_info($id){
        $this->sql="select * from 66app_gift_tb where is_del=0 and id=?";
        $this->params=array($id) ;
        $this->doResult();
        return $this->result;
    }

    public function get_gift_count($id){
        $this->sql="select count(1) as gift_unuse_count from game_gifts  where is_use=0 and batch_id=?";
        $this->params=array($id);
        $this->doResult();
        $result['gift_unuse_count']=$this->result['gift_unuse_count'];

        $this->sql="select count(1) as gift_count from game_gifts where batch_id=?";
        $this->params=array($id);
        $this->doResult();
        $result['gift_count']=$this->result['gift_count'];

        return $result;
    }

    public function upd_gift_count($params){
        $this->sql="update 66app_gift_tb set num=?,remain=? where id=?";
        $this->params=array($params['gift_count'],$params['gift_unuse_count'],$params['id']) ;
        $this->doExecute();
    }

    //获取首充号、礼包列表
    public function get_gifts_list($params,$page){
        $this->limit_sql="select a.*,b.game_name from 66app_gifts as a INNER JOIN 66app_game_tb as b on a.game_id=b.id where 1=1";
        if($params['gift_id'] && is_numeric($params['gift_id'])){
            $this->limit_sql=$this->limit_sql." and a.batch_id =".$params['gift_id'];
        }
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and a.game_id =".$params['game_id'];
        }
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and a.channel_id =".$params['channel_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql=$this->limit_sql." and a.serv_id =".$params['serv_id'];
        }
        if($params['is_use'] && is_numeric($params['is_use'])){
            $this->limit_sql=$this->limit_sql." and a.is_use =".$params['is_use'];
        }
        if($params['code']){
            $this->limit_sql=$this->limit_sql." and a.code = '".$params['code']."'";
        }
        $this->limit_sql=$this->limit_sql." order by a.id desc";

        $this->doLimitResultList($page);
        return $this->result;
    }


    //添加批次信息
    public function add_gift_info($gift){
        $this->sql = 'insert into 66app_gift_tb(game_id,serv_id,channel_id,title,remain,num,content,get_way,start_time,end_time,
        price,integral,`type`,effective_time,expired_time,is_recomm,is_attach,lastupdate) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $this->params = array($gift['game_id'],0,$gift['channel_id'],$gift['title'],$gift['remain'],$gift['num'],
            $gift['content'],$gift['get_way'],$gift['start_time'],$gift['end_time'],$gift['price'],$gift['integral'],$gift['type'],
            $gift['effective_time'],$gift['expired_time'],$gift['is_recomm'], $gift['is_attach'],strtotime("now"));
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    //更新批次信息
    public function upd_gift_info($gift){
        $this->sql = 'update 66app_gift_tb set game_id=?,serv_id=?,channel_id=?,title=?,content=?,get_way=?,start_time=?,end_time=?,
        price=?,integral=?,`type`=?,effective_time=?,expired_time=?,is_recomm=?,is_del=?,is_attach=?,is_top=?,lastupdate=? where id=?';
        $this->params = array($gift['game_id'],0,$gift['channel_id'],$gift['title'],$gift['content'],$gift['get_way'],$gift['start_time'],
            $gift['end_time'],$gift['price'],$gift['integral'],$gift['type'],$gift['effective_time'],$gift['expired_time'],$gift['is_recomm'],
            $gift['is_del'],$gift['is_attach'],$gift['is_top'],strtotime("now"),$gift['id']);
        $this->doExecute();
    }

    public function batch_add_gift($codes,$vals){
        $sql_str="insert  into 66app_gifts(batch_id,code,game_id,serv_id,channel_id,is_use,price,create_time)values(?,?,?,?,?,?,?,?)";
        $params=array();
        foreach ($codes as $key=>$code) {
            array_push($params, $vals['batch_id'], $code, $vals['game_id'], 0, $vals['channel_id'], "0", $vals['price'], strtotime("now"));
            if ($key > 0) {
                $sql_str .= ",(?,?,?,?,?,?,?,?)";
            }
        }
        $this->params=$params;
        $this->sql=$sql_str;
        $this->doInsert();
    }

    //删除信息
    public function del_gift($id){
        $this->sql="delete from 66app_gifts where id=?";
        $this->params=array($id);
        $this->doExecute();
    }

}