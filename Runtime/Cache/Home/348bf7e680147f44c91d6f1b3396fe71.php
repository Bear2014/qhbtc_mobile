<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="renderer" content="webkit">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php echo C('web_title');?></title>
	<meta name="Keywords" content="<?php echo C('web_keywords');?>">
	<meta name="Description" content="<?php echo C('web_description');?>">
	<meta name="author" content="qijianke.com">
	<meta name="coprright" content="qijianke.com">
	<link rel="shortcut icon" href=" /favicon.ico"/>
	<link rel="stylesheet" href="/Public/Home/css/movesay.css"/>
	<link rel="stylesheet" href="/Public/Home/css/style.css"/>
	<link rel="stylesheet" href="/Public/Home/css/font-awesome.min.css"/>
	<script type="text/javascript" src="/Public/Home/js/jquery.min.js"></script>
	<script type="text/javascript" src="/Public/Home/js/jquery.flot.js"></script>
	<script type="text/javascript" src="/Public/Home/js/jquery.cookies.2.2.0.js"></script>
	<script type="text/javascript" src="/Public/layer/layer.js"></script>
</head>
<body>
<div class="header bg_w" id="trade_aa_header">
	<div class="hearder_top">
		<div class="autobox po_re zin100" id="header">
			<div class="welcome"><?php echo C('top_name');?></div>
			<div class="right orange" id="login">
				<?php if(($_SESSION['userId']) > "0"): ?><dl class="mywallet">
						<dt id="user-finance">
						<div class="mywallet_name clear">
							<a href="/finance/"><?php echo (session('userName')); ?></a><i></i>
						</div>
						<div class="mywallet_list" style="display: none;">
							<div class="clear">
								<ul class="balance_list">
									<h4>可用余额</h4>
									<li>
										<a href="javascript:void(0)"><em style="margin-top: 5px;" class="deal_list_pic_cny"></em><strong>人民币：</strong><span><?php echo ($userCoin_top['cny']*1); ?></span></a>
									</li>
								</ul>
								<ul class="freeze_list">
									<h4>委托冻结</h4>
									<li>
										<a href="javascript:void(0)"><em style="margin-top: 5px;" class="deal_list_pic_cny"></em><strong>人民币：</strong><span><?php echo ($userCoin_top['cnyd']*1); ?></span></a>
									</li>
								</ul>
							</div>
							<div class="mywallet_btn_box">
								<a href="/finance/mycz.html">充值</a><a href="/finance/mytx.html">提现</a><a href="/finance/myzr.html">转入</a><a href="/finance/myzc.html">转出</a><a href="/finance/mywt.html">委托管理</a><a href="/finance/mycj.html">成交查询</a>
							</div>
						</div>
						</dt>
						<dd>
							ID：<span><?php echo (session('userId')); ?></span>
						</dd>
						<dd>
							<a href="<?php echo U('Login/loginout');?>">退出</a>
						</dd>
					</dl>
					<?php else: ?> <!-- 登陆前 -->
					<div class="orange">
						<span class="zhuce"><a class="orange" href="<?php echo U('Login/register');?>">注册</a></span> |
						<a href="javascript:;" class="orange" onclick="loginpop();">登录</a>
					</div><?php endif; ?>
			</div>
			<div class="nav  nav_po_1" id="menu_nav">
				<ul>
					<li style=" text-align: right; margin-right: 20px;">
						<a href="/" id="index_box">首页</a>
					</li>
					<li>
						<a id="trade_box" href="<?php echo U('Trade/index');?>"><span>交易中心</span>
							<img src="/Public/Home/images/down.png"></a>
						<div class="deal_list " style="display: none;    top: 36px;">
							<dl id="menu_list_json"></dl>
							<div class="sj"></div>
							<div class="nocontent"></div>
						</div>
					</li>

					<?php if(is_array($daohang)): $i = 0; $__LIST__ = $daohang;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
							<a id="<?php echo ($vo['name']); ?>_box" href="/<?php echo ($vo['url']); ?>"><?php echo ($vo['title']); ?></a>
						</li><?php endforeach; endif; else: echo "" ;endif; ?>

				</ul>
			</div>
		</div>
	</div>
	<div style="clear: both;"></div>
	<div class="autobox clear" id="trade_clear">
		<div class="logo">
			<a href="/"><img src="/Upload/public/<?php echo ($C['web_logo']); ?>" alt=""/></a>
		</div>
		<div class="phone right">
			<span class="iphone" style=""></span><a href="http://wpa.qq.com/msgrd?V=3&amp;uin=<?php echo C('contact_qq')[0];?>&amp;Site=QQ客服&amp;Menu=yes" target="_blank" class="qqkefu"></a>
		</div>
	</div>
