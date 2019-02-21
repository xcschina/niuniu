<?php
COMMON('imageUtils');
class uploadHelper {
	
	/////////////////////////////////////////////////////////////////////////////////
	// 使用示例：
    //    // DEMO1: 上传普通文件
	//    // 将页面<input type="file" name="form_fields"/>的表单元素（一个/多个）所指定的文件进行上传，上传到
	//	  // upload_folder目录下，必须上传文件的类型为application/vnd.ms-excel或application/msword类型
    //    $upFile = new uploadHelper("form_fields", "upload_folder", "application/vnd.ms-excel|application/msword");
    //    $upFile->upload();
    //    
    //    // 判断是否有发生错误
    //    if ($upFile->occur_err()) {
    //    	$this->V->assign('msg', $upFile->get_err_msgs());	// 获得上传出错信息
    //    } else {
    //    	$this->V->assign('msg', "上传成功！");
    //    }
    // 	  // 上传成功后，可以用$upFile->get_rel_files_path()来获得上传文件的相对路径集合，返回格式array('upload_folder/201104/xxx.xls','upload_folder/201104/yyy.xls')
    //    
    //    
    //    
    //    
    //    // DEMO2: 上传图片文件文件
    //    $upFile = new uploadHelper("form_fields", "upload_folder", "image/gif|image/jpeg|image/png");
    //    // 设置缩略图大小为200*200，0表示不删除原图片文件
    //    $upFile->set_thum_settings(200, 200, 0);
    //    $upFile->upload();
    //    
    //    // 判断是否有发生错误
    //    if ($upFile->occur_err()) {
    //    	$this->V->assign('msg', $upFile->get_err_msgs());	// 获得上传出错信息
    //    } else {
    //    	$this->V->assign('msg', "上传成功！");
    //    }
    //    
    //    
	/////////////////////////////////////////////////////////////////////////////////
	
	// 项目基路径
	var $PROJ_PATH;
	
	//////////////////////////////////
	// 文件设置
	//////////////////////////////////
	// 表单字段名
	var $form_filed;
	// 保存目录的相对目录路径
	var $rel_folder_path = "";
	// 保存目录的物理目录路径
	var $phy_folder_path = "";
	// 允许的文件类型MIME，为空时不限制格式(用|分开)
	var $allow_mime = "";
	// 文件最大字节(默认10M)
	var $max_size = 10485760;
	// 覆盖模式(0：不覆盖，1：覆盖)
	var $overwrite = 0;
	// 是否跳过上传空文件的标识(0：不跳过，1：跳过)，若$skip_empty=0，并且页面未上传文件，则报错
	var $skip_empty = 1;
	// 错误代号
	var $errno = 0;
	// 是否发生错误标识(0：无错误，1：有错误)
	var $has_err = 0;
	// 消息字符串
	var $err_msgs = array();
	// 上传文件的相对文件名集合
	var $rel_files_path = array();
	// 上传文件的物理文件名集合
	var $phy_files_path = array();
	// 上传文件的文件类型集合
	var $file_types = array();
    // 获取图片位置颜色
    var $point_color = 0;
    // 获取图片高度
    var $img_height = 0;
    // 获取图片宽度
    var $img_width = 0;
	
	
	//////////////////////////////////
	// 缩略图设置
	//////////////////////////////////
	// 缩略图大小（长，宽数组）
	var $thum_size = array();
	// 是否删除原始图片(0：不删除，1：删除)
	var $del_src_img = 1;
	// 缩略图的文件后缀
	var $thum_suffix = "_thum";
	// 是否维持比例
	var $thum_ratio=1;
	
	
	

	/* 构造函数
	 * $form_filed 上传文件的表单字段名
	 * $base_folder 保存的基目录名称
	 * $format 文件格式(用|分开)
	 * $max_size 文件最大限制
	 * $overwrite 复盖参数(0：不覆盖，1：覆盖)
	 */
	function __construct($form_filed, $base_folder_name = "up_files", $is_record=1, $allow_mime = "",
                         $max_size = 0, $overwrite = 1, $skip_empty = 1){
        $this->PROJ_PATH = PREFIX.DS."htdocs/static";
        $this->form_filed = $form_filed;									// 上传文件的表单字段名
        $this->allow_mime = $allow_mime;									// 允许的文件类型MIME(用|分开)
        $this->max_size = !$max_size ? $this->max_size : $max_size;			// 文件最大字节
        $this->overwrite = $overwrite;										// 是否复盖相同名字文件
        $this->skip_empty = $skip_empty;									// 是否跳过空文件
        $this->set_save_folder($base_folder_name, $is_record);							// 设置目录保存位置
	}
	
