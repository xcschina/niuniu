<?php
COMMON('niuniuDao');
class menu_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admin_menus";
    }

    public function get($id){
        $this->sql = "select a.*,b.pid as pp_id from admin_menus as a left join admin_menus as b on a.pid=b.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_cate_menu($pid){
        $this->sql = "select * from admin_menus where pid=? order by id desc";
        $this->params = array($pid);
        $this->doResultList();
        return $this->result;
    }

    public function get_all_menu(){
        $data = $this->mmc->get("all_menus");
        if(!$data){
            $this->sql="select * from admin_menus order by id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("all_apps", $data, 1, 7200);
        }
        return $data;
    }

    public function update_menu($menu, $id){
        $this->sql = "update admin_menus set name=?,pid=?,url=?,status=?,is_del=? where id=?";
        $this->params = array($menu['name'], $menu['pid'], $menu["url"], $menu['status'], $menu['is_del'], $id);
        $this->doExecute();
    }

    public function insert_menu($menu){
        $this->sql = "insert into admin_menus(pid,name,url,status,is_del)values(?,?,?,?,?)";
        $this->params = array($menu['pid'], $menu['name'], $menu['url'], $menu['status'], $menu['is_del']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}