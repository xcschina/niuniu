<?php
COMMON('niuniuDao');
class account_offline_admin_dao extends niuniuDao
{
    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "account_offline";
    }

    public function get_account_offline_list($page,$params){
        $this->limit_sql = "SELECT * FROM account_offline a where 1=1 ";
        if($params['game']){
            $this->limit_sql .= " and a.game='".$params['game']."'";
        }
        if($params['game_area']){
            $this->limit_sql .= " and a.game_area='".$params['game_area']."'";
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel='".$params['channel']."'";
        }
        if($params['channel_pay']){
            $this->limit_sql .= " and a.channel_pay='".$params['channel_pay']."'";
        }
        if($params['game_account']){
            $this->limit_sql .= " and a.game_account='".$params['game_account']."'";
        }
        if($params['input_id']){
            $this->limit_sql .= " and a.input_id=".$params['input_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.time <".strtotime($params['end_time']);
        }
        $this->limit_sql .= " ORDER BY a.id DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_account_offline_all($params){
        $this->sql = "SELECT * FROM account_offline a where 1=1 ";
        if($params['game']){
            $this->sql .= " and a.game='".$params['game']."'";
        }
        if($params['game_area']){
            $this->sql .= " and a.game_area='".$params['game_area']."'";
        }
        if($params['channel']){
            $this->sql .= " and a.channel='".$params['channel']."'";
        }
        if($params['channel_pay']){
            $this->sql .= " and a.channel_pay='".$params['channel_pay']."'";
        }
        if($params['game_account']){
            $this->sql .= " and a.game_account='".$params['game_account']."'";
        }
        if($params['input_id']){
            $this->sql .= " and a.input_id=".$params['input_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.time <".strtotime($params['end_time']);
        }
        $this->sql .= " ORDER BY a.id DESC";
        $this->doResultList();
        return $this->result;
    }

    public function get_search_list($search_condition){
        $data = memcache_get($this->mmc, "account_offline_".$search_condition);
        if (!$data){
            $this->sql = "SELECT ".$search_condition." FROM account_offline GROUP BY ".$search_condition." ORDER BY id";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"account_offline_".$search_condition,$data,1,86400);
        }
        return $data;
    }

    public function import_account_offline($import_data){
        $sql = 'INSERT INTO account_offline(game,game_area,game_account,channel,channel_pay,money,payment_discount,payment,money_pay,channel_receivmoney,time,input_id)VALUES';
        foreach ($import_data as $value){
            $sql .= "('".$value['game']."','".$value['game_area']."','".$value['game_account']."','".$value['channel']."','".$value['channel_pay']."',
                    ".$value['money'].",'".$value['payment_discount']."',".$value['payment'].",".$value['money_pay'].",'".$value['channel_receivmoney']."',".$value['time'].",".$_SESSION['usr_id']."),";
        }
        $this->sql = trim($sql,',');
        $this->doInsert();
        memcache_delete($this->mmc,"account_offline_game");
        memcache_delete($this->mmc,"account_offline_channel");
        memcache_delete($this->mmc,"account_offline_channel_pay");
        memcache_delete($this->mmc,"account_offline_input_id");
        return $this->LAST_INSERT_ID;
    }

    public function get_input_name($input_id_list){
        $this->sql = "SELECT id,real_name FROM admins WHERE id IN (".$input_id_list.")";
        $this->doResultList();
        return $this->result;
    }

    public function get_game_area($game_name){
        $this->sql = "SELECT game_area FROM account_offline WHERE game=? GROUP BY game_area";
        $this->params = array($game_name);
        $this->doResultList();
        return $this->result;
    }

}