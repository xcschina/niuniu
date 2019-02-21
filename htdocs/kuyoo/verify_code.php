<?php
header('Content-type: image/png');
require_once 'config.php';
COMMON('imageCore');
$image = new Image();
$image->verifyCodeImg();