<?php
header("Content-Type: text/html; charset=utf-8");
ini_set("display_errors","on");
require_once("config.php");
COMMON("loginCheck");
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>后台管理中心</title>
    <!-- bootstrap - css -->
    <link href="http://static.66173.cn/console/BJUI/themes/css/bootstrap.css" rel="stylesheet">
    <!-- core - css -->
    <link href="http://static.66173.cn/console/BJUI/themes/css/style.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/themes/blue/core.css" id="bjui-link-theme" rel="stylesheet">
    <!-- plug - css -->
    <!--<link href="http://static.66173.cn/console/BJUI/plugins/kindeditor_4.1.10/themes/default/default.css" rel="stylesheet">-->
    <link href="kindeditor/themes/default/default.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/plugins/colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/plugins/niceValidator/jquery.validator.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/plugins/bootstrapSelect/bootstrap-select.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/themes/css/FA/css/font-awesome.min.css" rel="stylesheet">
    <link href="http://static.66173.cn/console/BJUI/themes/css/chosen.min.css" rel="stylesheet">
    <!--[if lte IE 7]>
    <link href="http://static.66173.cn/console/BJUI/themes/css/ie7.css" rel="stylesheet">
    <![endif]-->
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lte IE 9]>
    <script src="http://static.66173.cn/console/BJUI/other/html5shiv.min.js"></script>
    <script src="http://static.66173.cn/console/BJUI/other/respond.min.js"></script>
    <![endif]-->
    <!-- jquery -->
    <script src="http://static.66173.cn/console/BJUI/js/jquery-1.7.2.min.js"></script>
    <script src="http://static.66173.cn/console/BJUI/js/jquery.cookie.js"></script>
    <!--[if lte IE 9]>
    <script src="http://static.66173.cn/console/BJUI/other/jquery.iframe-transport.js"></script>
    <![endif]-->
    <!-- BJUI.all 分模块压缩版 -->
    <script src="http://static.66173.cn/console/BJUI/js/bjui-all.js"></script>
    <!-- plugins -->
    <!-- swfupload for uploadify && kindeditor -->
    <script src="http://static.66173.cn/console/BJUI/plugins/swfupload/swfupload.js"></script>
    <!-- kindeditor -->
    <script src="kindeditor/kindeditor-all.min.js"></script>
    <script src="kindeditor/lang/zh_CN.js"></script>
    <!-- colorpicker -->
    <script src="http://static.66173.cn/console/BJUI/plugins/colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <!-- ztree -->
    <script src="http://static.66173.cn/console/BJUI/plugins/ztree/jquery.ztree.all-3.5.js"></script>
    <!-- nice validate -->
    <script src="http://static.66173.cn/console/BJUI/plugins/niceValidator/jquery.validator.js"></script>
    <script src="http://static.66173.cn/console/BJUI/plugins/niceValidator/jquery.validator.themes.js"></script>
    <!-- bootstrap plugins -->
    <script src="http://static.66173.cn/console/BJUI/plugins/bootstrap.min.js"></script>
    <script src="http://static.66173.cn/console/BJUI/plugins/bootstrapSelect/bootstrap-select.min.js"></script>
    <!-- icheck -->
    <script src="http://static.66173.cn/console/BJUI/plugins/icheck/icheck.min.js"></script>
    <!-- dragsort -->
    <script src="http://static.66173.cn/console/BJUI/plugins/dragsort/jquery.dragsort-0.5.1.min.js"></script>
    <!-- HighCharts -->
    <script src="http://static.66173.cn/console/BJUI/plugins/highcharts/highcharts.js"></script>
    <script src="http://static.66173.cn/console/BJUI/plugins/highcharts/highcharts-3d.js"></script>
    <script src="http://static.66173.cn/console/BJUI/plugins/highcharts/themes/gray.js"></script>
    <!-- ECharts -->
    <script src="http://static.66173.cn/console/BJUI/plugins/echarts/echarts.js"></script>
    <!-- other plugins -->
    <script src="http://static.66173.cn/console/BJUI/plugins/other/jquery.autosize.js"></script>
    <link href="http://static.66173.cn/console/BJUI/plugins/uploadify/css/uploadify.css" rel="stylesheet">
    <script src="http://static.66173.cn/console/BJUI/plugins/uploadify/scripts/jquery.uploadify.min.js"></script>

    <script src="http://static.66173.cn/console/js/chosen.jquery.min.js"></script>
    <!-- init -->
    <script type="text/javascript">
        $(function() {
            BJUI.init({
                JSPATH       : 'http://static.66173.cn/console/BJUI/',         //[可选]框架路径
                PLUGINPATH   : 'http://static.66173.cn/console/BJUI/plugins/',
                //loginInfo    : {url:'login_timeout.html', title:'登录', width:400, height:200}, // 会话超时后弹出登录对话框
                statusCode   : {ok:200, error:300, timeout:301}, //[可选]
                ajaxTimeout  : 1000000, //[可选]全局Ajax请求超时时间(毫秒)
                alertTimeout : 3000,  //[可选]信息提示[info/correct]自动关闭延时(毫秒)
                pageInfo     : {pageCurrent:'pageCurrent', pageSize:'pageSize', orderField:'orderField', orderDirection:'orderDirection'}, //[可选]分页参数
                keys         : {statusCode:'statusCode', message:'message'}, //[可选]
                ui           : {
                    showSlidebar     : true, //[可选]左侧导航栏锁定/隐藏
                    clientPaging     : true, //[可选]是否在客户端响应分页及排序参数
                    overwriteHomeTab : false //[可选]当打开一个未定义id的navtab时，是否可以覆盖主navtab(我的主页)
                },
                debug        : true,    // [可选]调试模式 [true|false，默认false]
                theme        : 'blue' // 若有Cookie['bjui_theme'],优先选择Cookie['bjui_theme']。皮肤[五种皮肤:default, orange, purple, blue, red, green]
            })
            //时钟
            var today = new Date(), time = today.getTime()
            $('#bjui-date').html(today.formatDate('yyyy/MM/dd'))
            setInterval(function() {
                today = new Date(today.setSeconds(today.getSeconds() + 1))
                $('#bjui-clock').html(today.formatDate('HH:mm:ss'))
            }, 1000)
        })

        //菜单-事件
        function MainMenuClick(event, treeId, treeNode) {
            if (treeNode.isParent) {
                var zTree = $.fn.zTree.getZTreeObj(treeId)

                zTree.expandNode(treeNode)
                return
            }

            if (treeNode.target && treeNode.target == 'dialog')
                $(event.target).dialog({id:treeNode.tabid, url:treeNode.url, title:treeNode.name})
            else
                $(event.target).navtab({id:treeNode.tabid, url:treeNode.url, title:treeNode.name, fresh:treeNode.fresh, external:treeNode.external})
            event.preventDefault()
        }
    </script>
