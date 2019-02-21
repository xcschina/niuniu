<?php
COMMON('baseCore', 'pageCore');

class website_yh extends baseCore{


    public function __construct(){
        parent::__construct();
    }

    public function merit(){

        $this->display("website/merit.html");
    }
}