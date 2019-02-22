delImg = [];
rFilter = /^(?:image\/bmp|image\/gif|image\/jpeg|image\/jpeg|image\/jpeg|image\/png|)$/i;
$("a.img").bind('click',function(){
    var a=document.createEvent("MouseEvents");
    a.initEvent("click", true, true);
    document.getElementById("fileImage").dispatchEvent(a);
});
$(document).ready(function(){
    rebuild_preview_img();
});
(function () {
    var viewFiles = document.getElementById("fileImage");
    viewFiles.addEventListener("change", function () {
        imgFiles = document.getElementById("fileImage");
        for(i=0;imgFile = imgFiles.files[i];i++){
            if(i>2){
                imgFiles.value="";
                $("#preview").html("");
                $("h3.error").html("最多3张图片").show();
                return;
            }
            if (!rFilter.test(imgFile.type)) {
                imgFiles.value="";
                $("#preview").html("");
                $("h3.error").html("图片格式不对，支持jpg、png、gif!").show();
                return;
            }
            if (imgFile.size>3000000) {
                imgFiles.value="";
                $("#preview").html("");
                $("h3.error").html("单张图片大小3M以内!"+imgFile.size).show();
                return;
            }
        }
        $("#preview").html('');
        $("h3.error").html("");
        for (var i = 0; file = this.files[i]; i++) {
            viewFile(file,i);
        }
        delImg = [];
    }, false);

    $("input[name='bind']").click(function(){
        var bind = $("input[name='bind']:checked").val();
        if(bind == 1) {
            $("input[name='content']").attr('type','text');
        } else if(bind == 2) {
            $("input[name='content']").attr('type','email');
        } else {
            $("input[name='content']").attr('type','tel');
        }
    });
})();
$(document).ready(function(){
    if($("#error").html()!=""){
        $("#error").show();
    }
    if($("#success").html()!=""){
        $("#success").fadeIn(1000);
    }
})
function viewFile(file,i) {
    html = $("#preview").html();
    //通过file.size可以取得图片大小
    var reader = new FileReader();
    reader.onload = function(evt){
        html +="<div class='item'><img class='img' src=\""+evt.target.result+"\" /></div>";
        $("#preview").html(html);

        rebuild_preview_img();
    }
    reader.readAsDataURL(file);
}
/*
* if(upload_flag) {
 var img_path = $('input[name=img_path]').value.trim();
 if((type == 5 || type == 6) && img_path == '') {
 alert('请上传相关截图');
 return false;
 }
 var img_length = img_path.split("|").length;
 if(img_length > 5) {
 alert('截图数量不能多于5张');
 return false;
 }
 }
 return true;
 */

function rebuild_preview_img(){
    $("#preview img").each(function(){
        if(this.width>this.height){
            $(this).css({"width":"auto","height":"90px"});
        }
    });
}