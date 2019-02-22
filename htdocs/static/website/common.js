$(document).ready(function(){
    $("a.more").bind("click",function(){
        var  target=document.getElementById("nav");
        if(target.style.display=="block") {
            $("#nav").css("display","none")
        }  else{
            $("#nav").css("display","block")
        }
    })
    $("div.gotop").bind("click",function(){
        $('html, body').animate({scrollTop:0}, 'fast');
    })
})
