<?php
class Image{
    /**
     * @param $img_name 图片控件名
     * @param $width	新图片宽度
     * @param $height	新图片高度
     * @param $tar_url	存放路径
     */
    protected $img_type;

    public function upImg($img_name,$width=0,$height=0,$tar_folder,$tar_name='',$tar_ext='jpg'){
        $img = $_FILES[$img_name];
        $tmpName = time().mt_rand(1, 1000);
        if (substr($img['type'], 0, 5) == 'image') {
            switch ($img['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                case 'image/pjpeg':
                    $ext = '.jpg';
                    $this->img_type = 'jpg';
                    break;
                case 'image/gif':
                    $ext = '.gif';
                    $this->img_type = 'gif';
                    break;
                case 'image/bmp':
                    $ext = '.bmp';
                    $this->img_type = 'bmp';
                    break;
                case 'image/png':
                case 'image/x-png':
                    $ext = '.png';
                    $this->img_type = 'png';
                    break;
                default:
                    return null;
            }

            // $file_type: 文件类型
            // $file_name: 原文件名
            // $file_tmp_name: 文件被上传到服务器端存储的临时文件名
            $dateFolder =date("Ym");
            $folder =  $this->make_dir(PREFIX.'/htdocs/'. $tar_folder."/");
            $temp_file = PREFIX.'/htdocs/static/temp_file/'. $tmpName . $ext;
            if($tar_name && $tar_ext){
                $tar_file = PREFIX.'/htdocs/'.$tar_folder.'/'. $tar_name.".".$tar_ext;
            }else{
                $tar_file = PREFIX.'/htdocs/'.$tar_folder.'/'. $tmpName.$ext;
            }
            move_uploaded_file($img["tmp_name"] , $temp_file);
            $img = $this->HrResize($temp_file, $tar_file, $width, $height, $tar_ext);
            return $tmpName.$ext;
        }else{
            return null;
        }
    }
	
    //生成缩略图、打水印
    public function HrResize($source_img,$tar_file,$new_width=0,$new_height=0,$tar_file_type=''){
        //图片类型
        $img_type   = $this->img_type;

        //图象的完整目标路径
        $tar_url   =   $tar_file;
        //初始化图象
        if($img_type=="jpg")   $temp_img   =   @imagecreatefromjpeg($source_img);
        if($img_type=="gif")   $temp_img   =   @imagecreatefromgif($source_img);
        if($img_type=="png")   $temp_img   =   @imagecreatefrompng($source_img);
        if($img_type=="bmp")   $temp_img   =   @imagecreatefromwbmp($source_img);

        //原始图象的宽和高
        $old_width = @imagesx($temp_img);
        $old_height = @imagesy($temp_img);

        //生成新图片
        $new_img = @imagecreatetruecolor($new_width,$new_height);
        @imagecopyresampled($new_img,$temp_img,0,0,0,0,$new_width,$new_height,$old_width,$old_height);

        if($tar_file_type){
            $img_type = $tar_file_type;
        }

        if($img_type=="jpg") @imagejpeg($new_img,$tar_url);
        if($img_type=="gif") @imagegif($new_img,$tar_url);
        if($img_type=="png") @imagepng($new_img,$tar_url);
    }

    public function make_dir($folder)
    {
        $reval = false;
        if (!file_exists($folder))
        {
            @mkdir($folder,0777,true);
        }else{
            /* 路径已经存在。返回该路径是不是一个目录 */
            $reval = is_dir($folder);
        }

        clearstatcache();
        return $folder;
    }

    /*star 文件上传 **/
    public function upFile($file_tmp_name,$file_type,$file_name,$tar_url){
        $exts = explode(".", $file_name);
        $ext = ".".$exts[count($exts)-1];
        $tmpName = time().mt_rand(1, 1000);
        $dateFolder = date("Ym");
        $folder =  $this->make_dir(PREFIX.'/htdocs/'. $tar_url ."/".$dateFolder."/");
        $tar_file = PREFIX.'/htdocs/'.$tar_url.'/'.$dateFolder.'/'. $tmpName.$ext;
        move_uploaded_file($file_tmp_name , $tar_file);
        return $dateFolder."/".$tmpName.$ext;
    }

    /** 文件上传:图片-生成缩略图 **/
    public function upFileImg($file_tmp_name,$file_type,$file_name,$tar_url,$width=142,$height=96){
        $exts = explode(".", $file_name);
        $ext = ".".$exts[1];
        $this->img_type = $exts[1];
        $tmpName = time().mt_rand(1, 1000);
        $dateFolder =date("Ym");
        $folder =  $this->make_dir(PREFIX.'/htdocs/'. $tar_url ."/".$dateFolder."/");
        $tar_file = PREFIX.'/htdocs/'.$tar_url.'/'.$dateFolder.'/'. $tmpName.$ext;
        $thumb_file = PREFIX.'/htdocs/'.$tar_url.'/'.$dateFolder.'/'. $tmpName."_thumb".$ext;
        move_uploaded_file($file_tmp_name , $tar_file);
        $thumb = $this->HrResize($tar_file, $thumb_file, $width, $height);
        return array($dateFolder."/".$tmpName.$ext, $dateFolder.'/'. $tmpName."_thumb".$ext);
    }
    
    // 生成缩略图
	public function createHrResize($tar_file, $thumb_file, $imgType, $width=142,$height=96){
		
        $this->img_type = $imgType;

        $thumb = $this->HrResize($tar_file, $thumb_file, $width, $height);
    }
    /*end 文件上传*/

    /*验证码*/
    public function verifyCodeImg(){
        $az = array('q', 'w', 'r', 't', 'y', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'th', 'wr');
        $aa = array('a', 'e', 'u','7','8','6','5','4');
        $len = rand(4,6);
        $str = '';

        for ($i = 2; $i <= $len; $i++) {
            if ($i % 2 == 0) {
                $c = strtoupper($az[rand(0, 19)]);
                $str = $str . $c;
            } else {
                $c = strtoupper($aa[rand(0, 7)]);
                $str = $str . $c;
            }
        }
        // put it to session
        $_SESSION['c'] = $str;

        // create the image
        $fn = PREFIX . '/htdocs/static/res/wbg' . rand(1,5) . '.png';
        $im = imagecreatefrompng($fn);

        // create some colors
        $fg = imagecolorallocate($im, 240, 240, 230);
        $bg = imagecolorallocate($im, 120, 140, 190);
        $bbg = imagecolorallocate($im, 20, 40, 40);

        $fonts = array(0 => 'zt', 1 => 'cgn', 2 => 'carbon', 3=>'Tuffy', 4=>'luixisbi');
        $font = PREFIX . '/htdocs/static/fonts/' . $fonts[rand(0,3)] . '.ttf';

        // add some shadow to the text
        $x = rand(10, 40);

        // add the text
        imagettftext($im, 22, 0, $x+2, 32, $bg, $font, $str);
        imagettftext($im, 22, 0, $x+1, 31, $bg, $font, $str);
        imagettftext($im, 22, 0, $x-1, 29, $bg, $font, $str);
        imagettftext($im, 22, 0, $x-2, 28, $bbg, $font, $str);
        imagettftext($im, 22, 0, $x, 30, $fg, $font, $str);

        //增加曲线干扰线
        $w = 150;
        $h1=rand(-5,5);
        $h2=rand(-1,1);
        $w2=rand(10,15);
        $h3=rand(4,6);
        for($j=40;$j<=46;$j++){
            $h = $j;
            for($i=-$w/2;$i<$w/2;$i=$i+0.1)
            {
                $y=$h/$h3*sin($i/$w2)+$h/2+$h1;
                imagesetpixel($im,$i+$w/2,$y,$bbg);
                $h2!=0?imagesetpixel($im,$i+$w/2,$y+$h2,$bbg):null;
            }
        }
//        for($i=0; $i<5; ++$i){
//            $x = rand(0, 176);
//            $y = rand(0, 45);
//            $x1 = rand(0, 176);
//            $y1 = rand(0, 45);
//            imageline($im, $x, $y, $x1, $y1, $bbg);
//        }

        imagepng($im);
    }

    public function occur_err() {
   		return $this->has_err;
   	}
}