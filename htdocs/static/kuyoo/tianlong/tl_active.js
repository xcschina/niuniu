//抽奖js
var lottery={
    index:0,	//当前转动到哪个位置
    count:9,	//总共有多少个位置
    timer:0,	//setTimeout的ID，用clearTimeout清除
    speed:200,	//初始转动速度
    times:0,	//转动次数
    cycle:100,	//转动基本次数：即至少需要转动多少次再进入抽奖环节
    prize:-1,	//中奖位置
    init:function(id){
        if ($("#"+id).find(".lottery-unit").length>0) {
            $lottery = $("#"+id);
            $units = $lottery.find(".lottery-unit");
            this.obj = $lottery;
            this.count = $units.length;
            $lottery.find(".lottery-unit-"+this.index).addClass("active");
        };
    },
    roll:function(){
        var index = this.index;
        var count = this.count;
        var lottery = this.obj;
        $(lottery).find(".lottery-unit-"+index).removeClass("active");
        index += 1;
        if (index>count-1) {
            index = 0;
        };
        $(lottery).find(".lottery-unit-"+index).addClass("active");
        this.index=index;
        return false;
    },
    stop:function(index){
        this.prize=index;
        return false;
    }
};

function roll(){
    lottery.times += 1;
    lottery.roll();
    var prize_site = $("#lottery").attr("prize_site");
    if (lottery.times > lottery.cycle+10 && prize_site==lottery.index) {
        var prize_id = $("#lottery").attr("prize_id");
        var prize_name = $("#lottery").attr("prize_name");
        var gift_code = $("#lottery").attr("gift_code");
        clearTimeout(lottery.timer);
        win_info(prize_site,prize_id,prize_name,gift_code);
        lottery.prize = -1;
        lottery.times = 0;
        click = false;
    }else{
        if (lottery.times<lottery.cycle) {
            lottery.speed -= 10;
        }else if(lottery.times==lottery.cycle) {
            var index = Math.random()*(lottery.count)|0;
            lottery.prize = index;
        }else{
            if (lottery.times > lottery.cycle+10 && ((lottery.prize==0 && lottery.index==7) || lottery.prize==lottery.index+1)) {
                lottery.speed += 110;
            }else{
                lottery.speed += 20;
            }
        }
        if (lottery.speed<40) {
            lottery.speed=40;
        };
        //console.log(lottery.times+'^^^^^^'+lottery.speed+'^^^^^^^'+lottery.prize);
        lottery.timer = setTimeout(roll,lottery.speed);
    }
    return false;
}

var click=false;

window.onload=function(){
    lottery.init('lottery');
    $("#lottery a").click(function(){
        var user_id = $('#user_id').val();
        var phone_num = $('#phone_num').val();
        //判断user_id
        if(!user_id){
            //提示页面
            $('.tc_bg,.login_box').show();
            return false;
        }else if(!phone_num){
            //绑定手机页面
            $('.tc_bg,.phone_box').show();
            return false;
        }
        if(click) {
            return false;
        }else{
            lottery.speed=100;
            $.ajax({
                type:'post',
                url:'/activity.php?act=activity_ajax',
                data:{
                    user_id:user_id
                },
                dataType: 'json',
                success: function (json) {
                    if(json.msg==1){
                        $("#lottery").attr("prize_site", json.prize_site);
                        $("#lottery").attr("prize_id", json.prize_id);
                        $("#lottery").attr("prize_name", json.prize_name);
                        $("#lottery").attr("gift_code", json.gift_code);
                        roll();
                        click=true;
                    }else{
                        alert("网络请求错误,刷新后重新抽奖.");
                    }

                },
                error:function(){
                }
            });
            return false;
        }
    });
};

//右侧浮动
$(function() {
    var fixE = $("#float");
    $(window).bind('scroll.fixed', function() {
        if ($(window).scrollTop() >=550) {
            fixE.removeClass("float").addClass('fixed');
        } else {
            fixE.removeClass("fixed").addClass('float');
        }
    }).trigger('scroll.fixed');
});

//中奖浮动信息
jQuery(".win_order").slide({mainCell:"ul",autoPlay:true,effect:"topMarquee",vis:7,interTime:50,trigger:"click"});

//弹窗相关


