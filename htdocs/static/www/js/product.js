$(document).ready(function(){
    set_price($("dl.summary-type dd a").first());
    //set_service($("ul.kf-list li").first());
    bind_price();
    //bind_service();
})

function set_price(obj){
    ch = $(obj).attr("rel");
    price = $("input[name='stprice']").val();
    discount = $(obj).find("span.s2").attr("rel");
    $("input[name='channel_id']").val(ch);
    $("dd.price b.orange").html(Math.round((discount*price)/10));
    $("input[name='price']").val(Math.round((discount*price)/10));
    $(obj).addClass("on");
}

function bind_price(){
    $("dl.summary-type dd a").click(function(){
        $("dl.summary-type dd a").removeClass("on");
        set_price(this);
    });
}

function bind_service(){
    $("ul.kf-list li").click(function(){
        $("ul.kf-list li").removeClass("on");
        set_service(this);
    });
}

function set_service(obj){
    $(obj).addClass("on");
    service_id = $(obj).find("a").attr("rel");
    $("input[name='service_id']").val(service_id);
}

function go_buy(){
    id = $("input[name='product_id']").val();
    ch_id = $("input[name='channel_id']").val();
    window.location.href = "/buy"+id+"/"+ch_id;
}