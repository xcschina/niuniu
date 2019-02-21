<?php
COMMON('niuniuDao');
class system_setting_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
    }

    public function get_module_list(){
        $this->sql="select * from admin_menus where status=0 and mid<>0 and is_del=0 order by mid asc";
        $this->doResultList();
        return $this->result;
    }

    public function get_menu_list($pid){
        $this->sql="select * from admin_menus where status=0 and pid=? and is_del=0";
        $this->params=array($pid);
        $this->doResultList();
        return $this->result;
    }

    public function get_menu_by_name($name,$pid,$id){
        $this->sql="select * from admin_menus where name=? and pid=? and is_del=0";
        if($id){$this->sql=$this->sql." and id<>$id";}
        $this->params=array($name,$pid);
        $this->doResult();
        return $this->result;
    }

    public function get_menu_by_tabid($tabid,$id){
        $this->sql="select * from admin_menus where tabid=? and is_del=0";
        if($id){$this->sql=$this->sql." and id<>$id";}
        $this->params=array($tabid);
        $this->doResult();
        return $this->result;
    }

    public function add_menu($params){
        $this->sql="insert into admin_menus(pid,`name`,mid,url,tabid,target,class)values(?,?,?,?,?,?,?)";
        $this->params=array($params['pid'],$params['name'],$params['mid'],$params['url'],$params['tabid'],$params['target'],$params['class']);
        $this->doInsert();
    }

    public function edit_menu($params){
        $this->sql="update admin_menus set name=?,tabid=?,url=?,target=?,class=? where id=?";
        $this->params=array($params['name'],$params['tabid'],$params['url'],$params['target'],$params['class'],$params['id']);
        $this->doExecute();
    }

    public function upd_menu($id){
        $this->sql="update admin_menus set is_del=1 where id=$id";
        $this->doExecute();
    }

    public function get_permissions_info($usr_id){
        $this->sql="select * from admin_permissions where usr_id=?";
        $this->params=array($usr_id);
        $this->doResult();
        return $this->result;
    }

    public function add_permissions($params){
        $this->sql="insert into admin_permissions(usr_id,`module`,permissions)values (?,?,?)";
        $this->params=array($params['usr_id'],$params['nodes_ids'],$params['pami_ids']);
        $this->doInsert();
    }

    public function upd_permissions($params){
        $this->sql="update admin_permissions set `module`=?,permissions=? where usr_id=?";
        $this->params=array($params['nodes_ids'],$params['pami_ids'],$params['usr_id']);
        $this->doExecute();
    }
}