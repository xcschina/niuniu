$(function() {
    //关闭头部停运提示
    $("a.close").bind("click",function(){
        $("div.close-tip").hide();
    });

	//去掉a的虚线
	$('a').bind("focus", function(){
	    $(this).blur();
	})

	//悬浮
	var leftpx = parseInt($('#main').outerWidth()) + parseInt($('#main').offset().left) + 25;
	$('#right_option').css({left:leftpx});
	$(window).bind('scroll', function() {
		if($(this).scrollTop() > 200) {
			$('#right_option').show();
		} else {
			$('#right_option').hide();
		}
	});

	//登录下拉栏目
	$('#login').bind('mouseover', function() {
		if($('#login_con').is(':hidden')) {
			$(this).addClass('visited');
			$('#login_con').prev().show(100);
			$('#login_con').slideDown(100);
		}
	});
	$(document).bind('mouseover', function(e) {
		var e = e || window.event;
		var target = $(e.target);
		if(target.closest('.option').length == 0) {
			$('#login').removeClass('visited');
			$('#login_con').prev().hide(100);
			$('#login_con').slideUp(100);
		}

		if(target.closest('h1').length == 0) {
			$('#game_tj').removeClass('visited');
			$('#js_tj').slideUp(100);
		}
	});

	//游戏推荐
	$('#game_tj').bind('mouseover', function() {
		if($('#js_tj').is(':hidden')) {
			if(!$('#js_tj').html()){
				$("#games a").each(function(){
					href = $(this).attr("href");
					game_name = $(this).text();
					rel = $(this).attr("rel");
					$('#js_tj').append('<li class="left"><a href="'+href+'"><img src="http://cdndl.go.cc/images/game/'+rel+'.png" class="left"><span class="left">'+game_name+'</span></a></li>');
				});
			}
			$(this).addClass('visited');
			$('#js_tj').slideDown(100);
		}

	});

	//图片轮播初始化
	//图片位移
	var lunboImg = $('#lunbo').find('img');
	lunboImg.each(function(index) {
		$(this).css({left:(index)* 670 + 'px'});
	});
	//按钮初始化
	var spans = '<span data="0" class="visited"></span>';
	for(var i=1,len=lunboImg.length; i<len; i++) {
		spans += '<span data=' + i + '></span>';
	}
	$('#lunbo_btn').append(spans);
	//绑定点击
	$('#lunbo_btn').find('span').bind('click', function() {

		$('#lunbo_btn span').removeClass('visited');
		$(this).addClass('visited');
		nowImg = $(this).attr('data');
		lunboImg.each(function(index){
			var num = index - nowImg;					
			$(this).animate({left: num * 670 + 'px'});
		});	

		//内容介绍
		$('#adannouce div').first().html($('#lunbo').find('a').eq(nowImg).attr('data'));

		//回归
		if(nowImg == lunboImg.length-1) {										
			nowImg = -1;
		}
	});
	//文字说明初始化
	$('#js_imgtitle').html($('#lunbo').find('a').eq(0).attr('data'));

	//开始轮播
    if(lunboImg.length > 2) {
	    var nowImg = 0;	//当前第几个轮播图
        setInterval(function() {

            //下一张
            nowImg++;
            lunboImg.each(function(index){
                var num = index - nowImg;
                $(this).animate({left: num * 670 + 'px'});
            });

            //内容介绍
            $('#adannouce div').first().html($('#lunbo').find('a').eq(nowImg).attr('data'));
            $('#lunbo_btn span').removeClass('visited');
            $('#lunbo_btn span').eq(nowImg).addClass('visited');

            //回归 初始值
            if(nowImg == lunboImg.length-1) {
                nowImg = -1;
            }

        },6000);
    }


	//回滚到顶部
	$('#js_top').bind('click', function() {
		$('body,html').animate({scrollTop:0}, 500);
	});


	//排行切换
	$('#dtranking').find('b').bind('click', function() {

		//切换class
		$('#dtranking').find('b').removeClass('select');
		$(this).addClass('select');

		//移动小三角
		var em =$('#dtranking').find('em');
		if($(this).index() == 1) {
			em.css({'bottom':'-5px', 'left':'32px'});
            $('.js_ranking #t_djph').show();
            $('.js_ranking #t_zdlph').hide();
		} else {
			em.css({'bottom':'-5px', 'left':'125px'});
            $('.js_ranking #t_djph').hide();
            $('.js_ranking #t_zdlph').show();
		}
	});

	//活动公告切换
	$('#dt_ranking').find('b').bind('click', function() {

		//切换class
		$('#dt_ranking').find('b').removeClass('select');
		$(this).addClass('select');

		//移动小三角
		var em =$('#dt_ranking').find('em');
		if($(this).index() == 1) {
			em.css({'bottom':'-5px', 'left':'32px'});
            $('#t_ggqh').hide();
            $('#t_hdqh').show();
		} else {
			em.css({'bottom':'-5px', 'left':'114px'});
            $('#t_ggqh').show();
            $('#t_hdqh').hide();
		}
	});

	//游戏截图点击轮播
	//初始化图片位置
	$('#yxjtlb').find('img').each(function(index) {
		$(this).css({left: $(this).outerWidth() * index});
	});
	//点击轮播
	var jtimg = 0;
    var alllen = $('#yxjtlb').find('img').length-1;
	$('#aleft').bind('click', function() {
		jtimg--;
		if(jtimg < 0) {
			jtimg = alllen;
		}
		$('#yxjtlb').find('img').each(function(index) {
			var num = index - jtimg;
			$(this).animate({left: $(this).outerWidth() * num},200);
		});
	});
	$('#aright').bind('click', function() {
		jtimg++;
		if(jtimg > alllen) {
			jtimg = alllen;
		}
		$('#yxjtlb').find('img').each(function(index) {
			var num = index - jtimg;
			$(this).animate({left: $(this).outerWidth() * num},200);
		});
		if(jtimg == alllen) {
			jtimg = -1;
		}
	});


	//登录提示控制
	$('#login_con input').bind('focus', function() {
		var str = $(this).val();
		$(this).bind('click', function() {
			$(this).val('');
			
		});

		$(this).bind('keydown', function() {
			$(this).css({color:'#000'});
		});

		$(this).bind('blur', function() {
			if(!$(this).val()) {
				$(this).val(str);
				$(this).css('color', '#A9A9A9');
			}
		});
	});

    $('#login_con input').placeholder();
    if($("#links li").length>1){
        var int=self.setInterval("links()",2000);
    }
});
function links(){
    $("#links ul").animate({"margin-bottom":10+"px" },1500 , function(){
        $("#links li:last").after($("#links li:first"));
        $("#links li:first").fadeIn(500);
    });
}

