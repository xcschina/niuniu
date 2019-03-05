// ------------------------
// 游戏币 <zbc> 
// ------------------------

// 选择平台时，初始化
var init_choose_pt_fn = function(){
	$('#choose_ch').empty().append('<option value="">选择渠道</option>').prop('disabled',true);
	$('#choose_serv').empty().append('<option value="">选择区服</option>').prop('disabled',true);
}

$gid = $('#gid').val();
$(function(){
	$('#weixin').show();
	// 商品细分
	$('#game_unit').on('change',function(){
		search_coin_fn('page=1');
	});

	// 平台选择
	$('#choose_pt').on('change',function(){
		init_choose_pt_fn();
		$pt_id = parseInt($(this).val());
		$choose_ch = $('#choose_ch');
		$url = "/product.php?act=search_coin&ftype=pt_id";
		$.get($url,{id:$pt_id,gid:$gid},function(data){
			$choose_ch.empty();
			$option = '';
			if(data.length>0){
				$option = '<option value="0">选择渠道</option>';
				for(var $i in data){
					$option += '<option value="'+data[$i]['ch_id']+'">'+data[$i]['ch_name']+'</option>';
				}
				$choose_ch.append($option).prop('disabled',false);
			}else{
				$option = '<option value="">暂无渠道</option>';
				$choose_ch.append($option).prop('disabled',true);
			}
		},'json');
		search_coin_fn('page=1');
	});

	// 渠道选择
	$('#choose_ch').on('change',function(){
		$ch_id = parseInt($(this).val());
		$choose_serv = $('#choose_serv');
		$url = "/product.php?act=search_coin&ftype=ch_id";
		$.get($url,{id:$ch_id,gid:$gid},function(data){
			$choose_serv.empty();
			$option = '';
			if(data.length>0){
				$option = '<option value="">选择区服</option>';
				for(var $i in data){
					$option += '<option value="'+data[$i]['serv_id']+'">'+data[$i]['serv_name']+'</option>';
				}
				$choose_serv.append($option).prop('disabled',false);
			}else{
				$option = '<option value="">暂无区服</option>';
				$choose_serv.append($option).prop('disabled',true);
			}
		},'json');
		search_coin_fn('page=1');
	});

	// 区服选择 / 价格选择 => 查询
	$('#choose_serv,#price_order').on('change',function(){
		search_coin_fn('page=1');
	});
})


// 查询后的游戏币列表
var search_coin_fn = function($nowpage){
	$url = "/product.php?act=search_coin&id=100&ftype=do&gid="+$gid+"&"+$nowpage;
	$.post($url,$('#buyfrm').serialize(),function(data){
		$coinlist = $('.game_money_list');
		$pagelist = $('#page');
		$coinlist.empty();
		$pagelist.empty();
		$coin = ''; // 数据
		$page = ''; // 分页
		if(data.pro){
			$pro = data.pro;
			if($pro.length>3){
				$('#weixin').slideDown('slow');
			}else{
				$('#weixin').slideUp('slow');
			}
			$url = 'http://shouyou.kuyoo.com/buy/'+$gid+'-coin.html';
			for(var $i in $pro){
				if(parseInt($pro[$i]['disprice'])>0){ // 过滤0元错误商品
					$coin += '	<li>';
					$coin += '<span class="r_price color_red">'+$pro[$i]['disprice']+'元</span>';
					$coin += '<a target="_blank" rel="noopener noreferrer" href="'+$url+'/'+$pro[$i]['product_id']+'/'+$pro[$i]['channel_id']+'">'+$pro[$i]['title']+' </a>';
					$coin += '<div class="small_word">虚拟游戏币/'+$pro[$i]['serv_name']+'</div>	'; 
					$coin += '</li>';
				}
			}
			// 分页
			$pagelist.append(data.show);
		}else{
			$('#weixin').slideUp('slow');
			$coin = '<li><a href="#">没有查询到您需要的商品，请重新选购!</a></li>'
		}
		$coinlist.append($coin);
		$pagelist.append($page);
	},'json');
}

var f_page_fn = function($nowpage){
	search_coin_fn($nowpage);
}