</div>
<script>
	$.getJSON("/Ajax/getJsonMenu?t=" + Math.random(), function (data) {
		if (data) {
			var list = '';
			for (var i in data) {
				list += '<dd><a href="/Trade/index/market/' + data[i]['name'] + '"><img src="/Upload/coin/' + data[i]['img'] + '" style="width: 18px; margin-right: 5px;">' + data[i]['title'] + '</a></dd>';
			}
			$("#menu_list_json").html(list);
		}
	});
	$('#trade_box').hover(function () {
		$('.deal_list').show()
	}, function () {
		$('.deal_list').hide()
	});
	$('.deal_list').hover(function () {
		$('.deal_list').show()
	}, function () {
		$('.deal_list').hide()
	});
	$('#user-finance').hover(function () {
		$('.mywallet_list').show();
	}, function () {
		$('.mywallet_list').hide()
	});
</script>
<!--头部结束-->
<!--焦点图-->
<div class="index_pic_wrap po_re">
    <div id="myCarousel" class="my-carousel">
        <!--<div class="my-carousel-indicators">-->
        <ol class="my-carousel-indicators">
            <?php if(is_array($indexAdver)): $i = 0; $__LIST__ = $indexAdver;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li data-target="#myCarousel" data-slide-to="<?php echo ($i); ?>"
                <?php if(($i) == "1"): ?>class="active"<?php endif; ?>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ol>
        <div class="my-carousel-inner">
            <?php if(is_array($indexAdver)): $i = 0; $__LIST__ = $indexAdver;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="item hand <?php if(($i) == "1"): ?>active<?php endif; ?>" onclick="window.open('<?php echo ($vo['url']); ?>')" style="background-image: url(/Upload/ad/<?php echo ($vo["img"]); ?>);"></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
    </div>
    <div class="login_wrap">
        <div class="login_box">
            <div class="login_bg"></div>
            <!-- 未登录状态 -->
            <?php if(($_SESSION['userId']) > "0"): ?><div id="login-bar" class="login_box_2">
                    <h2>欢迎登录<?php echo C('web_name');?>交易平台</h2>
                    <dl>
                        <dt>您正在使用的账号为:</dt>
                        <dd>
                            <a href="/finance/" class="user-email"><?php echo (session('userName')); ?></a>
                        </dd>
                        <dd>
                            ID：
                            <span class="user-id"><?php echo (session('userId')); ?></span>
                        </dd>
                        <dd>
                            总资产：
                            <span class="user-finance" id="user_finance">loading...</span>
                        </dd>
                    </dl>
                    <div class="login_box_2_btn">
                        <a href="/finance/mycz.html">充值</a>
                        <a href="/finance/mytx.html">提现</a>
                        <a href="/finance/mywt.html" class="w82">委托管理</a>
                    </div>
                    <div class="gotocenter">
                        <a href="/finance/" class="center">去财务中心</a>
                    </div>
                    <div class="service_qq"></div>
                </div>
                <?php else: ?>
                <form id="form-login-i">
                    <div class="login_box_1">
                        <div class="login_title">登录</div>
                        <div class="login_text zin90">
                            <input type="text" id='index_username' value="" placeholder="请输入手机号/会员名"/>

                            <div id="email-err-i" class="prompt" style="display: none"></div>
                        </div>
                        <div class="login_text zin80">
                            <input type="password" id="index_password" value="" placeholder="请输入登录密码"/>

                            <div id="pw-err-i" class="prompt" style="display: none"></div>
                        </div>
                        <?php if(($C['login_verify']) == "1"): ?><div class="login_text zin70" id="ga-box-i">
                                <img id="codeImg reloadverifyindex" src="<?php echo U('Verify/code');?>" width="120" height="38" onclick="this.src=this.src+'?t='+Math.random()" style="margin-top: 1px; cursor: pointer;" title="换一张">
                                <input type="text" class="code" id="index_verify" name="code" placeholder="请输入验证码" style="width: 106px; float: left;">
                            </div><?php endif; ?>
                        <div class="login_button">
                            <input type="button" value="登录" onclick="upLoginIndex();"/>
                        </div>
                        <div class="login-footer">
                            <!--<a href="/"> <img src="/Public/Home/images/qq2.png">QQ登录</a> -->
      <span> <a href="<?php echo U('Login/register');?>">免费注册</a> ｜ <a href="<?php echo U('Login/findpwd');?>">忘记密码</a>
      </span>
                        </div>
                    </div>
                </form><?php endif; ?>
        </div>
    </div>
</div>
<div class="zhanwei"></div>
<div class="price_today">
    <div class="autobox">
        <ul class="price_today_ull">
            <li data-sort="0" style="cursor: default;">交易市场</li>
            <li class="click-sort" data-sort="1" data-flaglist="0" data-toggle="0">最新成交价
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li class="click-sort" data-sort="2" data-flaglist="0" data-toggle="0">买一价
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li class="click-sort" data-sort="3" data-flaglist="0" data-toggle="0">卖一价
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li class="click-sort" data-sort="6" data-flaglist="0" data-toggle="0">成交量
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li class="click-sort" data-sort="4" data-flaglist="0" data-toggle="0">成交额
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li class="click-sort" data-sort="7" data-flaglist="0" data-toggle="0">日涨跌
                <i class="cagret cagret-down"></i>
                <i class="cagret cagret-up"></i>
            </li>
            <li data-sort="0">价格趋势(3日)</li>
            <li data-sort="0" style="width: 150px; text-align: center; text-indent: -1em;">操作</li>
        </ul>
    </div>
