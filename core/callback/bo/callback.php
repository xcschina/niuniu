<?php
COMMON('baseCore');
DAO('callback_dao');
class callback extends baseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new callback_dao();
    }

}