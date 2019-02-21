<?php
class cdnFtp {

    public function __construct(){
    }

    public static function upAvatar($file, $file_path){
        //联接成功后ftp_connect()传回一个handle句柄；这个handle将被以后使用的FTP函数使用。
        $conn = ftp_connect(CDN_HOST);

        //一旦建立联接，使用ftp_login()发送一个用户名称和用户密码。你可以看到，这个函数ftp_login()使用了 ftp_connect()函数传来的handle，以确定用户名和密码能被提交到正确的服务器。
        ftp_login($conn, CDN_USR, CDN_PWD);

        //close connection
        //ftp_quit($conn);

        //登录了FTP服务器，PHP提供了一些函数，它们能获取一些关于系统和文件以及目录的信息。
        //ftp_pwd()

        //获取当前所在的目录
        //$here = ftp_pwd($conn);

        //获取服务器端系统信息ftp_systype()
        //$server_os = ftp_systype($conn);

        //被动模式（PASV）的开关，打开或关闭PASV（1表示开）
        //ftp_pasv($conn, 1);

        //进入目录中用ftp_chdir()函数，它接受一个目录名作为参数。
        ftp_chdir($conn, "avatar");

        //回到所在的目录父目录用ftp_cdup()实现
        //ftp_cdup($conn);

        //上传文件，ftp_put()函数能很好的胜任，它需要你指定一个本地文件名，上传后的文件名以及传输的类型。比方说：如果你想上传 "abc.txt"这个文件，上传后命名为"xyz.txt"，命令应该是这样：
        ftp_put($conn, $file, $file_path.$file, FTP_BINARY);
        ftp_put($conn, $file.".md5", $file_path.$file.".md5", FTP_BINARY);
        ftp_quit($conn);

        //下载文件：PHP所提供的函数是ftp_get()，它也需要一个服务器上文件名，下载后的文件名，以及传输类型作为参数，例如：服务器端文件为his.zip，你想下载至本地机，并命名为hers.zip，命令如下：
        //ftp_get($conn, "hers.zip", "his.zip", FTP_BINARY);
    }
}
?>