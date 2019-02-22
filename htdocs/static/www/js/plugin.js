/*Use for:	66173*/

/*切换*/
var cTab=function(opt){
	//settings
	var settings=jQuery.extend(true,{
		tabHandleList:"#tabHnadle > li",//标签头
		tabBodyList:"#tabBody > li",//标签内容体序列
		//isAutoPlay:{time:3000},//是否自动播放
		isAutoPlay:false,
		bind:"mouseover",//标签绑定事件
		defIndex:0,//默认选中标签下标tabHnadle
		//tabOnCssList:"#tabHnadle > li",//标签on样式添加点
		tabOnCssName:"on"//选中标签样式
	},opt);
	var isAutoPlay=settings.isAutoPlay,
		bind=settings.bind,
		defIndex=settings.defIndex,
		$tabHandleList=$(settings.tabHandleList),
		tabOnCssName=settings.tabOnCssName,
		$tabOnCssList=$(settings.tabHandleList),
		$tabBodyList=$(settings.tabBodyList);
	var maxSize=$tabHandleList.size();
	var gotoIndex=function(i){
		if(i>=maxSize){i=0;}else if(i<0){i=maxSize-1;}
		$tabOnCssList.eq(defIndex).removeClass(tabOnCssName);
		$tabOnCssList.eq(i).addClass(tabOnCssName);
		$tabBodyList.eq(defIndex).hide();
		$tabBodyList.eq(i).show();
		$tabBodyList.eq(i);
		defIndex=i;
		return false;
	};
	gotoIndex(defIndex);
	$tabHandleList.each(function(i){
		$(this).bind(bind,function(){gotoIndex(i);});
	});
	//auto
	var timerID;
	var autoPlay=function(){
		timerID=window.setInterval(function(){
			var temp=defIndex+1;
			gotoIndex(temp);
		},isAutoPlay.time);
	};
	var autoStop=function(){
		window.clearInterval(timerID);
	};
	if(isAutoPlay){
		autoPlay();
		$tabHandleList.hover(autoStop,autoPlay);
		$tabBodyList.hover(autoStop,autoPlay);
	}
	//return
	return {gotoIndex:gotoIndex,defIndex:defIndex};
};
/*marquee*/
function DoScrollAnn(){
	var parent = $("#idx_ann_scroll"), items = parent.find("li"), par = { index:0, height:20, duration:300, timeout:2000 }, timer = null;
	parent.html(parent.html() + parent.html());
	function DoAuto(){ timer = setTimeout(function(){ DoScroll(++par.index); }, par.timeout); }
	function DoScroll(i){
		if(i > items.length){ par.index = 1; parent.animate({ "top":0 }, 0); }else{ par.index = i; }
		parent.animate({ "top":-1 * par.height * par.index }, par.duration);
		DoAuto();
	}DoAuto();
	parent.hover(function(){ clearTimeout(timer); }, DoAuto);
}

$(function (){
	ini_search();
	/*marquee*/
	if($('#idx_ann_scroll').size()>0){DoScrollAnn();}
	
	/*首充号*/
	//cTab({tabHandleList:"#index_game_tab a",tabBodyList:"#index_game_content_list li"});
	
	$('.product-order .order-btn').hover(function(){
		$(this).find('.order-btn-more').show();
	},function(){
		$(this).find('.order-btn-more').hide();	
	});
	$('.btn-share').hover(function(){
		$(this).find('.share-btn-more').show();
	},function(){
		$(this).find('.share-btn-more').hide();	
	});
	
	$('#show-detail-btn,#hide-detail-btn').click(function(e) {
		$('#order-info-detail').slideToggle(300);
		$('#order-info-simple').slideToggle(300);
	});
	/*支付*/
	//cTab({tabHandleList:"#pay-way-tab li",tabBodyList:"#pay-way-content div.pay-content",bind:"click",tabOnCssName:"active"});
	//$('#show-more-bank-btn').click(function(e) {
	//	$('#more-bank-block').slideToggle(300);
	//	$(this).find('span').toggle();
	//});
});

//游戏列表
function game_tab(obj){
    rel = $(obj).attr("rel");
    if(rel=="hot"){
        $("#index_game_content_list li.idx_gs_list:first").show();
    }else{
        $("#index_game_content_list li.idx_gs_list:first").hide();
        games = $.ajax({url:"index.php?act=get_games&letter="+rel,async:false});
        games= jQuery.parseJSON(games.responseText);//转换为json对象
        $("#index_game_content_list li.idx_gs_list:last").html("");
        $.each(games,function(idx,item){
            if(item.first_letter == rel){
                $("#index_game_content_list li.idx_gs_list:last").append('<dl><dt><a href="\/game'+item.id+'"><img src="http:\/\/cdn.66173.cn\/'+item.game_icon+'" \/><\/a><\/dt><dd>' +
                    '<strong><a href="\/game'+item.id+'">'+item.game_name+'<\/a><\/strong>' +
                    '<p><a href="\/game'+item.id+'/coin">游戏币<\/a>' +
                    '<a href="\/game'+item.id+'/props">装备<\/a>' +
                    '<a href="\/game'+item.id+'/account">帐号<\/a>' +
                    '<a href="\/game'+item.id+'/character">首充号<\/a>' +
                    '<a href="\/game'+item.id+'/recharge" class="link1">首充号代充<\/a>' +
                    '<a href="\/game'+item.id+'/topup">代充<\/a><\/p><\/dd><\/dl>');
            }
        })
        $("#index_game_content_list li.idx_gs_list:last").show();
    }
    $("#index_game_tab a").removeClass("on");
    $(obj).addClass("on");
}