</head>
<body>
<!--[if lte IE 7]>
<div id="errorie"><div>您还在使用老掉牙的IE，正常使用系统前请升级您的浏览器到 IE8以上版本 <a target="_blank" href="http://windows.microsoft.com/zh-cn/internet-explorer/ie-8-worldwide-languages">点击升级</a>&nbsp;&nbsp;强烈建议您更改换浏览器：<a href="http://down.tech.sina.com.cn/content/40975.html" target="_blank">谷歌 Chrome</a></div></div>
<![endif]-->
<header id="bjui-header">
    <!--<div class="bjui-navbar-header" style="height: 80px;width: 80px;background: url(static/css/images/logo.png)no-repeat 0 center;background-size:80px;margin:10px" ></div>-->
    <span style="font-size: 1.5em; color: #fff; height:58px; line-height:58px;margin-left: 10px">后台管理中心</span>
    <nav id="bjui-navbar-collapse">
        <ul class="bjui-navbar-right">
            <?php if($_SESSION['last_ip']){?><li>
                <a style="color: #fff;">您上次登录的IP：<?php echo $_SESSION['last_ip']?></a></h5></li>
            <?php }?>
            <!-- <li class="datetime"><div><span id="bjui-date"></span><br><i class="fa fa-clock-o"></i> <span id="bjui-clock"></span></div></li>-->
            <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">我的账户<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="system_password.php?act=password" data-toggle="dialog" data-id="changepwd_page" data-mask="true" data-width="600" data-height="160">&nbsp;<span class="glyphicon glyphicon-lock"></span> 修改密码&nbsp;</a></li>
                    <!--<li><a href="my_information.php?act=information" data-toggle="dialog" data-id="changepwd_page" data-mask="true" data-width="400" data-height="300">&nbsp;<span class="glyphicon glyphicon-user"></span> 我的资料</a></li>-->
                    <li class="divider"></li>
                    <li><a href="login.php?act=do_logout" class="red">&nbsp;<span class="glyphicon glyphicon-off"></span> 注销登陆</a></li>
                </ul>
            </li>
            <li class="dropdown"><a href="#" class="dropdown-toggle theme blue" data-toggle="dropdown"><i class="fa fa-tree"></i></a>
                <ul class="dropdown-menu" role="menu" id="bjui-themes">
                    <li><a href="javascript:;" class="theme_default" data-toggle="theme" data-theme="default">&nbsp;<i class="fa fa-tree"></i> 黑白分明&nbsp;&nbsp;</a></li>
                    <li><a href="javascript:;" class="theme_blue" data-toggle="theme" data-theme="blue">&nbsp;<i class="fa fa-tree"></i> 青出于蓝</a></li>
                    <!--<li><a href="javascript:;" class="theme_orange" data-toggle="theme" data-theme="orange">&nbsp;<i class="fa fa-tree"></i> 橘子红了</a></li>
                    <li><a href="javascript:;" class="theme_purple" data-toggle="theme" data-theme="purple">&nbsp;<i class="fa fa-tree"></i> 紫罗兰</a></li>
                    <li><a href="javascript:;" class="theme_red" data-toggle="theme" data-theme="red">&nbsp;<i class="fa fa-tree"></i> 红红火火</a></li>
                    <li><a href="javascript:;" class="theme_green" data-toggle="theme" data-theme="green">&nbsp;<i class="fa fa-tree"></i> 绿草如茵</a></li>-->
                </ul>
            </li>
        </ul>
    </nav>
