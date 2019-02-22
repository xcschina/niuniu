$(document).ready(function(){
    $("a#login-btn").on("click", function (e) {
        $(this).blur();
        login_mask();
        return false;
    });
})
function login_mask(){
    $('body,html').animate({scrollTop:0},0);
    // $("#login").load("/ajax/login",function(responseTxt,statusTxt,xhr){
    $("#login").load("/index.php?act=ajax_login",function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#login").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
    $("#login").find("input[name='mobile']").focus();
    return false;
}