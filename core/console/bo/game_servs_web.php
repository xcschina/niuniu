<?php
COMMON('baseCore', 'uploadHelper');
DAO('game_servs_dao', 'common_dao');

class game_servs_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO    = new game_servs_dao();
        $this->COMMON = new common_dao();
    }

    public function get_game_servs_list(){
        $params    = $_POST;
        $game_list = $this->COMMON->get_game_list();
        $dataList  = $this->DAO->get_game_servs_list($params, $this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list", $game_list);
        $this->assign("dataList", $dataList);
        $this->assign("params", $params);
        $this->display("game_servs_list.html");
    }

    public function serv_add_view(){
        $game_list = $this->COMMON->get_game_list();
        $channels  = $this->DAO->get_game_channels();
        $this->assign("game_list", $game_list);
        $this->assign("channels", $channels);
        $this->display("game_servs_add.html");
    }
    public function add_game_serv(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$params['serv_name']){
            die(json_encode($this->error_msg("请输入区服名")));
        }
        if(!$params['serv_id']){
            die(json_encode($this->error_msg("请输入区服ID")));
        }
        if(!is_numeric($params['serv_id'])){
            die(json_encode($this->error_msg("区服ID错误")));
        }
        $info = $this->DAO->get_serv_info($params);
        if($info){
            die(json_encode($this->error_msg("该区服已设置")));
        }
        $this->DAO->add_serv($params);
        $serv_id = $this->DAO->LAST_INSERT_ID;
        if(is_numeric($params['ch_id'])){
            $this->DAO->update_ch_servs($serv_id, $params['ch_id']);
        }
        echo json_encode($this->succeed_msg("区服添加成功", "game_servs_list"));
    }

    public function serv_edit_view($id){
        $serv_info = $this->DAO->get_serv_info_byid($id);
        $game_list = $this->COMMON->get_game_list();
        $this->assign("serv_info", $serv_info);
        $this->assign("game_list", $game_list);
        $this->display("game_servs_edit.html");
    }

    public function do_serv_edit(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$params['serv_name']){
            die(json_encode($this->error_msg("请输入区服名")));
        }
        if(!$params['serv_id']){
            die(json_encode($this->error_msg("请输入区服ID")));
        }
        if(!is_numeric($params['serv_id'])){
            die(json_encode($this->error_msg("区服ID错误")));
        }
        $info = $this->DAO->get_serv_info($params);
        if($info && $info['id'] != $params['id']){
            die(json_encode($this->error_msg("该区服已设置")));
        }
        $this->DAO->update_serv($params);
        echo json_encode($this->succeed_msg("区服编辑成功", "game_servs_list"));
    }

    public function import_view(){
        $game_list = $this->COMMON->get_game_list();
        $this->assign("game_list", $game_list);
        $this->display("game_servs_import.html");

    }

    public function do_import(){
        header("Content-Type:text/html;charset=utf-8");
        $game_id = $_POST['game_id'];
        if(!$game_id){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        $fileType = $_FILES['servs']['type'];
        if($fileType != 'application/vnd ms-excel' &&
            $fileType != 'application/kset' &&
            $fileType != 'application/vnd.ms-excel' &&
            $fileType != 'application/octet-stream' &&
            $fileType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            echo json_encode($this->error_msg("请选择xls,xlsx文件"));
            exit;
        }
        $file_name = $_FILES['servs']["tmp_name"];
        $ext_name  = $_FILES['servs']["name"];
        $ext_name  = explode(".", $ext_name);
        $ext_name  = $ext_name[1];

        if($ext_name == 'xlsx'){
            $PHPReader = new PHPExcel_Reader_Excel2007();
        } else{
            $PHPReader = new PHPExcel_Reader_Excel5();
        }

        $PHPExcel     = $PHPReader->load($file_name);
        $currentSheet = $PHPExcel->getSheet(0);
        $allRow       = $currentSheet->getHighestRow();
        for($currentRow = 2; $currentRow <= $allRow; $currentRow++){
            $serv_id   = $currentSheet->getCellByColumnAndRow("0", $currentRow)->getValue();
            $serv_name = $currentSheet->getCellByColumnAndRow("1", $currentRow)->getValue();
            if(strlen(trim($serv_id)) >= 1 && strlen(trim($serv_name)) >= 1){
                $params = array('game_id' => $game_id, 'serv_id' => $serv_id, 'serv_name' => $serv_name);
                $info   = $this->DAO->get_serv_info($params);
                if(!$info){
                    $this->DAO->add_serv($params);
                }
            }
        }
        unlink($file_name);
        echo json_encode($this->succeed_msg("区服导入成功！", "game_servs_list"));
    }

    public function ch_servs($game_id){
        $servs = $this->DAO->get_game_ch_servs($game_id);
        $chs   = $this->DAO->get_game_channels($game_id);

        $str = '';
        foreach($servs as $k => $s){
            $str .= '<form action="ch_servs.php?act=save&id=' . $game_id . '" method="post" data-toggle="validate" data-alertmsg="false">';
            $str .= '<tr align="center">';
            $str .= '<td>' . $s['serv_name'] . '</td>';
            foreach($chs as $kk => $ch){
                //$str.='<td><input type="checkbox" name="ch[]" value="ch_'.$ch['id'].'"';
                $str .= '<td>&nbsp;';
                if($s['ch_' . $ch['id']] == 1){
                    $str .= ' <span class="text-success">&#10004;</span>';
                } else{
                    $str .= ' <span class="text-muted">&#215;</span>';
                }
                $str .= '</td>';
            }
            //$str.='<td><p><input type="button" value="全选" class="btn btn-default all" /></p><button type="submit" class="btn btn-default">保存</button></td></tr></form>';
            $str .= '</tr></form>';
        }

        $this->assign("servs", $str);
        $this->assign("game_name", $servs[0]['game_name']);
        $this->assign("chs", $chs);
        $this->assign("game_id", $game_id);
        $this->display("ch_servs.html");
    }

    public function ch_serv_edit($game_id, $ch_id){
        $servs = $this->DAO->get_game_ch_servs($game_id);
        $ch    = $this->DAO->get_channel($ch_id);

        $str = '<tr>';
        if(!$servs){
            $str .= "<td>该游戏还没有配置区服，请到【游戏管理】->【游戏区服】配置";
        }
        foreach($servs as $k => $s){
            $str .= '<td>' . $s['serv_name'];
            $str .= '<input type="checkbox" name="serv[]" value="' . $s['id'] . '"';
            if($s['ch_' . $ch['id']] == 1){
                $str .= ' checked';
            }
            $str .= ' /></td>';
            if(($k + 1) % 5 == 0){
                $str .= "<td><input name='choose' type=\"button\" value=\"全选\" rel=\"0\" /></td></tr><tr>";
            }
        }
        $str .= '</tr>';
        $this->assign("ch", $ch);
        $this->assign("servs", $str);
        $this->assign("game_id", $game_id);
        $this->assign("game_name", $servs[0]['game_name']);
        $this->display("ch_servs_edit.html");
    }

    public function ch_serv_save($game_id, $ch_id){

        $this->DAO->clear_game_ch($game_id, $ch_id);

        if(!isset($_POST['serv'])){
            die(json_encode($this->succeed_msg("执行完毕", "ch_servs")));
        }

        foreach((array)$_POST['serv'] as $serv){
            $this->DAO->update_ch_servs($serv, $ch_id);
        }
        echo json_encode($this->succeed_msg("区服设置成功", "ch_servs"));
    }

}