</header>
<div id="bjui-hnav">
    <button type="button" class="bjui-hnav-toggle btn-default" data-toggle="collapse" data-target="#bjui-hnav-navbar">
        <i class="fa fa-bars"></i>
    </button>
    <ul id="bjui-hnav-navbar">
        <?php foreach($_SESSION['menu_list'] as $menu){?>
            <li><a href="javascript:;" data-toggle="slidebar"><i class="<?php echo $menu['class']?>"></i><?php echo $menu['name']?></a>
                <ul id="bjui-hnav-tree1" class="ztree ztree_main" data-toggle="ztree" data-on-click="MainMenuClick" data-expand-all="false" data-noinit="true">
                    <?php foreach($menu['p_menu'] as $p_menu) { ?>
                        <li data-id="<?php echo $p_menu['id'] ?>" data-pid="<?php echo $p_menu['pid'] ?>" data-url="<?php  echo $p_menu['url'] ?>" data-tabid="<?php echo $p_menu['tabid'] ?>"><?php echo $p_menu['name']?></li>
                        <?php foreach($p_menu['c_menu'] as $c_menu) { ?>
                            <li data-id="<?php echo $c_menu['id'] ?>" data-pid="<?php echo $c_menu['pid'] ?>" data-url="<?php echo $c_menu['url'] ?>" data-tabid="<?php echo $c_menu['tabid'] ?>"><?php echo $c_menu['name']?></li>
                        <?php }?>
                    <?php }?>
                </ul>
            </li>
        <?php }?>
    </ul>
