loading=true;
function select_servs(){
    channel_id = Number($("input[name='ch_id']").val());
    game_id = Number($("input[name='game_id']").val());
    $("input[name='serv_id']").val("0");
    if(channel_id==0 || channel_id==""){
        alert("请先选择渠道");
        return false;
    }

    $("#servs").load("/ajax/servs?buy_type=4&game_id="+game_id+"&ch_id="+channel_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

function select_ch(){
    game_id = Number($("input[name='game_id']").val());
    $("#servs").load("/ajax/channels",function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

function load_more(act){
    if(loading==false){
        return;
    }
    ch_id = Number($("input[name='ch_id']").val());
    serv_id = Number($("input[name='serv_id']").val());
    game_id = Number($("input[name='game_id']").val());
    price = Number($("input[name='price']").val());
    page = $("input[name='page']").val();
    page = parseInt(page)+1;
    $.ajax({
        type:'get', url:"/game"+game_id+"/"+act+"?ch_id="+ch_id+"&serv_id="+serv_id+"&price="+price+"&page="+page, data:{}, dataType: 'json',
        beforeSend:function(){
            $(".g-more").html("正在加载中...");
        },
        success: function (json) {
            var content;
            $(json).each(function(){
                content = "";
                if(this.user_id==1){
                    content+='<li class="sp">';
                }else{
                    content+="<li>";
                }
                content+='<a href="/shop'+this.id+'" data-txt="官">';
                if(act=='account'){
                    content+='<span class="acnt-img"><img src="http://static.66173.cn/'+this.img+'" alt=""><em>'+this.title+'</em></span>';
                    if(this.serv_id>0){
                        content+='<span class="acnt-name"><i class="i-91"></i>'+this.channel_name+'/'+this.serv_name+'</span>';
                    }else{
                        content+='<span class="acnt-name"><i class="i-91"></i>'+this.channel_name+'/全区全服</span>';
                    }
                    content+='<span class="acnt-price">¥'+this.price+'</span></a></li>';
                    $("ul.acnt-list").append(content);
                }else if(act=='coin'){
                    if(this.serv_id>0){
                        content+='<span>￥'+this.price+'</span>【'+this.serv_name+'】'+this.title+'</a>';
                    }else{
                        content+='<span>￥'+this.price+'</span>【全区全服】'+this.title+'</a></li>';
                    }

                    $("ul.game-mlist").append(content);
                }
            })
            if(content==""){
                $(".g-more").html("没有更多了").show();
                loading = false;
            }else{
                $(".g-more").html("点击加载更多").show();
                $("input[name='page']").val(page);
                loading = true;
            }
            if($(json).length<10){
                $(".g-more").html("没有更多了").show();
                loading=false;
            }
        },
        error:function(){
            $(".g-more").html("数据加载失败").show();
            loading = false;
        }
    });
}

function buy_num(obj){
    price = $("input[name='price']").val();
    $("#money").html(($(obj).val()*price)+"元");
}

function go_pay(){
    num = $("#num").val();
    channel_id = $("input[name='channel_id']").val();
    serv_id = $("#serv_id").val();
    tel = $("input[name='tel']").val();
    qq = $("input[name='qq']").val();
    $(".form-warning").removeClass("form-warning");

    if($.trim(channel_id)=='' || $.trim(channel_id)==0){
        $("h3.user").addClass("form-warning").focus();
        $("html,body").animate({scrollTop:$("h3.zb-detail-tit").offset().top},1000);
        return false;
    }

    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        $("#serv_id").addClass("form-warning").focus();
        return false;
    }

    if(num=='' || num<1){
        $("#num").addClass("form-warning").focus();
        return false;
    }

    if($.trim(tel)==''){
        $("input[name='tel']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(qq)==''){
        $("input[name='qq']").addClass("form-warning").focus();
        return false;
    }

    $("form[name='payform']").submit();
    return true;
}