	/*
	 * 功能：设置目录保存位置
	 * $base_folder_name 保存的基目录名称
	 */
	private function set_save_folder($base_folder_name, $is_record) {
        if (!$this->rel_folder_path && $is_record) {
                $cur_date = date("Ym");
            $this->rel_folder_path = DS.$base_folder_name.DS.$cur_date.DS;			// 上传目录的相对路径，注：这里前"/".表示相对于htdocs/而言
        }else{
            $this->rel_folder_path = DS.$base_folder_name.DS;			            // 上传目录的相对路径，注：这里前"/".表示相对于htdocs/而言
        }
        $this->phy_folder_path = $this->PROJ_PATH.$this->rel_folder_path;		// 上传目录的物理路径
        $this->make_dir($this->phy_folder_path);
	}

	/*
	 * 功能：上传文件
	 * $form 文件名称
	 * $file_name 上传文件保存名称，为空或者上传多个文件时由系统自动生成名称
	 */
	public function upload($file_name = ""){
        if (!isset($_FILES[$this->form_filed])) {
            $this->halt("错误：指定上传的表单字段名称[$this->form_filed]不存在。");
            return;
        } else {
            $files = $_FILES[$this->form_filed];
        }

        if(!is_writable($this->phy_folder_path)){
            $this->halt("ERROR：[$this->phy_folder_path] unwrite。");
            return;
        }

        if(is_array($files["name"])){										// 上传同文件域名称多个文件
            for($i = 0; $i < count($files["name"]); $i++){
                $name = $files["name"][$i];
                $type = $files["type"][$i];
                $tmp_name = $files["tmp_name"][$i];
                $size = $files["size"][$i];
                $error = $files["error"][$i];

                $this->start_upload($file_name, $name, $type, $tmp_name, $size, $error);
            }
        }else{	//上传单个文件
            $name = $files["name"];
            $type = $files["type"];
            $tmp_name = $files["tmp_name"];
            $size = $files["size"];
            $error = $files["error"];

            $this->start_upload($file_name, $name, $type, $tmp_name, $size, $error);
        }
        return true;
	}
	
	/*
	 * 功能：包装参数，检测并上传文件，（调用upFile()处理上传逻辑）
	 * $filear 上传文件资料数组
	 */
	private function start_upload($file_name, $name, $type, $tmp_name, $size, $error) {
		// 获得目标文件名（保存的文件名）
		$file_name = ($file_name ? $file_name : $this->get_rand_file_name()).($this->get_ext($name));
		// 上传文件，返回是否上传成功标识
		$isSuccess = $this->upload_file(array("file_name"=>$file_name, "type"=>$type, "tmp_name"=>$tmp_name, "size"=>$size, "error"=>$error));
		
		// 上传成功后设置相对/物理路径，失败则设置空
		if ($isSuccess) {
            $this->rel_files_path[] = str_replace('\\', '/', $this->rel_folder_path.$file_name);
            $this->phy_files_path[] = $this->phy_folder_path.$file_name;
            $this->file_types[] = $type;
            list($this->img_width,$this->img_height)=getimagesize($this->phy_folder_path.$file_name);
		} else {
            $this->rel_files_path[] = "";
            $this->phy_files_path[] = "";
            $this->file_types[] = "";
            // version2 added
            $this->unlike_file($tmp_name);
		}
	
		// 判断是否开启图片调整，即创建缩略图
		if (count($this->thum_size) > 0) {
            $w = $this->thum_size[0];
            $h = $this->thum_size[1];
            // 策略：
            // 1.如果删除原图片(del_src_img=1)，则将生成的缩略图相对/物理路径保存到rel_files_path/phy_files_path中
            // 2.如果保存原图片(del_src_img=0)，则生成的缩略图相对/物理路径默认为rel_files_path/phy_files_path的文件名+thum_suffix
            // 例如：
            // 原文件保存路径：xx/201104/abc.jpg，缩略图默认为xx/201104/abc_thum.jpg
            if (!$isSuccess) {
                    return;
            }
            if (preg_match('/^image\//i', $type)) {
                $cur_img_path = $this->phy_folder_path.$file_name;
                if ($this->del_src_img) {
                    imageUtils::resizeImage($cur_img_path, $cur_img_path, $w, $h, $this->thum_ratio);
                } else {
                    imageUtils::resizeImage($cur_img_path, $this->get_thum_file_name($cur_img_path), $w, $h, $this->thum_ratio);
                }
            }
		}

        if($this->point_color){
            $this->point_color = $this->get_img_point_color($this->phy_folder_path.$file_name);
        }
	}