</div>
<div id="bjui-container" class="clearfix">
    <div id="bjui-leftside">
        <div id="bjui-sidebar-s">
            <div class="collapse"></div>
        </div>
        <div id="bjui-sidebar">
            <div class="toggleCollapse"><h2><i class="fa fa-bars"></i> 导航栏 <i class="fa fa-bars"></i></h2><a href="javascript:;" class="lock"><i class="fa fa-lock"></i></a></div>
            <div class="panel-group panel-main" data-toggle="accordion" id="bjui-accordionmenu" data-heightbox="#bjui-sidebar" data-offsety="26">
                <div class="panel panel-default">
                    <div class="panel-heading panelContent">
                        <?php foreach($_SESSION['menu_list'] as $menu){?>
                        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#bjui-accordionmenu" href="#bjui-collapse0" class="active"><i class="fa fa-caret-square-o-down"></i>&nbsp;<?php echo $menu['name']?></a></h4>
                       <?php break; }?>
                    </div>
                    <div id="bjui-collapse0" class="panel-collapse panelContent collapse in">
                        <div class="panel-body" >
                            <?php foreach($_SESSION['menu_list'] as $menu){?>
                                <ul id="bjui-tree0" class="ztree ztree_main" data-toggle="ztree" data-on-click="MainMenuClick" data-expand-all="false">
                                    <?php foreach($menu['p_menu'] as $p_menu) { ?>
                                        <li data-id="<?php echo $p_menu['id'] ?>" data-pid="<?php echo $p_menu['pid'] ?>" data-url="<?php  echo $p_menu['url'] ?>" data-tabid="<?php echo $p_menu['tabid'] ?>"><?php echo $p_menu['name']?></li>
                                        <?php foreach($p_menu['c_menu'] as $c_menu) { ?>
                                            <li data-id="<?php echo $c_menu['id'] ?>" data-pid="<?php echo $c_menu['pid'] ?>" data-url="<?php echo $c_menu['url'] ?>" data-tabid="<?php echo $c_menu['tabid'] ?>"><?php echo $c_menu['name']?></li>
                                        <?php }?>
                                    <?php }?>
                                </ul>
                                <?php break; }?>
                        </div>
                    </div>
                    <div class="panelFooter"><div class="panelFooterContent"></div></div>
                </div>
            </div>
        </div>
    </div>
    <div id="bjui-navtab" class="tabsPage">
        <div class="tabsPageHeader">
            <div class="tabsPageHeaderContent">
                <ul class="navtab-tab nav nav-tabs">
                    <li><a href="javascript:;"><span><i class="fa fa-home"></i> #maintab#</span></a></li>
                </ul>
            </div>
            <div class="tabsLeft"><i class="fa fa-angle-double-left"></i></div>
            <div class="tabsRight"><i class="fa fa-angle-double-right"></i></div>
            <div class="tabsMore"><i class="fa fa-angle-double-down"></i></div>
        </div>
        <ul class="tabsMoreList">
            <li><a href="javascript:;">#maintab#</a></li>
        </ul>
        <div class="navtab-panel tabsPageContent">
            <div class="navtabPage unitBox">
                <div class="bjui-pageHeader" style="background:#FFF;">
                    <div style="padding: 0 5px;">
                        <div class="alert alert-success" role="alert" style="margin:0 0 5px; padding:5px 15px 0;">
                            <strong>欢迎使用后台管理中心！</strong>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><h3 class="panel-title">公告</h3></div>
                                    <div class="panel-body">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<footer id="bjui-footer">Copyright &copy; 2013 - 2015　66173</a></footer>
</body>
</html>