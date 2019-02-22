$(function () {
    var browser = {
        versions: function () {
            var u = navigator.userAgent;
            return {
                webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
                weixin: u.indexOf('MicroMessenger') > -1, //是否微信 （2015-01-22新增）
                qq: u.match(/\sQQ/i) == " QQ" //是否qq
            };
        }()
    };
    var $tabLi = $('.game-tab li'),
        $moreBtn = $('.game-more-btn'),
        pageArr = [1, 1],
        pageSize = 10,
        tags = '',
        loadFlag = false;
    new Swiper('.swiper-container-banner', {
        pagination: '.swiper-pagination',
        autoplay: 3e3,
        autoplayDisableOnInteraction: !1
    })

    var Swiper1 = new Swiper('.game-list-swiper', {
        autoHeight: true,
        onSlideChangeStart: function (swiper) {
            $tabLi.removeClass('on').eq(swiper.activeIndex).addClass('on');
        }
    })
    $tabLi.on('click', function () {
        var $this = $(this),
            $index = $this.index();
        $this.addClass('on').siblings().removeClass('on');
        Swiper1.slideTo($index);
        loadFlag = false;
        pageArr = [1, 1];
        if ($index == 0) {
            tags = '';
        } else {
            tags = 0;
        }
    })
    //分类筛选
    $('.game-type').on('click', 'span', function () {
        var $this = $(this),
            $type = $this.data('type'),
            page = 1;
        if ($this.hasClass('on')) return;
        $this.addClass('on').siblings().removeClass('on');
        $('#game-boutique-ul').html('');
        $moreBtn.eq(1).html('<i></i>努力加载中');
        loadFlag = true;
        tags = $type;
        pageArr = [1, 1];
        setTimeout(function () {
            getGamelist(page, tags, 1);
        }, 200);

    })
    //加载更多
    $moreBtn.on('click', function () {
        var $this = $(this),
            $index = $this.data('index');
        if (loadFlag) return;
        loadFlag = true;
        if ($this.text() == '暂无数据') return;
        $this.html('<i></i>努力加载中');
        if ($index == 0) {
            pageArr[$index]++;
            setTimeout(function () {
                getGamelist(pageArr[$index], '', $index);
            }, 200);
        } else {
            pageArr[$index]++;
            setTimeout(function () {
                getGamelist(pageArr[$index], tags, $index);
            }, 200);
        }
    })
    downLoadTip();
    /**
     *  获取游戏列表
     * @param {*} page 页码
     * @param {*} tags 分类
     * @param {*} index 索引值
     */
    function getGamelist(page, tags, index) {
        $.ajax({
            url: '/moreGame',
            type: 'POST',
            data: {
                page: page,
                pagesize: pageSize,
                tags: tags,
            },
            dataType: "json",
            async: false,
            success: function (res) {
                if (res.code == 1) {
                    var data = res.data;
                    loadFlag = false;
                    $moreBtn.eq(index).html('查看更多 >>');
                    index == 0 ? $('#game-news-ul').append(tplHtml(data)) : $('#game-boutique-ul').append(tplHtml(data));
                    Swiper1.update();
                    if (res.flag == 0) $moreBtn.eq(index).text('暂无数据');
                } else {
                    $moreBtn.eq(index).text('暂无数据');
                }


            }

        })
    }
    //下载提示
    function downLoadTip() {
        if (browser.versions.ios) {
            $(document).on('click', '.download-btn, .app_btn', function (e) {
                e.preventDefault();
                alert('仅支持安卓系统下载哦~~');
                return;
            });
        }
    }
    /**
     * 模板
     * @param {*} data 返回的数据列表
     * @returns
     */
    function tplHtml(data) {
        var html = '',
            li;
        for (var i = 0; i < data.length; i++) {
            li = data[i];
            html += '<li>';
            html += '<a href="mobile_index.php?act=game_detail&amp;id=' + li.id + '" class="item">';
            html += '<img class="img-block" src="//cdn.66173.cn/' + li.app_icon + '" alt="">';
            html += '<div class="content">';
            html += '<h2>' + li.title + '</h2>';
            html += '<p><em>' + li.down_num + '次下载</em> | ' + li.app_size + '</p>';
            html += '<p>' + li.subtitle + '</p>';
            html += '</div>';
            html += '</a>';
            html += '<a href="' + li.down_url + '" class="download-btn">下载</a>';
            html += '</li>';
        }
        return html;

    }
});