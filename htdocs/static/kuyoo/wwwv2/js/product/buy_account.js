// ------------------------
// 账号 account <zbc> 
// ------------------------
var $gid = $('#gid').val();
var f_page_fn = function($nowpage){
	search_count_fn($nowpage);
}
// 左侧显示
var init_left_fn = function($dplen){
	$clen = $dplen || $('.game_account_list li').length;
	if($clen > 5){
		$('#weixin').show();
		$('#recommend').show();
	}else if($clen>2 && $clen<4){
		$('#weixin').show();
		$('#recommend').show();
	}else if($clen>0 && $clen<3){
		$('#weixin').show();
		$('#recommend').slideUp('slow');
	}else{
		$('#recommend').slideUp('slow');
		$('#weixin').slideUp('slow');
	}
}
var search_count_fn = function($nowpage){
	$nowpage = $nowpage || 'page=1';
	$url = "/product.php?act=search_count&id="+$gid+"&"+$nowpage;
	$.post($url,$('#buyfrm').serialize(),function(data){
		$accountlist = $('.game_account_list');
		$pagelist = $('#page');
		$accountlist.empty();
		$pagelist.empty();
		$account = ''; // 数据
		$page = ''; // 分页
		$dplen = data.pro.length;
		if($dplen>0){
			// $('#weixin').show('slow');
			// 数据
			$pro = data.pro;
			$url = 'http://shouyou.kuyoo.com/buy/'+$gid+'-account.html';
			for(var $i in $pro){
				$account += '	<li><i class="icon-flag"></i>';
				$account += '<div class="desc">'+$pro[$i]['title']+'</div>';
				$account += '<a arget="blank" href="'+$url+'/'+$pro[$i]['id']+'/'+$pro[$i]['channel_id']+'"><img src="http://static.kuyoo.com/'+$pro[$i]['img']+'" class="img"></a>';
				$account += '<a arget="blank" href="'+$url+'/'+$pro[$i]['id']+'/'+$pro[$i].channel_id+'">'+$pro[$i]['channel_name']+'/'+$pro[$i]['sname']+'</a><br>';
				$account += '售价：<span class="color_red">¥'+$pro[$i]['price']+'</span>';
			}
			// 分页
			$pagelist.append(data.show);
			init_left_fn($dplen);
		}else{
			$('#weixin').slideUp('slow');
			$('#recommend').slideUp('slow');
			$account = '<li><a href="#">没有查询到您需要的商品，请重新选购!</a></li>'
		}
		$accountlist.append($account);
		$pagelist.append($page);
	},'json');	}

$(function(){
	init_left_fn();
	var $pt = $('#choose_pt');
	var $ch = $('#choose_ch');
	var $serv_id = $('#choose_serv');
	var $price   = $('#price_order');

	$('#choose_condition select').on('change',function($nowpage){
		search_count_fn();
	});

	//  -----------------
	//  账号详情页
	//  -----------------
	// 验证表单
	$qq   = $("input[name='qq']");
	$tel  = $("input[name='tel']");

	$tel.on('blur',function(){
		return $.z_is_empty($(this),'请输入手机号！');
	})
	$qq.on('blur',function(){
		return $.z_is_empty($(this),'请输入QQ号！');
	})

	function check_account(){
		$flag = true;
		if(!$tel.triggerHandler('blur') || !$.z_is_tel($tel.val(),$tel)){
			$flag = false;
		}
		if(!$qq.triggerHandler('blur') || !$.z_is_qq($qq.val(),$qq)){
			$flag = false;
		}
		return $flag;
	}

	// 提交表单
	$('#account').on('click',function(){
		if(f_show_login_box()){ return false; }
		if(check_account()){
			$('#buyfrm').attr('action','/product.php?act=order2&id=4').submit();
		}
		return false;
	});


})