</div>
<ul class="price_today_ul" id="price_today_ul"></ul>
<div class="footer_con" style="margin: 0px auto;width: 1180px;">
    <div class="autobox clear" style="padding: 0px 20px;">
        <p style="width: 1165px;">
            <span>风险警告：</span>
            <?php echo C('web_waring');?>
        </p>
    </div>
</div>
<div class="news_box">
    <div class="autobox">
        <div class="news_t clear"></div>
        <div class="news_s">
            <div class="news_sc">
                <div class="news_ct">
                    <div class="news_cti"></div>
                    <div class="news_cts">
                        <a target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[0]['id']); ?>"><?php echo ($indexArticleType[0]['title']); ?></a>
                    </div>
                </div>
                <div class="news_cl">
                    <ul class="news_clu">
                        <?php if(is_array($indexArticle[0])): $i = 0; $__LIST__ = $indexArticle[0];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
                                <a class="news_clua" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"><?php echo ($vo['title']); ?> </a>
                                <a class="news_clda" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"> [ <?php echo (date("y-m-d",$vo['addtime'])); ?> ] </a>
                            </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        <li>
                            <a class="news_clda" target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[0]['id']); ?>"> 更多&gt;&gt; </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="news_sc">
                <div class="news_ct">
                    <div class="news_cti news_ctin"></div>
                    <div class="news_cts">
                        <a target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[1]['id']); ?>"><?php echo ($indexArticleType[1]['title']); ?></a>
                    </div>
                </div>
                <div class="news_cl">
                    <ul class="news_clu">
                        <?php if(is_array($indexArticle[1])): $i = 0; $__LIST__ = $indexArticle[1];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
                                <a class="news_clua" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"><?php echo ($vo['title']); ?> </a>
                                <a class="news_clda" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"> [ <?php echo (date("y-m-d",$vo['addtime'])); ?> ] </a>
                            </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        <li>
                            <a class="news_clda" target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[1]['id']); ?>"> 更多&gt;&gt; </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="news_sc">
                <div class="news_ct">
                    <div class="news_cti news_ctic"></div>
                    <div class="news_cts">
                        <a target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[2]['id']); ?>"><?php echo ($indexArticleType[2]['title']); ?></a>
                    </div>
                </div>
                <div class="news_cl">
                    <ul class="news_clu">
                        <?php if(is_array($indexArticle[2])): $i = 0; $__LIST__ = $indexArticle[2];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
                                <a class="news_clua" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"><?php echo ($vo['title']); ?> </a>
                                <a class="news_clda" target="_blank" href="<?php echo U('Article/detail','id='.$vo['id']);?>"> [ <?php echo (date("y-m-d",$vo['addtime'])); ?> ] </a>
                            </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        <li>
                            <a class="news_clda" target="_blank" href="/Article/index/id/<?php echo ($indexArticleType[2]['id']); ?>"> 更多&gt;&gt; </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="coin_type" value="cny_btc"/>
<input type="hidden" name="amount" value="1000000"/>



<?php if(($C['index_lejimum']) == "1"): ?><div class="index_box_2 slogan">
    <div class="slogan_title">选择<?php echo C('web_title');?>,安全可信赖</div>
    <div class="slogan_tis">累计交易额<span id="yi" style="display: none;margin-left: 5px;" class="yiyi1"></span>
        <sapn style="display: none;" class="yiyi2"> 亿</sapn>
        <span id="wan"></span> 万
    </div>
    <div id="cumulative"></div>
</div>
<script src="/Public/Home/js/index_change.js"></script><?php endif; ?>


<!--友情链接-->
<div class="link" style="    padding-top: 0px;">
    <div class="linkbox">
        <h4>
            <a target="_blank" href="/about/partner.html">友情链接</a>
        </h4>
        <ul>
            <?php if(is_array($indexLink)): $i = 0; $__LIST__ = $indexLink;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li style="margin-left: 0px;">
                    <a target="_blank" href="<?php echo ($vo['url']); ?>"><?php echo ($vo['title']); ?></a>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>
