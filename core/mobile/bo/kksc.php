<?php
COMMON('baseCore');

class kksc extends baseCore{


    public function __construct(){
        parent::__construct();
    }

    public function kksc(){

        $this->display("kksc_game.html");
    }


}