//搜索栏游戏列表
function nav_game_tab(obj){
    rel = $(obj).attr("rel");
    if(rel=="hot"){
        $("#gsList").show();
		$("#gsFastSearch").hide();
    }else{
        $("#gsList").hide();
        games = $.ajax({url:"index.php?act=get_games&letter="+rel,async:false});
        games= jQuery.parseJSON(games.responseText);//转换为json对象
        $("#gsFastSearch").html("");
        $.each(games,function(idx,item){
            if(item.first_letter == rel){
                $("#gsFastSearch").append('<li><a title=\"'+item.game_name+'\" href=\"/game'+item.id+'\">'+item.game_name+'<\/a><\/li>');
            }
        })
        $("#gsFastSearch").show();
    }
    $("li.gsNav a").removeClass("current");
    $(obj).addClass("current");
}

//游戏列表
function character_game_tab(obj){
    rel = $(obj).attr("rel");
    if(rel=="hot"){
        $("#character_game_list li:first").show();
    }else{
        $("#character_game_list li:first").hide();
        games = $("input[name='games']").val();
        games= jQuery.parseJSON(games);//转换为json对象
        $("#game_box").html("");
        html_str ='';
        $.each(games,function(idx,item){
            if(item.first_letter == rel){
                html_str+='<div class="item"><div class="fl item-img">'+
                    '<a href="\/game'+item.id+'\/character"><img src="http:\/\/cdn.66173.cn\/'+item.game_icon+'" width="86" height="86" /><\/a><\/div>' +
                    '<div class="fl">' +
                    '<div class="item-name"><a href="/game'+item.id+'/character">'+item.game_name+'<\/a><\/div>' +
                    '<div class="item-prize"><span class="s1">'+item.discount+'<\/span><span class="s2">折起<\/span><\/div>' +
                    '<\/div><\/div>';
            }
        });
        $("#game_box").html(html_str).show();
    }
    $("#character_game_tab a").removeClass("on");
    $(obj).addClass("on");
}
function ini_search(){
    var keyup_timer;
    /*搜索*/
    $('.arrow').click(function(e) {
        show_search_box(this,e)}
    );
    $('.close_btn').click(function(e) {
        $('.gs_box').hide();
        $('#searchbar_arrow').hide();
    });
    $("#gsNav li.gsNav a").click(function(e){nav_game_tab(this);e.stopPropagation();});
    $("#keyword").click(function(e){
        show_keyword_search(this, e);
    });

    // 获取焦点的时候激活提示框
    $("#keyword").on("focus", function () {
        keyword_search($("#keyword").val().trim());
    });

    // 每次输入的事件
    $("#keyword").on("input", function () {
        clearTimeout(keyup_timer);
        var keyword = $.trim($("#keyword").val());
        // 延时请求
        keyup_timer = setTimeout(function () {
            keyword_search(keyword);
            console.log("input");
        }, 300);
    });

    keyword_search = function (keyword, callback) {
        games = $.ajax({url:"index.php?act=keyword&keyword="+encodeURIComponent(keyword),async:false});
        games= jQuery.parseJSON(games.responseText);//转换为json对象
        $("ul.keyword-inner").html("");
        $.each(games,function(idx,item){
            if(idx>9){
                return false;
            }
            $("ul.keyword-inner").append('<li><a href="\/game'+item.id+'"><img src="http:\/\/cdn.66173.cn\/'+item.game_icon+'" \/>'+item.game_name+'<\/a><\/li>');
        });
    };
    //$('#gs_game_box').on("click", function (e) {
    //    e.stopPropagation();
    //});
}
function show_search_box(obj,e){
    $('#gs_game_box').show();
    $("#keyword_box").hide();
    $('#searchbar_arrow').show().animate({left:$(obj).position().left+50+"px"},300);
    $("div.fr").on("click", function (e) {
        e.stopPropagation();
    });
    $(document).click(function(e){
        if(!(e.target == $('#gs_game_box') || $.contains($('#gs_game_box'), e.target))) {
            $('#gs_game_box').hide();
            $('#searchbar_arrow').hide();
        }
    });
}
function show_keyword_search(obj,e){
    $('#gs_game_box').hide();
    $('#searchbar_arrow').hide();
    $("#keyword_box").show();

    $("div.fr").on("click", function (e) {
        e.stopPropagation();
    });
    $(document).click(function(e){
        if(!(e.target == $('#keyword_box') || $.contains($('#keyword_box'), e.target))) {
            $('#keyword_box').hide();
        }
    });
}