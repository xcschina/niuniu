<?php
COMMON('baseCore','uploadHelper');
DAO('app_dao','common_dao');

class app_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new app_dao();
        $this->common = new common_dao();
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
    public function banner_list(){
        $data = $this->DAO->banner_gain($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("data",$data);
        $this->display("banner_list.html");
    }

    public function del_banner($id){
        $this->DAO->del_banner($id);
        die(json_encode($this->succeed_msg("轮播图片删除成功","banner_list")));
    }
    public function add_banner(){
        $params=$_POST;
        $data = $this->DAO->relation_play();
        $this->assign("params",$params);
        $this->assign("data",$data);
        $this->display("add_banner_view.html");
    }
    public function banner_save(){
        $params = $_POST;
        if(!$params['file']){
            die(json_encode($this->error_msg("请选择图片")));
        }
        if(!$params['remark']){
            die(json_encode($this->error_msg("请填写备注信息")));
        }
        if(!$params['url']){
            die(json_encode($this->error_msg("请填写图片链接")));
        }
        if($params['type']==0 ){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }
        }
        if($_FILES['pic']['tmp_name']){
            $params['img']=$this->up_img('pic','images/banner_img');
        }
        $data =  $this->DAO->insert_banner($params);
        if(!empty($data)){
            die(json_encode($this->succeed_msg("保存成功！","banner_list")));
        }else{
            die(json_encode($this->error_msg("未能保存成功.")));
        }
    }
    public function play_list(){
        $params=$_POST;
        $game_list = $this->DAO->play_name();
        $data_list = $this->DAO->play_list($params,$this->pageCurrent);
        foreach($data_list as $key=>$data){
            if(empty($data['tags'])){
                $data_list[$key]['new_tags']="";
            }else{
                $tags_array=explode(',',$data['tags']);
                $new_array=array();
                foreach ($tags_array as $k=>$v){
                    if($v){
                        array_push($new_array,$this->game_type[$v]);
                    }
                    $data_list[$key]['new_tags']=implode(',',$new_array);
                }
            }
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("tags",$this->game_type);
        $this->assign("data",$game_list);
        $this->assign("data_list",$data_list);
        $this->display("play_list.html");
    }
    public function add_play(){
        $params=$_POST;
        if(!$params['play_name']){
            die(json_encode($this->error_msg("请输入游戏名")));
        }
        if(!$_FILES['play_icon']['tmp_name']){
            die(json_encode($this->error_msg("请上传图片ICON")));
        }else{
            $params['game_icon']=$this->up_img('play_icon','images/play/game_icon');
        }
        if($_FILES['play_banner']['tmp_name']){
            $params['banner_url']=$this->up_img('play_banner','images/play/banner_url');
        }
        if($params['is_top']==1){
            if(!$params['file']){
                die(json_encode($this->error_msg("请上传游戏图片")));
            }
        }
        if(preg_match("/[\x7f-\xff]/", $params['game_packname'])){
            die(json_encode($this->error_msg("游戏包名必须是英文")));
        }
        if(!$params['game_size']){
            die(json_encode($this->error_msg("请输入包体大小")));
        }
        if($params['is_rate']==1 ){
            if(!$params['rate']){
                die(json_encode($this->error_msg("请填写折扣")));
            }elseif($params['rate']<0 || $params['rate']>10){
                die(json_encode($this->error_msg("折扣值超出范围之内")));
            }
        }
        if(!$params['download']){
            die(json_encode($this->error_msg("请输入下载次数")));
        }
        if(!$params['score']){
            die(json_encode($this->error_msg("请输入游戏评分")));
        }elseif($params['score']<0 || $params['score']>5){
            die(json_encode($this->error_msg("评分值超出范围之内")));
        }
        $info=$this->DAO->get_play_info($params);
        if($info){
            if($info['game_name']==$params['play_name']){
                die(json_encode($this->error_msg("同一渠道游戏名已存在")));
            }
        }
       $this->DAO->add_play($params);
        die(json_encode($this->succeed_msg("游戏信息添加成功","play_list")));
    }
    public function play_add_view(){
        $params=$_POST;
        $data = $this->DAO->relation_play();
        $this->assign("tags",$this->game_type);
        $this->assign("params",$params);
        $this->assign("data",$data);
        $this->display("play_add_view.html");
    }
    public function play_edit_view($id){
        $params=$_POST;
        $play_info=$this->DAO->get_play($id);
        $arr = explode(',',$play_info['tags']);
        $data = $this->DAO->relation_play();
        $this->assign("data",$data);
        $this->assign("tags",$this->game_type);
        $this->assign("arr",$arr);
        $this->assign("params",$params);
        $this->assign("play_info",$play_info);

        $this->display("play_edit_view.html");
    }
    public function edit_play(){
        $params=$_POST;
        if(!$params['play_name']){
            die(json_encode($this->error_msg("请输入游戏名")));
        }
        $info=$this->DAO->get_play_info($params);
        if($info){
            if($info['game_name']==$params['play_name'] && $info['id']!=$params['id']){
                die(json_encode($this->error_msg("同一渠道游戏名已存在")));
            }
        }
        if(!$_FILES['play_icon']['tmp_name']){
            $params['game_icon']=$params['old_game_icon'];
        }else{
            $params['game_icon']=$this->up_img('play_icon','images/play/game_icon');
        }
        if($params['is_top']==1){
            if(!$_FILES['play_banner']['tmp_name']){
                $params['banner_url']=$params['old_banner_url'];
            }else{
                $params['banner_url']=$this->up_img('play_banner','images/play/banner_url');
            }
        }
        if(!$params['game_size']){
            die(json_encode($this->error_msg("请输入包体大小")));
        }
        if($params['is_rate']==1 ){
           if(!$params['rate']){
               die(json_encode($this->error_msg("请填写折扣")));
           }elseif($params['rate']<0 || $params['rate']>10){
               die(json_encode($this->error_msg("折扣值超出范围之内")));
           }
        }
        if(!$params['download']){
            die(json_encode($this->error_msg("请输入下载次数")));
        }
        if(!$params['score']){
            die(json_encode($this->error_msg("请输入游戏评分")));
        }elseif($params['score']<0 || $params['score']>5){
            die(json_encode($this->error_msg("评分值超出范围之内")));
        }
        if(preg_match("/[\x7f-\xff]/", $params['game_packname'])){
            die(json_encode($this->error_msg("游戏包名必须是英文")));
        }
        $this->DAO->update_play($params);
        die(json_encode($this->succeed_msg("游戏信息编辑成功","play_list")));
    }
    public function del_play($id){
        $info=$this->DAO->gain_info($id);
       if(!$info){
           die(json_encode($this->error_msg("未查询到游戏信息")));
       }else {
           $this->DAO->del_play($id);
           die(json_encode($this->succeed_del("游戏信息删除成功", "play_list")));
       }
    }





}