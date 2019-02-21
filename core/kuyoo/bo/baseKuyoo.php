<?php
COMMON('baseCore');
class baseKuyoo extends baseCore {
	public function __construct(){
		parent::__construct();
		$ser_qq_group = array("3141424712", "270772735", "2874759177");
		$online_qq = $this->get_online_qq($ser_qq_group);
		$count = count($online_qq);
		$this->assign("ser_qq",$count?$ser_qq_group[rand(0,$count-1)]:$ser_qq_group[1]);
	}

}

