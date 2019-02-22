var upload_pic=function(object){
    var act=$(object).attr("data-act")
    if(act=="upload"){
        file_id=$(object).attr("file-id");
        file_show=$(object).attr("file-show");
        if(file_id=="" || file_id==null){
            $(object).alertmsg("error","to-file配置错误")
            return ;
        }
        if(file_show=="" || file_show==null){
            $(object).alertmsg("error","show-file配置错误")
            return ;
        }

        var ie=navigator.appName=="Microsoft Internet Explorer" ? true : false;
        if(ie){
            $("#"+file_id).click;
        }else{
            var a=document.createEvent("MouseEvents");
            a.initEvent("click", true, true);
            document.getElementById(file_id).dispatchEvent(a);
        }
    }
}

file_size=500000;
file_num=10;//默认10张
files_type = /^(?:image\/bmp|image\/gif|image\/jpeg|image\/jpeg|image\/jpeg|image\/png|)$/i;
file_id="";
file_show="";

!(function () {
    var viewFiles=document.getElementById(file_id);
    alert(viewFiles);
    if(viewFiles==null){
       return ;
    }
    function viewFile (file,i) {
        html = $("#"+file_show).html();
        //通过file.size可以取得图片大小
        var reader = new FileReader();
        reader.onload = function(evt){
            html +="<div class='item' style='float: left'>" +
            "<img class='img' style='float:left' src=\""+evt.target.result+"\" /></div>";
            $("#"+file_show).html(html);
        }
        reader.readAsDataURL(file);
    }

    viewFiles.addEventListener("change", function () {
        for (i = 0; imgFile = document.getElementById(file_id).files[i]; i++) {
            if (i > (file_num - 1)) {
                document.getElementById(file_id).value = "";
                $("#" + file_show).html("一次最多" + file_num + "张图片");
                return;
            }
            if (!files_type.test(imgFile.type)) {
                document.getElementById(file_id).value = "";
                $("#" + file_show).html("图片格式不对，支持jpg、png、gif!");
                return;
            }
            if (imgFile.size > file_size) {
                document.getElementById(file_id).value = "";
                $("#" + file_show).html("单张图片大小" + (file_size / 1000) + "k以内!");
                return;
            }
        }
        $("#" + file_show).html('');
        for (var i = 0; file = this.files[i]; i++) {
            viewFile(file, i);
        }
    }, false);
})();