<script>
    //轮播图
    var $allItems = $('.my-carousel .my-carousel-inner .item');
    var $allIndicators = $('.my-carousel .my-carousel-indicators li');
    var currentIndex = 0;
    var currentItem = null;
    var nextItem = null;
    var time;


    $(".my-carousel").hover(function () {
        time = window.clearInterval(time)
    }, function () {
        time = setInterval(function () {
                    currentItem = $allItems.filter('.active');
                    if (currentIndex + 1 === $allItems.length) {
                        nextItem = $allItems.eq(0);
                        currentIndex = 0;
                    } else {
                        nextItem = $allItems.eq(currentIndex + 1);
                        currentIndex += 1;
                    }
                    nextItem.addClass('active').fadeIn(500);
                    $allIndicators.removeClass('active').eq(currentIndex).addClass('active');
                    currentItem.removeClass('active').fadeOut(1000);
                },
                5000);
    }).trigger("mouseleave");

    $(".my-carousel-indicators li").click(function () {
        var nextIndex = parseInt($(this).attr('data-slide-to'));
        if (nextIndex == currentIndex) return false;
        currentIndex = nextIndex;
        currentItem = $allItems.filter('.active');
        currentItem.removeClass('active').fadeOut(1000);
        $allItems.eq(currentIndex).addClass('active').fadeIn(500);
        $allIndicators.removeClass('active').eq(currentIndex).addClass('active');
    });


    $('.price_today_ull > .click-sort').each(function () {
        $(this).click(function () {
            click_sortList(this);
        })
    })

    function allcoin_callback(priceTmp) {
        for (var i in priceTmp) {
            var c = priceTmp[i][8];
            if (typeof (trends[c]) != 'undefined' && typeof (trends[c]['data']) != 'undefined' && trends[c]['data'].length > 0) {
                $.plot($("#" + c + "_plot"), [
                    {
                        shadowSize: 0,
                        data: trends[c]['data']
                    }
                ], {
                    grid: {borderWidth: 0},
                    xaxis: {
                        mode: "time",
                        ticks: false
                    },
                    yaxis: {
                        tickDecimals: 0,
                        ticks: false
                    },
                    colors: ['#f99f83']
                });
            }
        }
    }

    function click_sortList(sortdata) {
        var a = $(sortdata).attr('data-toggle');
        var b = $(sortdata).attr('data-sort');
        $(".price_today_ull > li").find('.cagret-up').css('border-bottom-color', '#848484');
        $(".price_today_ull > li").find('.cagret-down').css('border-top-color', '#848484');
        $(".price_today_ull > li").attr('data-flaglist', 0).attr('data-toggle', 0);
        $(".price_today_ull > li").css('color', '');
        $(sortdata).css('color', '#ff7950');

        if (a == 0) {
            priceTmp = priceTmp.sort(sortcoinList('dec', b));
            $(sortdata).find('.cagret-down').css('border-top-color', '#ff7950');
            $(sortdata).find('.cagret-up').css('border-bottom-color', '#848484');
            $(sortdata).attr('data-flaglist', 1).attr('data-toggle', 1)
        }
        else if (a == 1) {
            $(sortdata).attr('data-toggle', 0).attr('data-flaglist', 2);
            ;
            $(sortdata).find('.cagret-up').css('border-bottom-color', '#ff7950');
            $(sortdata).find('.cagret-down').css('border-top-color', '#848484');
            priceTmp = priceTmp.sort(sortcoinList('asc', b));
        }
        renderPage(priceTmp);
        change_line_bg('price_today_ul', 'li');
        allcoin_callback(priceTmp);
    }

    function trends() {
        $.getJSON('/Ajax/trends?t=' + rd(), function (d) {
            trends = d;
            allcoin();
        });
    }

    function allcoin(cb) {
        $.get('/Ajax/allcoin?t=' + rd(), cb ? cb : function (d) {
            ALLCOIN = d;
            var t = 0;
            var img = '';
            priceTmp = [];
            //把json转换为二维数组 进行渲染
            for (var x in d) {
                if (typeof(trends[x]) != 'undefined' && parseFloat(trends[x]['yprice']) > 0) {
                    rise1 = (((parseFloat(d[x][4]) + parseFloat(d[x][5])) / 2 - parseFloat(trends[x]['yprice'])) * 100) / parseFloat(trends[x]['yprice']);
                    rise1 = rise1.toFixed(2);
                } else {
                    rise1 = 0;
                }
                img = d[x].pop();
                d[x].push(rise1);
                d[x].push(x);
                d[x].push(img);
                priceTmp.push(d[x]);
            }
            //二次排序
            $('.price_today_ull > .click-sort').each(function () {
                var listId = $(this).attr('data-sort');
                if ($(this).attr('data-flaglist') == 1 && $(this).attr('data-sort') !== 0) {
                    priceTmp = priceTmp.sort(sortcoinList('dec', listId))
                } else if ($(this).attr('data-flaglist') == 2 && $(this).attr('data-sort') !== 0) {
                    priceTmp = priceTmp.sort(sortcoinList('asc', listId))
                }
            });
            renderPage(priceTmp);
            allcoin_callback(priceTmp);
            change_line_bg('price_today_ul', 'li');
            t = setTimeout('allcoin()', 5000);

        }, 'json');
    }

    function rd() {
        return Math.random()
    }
    //渲染函数
    function renderPage(ary) {
        var html = '';
        for (var i in ary) {
            var coinfinance = 0;
            if (typeof FINANCE == 'object') coinfinance = parseFloat(FINANCE.data[ary[i][8] + '_balance']);
            html += '<li><dl class="autobox clear"><dt><a href="/trade/index/market/' + ary[i][8] + '/">' +
                    '<img src="/Upload/coin/' + ary[i][9] + '" style="vertical-align: middle;margin-right: 5px;width: 19px; */height: 19px;height: 19px;">' + ary[i][0] + '</a></dt><dd class="orange" style="text-indent: 0.5em;">￥' + ary[i][1] + '</dd><dd style="text-indent: 1.2rem;">￥' + ary[i][2] + '</dd><dd style="text-indent: 1.5rem;">￥' + ary[i][3] + '</dd><dd class="w142" style="    text-indent: 1.5rem;">' + formatCount(ary[i][6]) + '</dd><dd class="w142" style="    text-indent: 1.5rem;">' + formatCount(ary[i][4]) + '</dd><dd class="w142 ' + (ary[i][7] >= 0 ? 'red' : 'green') + '" style="    text-indent: 1.5rem;color:red">' + (parseFloat(ary[i][7]) < 0 ? '' : '+') + ((parseFloat(ary[i][7]) < 0.01 && parseFloat(ary[i][7]) > -0.01) ? "0.00" : ary[i][7]) + '%</dd><dd id="' + ary[i][8] + '_plot"  style="width:150px;height:35px;"></dd><dd class="w102" style="width:150px;text-align: center;text-indent: -1.5em;"><input type="button" value="去交易" onclick="top.location=\'/trade/index/market/' + ary[i][8] + '/\'" /></dd></dl></li>'
        }
        $('#price_today_ul').html(html);
    }
    //保留2位小鼠
    function formatCount(count) {
        var countokuu = (count / 100000000).toFixed(3)
        var countwan = (count / 10000).toFixed(3)
        if (count > 100000000)
            return countokuu.substring(0, countokuu.lastIndexOf('.') + 3) + '亿'
        if (count > 10000)
            return countwan.substring(0, countwan.lastIndexOf('.') + 3) + '万'
        else
            return count
    }
    //移入行变色
    function change_line_bg(id, tag, nobg) {
        var oCoin_list = $('#' + id);
        var oC_li = oCoin_list.find(tag);
        var oInp = oCoin_list.find('input');
        var oldCol = null;
        var newCol = null;
        if (!nobg) {
            for (var i = 0; i < oC_li.length; i++) {
                oC_li.eq(i).css('background-color', i % 2 ? '#fff' : '#f8f8f8');
                oInp.mouseover(function () {
                    this.style.color = '#fff';
                    this.style.backgroundColor = '#e55600';
                });
                oInp.mouseout(function () {
                    this.style.color = '#e55600';
                    this.style.background = 'none';
                });
            }
        }
        oCoin_list.find(tag).hover(function () {
            oldCol = $(this).css('backgroundColor');
            $(this).css('background-color', '#f9f2dd');
        }, function () {
            $(this).css('background-color', oldCol);
        })
    }

    //排序函数
    function sortcoinList(order, sortBy) {
        var ordAlpah = (order == 'asc') ? '>' : '<';
        var sortFun = new Function('a', 'b', 'return parseFloat(a[' + sortBy + '])' + ordAlpah + 'parseFloat(b[' + sortBy + '])? 1:-1');
        return sortFun;
    }


    trends();


    var cookieValue = $.cookies.get('cookie_username');
    if (cookieValue != '' && cookieValue != null) {
        $("#index_username").val(cookieValue);
    }
    function upLoginIndex() {
        var username = $("#index_username").val();
        var password = $("#index_password").val();
        var verify = $("#index_verify").val();
        if (username == "" || username == null) {
            layer.tips('请输入用户名', '#index_username', {tips: 3});
            return false;
        }
        if (password == "" || password == null) {
            layer.tips('请输入登录密码', '#index_password', {tips: 3});
            return false;
        }

        $.post("<?php echo U('Login/submit');?>", {
            username: username,
            password: password,
            verify:verify,
        }, function (data) {
            if (data.status == 1) {
                $.cookies.set('cookie_username', username);
                layer.msg(data.info, {icon: 1});
                window.location = '/Finance';
            } else {
                //刷新验证码
                $(".reloadverifyindex").click();
                layer.msg(data.info, {icon: 2});
                if (data.url) {
                    window.location = data.url;
                }
            }
        }, "json");
    }
