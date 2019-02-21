<?php
function COMMON(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (COMMON.$arg.'.php');
    }
}
function BO(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (BO.$arg.'.php');
    }
}
function DAO(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (DAO.$arg.'.php');
    }
}
function VIEW(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (VIEW.$arg.'.php');
    }
}
function CACHE(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (CACHE.$arg.'.php');
    }
}
function VALIDATOR(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (VALIDATOR.$arg.'.php');
    }
}
function BEAN(){
    $args = func_get_args();
    foreach($args as $arg){
        require_once (BEAN.$arg.'.php');
    }
}

// 用法：htmlspecialchars配合ENT_QUOTES
// $new = htmlspecialchars("<a href='test'>Test</a>", ENT_QUOTES);
// echo $new; // &lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;
if($_POST){
    foreach ($_POST as $k=>$v){
        if(is_array($v)){
            foreach ($v as $kk=>$vv){
                $html_post[$k][$kk]=htmlspecialchars($vv,ENT_QUOTES);
            }
        }else{
            $html_post[$k]=htmlspecialchars($v,ENT_QUOTES);
        }
    }
    $_POST = $html_post;
}
if($_GET){
    foreach ($_GET as $k=>$v){
        if(is_array($v)){
            foreach ($v as $kk=>$vv){
                $html_get[$k][$kk]=htmlspecialchars($vv,ENT_QUOTES);
            }
        }else{
            $html_get[$k]=htmlspecialchars($v,ENT_QUOTES);
        }
    }
    $_GET = $html_get;
}

function core_autoloader($class) {
    $suffix = substr($class, -4, 4);
    if ($suffix == '_web') {
        $file = BO.$class.'.php';
    }
    elseif ($suffix == '_dao') {
        $file = DAO.$class.'.php';
    }
    else {
        return;
    }

    if (file_exists($file)) {
        require $file;
    }
}