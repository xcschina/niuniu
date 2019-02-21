<?php
if(!$_SESSION["usr_id"]) {
    header("location:login.php?act=login_view");
    exit;
}
?>