<?php
COMMON('dao');
class niuniuDao extends Dao{
    public function  __construct() {
        parent::__construct();
        $this->DBNAME = NIUNIU_DBNAME;
    }
}