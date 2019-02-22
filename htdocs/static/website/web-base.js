var Web = new function(){
    var Base = { code:"web_" }, F = this, P = {
        Page:{
            Scroll:{ parentClass:".isc_parent", childClass:".isc_con", headerClass:".isc_header", onMove:[], onMoveEnd:[], onRefresh:[] },
            Functions:{ Init:[], OrientatiChange:[] },
            Links:["link"]
        },
        BaseFloat:{ margin:100, fbgId:"float_bg", scrollClName:"float_scroll", startIndex:1 }
    }, T = this;
    this.Par = P;

    //获取URL参数
    this.GetUrlPar = function(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)"), r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    };

    //添加页面加载相关函数(加载函数，横竖屏切换函数，滚屏函数)
    this.AddInitFun = function(fun){ if(fun){ P.Page.Functions.Init.push(fun); } };
    this.AddOriFun = function(fun){ if(fun){ P.Page.Functions.OrientatiChange.push(fun); } };
    this.AddScrollEvent = function(D){
        if(D.onMove){ P.Page.Scroll.onMove.push(D.onMove); }
        if(D.onMoveEnd){ P.Page.Scroll.onMoveEnd.push(D.onMoveEnd); }
        if(D.onRefresh){ P.Page.Scroll.onRefresh.push(D.onRefresh); }
    };
    //循环输出对象属性（调试用）
    this.$ObjValue = function(obj){
        var info = "";
        for(var s in obj) info += s.toString() + "：" + obj[s].toString() + "\n";
        return info;
    };

    //处理移动WEB端滚动优化
    function DoMobilePageScroll(){
        var d = P.Page.Scroll, header = $(d.headerClass).eq(0), parent = $(d.parentClass).eq(0), scr = null, id = "scroll_parent", doc = $(document);
        parent.attr("id", Base.code + id);
        scr = new iScroll(Base.code + id, { hideScrollbar:true, fadeScrollbar:true, useTransition:false, checkDOMChanges:false, onRefresh:function(){
            var obj = this;
            if(P.Page.Scroll.onRefresh.length > 0){$.each(P.Page.Scroll.onRefresh, function(i,d){ d(obj); });}
        }, onScrollMove:function(){
            var obj = this;
            if(P.Page.Scroll.onMove.length > 0){$.each(P.Page.Scroll.onMove, function(i,d){ d(obj); });}
        }, onScrollEnd:function(){
            var obj = this;
            if(P.Page.Scroll.onMoveEnd.length > 0){$.each(P.Page.Scroll.onMoveEnd, function(i,d){ d(obj); });}
        }});
        function DoChange(){
            setTimeout(function(){
                parent.css({"height":(doc.height() - header.height()) + "px", "top":header.height() + "px"});
                scr.refresh();
            },500);
        }DoChange();
        P.Page.Functions.OrientatiChange.push(DoChange);
        DoMobilePageScroll.Refresh = function(){ scr.refresh(); };
        DoMobilePageScroll.ScrollToElement = function(el, time){ scr.scrollToElement(el, time); };
        DoMobilePageScroll.ScrollTo = function(x,y,time,relative){ scr.scrollTo(x,y,time,relative); };
        DoMobilePageScroll.Par = function(){ return { X:scr.x, Y:scr.y, maxScrollY:scr.maxScrollY, maxScrollX:scr.maxScrollX }; }
    }
    this.ScrollTo = function(x,y,time,relative){ if(DoMobilePageScroll.ScrollTo){ DoMobilePageScroll.ScrollTo(x,y,time,relative); } };
    this.ScrollToElement = function(el, time){ if(DoMobilePageScroll.ScrollToElement){ DoMobilePageScroll.ScrollToElement(el, time); } };
    this.ScrollPar = function(){ return (DoMobilePageScroll.Par) ? DoMobilePageScroll.Par() : null;  }

    //处理移动WEB端链接优化
    function DoMobilePageLink(){
        var isMobile = ("onorientationchange" in window), hoverClassName = "link_hover", normalClassName = "link";
        function InitPageLink(clName){
            var _links = [], _this;
            function BeginTouchWithClass(){
                _this = $(this);
                if(!_this.hasClass(hoverClassName)){ _this.removeClass(normalClassName); _this.addClass(hoverClassName); }
            }
            function EndTouchWithClass(){
                _this = $(this);
                if(_this.hasClass(hoverClassName)){ _this.removeClass(hoverClassName); _this.addClass(normalClassName) }
            }
            $.each($("." + clName), function(i, d){ _links.push($(d)); });
            $.each(_links, function(i,d){
                if(d.attr("class") && d.attr("class").length > 0){
                    d.unbind();
                    if(isMobile){
                        d.bind("touchstart", BeginTouchWithClass);
                        d.bind("touchend", EndTouchWithClass);
                    }else{
                        d.bind("mousedown", BeginTouchWithClass);
                        d.bind("mouseup", EndTouchWithClass);
                    }
                }
            });
        }

        function Refresh(){ $.each(P.Page.Links, function(i,d){ InitPageLink(d); }); }
        Refresh();
        DoMobilePageLink.Refresh = Refresh;
    }
    this.RefrePageLink = function(){ if(DoMobilePageLink.Refresh){ DoMobilePageLink.Refresh(); }else{ DoMobilePageLink(); } };

    //公共类弹窗初始化
    this.InfoWin = function(D){
        return new function(){
            var Txt = $("#float_tip_info"), Title = $("#float_tip_title"),  closeBtn = $("#btn_float_tip_cls"), fwin = new T.BaseFloatWithBG({ floatCon:$("#float_tip"), closeBtn:closeBtn }), confirmBtn = $("#btn_float_tip_confirm"), timer, t = this;
            confirmBtn.click(function(){ if(D && D.onConfirm){ D.onConfirm(t); } });
            this.Open = function(d){
                Title.html((d.title) ? d.title : "提示");
                Txt.html((d.info) ? d.info : "");
                fwin.Open();
                t.IsOpen = true;
                if(D && D.onOpen){ D.onOpen(t); }
            };
            this.Close = function(){
                fwin.Close();
                t.IsOpen = false;
                if(D && D.onClose){ D.onClose(t); }
            };
            this.El = { float:fwin, title:Title, confirmBtn:confirmBtn, closeBtn:closeBtn, infoCon:Txt };
        };
    };

    //处理当前页面刷新
    this.PageReload = function(){
        var href = window.location.href;
        window.location.href = href;
    }

    //基类弹窗控制
    this.BaseFloatWithBG = function(flt){
        var fbg = $("#" + P.BaseFloat.fbgId), fcon = $(flt.floatCon), cbtn = $(flt.closeBtn), margin = (flt.margin) ? flt.margin : P.BaseFloat.margin, doc = $(document), wd, incon = fcon.find("." + P.BaseFloat.scrollClName), scr = null, Ft = this, doct = document.documentElement, height = 0;
        $(incon).attr("id", Base.code + "float_" + P.BaseFloat.startIndex);
        this.El = { float:fcon, fbg:fbg };
        scr = (incon.length < 1) ? null : new iScroll(Base.code + "float_" + P.BaseFloat.startIndex++, { hideScrollbar:true, fadeScrollbar:true });
        function init(){
            wd = doc.width() - margin;
            fcon.width(wd);
            if(incon && incon.height() > (doc.height() - margin)){ incon.css({"height":(doc.height() - margin) + "px"}); };
            if(incon && height < incon.height()){ incon.css({"height":height + "px"}); }
            fcon.css({"margin-left": "-" + (Math.floor(wd/2)) + "px", "top":"50%", "margin-top":"-" + (Math.floor(incon.height() / 2 + 38)) + "px"});
            fbg.css("height", "100%");
            if(scr){ scr.refresh(); }
        }init();
        this.Open = function(callback){
            $.each($("input"), function(i,d){ $(d).blur(); });
            fbg.css("display", "block");
            fcon.css("display", "block");
            height = (incon.height() > height) ? doc.height() : height;
            if(incon && incon.height() > (doc.height() - margin)){ incon.css({"height":(doc.height() - margin) + "px"}); }
            if(incon && height < incon.height()){ incon.css({"height":height + "px"}); }
            if(callback){ callback(); }
            if(scr){ setTimeout(function(){scr.refresh();},500); }
        };
        this.Close = function(callback){
            fbg.css("display", "none"); fcon.css("display", "none");
            if(callback){ callback(); }
        };
        if(incon && incon.height() > (doc.height() - margin)){ incon.css({"height":(doc.height() - margin) + "px"}); }
        if(cbtn){cbtn.bind("click", function(){ Ft.Close(); })};
        T.AddOriFun(function(){ setTimeout(init, 500); });
    };

    //页面加载完成后统一执行函数
    this.BaseInit = function(callback){
        function DoOrientationChange(){
            if(P.Page.Functions.OrientatiChange.length > 0){
                for(var i = 0; i < P.Page.Functions.OrientatiChange.length; i++){ P.Page.Functions.OrientatiChange[i](); }
            }
        }
        $(document).ready(function(e){
            //DoMobilePageScroll();
            T.RefrePageLink();
            window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", DoOrientationChange, false);
            if(P.Page.Functions.Init.length > 0){
                for(var i = 0; i < P.Page.Functions.Init.length; i++){ P.Page.Functions.Init[i](); }
            }
            if(callback){ callback(); }
        });
    };

    //处理页面滚动刷新
    this.DoScrollRefresh = function(){ try{ setTimeout(DoMobilePageScroll.Refresh, 500); }catch(e){} };
};