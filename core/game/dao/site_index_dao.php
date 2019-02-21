<?php
COMMON('dao');
class site_index_dao extends Dao {

	public function __construct() {
		parent::__construct();
	}

    public function get_template_info($name) {
        $this->sql = "select id from game where en_name=? limit 1";
        $this->params = array($name);
        $this->doResult();
        return $this->result;
    }

}
?>