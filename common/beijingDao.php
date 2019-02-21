<?php
COMMON('dao');
class beijingDao extends Dao{
    public function  __construct() {
        parent::__construct();
        $this->DBNAME = 'beijing';
    }
}