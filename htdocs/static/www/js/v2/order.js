/**
 * Created by zcl on 15/5/27.
 */
    //弹窗
$(".batch_change").click(function(){
    var w_w = $(window).width();
    var w_h = $(window).height();
    var b_w = $(".BlackBoxContent").width();
    var b_h = $(".BlackBoxContent").width();
    var _left = (w_w-b_w)/2;
    _left = _left>0?_left:0;
    var _top = (w_h-b_h)/2;
    _top = _top>0?_top:0;
    $(".BlackBoxContent").css({"left":_left,"top":_top});
    $(".BlackBoxContent,#BlackBox").show();
    id = $(this).attr("rel");
    $("input[name='cancel_id']").val(id)
});
$(".close,.cancel").click(function(){
    $(".BlackBoxContent,#BlackBox").hide();
});
$("button.submit").click(function(){
    id = $("input[name='cancel_id']").val();
    window.location.href = "my.php?act=order_cancel&id="+id;
});

$(document).ready(function(){
    $("ul#order_tab li").click(function(){
        $(this).addClass("on").siblings().removeAttr("class");
         rel=$(this).attr("rel");
        $("input[name='status']").val(rel);
        $('#order-list').submit();
    });
})

$(function() {
    $('select[name=game_id]').change(function () {
        var game_id=$("select[name=game_id] option:selected").val();
        $('#order-list').submit();
    })

    $('select[name=serv_id]').change(function () {
        var serv_id=$("select[name=serv_id] option:selected").val();
        $('#order-list').submit();
    })

    $('select[name=status]').change(function () {
        var status=$("select[name=status] option:selected").val();
        $('#order-list').submit();
    })
});