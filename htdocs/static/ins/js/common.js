var timer = null;
var loading = false;
var isCompleted = false;

var browser = {
    versions: function () {
        var u = navigator.userAgent, app = navigator.appVersion;
        return {         //移动终端浏览器版本信息
            trident: u.indexOf('Trident') > -1, //IE内核
            presto: u.indexOf('Presto') > -1, //opera内核
            webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
            gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
            mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
            iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
        };
    }(),
    language: (navigator.browserLanguage || navigator.language).toLowerCase()
};

if(browser.versions.mobile && browser.versions.android){
    document.querySelector("#viewport").content = "width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=0,target-densitydpi=device-dpi";
}

var ajax = function(url, options){
    options = options || {};
    options.url = url || "";
    options.method = options.method || "get";
    options.args = options.args || null;
    options.beforeSend = options.beforeSend || function(){};
    options.sending = options.sending || function(){};
    options.success = options.success || function(result){};
    options.complete = options.complete || function(){}
    options.error = options.error || function(){};
    options._is_sending = false;

    if(options.url == ""){
        ajax.options = options;
        return ajax;
    }

    var request = new XMLHttpRequest();

    if (!request) {
        options.error();
    } else {
        request.onreadystatechange = function () {
            //0 请求初始化
            //1 服务器连接已建立
            //2 请求已接受
            //3 请求处理中
            //4 请求完成
            if(request.readyState == 0 || request.readyState == 1){
                options.beforeSend();
            } else if(request.readyState == 2 || request.readyState == 3){
                if(!options._is_sending){
                    options.sending();
                    options._is_sending = true;
                }
            }else if (request.readyState == 4) {
                options._is_sending = false;
                if (request.status == 200) {
                    options.success(request.responseText);
                } else {
                    options.error();
                }
            }
        };

        request.open(options.method, url, true);
        if (options.args) {
            request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }

        request.send(options.args);
    }

    ajax.options = options;
    return ajax;
}

var completed = function(){
    if (!isCompleted) {
        isCompleted = true;
        document.removeEventListener("DOMContentLoaded", function () {
        }, false);
        window.removeEventListener("load", function () {
        }, false);
        if(typeof completed_callack == "function"){
            completed_callack();
        }
    }
};

if (document.readyState === "complete") {
    // Handle it asynchronously to allow scripts the opportunity to delay ready
    completed();
} else {
    // Use the handy event callback
    document.addEventListener("DOMContentLoaded", completed, false);
    // A fallback to window.onload, that will always work
    window.addEventListener("load", completed, false);
}


function base64_encode(str){
    var str = CryptoJS.enc.Utf8.parse(str);
    return CryptoJS.enc.Base64.stringify(str);
}
function base64_decode(str){
    var words  = CryptoJS.enc.Base64.parse(str);
    return words.toString(CryptoJS.enc.Utf8);
}

function go_qq(qq){
    var click_time = new Date();
    if(browser.versions.android){
        window.location = 'mqqwpa://im/chat?chat_type=wpa&uin='+qq;
    }else{
        setTimeout(function(){
            if(new Date() - click_time < 1000){
                window.location = 'https://itunes.apple.com/cn/app/qq/id444934666?mt=8';
            }
        }, 500);
        window.location = 'mqqwpa://im/chat?chat_type=crm&uin='+qq+'&version=1&src_type=web&web_src=http:://api';
    }
}