/* placeholder polyfill plugin */
/*! http://mths.be/placeholder v2.0.7 by @mathias */
(function(f,h,$){var a='placeholder' in h.createElement('input'),d='placeholder' in h.createElement('textarea'),i=$.fn,c=$.valHooks,k,j;if(a&&d){j=i.placeholder=function(){return this};j.input=j.textarea=true}else{j=i.placeholder=function(){var l=this;l.filter((a?'textarea':':input')+'[placeholder]').not('.placeholder').bind({'focus.placeholder':b,'blur.placeholder':e}).data('placeholder-enabled',true).trigger('blur.placeholder');return l};j.input=a;j.textarea=d;k={get:function(m){var l=$(m);return l.data('placeholder-enabled')&&l.hasClass('placeholder')?'':m.value},set:function(m,n){var l=$(m);if(!l.data('placeholder-enabled')){return m.value=n}if(n==''){m.value=n;if(m!=h.activeElement){e.call(m)}}else{if(l.hasClass('placeholder')){b.call(m,true,n)||(m.value=n)}else{m.value=n}}return l}};a||(c.input=k);d||(c.textarea=k);$(function(){$(h).delegate('form','submit.placeholder',function(){var l=$('.placeholder',this).each(b);setTimeout(function(){l.each(e)},10)})});$(f).bind('beforeunload.placeholder',function(){$('.placeholder').each(function(){this.value=''})})}function g(m){var l={},n=/^jQuery\d+$/;$.each(m.attributes,function(p,o){if(o.specified&&!n.test(o.name)){l[o.name]=o.value}});return l}function b(m,n){var l=this,o=$(l);if(l.value==o.attr('placeholder')&&o.hasClass('placeholder')){if(o.data('placeholder-password')){o=o.hide().next().show().attr('id',o.removeAttr('id').data('placeholder-id'));if(m===true){return o[0].value=n}o.focus()}else{l.value='';o.removeClass('placeholder');l==h.activeElement&&l.select()}}}function e(){var q,l=this,p=$(l),m=p,o=this.id;if(l.value==''){if(l.type=='password'){if(!p.data('placeholder-textinput')){try{q=p.clone().attr({type:'text'})}catch(n){q=$('<input>').attr($.extend(g(this),{type:'text'}))}q.removeAttr('name').data({'placeholder-password':true,'placeholder-id':o}).bind('focus.placeholder',b);p.data({'placeholder-textinput':q,'placeholder-id':o}).before(q)}p=p.removeAttr('id').hide().prev().attr('id',o).show()}p.addClass('placeholder');p[0].value=p.attr('placeholder')}else{p.removeClass('placeholder')}}}(this,document,jQuery));
