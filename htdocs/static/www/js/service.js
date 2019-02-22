$(document).ready(function(){
    $("li.sidebar-service").hover(function(){
        $("ul.service-list").toggle();
    },function(){
        $("ul.service-list").toggle();
    });
});