$(function(){
    var newsTab = $('.wraps-aside').find('.news-ranking-tab'),
        newsList = $('.wraps-aside').find('.aside-list-ranking'),
        showMore = $('.wraps-content').find('.content-parts-more'),
        newestBuild = $('.public-newest-loupan .loupan-name');

    // @@ 边栏新闻排行 切换功能
    newsTab.on('mouseenter', 'span', function () {
        var index = $(this).index();
        $(this).addClass('current').siblings().removeClass('current');
        newsList.removeClass('show').eq(index).addClass('show');
    });

    // @@ 正文专栏 点击更多功能
    showMore.on('click', function () {
        $(this).siblings('.content-parts-news').children('.item.hide').removeClass('hide');
        $(this).hide();
    });
    // @@ 最新楼盘 显示菜单功能
    newestBuild.hover(
        function () {
            $(this).children('.newest-tip').show();
        },
        function () {
            $(this).children('.newest-tip').hide();

        }
    )

    // @@ 轮播
    var focusMod = function(oConfig){
        this.ele = oConfig.$ele;
        this.unitMoveTime = oConfig.period;
        this.unitAntiTime = oConfig.interval;
        this.eType = oConfig.eType;
        this.width = this.ele.find('.focus-set').children().width();
        this.len = this.ele.find('.focus-set').children().length;
        this.val = {
            counter: 1,
            arrowOn: true,
            btnOn: true,
            timer: null
        };
        this.init();
    };
    focusMod.prototype = {

        init: function () {
            this.animateFn().floatFn().arrowBtnFn().btnFn();
        },
        /* 单张图片动画 */
        moveFn: function () {
            var t = this.unitMoveTime, that = this;

            if(this.val.counter > this.len - 1){
                this.val.counter = 0;
            }else if(this.val.counter < 0){
                this.val.counter = this.len - 1;
            }

            this.ele.find('.focus-set').stop().animate({
                left: -(this.val.counter)*(this.width)
            }, t, function () {
                that.val.counter++;
                that.val.arrowOn = true;
                that.val.btnOn = true;
            });
            //更换按钮和文字栏样式
            this.ele.find('.focus-btns a').eq(this.val.counter).addClass('active').siblings().removeClass('active');
            //this.ele.find('.focus-title a').eq(this.val.counter).addClass('current').siblings().removeClass('current');
            return this;
        },
        /* 自动播放轮播 */
        animateFn: function () {
            var that = this;
            this.val.timer = setInterval(function(){that.moveFn()}, this.unitAntiTime);
            return this
        },
        /* 按键轮播图片 */
        btnFn: function () {
            var eType, that = this;
            if(this.eType === 'click' || this.eType == undefined){
                eType = 'click'
            }else if(this.eType == 'mouse'){
                eType = 'mouseenter';
            }else{
                alert('输入事件类型有误，参考:click or mouse')
            }
            this.ele.find('.focus-btns a').on(eType, function (e) {
                if(that.val.btnOn === false && eType == 'click'){
                    return
                }
                that.val.btnOn = false;
                clearInterval(that.val.timer);
                var index = $(this).index();
                that.val.counter = index;
                that.moveFn();
                e.stopPropagation();
            });
            return this
        },
        /* 获取焦点停止轮播, 失去焦点继续轮播 */
        floatFn: function () {
            var that = this;
            this.ele.hover(
                function () {
                    $(this).find('.focus-arrow').show();
                    clearInterval(that.val.timer)
                },
                function () {
                    $(this).find('.focus-arrow').hide();
                    that.val.timer = setTimeout(function(){that.animateFn()}, that.unitAntiTime);
                }
            );
            return this
        },
        /* 左右箭头点击轮播 */
        arrowBtnFn: function () {
            var that = this;
            this.ele.find('.focus-arrow').click(function(e){
                if(that.val.arrowOn === false){
                    return
                }
                clearInterval(that.val.timer);
                that.val.arrowOn = false;
                if($(this).is('.focus-arrow.left')){
                    that.val.counter = that.val.counter - 2;
                }
                that.moveFn();
                e.stopPropagation();
            });
            return this
        },
        /* 介绍层上移功能 */
        slideUpFn: function () {
            this.ele.find('.focus-title-container').hover(
                function () {
                    $(this).stop().animate({marginTop: -157}, 300)
                },
                function () {
                    $(this).stop().animate({marginTop: -61}, 300)

                }
            )
        }
    };

    var obj = {
        $ele: $('.news-focus'),
        interval: 3000,
        period: 700,
        eType: 'mouse'
    };
    new focusMod(obj);
});