</script>
<script>
    //菜单高亮
    $('#index_box').addClass('active');
</script>
	<style>
        .footer{
		clear:both;
	}

	.footer .main{
		height:240px;
	}

	#footer a{
		color:#FFF;
		margin:0px 0px;
	}

	.footer .bottom{
		height:80px;
		background:#2C2C2C;
	}

	.footer .main .list{
		float:left;
		margin-top:40px;
		width: 185px;
		padding: 0px 5px;
	}

	.footer .main .list label{
		margin-top:10px;
		display:block;
		font-weight:bold;
		color:#FFF;
		font-size:16px;
		text-align: left;
		padding-left: 45px;
	}

	.footer .main .list ul{
		margin:10px 0px 0px;
		padding:0px;
	}

	.footer .main .list li{
		display:block;
		height:30px;
		line-height:30px;
		color:#CCC;
		text-align:center;
		list-style:none;
		text-align: left;
		padding-left: 45px;
	}

	.footer .main .list li a{
		display:block;
		width:100%;
		height:100%;
		color:#CCC;
		font-size:14px;
	}

	.footer .about_me{
		float:left;
		margin-top:40px;
		width:280px;
		height:150px;
		border-right:1px #606060 solid;
		padding-right:50px;
	}

	.footer .wx{
		margin-top:0px;
		height:55px;
	}

	.footer .wx a{
		position:relative;
		margin:0 14px;
		cursor:pointer;
	}

	.footer .wx a img{

		left:-69px;

		transition:300ms;
		-webkit-transition:300ms;
		-ms-transition:300ms;
		-o-transition:300ms;
		-moz-transition:300ms;
	}

	.footer .wx a:hover img{
		display:block;
		top:-180px;
	}

	.footer .footer_wx_icon{
		float:left;

		border-radius:55px;
		-webkit-border-radius:55px;
		-ms-border-radius:55px;
		-o-border-radius:55px;
		-moz-border-radius:55px;

		transition:300ms;
		-webkit-transition:300ms;
		-ms-transition:300ms;
		-o-transition:300ms;
		-moz-transition:300ms;
	}

	.footer .footer_wx_icon:hover{

	}

	.footer .footer_sn_icon{
		float:left;
		width:55px;
		height:55px;

		background-color:#34353A;

		border-radius:55px;
		-webkit-border-radius:55px;
		-ms-border-radius:55px;
		-o-border-radius:55px;
		-moz-border-radius:55px;

		transition:300ms;
		-webkit-transition:300ms;
		-ms-transition:300ms;
		-o-transition:300ms;
		-moz-transition:300ms;
	}

	.footer .footer_sn_icon:hover{

		background-color:#F00;
	}

	.footer .footer_qq_icon{
		float:left;
		width:55px;
		height:55px;

		background-color:#34353A;

		border-radius:55px;
		-webkit-border-radius:55px;
		-ms-border-radius:55px;
		-o-border-radius:55px;
		-moz-border-radius:55px;

		transition:300ms;
		-webkit-transition:300ms;
		-ms-transition:300ms;
		-o-transition:300ms;
		-moz-transition:300ms;
	}

	.footer .footer_qq_icon:hover{

		background-color:#F00;
	}

	.footer .about_me h4{
		margin:10px 0px 0px 44px;
		color:#FFF;
		font-size:14px;
		font-weight:normal;
	}

	.footer .about_me .about_me_text{
		margin-top:20px;
		margin-left:44px;
		font-size:14px;
		color:#CCC;
	}

	.footer .contact_us{
		float:left;
		margin-top:50px;
		padding-left:57px;
		border-left:1px #606060 solid;
		height:150px;
		color:#CCC;
		font-size:14px;
	}

	.footer .contact_us_text1{
		margin-top:6px;
		font-size:28px;
		color:#FFF;
	}

	.footer .contact_us_text2{
		margin-top:5px;
		font-size:12px;
	}

	.footer .contact_us_text3 span{
		float:left;
		line-height:31px;
	}

	.footer .contact_us_text3{
		margin-top:18px;
		display:block;
		color:#CCC;
	}

	.footer .contact_us_text3 i{
		display:block;
		float:left;
		margin-left:10px;
		width:32px;
		height:30px;
		cursor:pointer;
		border:1px #CCC solid;

		border-radius:16px;
		-webkit-border-radius:16px;
		-ms-border-radius:16px;
		-o-border-radius:16px;
		-moz-border-radius:16px;

		transition:300ms;
		-webkit-transition:300ms;
		-ms-transition:300ms;
		-o-transition:300ms;
		-moz-transition:300ms;

	}

	.footer .contact_us_text3 i:hover{
		border:1px #DB0015 solid;
		background-color:#DB0015;
	}

	.footer .bottom .text{
		float:left;
		margin-top:34px;
		color:#999;
		font-size:14px;
	}

	.footer .bottom .g{
		float:right;
		margin-right:10px;
	}

	.footer .bottom .g a{
		float:left;
		margin-left:20px;
		margin-top:24px;
		width:100px;
		height:36px;
	}
