<?php
COMMON('paramUtils');
DAO('site_index_dao');
class site_index_web {

    private $template_info;
    private $bo;

    public function __construct(){
        $this->DAO = new site_index_dao();
        $field = explode('.', $_SERVER['HTTP_HOST']);
        $game_name = $field[0];
/*        $game_name = 'MXQST';*/
        $this->template_info = $this->DAO->get_template_info($game_name);
        $this->template_info['template_name'] = 'default';
        switch($this->template_info['template_name']) {
            case 'default':
                $this->template_info['template_name'] = 'default';
                BO("default/default_site_web");
                $this->bo = new default_site_web($this->template_info);
                break;
            default:
                $this->template_info['template_name'] = 'default';
                BO("default/default_site_web");
                $this->bo = new default_site_web($this->template_info);
                break;
        }
    }



    public function __call($name, $arguments) {
        eval('$this->bo->'.$name.'("'.implode('","', $arguments).'");');
    }

}
?>