	/*
	 * 功能：检测并上传文件
	 * $filear 上传文件资料数组
	 */
	private function upload_file($file){
		if (!$file["tmp_name"]) {
			if (!$this->skip_empty) {
				$this->halt("错误：上传文件文件不能为空。");
			}
			return false;	
		}
		
		if($file["size"] > $this->max_size){
			$this->halt("错误：上传文件 ".$file["file_name"]." 大小超出系统限定值[".$this->max_size." 字节]，不能上传。");
			return false;
		}

		$phy_file_path = $this->phy_folder_path.$file["file_name"];					// 目标文件的物理保存路径
		if(!$this->overwrite && file_exists($phy_file_path)){
			$this->halt("错误：".$phy_file_path." 文件已经存在。");
			return false;
		}

		if($this->allow_mime != "" && !in_array(strtolower($file["type"]), explode("|", strtolower($this->allow_mime)))){
			$this->halt("错误：".$file["type"]." 文件格式不允许上传。");
			return false;
		}

		if(!move_uploaded_file($file["tmp_name"], $phy_file_path)){
			$errors = array(0=>"提示：文件上传成功。",
			1=>"警告：上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。 ",
			2=>"警告：上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。 ",
			3=>"警告：文件只有部分被上传。 ",
			4=>"警告：没有文件被上传。 ");
			$this->halt($errors[$file["error"]]);
			return false;
		}
		// else {// version1
		//	@unlink($file["tmp_name"]);				// 删除临时文件
		//	return true;
		//}
		return true;	// version2
	}
	
	/*
	 * 功能: 创建目录
	 * $folder_path 目录的物理路径
	 */
    public function make_dir($folder_path) {
        $reval = false;
        if (!file_exists($folder_path)) {
            @mkdir($folder_path, 0777, true);
        } else {
            // 路径已经存在。返回该路径是不是一个目录
            $reval = is_dir($folder_path);
        }
        clearstatcache();
    }
    
	/*
	 * 功能: 根据原图片文件名称生成缩略图文件名称
	 * 例：原图: abc.jpg，缩略图：abc_thum.jpg
	 * $src_file_name 原图片名称
	 */
    private function get_thum_file_name($src_file_name) {
    	$index = strripos($src_file_name, '.');
    	return substr($src_file_name, 0, $index).$this->thum_suffix.substr($src_file_name, $index);
    }

	/*
	 * 功能: 取得文件扩展名
	 * $file_name 为文件名称
	 */
	function get_ext($file_name) {
		if($file_name == "") {
			return;
		}

		$exts = explode(".", $file_name);
	    if (count($exts) === 0) {
    		 die('FILE WITHOUT EXTENSION ...');
    	}
		return '.'.$exts[count($exts) - 1];
	}

	/*
	 * 功能：获得随机文件名
	 * $ext 文件后缀名
	 */
	private function get_rand_file_name() {
		return (time().mt_rand(1, 1000));
	}

	/*
	 * 功能：错误提示
	 * $msg 为输出信息
	 */
	private function halt($msg){
		$this->has_err = 1;
		$this->err_msgs[] = $msg;
	}
	
	/*
	 * 功能：设置覆盖模式(0：不覆盖，1：覆盖)
	 */
	public function set_overwrite($overwrite) {
		$this->overwrite = $overwrite;
	}
	