</style>
<footer id="footer" class="footer" style="padding: 0px 0px 20px 0px;">
	<section class="main">
		<div class="about_me">
			<div class="wx">
				<a href="javascript:" class="footer_wx_icon"><img src="/Upload/public/<?php echo ($C['footer_logo']); ?>"></a>
			</div>
		</div>
		<div class="layout_center">
			<?php if(is_array($footerArticleType)): $i = 0; $__LIST__ = $footerArticleType;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list"><label><?php echo ($vo['title']); ?></label>
					<ul>
						<?php if(is_array($footerArticle[$vo['name']])): $i = 0; $__LIST__ = $footerArticle[$vo['name']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vvo): $mod = ($i % 2 );++$i;?><li><a href="<?php echo U('Article/type',array('id'=>$vvo['id']));?>" style="overflow: hidden;"><?php echo ($vvo['title']); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
						<li><a href="<?php echo U('Article/type',array('id'=>$vo['id']));?>" style="overflow: hidden;    text-align: left;">更多</a></li>
					</ul>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>





			<div class="contact_us">
				<div class="contact_us_text0" style="text-align: left;">全国免费咨询电话 :</div>
				<div class="contact_us_text1" style="text-align: left;margin-top: 10px;margin-bottom: 12px;"><?php echo C('contact_moble');?></div>
				<div class="contact_us_text2" style="text-align: left;margin-bottom: 5px;">工作时间：周一至周日 8:00-23:00</div>
				<?php $_result=C('contact_qqun');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><a href="#" class="contact_us_text3"><span>会员群号 :<?php echo ($i); ?>群：<?php echo ($v); ?></span></a><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
		</div>
	</section>