//登录验证
function login_verify(){
    var mobile=$('#login_mobile').val();
    var password=$('#login_password').val();
    $.ajax({
        type:'post',
        url:'/account.php?act=ky_login',
        data:{
            mobile:mobile,
            password:password
        },
        dataType: 'json',
        success: function (json) {
            if(json.msg==2){
                f_hide_login_box();
                window.location.href='/activity.php';
            }else{
                alert(json.desc);
            }
        },
        error:function(){
        }
    });
}

//点击获取验证码按钮
$("#send_code").bind("click",function(){
    var mobile = $("#b_mobile").val().trim();
    if(!mobile){
        alert("手机号不能为空");
        return;
    }
    if(mobile.length != 11){
        alert("手机格式化错误");
        return ;
    }
    var _th=$(this);
    if(_th.attr("rel")=="0"){
        return ;
    }
    _th.css("background","#a5a5a5");
    var second = parseInt(179);//倒计时总秒数量
    time=window.setInterval(function(){
        _th.attr("rel","0");
        //$('#sec').html(second);
        second--;
        if(second<1){
            window.clearTimeout(time);
            _th.css("background","#fe7241");
            _th.attr("rel","1");
            _th.html("重新发送");
            //_th.html("获取验证码");
        }else{
            _th.css("background","#999");
            _th.attr("rel","0");
            _th.html(second+"秒");
        }
    }, 1000);

    $.ajax({
        type:'post',
        url:'account.php?act=register_sms_ky',
        data:{
            mobile:$("input[name=mobile]").val().trim()
        },
        dataType: 'json',
        success: function (json) {
            if(json.res==0){
                window.clearTimeout(time);
                alert(json.msg);
                _th.css("background","#999");
            }
        }
    })
});

function qq_login_phone_bind(){
    var pass = $("#b_password").val().trim();
    var cpass = $("#b_cpassword").val().trim();
    var sms_code = $("#b_sms_code").val().trim();
    var mobile = $("#b_mobile").val().trim();
    if(!mobile){
        alert("手机号不能为空");
        return;
    }
    if(mobile.length != 11){
        alert("手机格式化错误");
        return ;
    }
    if(pass.length < 6 || pass.length > 18){
        alert("密码为字母和数字，支持大小写，长度6-18个字符");
        return ;
    }
    if(pass!=cpass){
        alert("两次密码不一致");
        return;
    }
    if(!sms_code){
        alert("短信验证码不能为空");
        return;
    }
    $.ajax({
        type:'post',
        url:'/account.php?act=ajax_qqlogin_phone_bind',
        data:{
            mobile: mobile,
            sms_code: sms_code,
            password: pass,
            cpassword: cpass
        },
        dataType: 'json',
        success: function (json) {
            if(json.msg==2){
                phone_box_hide();
                window.location.href='/activity.php';
            }else{
                alert(json.desc);
            }
        }
    })
}
function get_gifts(){
    var user_id = $('#user_id').val();
    var gift_id = 257;
    //判断user_id
    if(!user_id){
        //登录页面
        $('.tc_bg,.login_box').show();
        return false;
    }
    $.ajax({
        type:'post',
        url:'/activity.php?act=ajax_get_gift',
        data:{
            user_id: user_id,
            gift_id: gift_id
        },
        dataType: 'json',
        success: function (json) {
            if(json.msg==1){
                alert(json.desc);
            }else{
                alert(json.desc);
            }
        }
    })
}

function my_gifts(){
    var user_id = $('#user_id').val();
    var gift_id = 257;
    //判断user_id
    if(!user_id){
        //登录页面
        $('.tc_bg,.login_box').show();
        return false;
    }
    $.ajax({
        type:'post',
        url:'/activity.php?act=get_my_gift',
        data:{
            user_id: user_id,
            gift_id: gift_id
        },
        dataType: 'json',
        success: function (json) {
            if(json.msg==1){
                alert(json.desc);
            }else{
                alert(json.desc);
            }
        }
    })
}


function receive_coupon(id){
    var user_id = $('#user_id').val();
    //判断user_id
    if(!user_id){
        //登录页面
        $('.tc_bg,.login_box').show();
        return false;
    }
    $.ajax({
        type:'post',
        url:'/activity.php?act=ajax_get_coupon',
        data:{
            user_id: user_id,
            btn_id: id
        },
        dataType: 'json',
        success: function (json) {
            if(json.msg==1){
                alert(json.desc);
            }else{
                alert(json.desc);
            }
        }
    })
}