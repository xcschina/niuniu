$(document).ready(function(){
    $("ul.openmask a").click(function(){open_mask(this);});
})
function get_code(id,obj){
    code = $("input[name='page-hash']").val();
    try{
        $.ajax({
            type:'get',
            url:'gifts.php?act=get_code',
            data:{
                csrf:code,
                id:id
            },
            dataType: 'json',
            success: function (result) {
                //禁止页面滑动
                $("body").bind("touchmove",function(event){event.preventDefault();});
                if(result.res==0){
                    $("div.tip-box h3").html("领取失败");
                    $("div.tip-box p.code").html(result.msg);
                    $("div.tip-box").show();
                    $(obj).removeAttr("onclick");
                    $(obj).html("已领取").addClass("gray");
                    return false;
                }else{
                    $("div.tip-box h3").html(result.msg);
                    $("div.tip-box p.code").html(result.code);
                    $("div.tip-box").show();
                    return true;
                }
            },error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.status);
                alert(XMLHttpRequest.readyState);
                alert(textStatus);
            }
        });
        //点击遮罩层
        $("div.mask").click(function(){
            close_tip_box();
        })
    }catch (e)   {
        alert(e.name  +   " :  "   +  e.message);
    }
}
function close_mask(){
    $("div.mask").hide();
}
function open_mask(obj){
    $(obj).attr("href","#;");
    $("div.mask").show();
}

function close_tip_box(){
    $("div.tip-box").hide();
    //解除页面滑动
    $("body").unbind("touchmove");
}