</footer>
<div class="footer_bottom">
	<div class="autobox" style="height: 40px;margin-top: 5px;">
		<span style="display: inline-block;color:#A6A9AB;">CopyRight© 2013-2016 <?php echo ($C['web_name']); ?>交易平台 All Rights Reserved &nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.miitbeian.gov.cn/publish/query/indexFirst.action" target="_blank"><?php echo ($C['web_icp']); ?></a><span style="display: inline-block; color:#A6A9AB"></span></span>
		<span style="float: right;">
			<a href="http://www.gov.cn/" target="_blank" class="margin10" style="margin-left:5px;"> <img src="/Upload/footer/footer_1.png">
			</a>
			<a href="http://www.szfw.org/" target="_blank" class="margin10" style="margin-left:5px;"> <img src="/Upload/footer/footer_2.png">
			</a>
			<a href="http://www.miibeian.gov.cn/" target="_blank" class="margin10" style="margin-left:5px;"><img src="/Upload/footer/footer_3.png">
			</a>
			<a href="http://www.cyberpolice.cn/" target="_blank" class="margin10" style="margin-left:5px;"><img src="/Upload/footer/footer_4.png">
			</a>
		</span>
	</div>
	<!-- 原安全验证位置 -->
</div>
<!--代码部分begin-->
<div id="floatTools" class="rides-cs" style="height: 200px;">
	<div class="floatL">
		<a id="aFloatTools_Show" class="btnOpen" title="查看在线客服" style="top: 20px; display: block" href="javascript:void(0);">展开</a>
		<a id="aFloatTools_Hide" class="btnCtn" title="关闭在线客服" style="top: 20px; display: none" href="javascript:void(0);">收缩</a>
	</div>
	<div id="divFloatToolsView" class="floatR" style="display: none; width: 140px; background: #E55600;">
		<div class="cn">
			<h3 class="titZx">官方在线客服</h3>
			<ul id="jisuan_qq">
				<?php $_result=C('contact_qq');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li>
						<span>客服<?php echo ($i); ?></span>
						<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&amp;uin=<?php echo ($v); ?>&amp;site=qq&amp;menu=yes">
							<img border="0" src="/Application/Home/View/Kefu/a/images/online.png" alt="点击这里给我发消息" title="点击这里给我发消息"/>
						</a>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
				<?php $_result=C('contact_qqun');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li>
						<span>QQ群<?php echo ($i); ?></span> <?php echo ($v); ?>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
				<?php if(!empty($C['contact_moble'])): ?><li style="border: none;">
					<span>电话：<?php echo C('contact_moble');?></span>
				</li><?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<script>
	$(function () {
		$("#floatTools").hover(function () {
			$('#divFloatToolsView').animate({
				width: 'show',
				opacity: 'show'
			}, 100, function () {
				$('#divFloatToolsView').show();
			});
			$('#aFloatTools_Show').hide();
			$('#aFloatTools_Hide').show();
		}, function () {
			$('#divFloatToolsView').animate({
				width: 'hide',
				opacity: 'hide'
			}, 100, function () {
				$('#divFloatToolsView').hide();
			});
			$('#aFloatTools_Show').show();
			$('#aFloatTools_Hide').hide();
		});
		$("#divFloatToolsView").height(36 + $("#jisuan_qq li").length * 40);
	});
