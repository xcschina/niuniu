<?php
header('Content-type: image/png');
require_once 'config.php';
COMMON('niuguoimageCore');
$image = new Image();
$image->verifyCodeImg();