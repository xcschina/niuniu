<?php
COMMON('baseCore','uploadHelper');
DAO('market_dao','common_dao');

class market_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new market_dao();
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
        $data = $this->DAO->get_banner($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("data",$data);
        $this->display("market_banner_list.html");
    }

    public function del_banner($id){
        $this->DAO->del_banner($id);
        die(json_encode($this->succeed_del("轮播图片删除成功","market_banner_list")));
    }

    public function add_banner(){
        $params = $_POST;
        $game = $this->DAO->get_name_list();
        $theme = $this->DAO->get_theme_list();
        $rec_list = $this->DAO->get_rec_list();
        $this->assign("rec_list",$rec_list);
        $this->assign("theme_list",$theme);
        $this->assign("game",$game);
        $this->assign("params",$params);
        $this->display("market_add_banner.html");
    }

    public function banner_save(){
        $params = $_POST;
        if(!$params['file']){
            die(json_encode($this->error_msg("请选择图片")));
        }
        if(!$params['title']){
            die(json_encode($this->error_msg("请填写备注信息")));
        }
        if($params['type'] == 1){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 2){
            if(!$params['url']){
                die(json_encode($this->error_msg("请填写图片链接")));
            }else{
                $params['game_id'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
            $params['url'] = trim($params['url']);
            $array = get_headers($params['url'],1);
            if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
                die(json_encode($this->error_msg("请输入正确的图片链接")));
            }
            if( $_SERVER['HTTP_REFERER'] == "" ){
                header("Location:".$params['url']); exit;
            }
        }else if($params['type'] == 3){
            if(!$params['theme_id']){
                die(json_encode($this->error_msg("请选择关联主题")));
            }else{
                $params['url'] = '';
                $params['game_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 4){
            if(!$params['rec_id']){
                die(json_encode($this->error_msg("请选择关联推荐游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['game_id'] = $params['rec_id'];
            }
        }
        if($_FILES['pic']['tmp_name']){
            if (!$params['is_disc']){
                list($width,$height)=getimagesize($_FILES['pic']['tmp_name']);
                if($width == 720 && $height == 308){
                    $params['img']=$this->up_img('pic','images/banner_img');
                }else{
                    die(json_encode($this->error_msg("请上传720*308的图片")));
                }
            }else{
                $params['img']=$this->up_img('pic','images/banner_img');
            }
        }else{
            die(json_encode($this->error_msg("请选择上传图片")));
        }
        $data =  $this->DAO->insert_banner($params);
        if(!empty($data)){
            die(json_encode($this->succeed_msg("保存成功！","app_banner")));
        }else{
            die(json_encode($this->error_msg("未能保存成功.")));
        }
    }

    public function game_list(){
        $params = $_POST;
        $game_list = $this->DAO->game_name();
        $data_list = $this->DAO->game_list($params,$this->pageCurrent);
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
        $this->assign("game_list",$game_list);
        $this->assign("data_list",$data_list);
        $this->display("game_list.html");
    }

    public function add_game(){
        $params = $_POST;
        if(!$params['game_name']){
            die(json_encode($this->error_msg("请输入游戏名")));
        }
        if(!$_FILES['game_icon']['tmp_name']){
            die(json_encode($this->error_msg("请上传图片ICON")));
        }else{
            $params['game_icon'] = $this->up_img('game_icon','images/play/game_icon');
        }
        if($_FILES['game_banner']['tmp_name']){
            list($width,$height) = getimagesize($_FILES['game_banner']['tmp_name']);
            if($width == 308 && $height == 188){
                $params['game_banner'] = $this->up_img('game_banner','images/play/banner_url');
            }else{
                die(json_encode($this->error_msg("请上传308*188的游戏图片")));
            }
        }
        if($params['is_disc_new'] == 1 && $params['is_disc_rec'] == 0){
            die(json_encode($this->error_msg("是新发现游戏，就必须是推荐手游")));
        }
        if($params['is_disc_rec'] == 1){
            if(!$params['m_title']) {
                die(json_encode($this->error_msg("请输入游戏主标题")));
            }
            if(!$_FILES['disc_img']['tmp_name']){
                die(json_encode($this->error_msg("请上传图片横排图")));
            }else{
                $params['disc_img'] = $this->up_img('disc_img','images/play/game_icon');
            }
            if(!($_FILES['disc_rec_img']['tmp_name'][0] && $_FILES['disc_rec_img']['tmp_name'])){
                die(json_encode($this->error_msg("请上传游戏展示图片")));
            }else{
                $disc_rec_img = '';
                if($_FILES['disc_rec_img']['tmp_name'] && $_FILES['disc_rec_img']['tmp_name'][0]){
                    $rec_img = $this->batch_up_img('disc_rec_img', PRODUCT_IMG);
                    foreach($rec_img as $key=>$img){
                        if($img){
                            $disc_rec_img .= $img.",";
                        }
                    }
                    $params['disc_rec_img'] = trim($disc_rec_img,",");
                }
            }
            if(!$params['tags']){
                die(json_encode($this->error_msg("请选择游戏标签")));
            }elseif(count($params['tags'])>3){
                die(json_encode($this->error_msg("推荐手游不能超过三个标签")));
            }
            if(!$params['disc_img_text']){
                die(json_encode($this->error_msg("小编吐槽不能为空")));
            }
        }
        if(preg_match("/[\x7f-\xff]/", $params['apk_name'])){
            die(json_encode($this->error_msg("游戏包名必须是英文")));
        }
        if(!$params['apk_size']){
            die(json_encode($this->error_msg("请输入包体大小")));
        }
        if(!$params['down_num']){
            die(json_encode($this->error_msg("请输入下载次数")));
        }
        $info = $this->DAO->get_game_info($params);
        if($info && $info['game_name']==$params['game_name']){
            die(json_encode($this->error_msg("同一渠道游戏名已存在")));
        }
        //判断最多2个推荐位
        if ($params['app_recommend']){
            $recommend_no = $this->DAO->get_recommend_list();
            if ($recommend_no['recommend_no']>=2){
                die(json_encode($this->error_msg("首页最多只能2个推荐位")));
            }
        }
        $img_path = $this->up_imgs();
        $params['img1'] = $img_path['img1'];
        $params['img2'] = $img_path['img2'];
        $params['img3'] = $img_path['img3'];
        $params['img4'] = $img_path['img4'];
        $params['update_time'] = strtotime($params['update_time']);
        $this->DAO->add_game($params);
        die(json_encode($this->succeed_msg("游戏信息添加成功","market_game_list")));
    }

    protected function up_imgs(){
        $img_path['img1']='';
        $img_path['img2']='';
        $img_path['img3']='';
        $img_path['img4']='';
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $imgs = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path['img'.($key+1)]=$img;
                }
            }
        }
        return $img_path;
    }

    public function game_add_view(){
        $params = $_POST;
        $this->assign("tags",$this->game_type);
        $this->assign("params",$params);
        $this->display("game_add_view.html");
    }

    public function game_edit_view($id){
        $play_info = $this->DAO->get_game($id);
        $arr = explode(',',$play_info['tags']);
        $disc_rec_img = explode(',',$play_info['disc_rec_img']);
        $this->assign("tags",$this->game_type);
        $this->assign("arr",$arr);
        $this->assign("disc_rec_img",$disc_rec_img);
        $this->assign("play_info",$play_info);
        $this->display("game_edit_view.html");
    }

    public function edit_game(){
        $params = $_POST;
        if(!$params['game_name']){
            die(json_encode($this->error_msg("请输入游戏名")));
        }
        $info=$this->DAO->get_game_info($params);
        if($info){
            if($info['game_name']==$params['game_name'] && $info['id']!=$params['id']){
                die(json_encode($this->error_msg("同一渠道游戏名已存在")));
            }
        }
        if(!$_FILES['game_icon']['tmp_name']){
            $params['game_icon'] = $params['old_game_icon'];
        }else{
            $params['game_icon'] = $this->up_img('game_icon','images/play/game_icon');
        }
        if(!$_FILES['game_banner']['tmp_name']){
                $params['game_banner'] = $params['old_game_banner'];
            }else{
                list($width,$height) = getimagesize($_FILES['game_banner']['tmp_name']);
                if($width == 308 && $height == 188){
                    $params['game_banner']=$this->up_img('game_banner','images/play/banner_url');
                }else{
                    die(json_encode($this->error_msg("请上传308*188的游戏图片")));
                }
        }
        if($params['is_disc_new'] == 1 && $params['is_disc_rec'] == 0){
            die(json_encode($this->error_msg("是新发现游戏，就必须是推荐手游")));
        }
        if($params['is_disc_rec'] == 1){
            if(!$params['m_title']) {
                die(json_encode($this->error_msg("请输入游戏主标题")));
            }
            if(!$_FILES['disc_img']['tmp_name']){
                if(!$params['old_disc_img']){
                    die(json_encode($this->error_msg("请上传图片横排图")));
                }else{
                    $params['disc_img'] = $params['old_disc_img'];
                }
            }else{
                $params['disc_img'] = $this->up_img('disc_img','images/play/game_icon');
            }
            if(!($_FILES['disc_rec_img']['tmp_name'][0] && $_FILES['disc_rec_img']['tmp_name'])){
                if(!$params['old_disc_rec_img']){
                    die(json_encode($this->error_msg("请上传游戏展示图片")));
                }else{
                    $params['disc_rec_img'] = $params['old_disc_rec_img'];
                }
            }else{
                $disc_rec_img = '';
                if($_FILES['disc_rec_img']['tmp_name'] && $_FILES['disc_rec_img']['tmp_name'][0]){
                    $rec_img = $this->batch_up_img('disc_rec_img', PRODUCT_IMG);
                    foreach($rec_img as $key=>$img){
                        if($img){
                            $disc_rec_img .= $img.",";
                        }
                    }
                    $params['disc_rec_img'] = trim($disc_rec_img,",");
                }
            }
            if(!$params['tags']){
                die(json_encode($this->error_msg("请选择游戏标签")));
            }elseif(count($params['tags'])>3){
                die(json_encode($this->error_msg("推荐手游不能超过三个标签")));
            }
            if(!$params['disc_img_text']){
                die(json_encode($this->error_msg("小编吐槽不能为空")));
            }
        }
        if(!$_FILES['imgs']['tmp_name'][0]){
            $params['img1'] = $params['old_img1'];
            $params['img2'] = $params['old_img2'];
            $params['img3'] = $params['old_img3'];
            $params['img4'] = $params['old_img4'];
        }else{
            $img_path = $this->up_imgs();
            $params['img1'] = $img_path['img1'];
            $params['img2'] = $img_path['img2'];
            $params['img3'] = $img_path['img3'];
            $params['img4'] = $img_path['img4'];
        }
        if(!$params['apk_size']){
            die(json_encode($this->error_msg("请输入包体大小")));
        }
        if(!$params['down_num']){
            die(json_encode($this->error_msg("请输入下载次数")));
        }
        if(preg_match("/[\x7f-\xff]/", $params['apk_name'])){
            die(json_encode($this->error_msg("游戏包名必须是英文")));
        }
        //判断最多2个推荐位
        if ($params['app_recommend']){
            $recommend_no = $this->DAO->get_recommend_list($params['id']);
            if ($recommend_no['recommend_no']>=2){
                die(json_encode($this->error_msg("首页最多只能2个推荐位")));
            }
        }
        $params['update_time'] = strtotime($params['update_time']);
        $this->DAO->update_game($params);
        die(json_encode($this->succeed_msg("游戏信息编辑成功","market_game_list")));
    }

    public function del_game($id){
        $info = $this->DAO->get_info($id);
        if(!$info){
            die(json_encode($this->error_msg("未查询到游戏信息")));
        }else{
            $this->DAO->del_game($id);
            die(json_encode($this->succeed_del("游戏信息删除成功", "market_game_list")));
        }
    }





}