</script>
<div id="all_mask" class="all_mask" style="height: 0px; display: none;"></div>
<div class="all_mask_loginbox" style="top: 313px; display: none;">
	<div class="login_title pl20">登录</div>
	<form id="form-login" class="mask_wrap login-fb">
		<div class="login_text zin90">
			<div class="mask_wrap_title">账号：</div>
			<input id="login_username" name="username" type="text" placeholder="请输入手机号/会员名">
		</div>
		<div class="login_text zin80">
			<div class="mask_wrap_title">密码：</div>
			<input id="login_password" name="password" type="password" placeholder="请输入登录密码">
		</div>
		<?php if(($C['login_verify']) == "1"): ?><div class="login_text zin70" id="ga-box-i">
				<img id="codeImg reloadverifyindex" src="<?php echo U('Verify/code');?>" width="120" height="38" onclick="this.src=this.src+'?t='+Math.random()" style="margin-top: 1px; cursor: pointer;" title="换一张">
				<input type="text" class="code" id="login_verify" name="code" placeholder="请输入验证码" style="width: 106px; float: left;">
			</div><?php endif; ?>
		<div class="login_button">
			<input type="button" value="登录" onclick="upLogin();">
		</div>
		<div class="login-footer wwxwwx" style="border-bottom-left-radius: 3px; border-bottom-right-radius: 3px;">
			<!--<a target="_blank" href="/"><img src="/Public/Home/images/qq2.png" style="vertical-align: text-bottom; padding-right: 5px;">zzQQ登录</a>-->

			<span style="color: #CCC; float: right; margin-right: 25px;">
			<a style="font-size: 12px;" href="<?php echo U('Login/register');?>">免费注册</a>｜<a href="<?php echo U('Login/findpwd');?>" style="font-size: 12px;">忘记密码</a></span>
		</div>
	</form>
	<div class="mask_wrap_close" onclick="wrapClose()"></div>
</div>
<script type="text/javascript" src="/Public/Home/js/jquery.cookies.2.2.0.js"></script>
<script>
	$('input').focus(function () {
		var t = $(this);
		if (t.attr('type') == 'text' || t.attr('type') == 'password')
			t.css({'box-shadow': '0px 0px 3px #1583fb', 'border': '1px solid #1583fb', 'color': '#333'});
		if (t.val() == t.attr('placeholder'))
			t.val('');
	});
	$('input').blur(function () {
		var t = $(this);
		if (t.attr('type') == 'text' || t.attr('type') == 'password')
			t.css({'box-shadow': 'none', 'border': '1px solid #e1e1e1', 'color': '#333'});
		if (t.attr('type') != 'password' && !t.val())
			t.val(t.attr('placeholder'));
	});


	function NumToStr(num) {
		if (!num) return num;
		num = Math.round(num * 100000000) / 100000000;
		num = num.toFixed(8);
		var min = 0.0001;
		var times = 0;
		var arr;
		if (num <= min) {
			times = 0;
			while (num <= min) {
				num *= 10;
				times++;
				if (times > 100) break;
			}
			num = num + '';
			arr = num.split(".");
			for (var i = 0; i < times; i++) {
				arr['1'] = '0' + arr['1'];
			}
			return arr[0] + '.' + arr['1'] + '';
		}
		return num.toFixed(8) + ' ';
	}


	function loginpop() {
		$('.all_mask').css({'height': $(document).height()});
		$('.all_mask').show();
		$('.all_mask_loginbox').show();
		$(".reloadverify").click();
	}

	var is_login = <?php echo (session('userId')); ?>
	;

	if (window.location.hash == '#login') {
		if (!is_login) {
			loginpop();
		}
	}

	if (is_login) {
		$.getJSON("/Ajax/allfinance?t=" + Math.random(), function (data) {

			$('#user_finance').html(data);
		});
	}


	function wrapClose() {
		$('.all_mask').hide();
		$('.all_mask_loginbox').hide();
	}

	var cookieValue = $.cookies.get('cookie_username');
	if (cookieValue != '' && cookieValue != null) {
		$("#login_username").val(cookieValue);
	}

	function upLogin() {
		var username = $("#login_username").val();
		var password = $("#login_password").val();
		var verify = $("#login_verify").val();
		if (username == "" || username == null) {
			layer.tips('请输入用户名', '#login_username', {tips: 3});
			return false;
		}
		if (password == "" || password == null) {
			layer.tips('请输入登录密码', '#login_password', {tips: 3});
			return false;
		}

		$.post("<?php echo U('Login/submit');?>", {
			username: username,
			password: password,
			verify: verify,
		}, function (data) {
			if (data.status == 1) {
				$.cookies.set('cookie_username', username);
				layer.msg(data.info, {icon: 1});
				window.location = '/Finance';
			} else {
				//刷新验证码
				$(".reloadverifyindex").click();
				layer.msg(data.info, {icon: 2});
				if (data.url) {
					window.location = data.url;
				}
			}
		}, "json");
	}

       //隐藏双重验证
      $("#user_ga").hide();
</script></body></html>