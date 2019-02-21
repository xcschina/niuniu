<?php
class paramUtils {
	
    /////////////////////////////////
    // 请求参数
    /////////////////////////////////
    public static function intByGET($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = 0;
        if(isset($_GET[$paramName]) && intval($_GET[$paramName])){
                $val = intval($_GET[$paramName]);
        }
        if (!$allowEmpty && !$val) {
                header("Location:http://".SITEURL."/".($errRedirect ? $errRedirect : "error.html"));
                exit();
        }
        return $val;
    }
	
    public static function strByGET($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = '';
		if(isset($_GET[$paramName])){
			$val = $_GET[$paramName];
		}
		if (!$allowEmpty && trim($val)=='') {
			header("Location:http://".SITEURL."/".($errRedirect ? $errRedirect : "error.html"));
			exit();
		}
		return $val;
    }

    public static function intByPOST($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = 0;
		if(isset($_POST[$paramName]) && intval($_POST[$paramName])){
			$val = intval($_POST[$paramName]);
		}
		if (!$allowEmpty && !$val) {
			header("Location:http://".SITEURL."/".(isset($errRedirect) ? $errRedirect : "error.html"));
			exit();
		}
		return $val;
    }
	
    public static function strByPOST($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = '';
		if(isset($_POST[$paramName])){
			$val = $_POST[$paramName];
		}
		if (!$allowEmpty && !$val) {
			header("Location:http://".SITEURL."/".(isset($errRedirect) ? $errRedirect : "error.html"));
			exit();
		}
		return $val;
    }

    public static function intByREQUEST($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = 0;
        if(isset($_REQUEST[$paramName]) && intval($_REQUEST[$paramName])){
                $val = intval($_REQUEST[$paramName]);
        }
        if (!$allowEmpty && !$val) {
                header("Location:http://".SITEURL."/".($errRedirect ? $errRedirect : "error.html"));
                exit();
        }
        return $val;
    }

    public static function strByREQUEST($paramName, $allowEmpty=true, $errRedirect='') {
    	$val = '';
		if(isset($_REQUEST[$paramName])){
			$val = $_REQUEST[$paramName];
		}
		if (!$allowEmpty && !$val) {
			header("Location:http://".SITEURL."/".($errRedirect ? $errRedirect : "error.html"));
			exit();
		}
		return $val;
    }
}