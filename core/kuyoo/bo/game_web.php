<?php
COMMON('baseCore', 'paramUtils', 'pageCore', 'phpqrcode');
BO('baseKuyoo');
DAO('game_dao','common_dao');

class game_web extends baseKuyoo {

    public $DAO;
    public $COMDAO;
    public $gid;

    public function __construct($game_id) {
        parent::__construct();
        $this->gid = $game_id;
        // if($this->is_mobile_client()){
        //     header("Location:http://m.66173.cn/game".$game_id);
        // }
        $this->DAO = new game_dao();
    }

    /**
     * 指定游戏的所有可下载渠道客户端
     * 访问：http://shouyou.kuyoo.com/game77/down
     */
    public function game_ch_download(){
        $downs = $this->DAO->get_game_ch_download($this->gid);
        $game_ch_downs = array();
        foreach ($downs as $key => $val) {
            switch ($val['platform']) {
                case '1': $game_ch_downs['android'][] = $downs[$key]; break;
                case '2': $game_ch_downs['ios'][]     = $downs[$key]; break;
                default : break;
            }
        }
        $data['game_ch_downs'] = $game_ch_downs;
        $data['gameinfo'] = $this->DAO->get_game($this->gid);
        $this->assign('web_title',$data['gameinfo']['game_name'].'下载_'.$data['gameinfo']['game_name']);
        $this->assign('web_key',$data['gameinfo']['game_name'].','.$data['gameinfo']['game_name'].'免费下载');
        $this->assign('web_des','酷游(shouyou.kuyoo.com)是国内权威的游戏交易平台，安全快捷有保障的手游充值中心，酷游提供最新最安全的'.$data['gameinfo']['game_name'].'安卓下载，'.$data['gameinfo']['game_name'].'iOS下载。');
        $this->assign('data',$data);
        $this->display('game_download.html');
    }

    public function get_game_qrcode($down_id = null){
        $down_info = $this->DAO->get_game_down_info($down_id);
        QRcode::png(htmlspecialchars_decode($down_info[0]['url']),false, 'L', 5);
    }

    public function get_qrcode($url=''){
        QRcode::png(htmlspecialchars_decode($url),false, 'H', 5);
    }   
}