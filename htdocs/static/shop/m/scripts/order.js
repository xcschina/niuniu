$(document).ready(function(){
    $('select[name=game_id]').change(function () {
        var game_id=$("select[name=game_id] option:selected").val();
        $('#order-list').submit();
    })

    $('select[name=serv_id]').change(function () {
        var serv_id=$("select[name=serv_id] option:selected").val();
        $('#order-list').submit();
    })

    $('ul li').bind('click',function(){
        $("input[name=status]").val($(this).attr("rel"));
        $('#order-list').submit();
    })

    bind_pay_channel();
    more_order_list();
})

function bind_pay_channel(){
    $("section.pay-method a").click(
        function(){
            channel = $(this).attr("rel");
            $("section.pay-method a").removeClass("on");
            $(this).addClass("on");
            $("input[name='pay-channel']").val(channel);
        }
    );
}
function go_pay(){
    $("form[name='payform']").submit();
}

//下拉分页
function more_order_list(){
    $("#more").bind("click",function(){
        if($("#more").attr("id")==null){
            return ;
        };
        $.ajax({
            type:'get',
            url:'my.php?act=my_orders',
            data:{
                page:parseInt($("#page").val())+1,
                game_id:$("select[name=game_id] option:selected").val(),
                serv_id:$("select[name=serv_id] option:selected").val(),
                status:$("input[name=status]").val()
            },
            dataType: 'json',
            beforeSend:function(){
            },
            success: function (json) {
                var content = "";
                $(json).each(function(){
                    content+="<li><a href='my.php?act=order_detail&id="+this.id+"'><span class='md-name'>";
                    if(this.status=='0'){
                        content+="<i class='md-state-4'></i>";
                    }else if(this.status=='1'){
                        content+="<i class='md-state-3'></i>";
                    }else if(this.status=='2'){
                        content+="<i class='md-state-2'></i>";
                    }else{
                        content+="<i class='md-state-1'></i>";
                    }
                    content+=this.title;
                    content+="</span><em class='md-price'>￥"+this.money+"</em>";
                    content+="<em class='md-num'>"+this.amount+"件</em>";
                    content+="<span class='md-service'>"+this.game_name+" /"+this.serv_name+"</span>";
                    content+="<span class='md-date'>"+this.date+"</span>";
                    content+="<span class='md-time'>"+this.time+"</span> </li>";
                })
                if(content==""){
                    $("#more").html("没有更多了");
                    $("#more").attr("id","");
                }else{
                    $("#content_befor").before(content);
                    $("#page").val(parseInt($("#page").val())+1);
                }
                if($(json).length<10){
                    $("#more").html("没有更多了");
                    $("#more").attr("id","");
                }
            },
            error:function(){
                $("#more").html("加载失败");
            }
        });
    })
}
