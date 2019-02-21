<?php
COMMON("ApplicationQQ");
class ApplicationQQ_api{
    private $ApplicationObj;

    public function __construct(){
        $this->ApplicationObj = new ApplicationQQ();
    }

    //获取应用列表   getAppList08
    public function getAppList($tag,$page='[]'){
        //分页对08无效
        $business_req_body = '{
            "listType":8,
            "identification":"'.$tag.'",
            "contextData":'.$page.',
            "pageSize":0,
            "categoryId":0,
            "tagId":0,
            "columnId":0
        }';
        $res = $this->ApplicationObj->sendApi("getAppList",$business_req_body,1);
        return $res;
    }

    //搜索接口
    public function searchApp($keyword,$pagesize,$page='[]'){
        $business_req_body = '{
            "keyword":"'.$keyword.'",
            "contextData":'.$page.',
            "pageSize":'.$pagesize.',
            "searchSrc":3
        }';
        $res = $this->ApplicationObj->sendApi("searchApp",$business_req_body,1);
        return $res;
    }

    //商业化搜索
    public function searchADApp($keyword,$pagesize,$page='[]'){
        $business_req_body = '{
            "keyword":"'.$keyword.'",
            "contextData":'.$page.',
            "pageSize":'.$pagesize.'
        }';
        $res = $this->ApplicationObj->sendApi("searchADApp",$business_req_body,1);
        return $res;
    }

    //获取个性化推荐列表
    public function getRecommendList($pagesize,$page='[]'){
        $business_req_body = '{
            "pageContext":'.$page.',
            "pageSize":'.$pagesize.',
            "sceneId":3
        }';
        $res = $this->ApplicationObj->sendApi("getRecommendList",$business_req_body,1);
        return $res;
    }

    //获取分类
    public function getCategoryDetailList(){
        $business_req_body = '{
            "reqType":-2
        }';
        $res = $this->ApplicationObj->sendApi("getCategoryDetailList",$business_req_body,1);
        return $res;
    }

    //获取榜单
    public function getRankAppADList($sceneId,$pagesize,$page='[]'){
        $business_req_body = '{
            "categoryId":-2,
            "pageSize":'.$pagesize.',
            "sceneId":'.$sceneId.',
            "pageContext":'.$page.',
            "page":0
        }';
        $res = $this->ApplicationObj->sendApi("getRankAppADList",$business_req_body,1);
        return $res;
    }

    //获取详情
    public function getAppDetailBatchNew($pkgNameList){
        $business_req_body = '{
            "pkgNameList":'.$pkgNameList.'
        }';
        $res = $this->ApplicationObj->sendApi("getAppDetailBatchNew",$business_req_body,1);
        return $res;
    }

    //获取06应用列表
    public function getAppList_06($tagid,$pagesize,$page='[]'){
        $business_req_body = '{
            "listType":6,
            "identification":"0",
            "contextData":'.$page.',
            "pageSize":'.$pagesize.',
            "categoryId":0,
            "tagId":'.$tagid.',
            "columnId":0
        }';
        $res = $this->ApplicationObj->sendApi("getAppList",$business_req_body,1);
        return $res;
    }

    //获取15应用列表
    public function getAppList_15($page='[]'){
        $business_req_body = '{
            "listType":15,
            "identification":"0",
            "contextData":'.$page.',
            "pageSize":0,
            "categoryId":0,
            "tagId":0,
            "columnId":0
        }';
        $res = $this->ApplicationObj->sendApi("getAppList",$business_req_body,1);
        return $res;
    }

    //获取17应用列表
    public function getAppList_17($page='[]'){
        $business_req_body = '{
            "listType":17,
            "identification":"0",
            "contextData":'.$page.',
            "pageSize":0,
            "categoryId":0,
            "tagId":0,
            "columnId":0
        }';
        $res = $this->ApplicationObj->sendApi("getAppList",$business_req_body,1);
        return $res;
    }

    //获取搜索联想词
    public function getSearchSuggestNew($keyword){
        $business_req_body = '{
            "keyword":"'.$keyword.'",
            "type":2
        }';
        $res = $this->ApplicationObj->sendApi("getSearchSuggestNew",$business_req_body,1);
        return $res;
    }

    //获取分类列表（新）
    public function getNewCategoryList(){
        $business_req_body = '{
            "reqType":-2
        }';
        $res = $this->ApplicationObj->sendApi("getNewCategoryList",$business_req_body,1);
        return $res;
    }

    //获取分类对应应用列表
    public function getCategoryAppList($categoryId,$parentId,$pagesize,$page='[]'){
        $business_req_body = '{
            "categoryId":'.$categoryId.',
            "contextData":'.$page.',
            "pageSize":'.$pagesize.',
            "reqType":-2,
            "parentId":'.$parentId.'
        }';
        $res = $this->ApplicationObj->sendApi("getCategoryAppList",$business_req_body,1);
        return $res;
    }

    //曝光上报
    public function reportExposure($report_data){
        //获取公用头部信息
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'imei') {
                    $imei = $param[1];
                }
                if ($param[0] == 'mac') {
                    $mac = $param[1];
                }
            }
        }
        if (!$imei) $imei = "";
        if (!$mac) $mac = "";
        foreach ($report_data as $key=>$value){
            $report_data[$key]['operateTime'] = time();
            $report_data[$key]['imei'] = $imei;
            $report_data[$key]['imsi'] = "0";
            $report_data[$key]['macAddr'] = $mac;
            $report_data[$key]['wifiSsid'] = "";
            $report_data[$key]['wifiBssid'] = "";
            $report_data[$key]['routeId'] = "0";
            $report_data[$key]['hostVersionCode'] = 3;
        }
        $report = json_encode($report_data,JSON_UNESCAPED_UNICODE);
        $business_req_body = '{
            "appList":'.$report.'
        }';
        $res = $this->ApplicationObj->sendApi("reportExposure",$business_req_body,1);
        $res['body']['imei'] = $imei;
        $res['body']['mac'] = $mac;
        $res['body']['bodyContent'] = $business_req_body;
        return $res;
    }

    //点击上报
    public function reportClick($report_data){
        //获取公用头部信息
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'imei') {
                    $imei = $param[1];
                }
                if ($param[0] == 'mac') {
                    $mac = $param[1];
                }
            }
        }
        if (!$imei) $imei = "";
        if (!$mac) $mac = "";
        foreach ($report_data as $key=>$value){
            $report_data[$key]['operateTime'] = time();
            $report_data[$key]['imei'] = $imei;
            $report_data[$key]['imsi'] = "0";
            $report_data[$key]['macAddr'] = $mac;
            $report_data[$key]['wifiSsid'] = "";
            $report_data[$key]['wifiBssid'] = "";
            $report_data[$key]['routeId'] = "0";
            $report_data[$key]['hostVersionCode'] = 3;
        }
        $report = json_encode($report_data,JSON_UNESCAPED_UNICODE);
        $business_req_body = '{
            "appList":'.$report.'
        }';
        $res = $this->ApplicationObj->sendApi("reportClick",$business_req_body,1);
        $res['body']['imei'] = $imei;
        $res['body']['mac'] = $mac;
        $res['body']['bodyContent'] = $business_req_body;
        return $res;
    }

    //下载上报
    public function reportDownload($report_data){
        //获取公用头部信息
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'imei') {
                    $imei = $param[1];
                }
                if ($param[0] == 'mac') {
                    $mac = $param[1];
                }
            }
        }
        if (!$imei) $imei = "";
        if (!$mac) $mac = "";
        foreach ($report_data as $key=>$value){
            $report_data[$key]['operateTime'] = time();
            $report_data[$key]['imei'] = $imei;
            $report_data[$key]['imsi'] = "0";
            $report_data[$key]['macAddr'] = $mac;
            $report_data[$key]['wifiSsid'] = "";
            $report_data[$key]['wifiBssid'] = "";
            $report_data[$key]['routeId'] = "0";
            $report_data[$key]['hostVersionCode'] = 3;
        }
        $report = json_encode($report_data,JSON_UNESCAPED_UNICODE);
        $business_req_body = '{
            "appList":'.$report.'
        }';
        $res = $this->ApplicationObj->sendApi("reportDownload",$business_req_body,1);
        $res['body']['imei'] = $imei;
        $res['body']['mac'] = $mac;
        $res['body']['bodyContent'] = $business_req_body;
        return $res;
    }

    //安装上报
    public function reportInstall($report_data){
        //获取公用头部信息
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'imei') {
                    $imei = $param[1];
                }
                if ($param[0] == 'mac') {
                    $mac = $param[1];
                }
            }
        }
        if (!$imei) $imei = "";
        if (!$mac) $mac = "";
        foreach ($report_data as $key=>$value){
            $report_data[$key]['operateTime'] = time();
            $report_data[$key]['imei'] = $imei;
            $report_data[$key]['imsi'] = "0";
            $report_data[$key]['macAddr'] = $mac;
            $report_data[$key]['wifiSsid'] = "";
            $report_data[$key]['wifiBssid'] = "";
            $report_data[$key]['routeId'] = "0";
            $report_data[$key]['hostVersionCode'] = 3;
        }
        $report = json_encode($report_data,JSON_UNESCAPED_UNICODE);
        $business_req_body = '{
            "appList":'.$report.'
        }';
        $res = $this->ApplicationObj->sendApi("reportInstall",$business_req_body,1);
        $res['body']['imei'] = $imei;
        $res['body']['mac'] = $mac;
        $res['body']['bodyContent'] = $business_req_body;
        return $res;
    }
}