<?php
COMMON('baseCore');
/**
 * Created by PhpStorm.
 * User: may
 * Date: 17/5/18
 * Time: 20:04
 */
class app_guide extends baseCore {
    function guide_view() {
        $this->display("app_guide.html");
    }
}