	/*
	 * 功能：设置是否跳过上传空文件的标识(0：不跳过，1：跳过)
	 */
	public function set_skip_empty($skip_empty) {
		$this->skip_empty = $skip_empty;
	}
	
	/*
	 * 功能：设置缩略图大小（长，宽数组）
	 */
	public function set_thum_size($thum_width, $thum_height) {
		$this->thum_size = array($thum_width, $thum_height);
	}
	
	/*
	 * 功能：设置缩略图属性
	 */
	public function set_thum_settings($thum_width=400, $thum_height=400, $del_src_img=1, $thum_suffix="_thum", $ratio=1) {
		$this->thum_size = array($thum_width, $thum_height);
		$this->del_src_img = $del_src_img;
		$this->thum_suffix = $thum_suffix;
		$this->thum_ratio = $ratio;
	}
	
	/*
	 * 功能：获得是否出错标识
	 */
	public function occur_err() {
		return $this->has_err;
	}
	
	/*
	 * 功能：获得错误信息
	 */
	public function get_err_msgs() {
		return $this->err_msgs;
	}
	
	/*
	 * 功能：获得上传文件的相对路径集合
	 */
	public function get_rel_files_path() {
		return $this->rel_files_path;
	}
	
	/*
	 * 功能：获得上传文件的物理路径集合
	 */
	public function get_phy_files_path() {
		return $this->phy_files_path;
	}
	
	/*
	 * 功能：获得上传文件的相对路径，取上传文件集合中的第一个
	 */
	public function get_rel_file_path() {
        $collection = $this->rel_files_path;
		return $collection[0];
	}

    public function get_rel_file_pathes(){
        return $this->rel_files_path;
    }
	/*
	 * 功能：获得上传文件的物理路径，取上传文件集合中的第一个
	 */
	public function get_phy_file_path() {
		$collection =  $this->phy_files_path;
		return $collection[0];
	}
	
	/*
	 * 功能：删除文件
	 * $file_name 待删除的文件路径（从项目根路径开始）
	 */
	public static function unlike_file($file_name) {
        if($file_name && file_exists(PREFIX.$file_name)){
            unlink(PREFIX.$file_name);
            // 删除缩略图
            if (file_exists(PREFIX.DS.($thum = uploadHelper::get_thumfile_name($file_name)))){
            	unlink(PREFIX.$thum);
            }
        }        
	}
	
	/*
	 * 功能：获得对应的缩略图文件
	 * $file_name 文件路径（从项目根路径开始）
	 * 注：此方法不建议使用，方法同$this->get_thumfile_name()实现的功能一样，但限制了缩略图格式为'_thum'
	 */
	public static function get_thumfile_name($file_name) {
		if (!trim($file_name) || !($dot_pos = strripos($file_name, '.'))) {
			return '';
		}
		return substr($file_name, 0, $dot_pos).'_thum'.substr($file_name, $dot_pos);
	}
        
    public function set_form_filed($form_filed){
        $this->form_filed = $form_filed;
    }

    protected function get_img_point_color($image){
        if($temp_img_type = @getimagesize($image)) {eregi('/([a-z]+)$', $temp_img_type['mime'], $tpn); $img_type = $tpn[1];}
        else {eregi('.([a-z]+)$', $image, $tpn); $img_type = $tpn[1];}
        $all_type = array(
            "jpg"   => 'ImageCreateFromjpeg',
            "gif"   => 'ImageCreateFromGIF',
            "jpeg"  => 'ImageCreateFromjpeg',
            "png"   => 'imagecreatefrompng',
            "wbmp"  => 'imagecreatefromwbmp'
        );

        $func_create = $all_type[$img_type];

        $img = $func_create($image);

        $rgb = imagecolorat($img,1,1);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return $this->RGB2Hex($r, $g, $b);
    }

    protected function RGB2Hex($r=0, $g=0, $b=0){
        if($r < 0 || $g < 0 || $b < 0 || $r > 255 || $g > 255|| $b > 255){
            return false;
        }
        return "#".(substr("00".dechex($r),-2)).(substr("00".dechex($g),-2)).(substr("00".dechex($b),-2));
    }

}
?>
