<?php
COMMON('baseCore','pageCore');
DAO('qq_web_dao');
class  qq_web extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qq_web_dao();
        $this->game_type = array(
            101 => '动作',
            102 => '角色',
            103 => '射击',
            104 => '策略',
            105 => '即时',
            106 => '回合',
            107 => '休闲',
            108 => '冒险',
            109 => '模拟',
            110 => '竞技',
            111 => '卡牌',
            112 => '体育',
            113 => '格斗',
            114 => 'MOBA');
    }
    //获取首页推荐10个
    public function recommend_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_game = $this->DAO->get_recommend_game();
        if ($result_game['head']['ret']===0 && $result_game['body']['ret']===0){
            if ($result_game['body']['appList']){
                $recommend_game = array();
                foreach ($result_game['body']['appList'] as $value){
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($recommend_game,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>$value['shortDesc'],"version"=>$value['versionName'],
                        "down_url"=>$value['apkUrl'], "apk_name"=>$value['pkgName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['totalDownloadTimes']),"isTX"=>"1"));
                }
                $result = array("result" => "1", "desc" => "查询成功", "data" => $recommend_game);
            }else{
                $result['desc'] = "未能获取到腾讯数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //获取更多推荐
    public function recommend_list($page,$pageContext){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_game = $this->DAO->get_recommend_list($page,$pageContext);
        if ($result_game['head']['ret']===0 && $result_game['body']['ret']===0){
            if ($result_game['body']['appList']){
                //曝光上报
                $report = array();
                $recommend_game = array();
//                $this->err_log(var_export($result_game['body']['appList'],1),'yyb_yyq');
                foreach ($result_game['body']['appList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['pkgName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getRecommendList","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($recommend_game,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>$value['shortDesc'],"version"=>$value['versionName'],
                        "down_url"=>$value['apkUrl'], "apk_name"=>$value['pkgName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['totalDownloadTimes']),
                        "tag"=>"","isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                if ($res['head']['ret']===0 && $res['body']['ret']===0){
                    $result = array("result" => "1", "desc" => "查询成功", "data" => $recommend_game,"count"=>"","hasNext"=>((string)$result_game['body']['hasNext']),"pageContext"=>implode(",",$result_game['body']['pageContext']));
                }else{
                    $result['desc'] = "曝光上报失败";
                }
            }else{
                $result['desc'] = "未能获取到腾讯数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //获取网游列表
    public function app_list($page,$pageContext,$type){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_app = $this->DAO->get_app_list($page,$pageContext,$type);
        if ($result_app['head']['ret']===0 && $result_app['body']['ret']===0){
            if ($result_app['body']['appInfoList']){
                //曝光上报
                $report = array();
                $app_list = array();
                foreach ($result_app['body']['appInfoList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['pkgName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getAppList","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($app_list,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>"","version"=>$value['versionName'], "down_url"=>$value['apkUrl'],
                        "apk_name"=>$value['pkgName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['totalDownloadTimes']), "tag"=>"","isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                if ($res['head']['ret']===0 && $res['body']['ret']===0){
                    $result = array("result" => "1", "desc" => "查询成功", "data" => $app_list,"count"=>"","hasNext"=>((string)$result_app['body']['hasNext']),"pageContext"=>implode(",",$result_app['body']['contextData']));
                }else{
                    $result['desc'] = "曝光上报失败";
                }
            }else{
                $result['desc'] = "未能获取到腾讯数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //获取榜单
    public function rank_app_list($rank_id,$page,$pageContext){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_rank = $this->DAO->get_rank_app_list($rank_id,$page,$pageContext);
        if ($result_rank['head']['ret']===0 && $result_rank['body']['ret']===0){
            if ($result_rank['body']['appList']){
                //曝光上报
                $report = array();
                $rank_app_list = array();
                foreach ($result_rank['body']['appList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['packageName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getRankAppADList","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($rank_app_list,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>$value['shortDesc'],"version"=>$value['versionName'], "down_url"=>$value['apkUrl'],
                        "apk_name"=>$value['packageName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['appDownCount']), "tag"=>$value['categoryName'],"isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                if ($res['head']['ret']===0 && $res['body']['ret']===0){
                    $result = array("result" => "1", "desc" => "查询成功", "data" => $rank_app_list,"count"=>"","hasNext"=>((string)$result_rank['body']['hasNext']),"pageContext"=>implode(",",$result_rank['body']['pageContext']));
                }else{
                    $result['desc'] = "曝光上报失败";
                }
            }else{
                $result['desc'] = "未能获取到腾讯数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //获取游戏详情
    public function app_detail($apk_name){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_app_detail = $this->DAO->get_app_detail($apk_name);
        if ($result_app_detail['head']['ret']===0 && $result_app_detail['body']['ret']===0){
            if ($result_app_detail['body']['mpRes']){
                //点击详情上报
                $app_res = $result_app_detail['body']['mpRes'][$apk_name];
                $apk_size = sprintf("%.1f", ((int)$app_res['fileSize'])/1024/1024);
                $report_data = array("appId"=>$app_res['appId'],"apkId"=>$app_res['apkId'],"packageName"=>$app_res['pkgName'],
                    "versionCode"=>$app_res['versionCode'],"interfaceName"=>"getAppDetailBatchNew","recommendId"=>$app_res['recommendId'],"source"=>$app_res['source'],
                    "channelId"=>$app_res['channelId'],"dataAnalysisId"=>$app_res['dataAnalysisId']);
                $app_detail = array(
                    "id"=>$app_res['appId'],"game_name"=>$app_res['appName'],"game_icon"=>$app_res['iconUrl'],"game_banner"=>"",
                    "game_desc"=>$app_res['description'], "game_title"=>"","version"=>$app_res['versionName'],"down_url"=>$app_res['apkUrl'],
                    "down_num"=>((string)$app_res['appDownCount']),"apk_size"=>$apk_size."M", "apk_name"=>$app_res['pkgName'],"channel"=>$app_res['channelId'],
                    "language"=>"0","system"=>"","img1"=>$app_res['screenshots'][0]['size550Url'],"img2"=>$app_res['screenshots'][1]['size550Url'],
                    "img3"=>$app_res['screenshots'][2]['size550Url'],"img4"=>$app_res['screenshots'][3]['size550Url'],"is_gift"=>"0","tag"=>$app_res['categoryInfo']['categoryName'],
                    "isTX"=>"1","reportData"=>$report_data);
                $result = array("result" => "1", "desc" => "查询成功", "data" => $app_detail);
            }else{
                $result['desc'] = "未能获取到腾讯游戏详情数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //获取游戏礼包
    public function app_gift($apkname){
        $result = array("result" => "0", "desc" => "腾讯游戏暂无礼包");
        die("0".base64_encode(json_encode($result)));
    }

    //获取分类
    public function category_list(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_category_list = $this->DAO->get_category_list();
        if ($result_category_list['head']['ret']===0 && $result_category_list['body']['ret']===0){
            if ($result_category_list['body']['categoryList']){
                $category_list = array();
                foreach ($result_category_list['body']['categoryList'] as $value){
                    array_push($category_list,array("categoryId"=>$value['categoryId'],"categoryName"=>$value['categoryName'],"description"=>$value['description'],
                        "iconUrl"=>$value['iconUrl'],"tagList"=>$value['tagList'],"isTX"=>"1"));
                }
                $result = array("result" => "1", "desc" => "查询成功", "data" => $category_list);
            }else{
                $result['desc'] = "未能获取到腾讯游戏分类";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //通过聚合id获取应用列表
    public function app_list_by_tagid($tag_id,$page,$pageContext){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_app = $this->DAO->get_app_list_by_tagid($tag_id,$page,$pageContext);
        if ($result_app['head']['ret']===0 && $result_app['body']['ret']===0){
            if ($result_app['body']['appADList']){
                //曝光上报
                $report = array();
                $app_list = array();
                foreach ($result_app['body']['appADList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['pkgName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getAppList","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($app_list,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>"","version"=>$value['versionName'], "down_url"=>$value['apkUrl'],
                        "apk_name"=>$value['packageName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['appDownCount']), "tag"=>$value['categoryName'],"isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                if ($res['head']['ret']===0 && $res['body']['ret']===0){
                    $result = array("result" => "1", "desc" => "查询成功", "data" => $app_list,"count"=>"","hasNext"=>((string)$result_app['body']['hasNext']),"pageContext"=>implode(",",$result_app['body']['contextData']));
                }else{
                    $result['desc'] = "曝光上报失败";
                }
            }else{
                $result['desc'] = "未能获取到腾讯数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //搜索
    public function search_list(){
        $result = array('result'=>0,'desc'=>'网络请求出错');
        $keyword = $_GET['keyword'];
        $page = $_GET['page'];
        $pageContext = $_GET['pageContext'];
        if($keyword) {
            $search_list = $this->DAO->get_search_list($keyword, $page);
            foreach ($search_list as $key=>$value){
                $search_list[$key]['tag'] = "";
                $search_list[$key]['isTX'] = "0";
            }
            $count = $this->DAO->get_search_count($keyword);
            if ((PERPAGE*($page-1)+count($search_list))>= (int)($count['count'])){
                if ($count['count']==0){
                    $count_page = 0;
                }else{
                    $count_page = ceil(((int)$count['count'])/PERPAGE)-1;
                }
                $result_search_qq = $this->DAO->get_search_qq_list($keyword,((int)$page-$count_page),$pageContext);
                if ($result_search_qq['head']['ret']===0 && $result_search_qq['body']['ret']===0){
                    if ($result_search_qq['body']['appList']){
                        //曝光上报
                        $report = array();
                        $search_app_list = array();
                        foreach ($result_search_qq['body']['appList'] as $value){
                            $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                            $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['packageName'],
                                "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"searchADApp","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                                "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                            array_push($search_app_list,array("id"=>$value['appId'],"game_icon"=>$value['iconUrl'],"down_url"=>$value['apkUrl'],
                                "game_name"=>$value['appName'],"is_gift"=>0,"apk_name"=>$value['packageName'],"game_desc"=>$value['shortDesc'], "down_num"=>((string)$value['appDownCount']),
                                "version"=>$value['versionName'],"apk_size"=>$apk_size."M","game_title"=>"","isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                            array_push($report,$report_data);
                        }
                        //发送曝光上报
                        $res = $this->DAO->update_report_exposure($report);
                        $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                        if (((int)$page-$count_page)==1){
                            $search_list = array_merge($search_list,$search_app_list);
                        }else{
                            $search_list = $search_app_list;
                        }
                        $has_next = ((string)$result_search_qq['body']['hasNext']);
                        $result['pageContext'] = implode(",",$result_search_qq['body']['contextData']);
                    }else{
                        $has_next = "0";
                    }
                }else{
                    $has_next = "0";
                }
            }else{
                $has_next = "1";
                $result['pageContext'] = "";
            }
            $result['desc'] = "查询成功";
            $result['count'] = $count['count'];
            $result['data'] = $search_list;
            $result['hasNext']=$has_next;
        }else{
            $result['desc'] = "查询成功";
            $result['count'] = 0;
            $result['data'] = array();
            $result['hasNext']="0";
        }
        $result['result'] = 1;
        die("0".base64_encode(json_encode($result)));
    }

    //新游，单机列表
    public function fine_list($type,$page){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $fine_game = $this->DAO->get_fine_game_list($type,$page);
        if($fine_game){
            foreach ($fine_game as $key=>$value){
                $fine_game[$key]['tag'] = "";
                $fine_game[$key]['isTX'] = "0";
            }
            $count = $this->DAO->get_game_count($type);
            if ((PERPAGE*($page-1)+count($fine_game))== (int)($count['num'])){
                $has_next = "0";
            }else{
                $has_next = "1";
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $fine_game,"count"=>$count['num'],"hasNext"=>$has_next);
        }else{
            $result['desc'] = "未能获取更多66游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //首页牛果游戏推荐位
    public function re_niuguo_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $re_niuguo_game = $this->DAO->get_niuguo_game();
        if($re_niuguo_game){
            foreach ($re_niuguo_game as $key=>$value){
                $re_niuguo_game[$key]['tag'] = "";
                $re_niuguo_game[$key]['isTX'] = "0";
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $re_niuguo_game);
        }else{
            $result['desc'] = "未能获取到牛果游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //点击上报
    public function report_click($data,$type){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if ($data){
            if ($type == 1){
                $data_arr = array(json_decode(stripslashes(html_entity_decode($data)),JSON_UNESCAPED_UNICODE));
                foreach ($data_arr as $key=>$value){
                    $data_arr[$key]['appId'] = (int)$data_arr[$key]['appId'];
                    $data_arr[$key]['apkId'] = (int)$data_arr[$key]['apkId'];
                    $data_arr[$key]['versionCode'] = (int)$data_arr[$key]['versionCode'];
                    $data_arr[$key]['clickType'] = 200;
                }
                $res = $this->DAO->update_report_click($data_arr);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],2,$res['body']['ret'],$res['body']['bodyContent']);
                if (($res['head']['ret']===0 && $res['body']['ret']===0)){
                    $result = array("result" => "1", "desc" => "上报成功");
                }else{
                    $result['desc'] = "上报失败";
                }
            }elseif($type == 2){
                $data_arr = array(json_decode(stripslashes(html_entity_decode($data)),JSON_UNESCAPED_UNICODE));
                foreach ($data_arr as $key=>$value){
                    $data_arr[$key]['appId'] = (int)$data_arr[$key]['appId'];
                    $data_arr[$key]['apkId'] = (int)$data_arr[$key]['apkId'];
                    $data_arr[$key]['versionCode'] = (int)$data_arr[$key]['versionCode'];
                    $data_arr[$key]['clickType'] = 900;
                }
                $res = $this->DAO->update_report_click($data_arr);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],2,$res['body']['ret'],$res['body']['bodyContent']);
                if (($res['head']['ret']===0 && $res['body']['ret']===0)){
                    $result = array("result" => "1", "desc" => "上报成功");
                }else{
                    $result['desc'] = "上报失败";
                }
            }else{
                $result['desc'] = "参数获取异常";
            }
        }else{
            $result['desc'] = "点击上报数据为空";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //下载上报
    public function report_download($data){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if ($data){
            $data_arr = array(json_decode(stripslashes(html_entity_decode($data)),JSON_UNESCAPED_UNICODE));
            foreach ($data_arr as $key=>$value){
                $data_arr[$key]['appId'] = (int)$data_arr[$key]['appId'];
                $data_arr[$key]['apkId'] = (int)$data_arr[$key]['apkId'];
                $data_arr[$key]['versionCode'] = (int)$data_arr[$key]['versionCode'];
            }
            $res = $this->DAO->update_report_download($data_arr);
            $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],3,$res['body']['ret'],$res['body']['bodyContent']);
            if (($res['head']['ret']===0 && $res['body']['ret']===0)){
                $result = array("result" => "1", "desc" => "上报成功");
            }else{
                $result['desc'] = "上报失败";
            }
        }else{
            $result['desc'] = "下载上报数据为空";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //安装上报
    public function report_install($data){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if ($data){
            $data_arr = array(json_decode(stripslashes(html_entity_decode($data)),JSON_UNESCAPED_UNICODE));
            foreach ($data_arr as $key=>$value){
                $data_arr[$key]['appId'] = (int)$data_arr[$key]['appId'];
                $data_arr[$key]['apkId'] = (int)$data_arr[$key]['apkId'];
                $data_arr[$key]['versionCode'] = (int)$data_arr[$key]['versionCode'];
            }
            $res = $this->DAO->update_report_install($data_arr);
            $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],4,$res['body']['ret'],$res['body']['bodyContent']);
            if (($res['head']['ret']===0 && $res['body']['ret']===0)){
                $result = array("result" => "1", "desc" => "上报成功");
            }else{
                $result['desc'] = "上报失败";
            }
        }else{
            $result['desc'] = "安装上报数据为空";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //搜索联想词
    public function search_suggest($keyword){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_search_suggest = $this->DAO->get_search_suggest($keyword);
        if ($result_search_suggest['head']['ret']===0 && $result_search_suggest['body']['ret']===0){
            $app_list = array();
            $keywords_list = array();
            if ($result_search_suggest['body']['appList']){
                //联想的应用
                //曝光上报
                $report = array();
                foreach ($result_search_suggest['body']['appList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['packageName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getSearchSuggestNew","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($app_list,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>"","version"=>$value['versionName'], "down_url"=>$value['apkUrl'],
                        "apk_name"=>$value['packageName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['appDownCount']), "tag"=>"","isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
            }
            if ($result_search_suggest['body']['keywords']){
                //联想的关键词
                foreach ($result_search_suggest['body']['keywords'] as $val){
                    array_push($keywords_list,array("game_name"=>$val));
                }
            }
            $data = array("applist"=>$app_list,"keywordslist"=>$keywords_list);
            $result = array("result" => "1", "desc" => "查询成功", "data" => $data);
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //新分类
    public function category_list_new(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_category_list = $this->DAO->get_category_list_new();
        if ($result_category_list['head']['ret']===0 && $result_category_list['body']['ret']===0){
            if ($result_category_list['body']['categoryList']){
                $result_category_list_se = $result_category_list['body']['categoryList'];
                $category_list = array();
                foreach ($result_category_list['body']['categoryList'] as $value){
                    //确定一级分类
                    if ($value['parentId']===$value['reqType']){
                        $category_list_se = array();
                        foreach ($result_category_list_se as $val){
                            if ($val['parentId']===$value['categoryId']){
                                //二级分类
                                array_push($category_list_se,array("categoryId"=>$val['categoryId'],"categoryName"=>$val['categoryName'],"parentId"=>$val['parentId']));
                            }
                        }
                        array_push($category_list,array("categoryId"=>$value['categoryId'],"categoryName"=>$value['categoryName'],"iconUrl"=>$value['iconUrl'],"parentId"=>$value['parentId'],"tagList"=>$category_list_se,"isTX"=>"1"));
                    }
                }
                $result = array("result" => "1", "desc" => "查询成功", "data" => $category_list);
            }else{
                $result['desc'] = "未能获取腾讯分类数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    //通过分类获取APP
    public function app_list_by_category($category_id,$parent_id,$page,$pageContext){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $result_app_list = $this->DAO->get_app_list_by_category($category_id,$parent_id,$page,$pageContext);
        if ($result_app_list['head']['ret']===0 && $result_app_list['body']['ret']===0){
            if ($result_app_list['body']['appList']){
                //曝光上报
                $report = array();
                $app_list = array();
                foreach ($result_app_list['body']['appList'] as $value){
                    if (!$value['dataAnalysisId']) $value['dataAnalysisId']="";
                    $report_data = array("appId"=>(int)$value['appId'],"apkId"=>(int)$value['apkId'],"packageName"=>$value['pkgName'],
                        "versionCode"=>(int)$value['versionCode'],"interfaceName"=>"getCategoryAppList","recommendId"=>$value['recommendId'],"source"=>$value['source'],
                        "channelId"=>$value['channelId'],"dataAnalysisId"=>$value['dataAnalysisId']);
                    $apk_size = sprintf("%.1f", ((int)$value['fileSize'])/1024/1024);
                    array_push($app_list,array("id"=>$value['appId'],"game_name"=>$value['appName'],"game_icon"=>$value['iconUrl'],
                        "game_banner"=>"","is_gift"=>0,"game_title"=>"","version"=>$value['versionName'], "down_url"=>$value['apkUrl'],
                        "apk_name"=>$value['pkgName'],"apk_size"=>$apk_size."M","down_num"=>((string)$value['totalDownloadTimes']), "tag"=>$value['categoryName'],"isTX"=>"1","reportData"=>json_encode($report_data,JSON_UNESCAPED_UNICODE)));
                    array_push($report,$report_data);
                }
                //发送曝光上报
                $res = $this->DAO->update_report_exposure($report);
                $this->DAO->insert_report_log($res['body']['imei'],$res['body']['mac'],1,$res['body']['ret'],$res['body']['bodyContent']);
                if ($res['head']['ret']===0 && $res['body']['ret']===0){
                    $result = array("result" => "1", "desc" => "查询成功", "data" => $app_list,"count"=>"","hasNext"=>((string)$result_app_list['body']['hasNext']),"pageContext"=>implode(",",$result_app_list['body']['contextData']));
                }else{
                    $result['desc'] = "曝光上报失败";
                }
            }else{
                $result['desc'] = "未能获取腾讯应用数据";
            }
        }else{
            $result['desc'] = "获取腾讯数据出错";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function chosen_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $chosen_game = $this->DAO->get_chosen_game();
        if($chosen_game){
            foreach($chosen_game as $key=>$data){
                if($data['down_num'] > 100000000){
                    $num = $data['down_num']/100000000;
                    $chosen_game[$key]['down_num'] = round($num,2).'亿';
                }elseif($data['down_num'] > 10000){
                    $num = $data['down_num']/10000;
                    $chosen_game[$key]['down_num'] = round($num,2).'万';
                }
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $chosen_game);
        }else{
            $result['desc'] = "未能获取到小编精选数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function chosen_list($type,$page){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $chosen_game = $this->DAO->get_chosen_game_list($type,$page);
        if($chosen_game){
            foreach($chosen_game as $key=>$value){
                $chosen_game[$key]['tag'] = "";
                $chosen_game[$key]['isTX'] = "0";
                if($value['down_num'] > 100000000){
                    $num = $value['down_num']/100000000;
                    $chosen_game[$key]['down_num'] = round($num,2).'亿';
                }elseif($value['down_num'] > 10000){
                    $num = $value['down_num']/10000;
                    $chosen_game[$key]['down_num'] = round($num,2).'万';
                }
            }
            $count = $this->DAO->get_chosen_game_count($type);
            if((PERPAGE*($page-1)+count($chosen_game))== (int)($count['num'])){
                $has_next = "0";
            }else{
                $has_next = "1";
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $chosen_game,"count"=>$count['num'],"hasNext"=>$has_next);
        }else{
            $result['desc'] = "未能获取更多小编精选数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

}
?>