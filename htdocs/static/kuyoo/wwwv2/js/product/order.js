// ------------------------
// 订单付款 <zbc> 
// ------------------------
$(function(){
	$(".form li .item").click(function(){
		$(this).parent().find(".item").removeClass("cur");
		$(this).addClass("cur");
	})

	$("#pay_other").click(function(){
		if($("input[name='pay_other']").attr("checked")=="checked"){
			$("#li_other").show();
		}else{
			$("#li_other").hide();
		}
	})

	$("#pay_balance").click(function(){
		if($("input[name='pay_balance']").attr("checked")=="checked"){
			$("#span_pay,#span_balance_pay").html($("#span_balance").html());
		}else{
			$("#span_pay,#span_balance_pay").html("0 元");
		}
	})

	// 支付方式
	$('.pay_method_tab li').on('click',function(){
		$pc = parseInt($(this).attr('data-pc'));
		$('#pay_channel').val($pc);
		if($pc!=2||$pc!=5){
			$.z_del_msg($(this));
		}
		$(this).addClass("cur").siblings('li').removeClass("cur");
		$(".pay_method_box").hide();
		$("#"+$(this).attr("lang")).show();
		$("#method_name").html($(this).html()+"：")
	});

	// 银行选择
	$('.banklist img').on('click',function(){
		$checkbox = $(this).parent().prev();
		$bank_code = $checkbox.prop('checked',true);
		$bank_code = $checkbox.attr('data-code');
		$('#bank').val($bank_code);
	});

	// 充值按钮
	$('.btn_pay').on('click',function(){
		// 校验
		$pay_ch = $("#pay_channel");
		$lastch = $('.pay_method_tab li:last');

		// 过滤网银支付代码
		if($pay_ch.val()==2){
			$selected_bank = $('input[name=radio_bank]:checked');
			if($selected_bank.length==0){
				$.z_add_msg($lastch,'请选择网银');
				return false;
			}else{
				// 兼容IE
				$bank_code = $selected_bank.attr('data-code');
				$('#bank').val($bank_code);
			}
		}
		$('#payfrm').attr('action','/product.php?act=topay&id='+$('#pid').val()).submit();
	});


})