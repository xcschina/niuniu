<?php
COMMON('dao','ApplicationQQ_api');
class qq_web_dao extends Dao {
    private $api;
    public function __construct(){
        parent::__construct();
        $this->api = new ApplicationQQ_api();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_recommend_game(){
        $res = $this->api->getRecommendList(10);
        return $res;
    }

    public function get_recommend_list($page,$pageContext){
        if ($page==1){
            $res = $this->api->getRecommendList(20);
        }else {
            if ($pageContext){
                $res = $this->api->getRecommendList(20,'['.$pageContext.']');
            }else{
                $res = array();
            }
        }
        return $res;
    }

    public function get_app_list($page,$pageContext,$type){
        if ($type==1){
            //网游
            if ($page==1){
                $res = $this->api->getAppList('wangyou');
            }else {
                if ($pageContext){
                    $res = $this->api->getAppList('wangyou','['.$pageContext.']');
                }else{
                    $res = array();
                }
            }
        }elseif ($type==2){
            //新游
            if ($page==1){
                $res = $this->api->getAppList_15();
            }else {
                if ($pageContext){
                    $res = $this->api->getAppList_15('['.$pageContext.']');
                }else{
                    $res = array();
                }
            }
        }elseif ($type==3){
            //单机
            if ($page==1){
                $res = $this->api->getAppList_17();
            }else {
                if ($pageContext){
                    $res = $this->api->getAppList_17('['.$pageContext.']');
                }else{
                    $res = array();
                }
            }
        }else{
//            //兼容旧版
//            if ($page==1){
//                $res = $this->api->getAppList('wangyou');
//            }else {
//                if ($pageContext){
//                    $res = $this->api->getAppList('wangyou','['.$pageContext.']');
//                }else{
//                    $res = array();
//                }
//            }
            if ($page==1){
                $res = $this->api->getAppList_15();
            }else {
                if ($pageContext){
                    $res = $this->api->getAppList_15('['.$pageContext.']');
                }else{
                    $res = array();
                }
            }
        }
        return $res;
    }

    public function get_rank_app_list($rank_id,$page,$pageContext){
        if ($page==1){
            $res = $this->api->getRankAppADList($rank_id,20);
        }else {
            if ($pageContext){
                $res = $this->api->getRankAppADList($rank_id,20,'['.$pageContext.']');
            }else{
                $res = array();
            }
        }
        return $res;
    }

    public function get_app_detail($apk_name){
        $res = $this->api->getAppDetailBatchNew('["'.$apk_name.'"]');
        return $res;
    }

    public function get_category_list(){
        $res = $this->api->getCategoryDetailList();
        return $res;
    }

    public function get_search_qq_list($keyword,$page,$pageContext){
        if ($page==1){
            $res = $this->api->searchADApp($keyword,20);
        }else{
            if ($pageContext){
            $res = $this->api->searchADApp($keyword,20,'['.$pageContext.']');
            }else {
                $res = array();
            }
        }
        return $res;
    }

    public function get_app_list_by_tagid($tagid,$page,$pageContext){
        if ($page==1){
            $res = $this->api->getAppList_06($tagid,20);
        }else {
            if ($pageContext){
                $res = $this->api->getAppList_06($tagid,20,'['.$pageContext.']');
            }else{
                $res = array();
            }
        }
        return $res;
    }

    public function get_fine_game_list($type,$page){
        //$data = memcache_get($this->mmc, "66apk_fine_game_list_".$type.$page);
        if(!$data){
            if ($type==1){
                $this->limit_sql="select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and type=1 and app_type=".$type;
            }elseif($type==2){
                $this->limit_sql="select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and app_type=".$type;
            }
            $this->limit_sql.=" order by last_update desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_fine_game_list_".$type.$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_game_count($type){
        if ($type==1){
            $this->sql="select count(1) as num from 66app_game_tb where is_del=0 and type=1 and app_type=".$type;
        }elseif($type==2){
            $this->sql="select count(1) as num from 66app_game_tb where is_del=0 and app_type=".$type;
        }
        $this->doResult();
        return $this->result;
    }

    public function get_niuguo_game(){
        $data = memcache_get($this->mmc, "66apk_re_niuguo_game");
        if(!$data){
            $this->sql = "select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and type = 1 and app_recommend=1";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_re_niuguo_game",$data, 1, 3600);
        }
        return $data;
    }

    public function get_search_count($keyword){
        $this->sql = "select count(1) as count from 66app_game_tb where game_name like ? and is_del=0 and type=1 ";
        $this->params = array('%'.$keyword.'%');
        $this->doResult();
        return $this->result;
    }

    public function get_search_list($keyword,$page){
        $this->limit_sql = "select id,game_icon,down_url,game_name,is_gift ,apk_name ,game_desc,
                            down_num ,version ,apk_size,game_title  from 66app_game_tb where game_name like ? and is_del=0 and type=1 ";
        $this->limit_sql .= " order by is_hot_search desc";
        $this->params = array('%'.$keyword.'%');
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_report_exposure($report_data){
        $res = $this->api->reportExposure($report_data);
        return $res;
    }

    public function update_report_click($report_data){
        $res = $this->api->reportClick($report_data);
        return $res;
    }

    public function update_report_download($report_data){
        $res = $this->api->reportDownload($report_data);
        return $res;
    }

    public function update_report_install($report_data){
        $res = $this->api->reportInstall($report_data);
        return $res;
    }

    public function get_search_suggest($keyword){
        $res = $this->api->getSearchSuggestNew($keyword);
        return $res;
    }

    public function get_category_list_new(){
        $res = $this->api->getNewCategoryList();
        return $res;
    }

    public function get_app_list_by_category($categoryId,$parentId,$page,$pageContext){
        if ($page==1){
            $res = $this->api->getCategoryAppList($categoryId,$parentId,20);
        }else {
            if ($pageContext){
                $res = $this->api->getCategoryAppList($categoryId,$parentId,20,'['.$pageContext.']');
            }else{
                $res = array();
            }
        }
        return $res;
    }

    public function insert_report_log($imei,$mac,$type,$status_code,$body_content){
        if (APIADDRESS=="http://maapi.3g.qq.com:8080/v1/"){
            $environment = 1;
        }elseif (APIADDRESS=="http://devmaapi.3g.qq.com:80/v1/"){
            $environment = 2;
        }else{
            $environment = 1;
        }
//        $this->sql = "INSERT INTO niuniu.report_log(imei,mac,type,status_code,time,environment,body_content)VALUES(?,?,?,?,?,?,?)";
//        $this->params = array($imei,$mac,$type,$status_code,time(),$environment,$body_content);
        $this->sql = "INSERT INTO niuniu.report_log_3(imei,mac,type,status_code,time,environment)VALUES(?,?,?,?,?,?)";
        $this->params = array($imei,$mac,$type,$status_code,time(),$environment);
        $this->doInsert();
    }

    public function get_chosen_game(){
        $data = memcache_get($this->mmc, "66apk_chosen_game");
        if(!$data){
            $this->sql="select id,game_banner,game_icon,down_url,game_name,down_num,apk_name,game_title from 66app_game_tb where is_del=0 and is_chosen=1 and type = 1 order by is_top desc, id desc limit 12";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"66apk_chosen_game",$data, 1, 600);
        }
        return $data;
    }

    public function get_chosen_game_list($type,$page){
        $data = memcache_get($this->mmc, "66apk_chosen_game_list_".$type.'_'.$page);
        if(!$data){
            if($type==1){
                $this->limit_sql="select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and is_chosen=1 and type=1 and app_type=".$type;
            }elseif($type==2){
                $this->limit_sql="select id,game_name,game_icon,game_banner,is_gift,game_title,version,down_url,apk_name,apk_size,down_num from 66app_game_tb where is_del=0 and is_chosen=1 and app_type=".$type;
            }
            $this->limit_sql.=" order by is_top desc,id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_chosen_game_list_".$type.'_'.$page,$data, 1, 600);
        }
        return $data;
    }

    public function get_chosen_game_count($type){
        if ($type==1){
            $this->sql="select count(1) as num from 66app_game_tb where is_del=0 and is_chosen=1 and type=1 and app_type=".$type;
        }elseif($type==2){
            $this->sql="select count(1) as num from 66app_game_tb where is_del=0 and is_chosen=1 and app_type=".$type;
        }
        $this->doResult();
        return $this->result;
    }
}

