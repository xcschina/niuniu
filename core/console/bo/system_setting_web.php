<?php
COMMON('baseCore');
DAO('system_setting_dao');

class system_setting_web extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new system_setting_dao();
    }

    //查询菜单列表
    //查询菜单列表
    public function menu_list() {
        $datas=$this->DAO->get_module_list();
        foreach($datas as $key=>$data){
            $p_menu_list=$this->DAO->get_menu_list($data['id']);
            foreach($p_menu_list as $i=>$list){
                $menu_list=$this->DAO->get_menu_list($list['id']);
                $p_menu_list[$i]['c_menu']=$menu_list;
            }
            $datas[$key]['p_menu']=$p_menu_list;
        }
        $this->assign("menus",$datas);
        $this->display("system_menu_view.html");
    }

    //保存菜单
    public function  sava_menu(){
        $params=$_POST;
        if(!$params['name']){
            echo json_encode($this->error_msg("菜单名不能为空"));
            return;
        }
        if(!$params['pid']){
            echo json_encode($this->error_msg("无法获取到父级菜单"));
            return;
        }

        $data=$this->DAO->get_menu_by_name($params['name'],$params['pid'],$params['id']);
        if($data){
            echo json_encode($this->error_msg("同级菜单名已存在"));
            return;
        }

        if($params['tabid']){
            $data=$this->DAO->get_menu_by_tabid($params['tabid'],$params['id']);
            if($data){
                echo json_encode($this->error_msg("tabid已经存在"));
                return;
            }
        }
        if($params['id']){
            $this->DAO->edit_menu($params);
            echo json_encode($this->succeed_msg("更新成功"));
        }else{
            $this->DAO->add_menu($params);
            echo json_encode($this->succeed_msg("保存成功"));
        }
    }

    public function del_menu(){
        $param=$_POST;
        if(!$param['id']){
            echo json_encode($this->error_msg("节点删除失败"));
            return ;
        }
        $this->DAO->upd_menu($param['id']);
        echo json_encode($this->succeed_msg("节点删除成功"));
    }

    //获取用户已有权限列表
    public function perm_list($usr_id) {
        $datas=$this->DAO->get_module_list();
        $perm_info=$this->DAO->get_permissions_info($usr_id);
        $module_arr =explode(',',$perm_info['module']);
        $perm_arr=explode(",",$perm_info['permissions']);
        foreach($datas as $key=>$data){
            $p_menu_list=$this->DAO->get_menu_list($data['id']);
            foreach($p_menu_list as $i=>$list){
                $menu_list=$this->DAO->get_menu_list($list['id']);
                $p_menu_list[$i]['c_menu']=$menu_list;
            }
            $datas[$key]['p_menu']=$p_menu_list;
        }
        $this->assign("module_arr",$module_arr);
        $this->assign("perm_arr",$perm_arr);
        $this->assign("menus",$datas);
        $this->assign("usr_id",$usr_id);
        $this->display("system_perm_view.html");
    }

    public function sava_perm(){
        $params=$_POST;
        $params['nodes_ids']=mb_substr($params['nodes_ids'],"1");
        $params['pami_ids']=mb_substr($params['pami_ids'],"1");
        if($params['usr_id']){
           $permissions_info=$this->DAO->get_permissions_info($params['usr_id']);
           if($permissions_info){
              $this->DAO->upd_permissions($params);
           }else{
             $this->DAO->add_permissions($params);
            }
            echo json_encode($this->succeed_msg("权限设置成功"));
        }else{
            echo json_encode($this->error_msg("无法获取用户id"));
        }
    }
}
?>