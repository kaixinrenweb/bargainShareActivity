<?php 
header( 'Content-Type:text/html;charset=utf-8');
require_once("apis/config/pdo.class.php");
require_once("apis/config/config.init.php");
require_once("apis/config/function.php");
require_once "apis/wxJs/jssdk.php";

$jssdk = new JSSDK("wx42beab434d168a86", "d91f4fdbac054a7254cc7518ba70f72e");
$signPackage = $jssdk->GetSignPackage();

//接收参数
$uid = $_GET['uid'];
$gid = $_GET['gid'];
$orderid = $_GET['orderid'];
$openid  = $_GET['openid'];
if(!$uid || !$gid || !$orderid || !$openid){
	echo "<script>alert('页面接收参数错误~');</script>";exit;
}

$imgNameStr = "";
$descStr = "";
if($gid==1){
	$imgNameStr = "children";
	$descStr = "费尽心思，却看不出孩子擅长什么？严选天赋基因位点，帮您一次看清孩子7大潜能";
}else if($gid==2){
	$imgNameStr = "woman";
	$descStr = "为什么你喝水也会胖？不同的基因决定了减肥方式的不同";
}else if($gid==3){
	$imgNameStr = "man";
	$descStr = "春节欢聚，酒量有多大？生物学家科学分析，人们酒量不同主要取决于酒精代谢基因";
}

//获取砍价发起后的时间差
$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
$configs = $pdo->query($sqlc, "row");

//根据orderid获取当前的砍价的订单信息
$sql = "select * from ak_bargain_orders where id={$orderid} and openid='{$openid}' and uid={$uid} and goods_id={$gid}";
$resOrder = $pdo->query($sql, "row");
if(!$resOrder){
	echo "<script>alert('页面接收参数错误~');</script>";exit;
}
$isValid = 1;
if($resOrder['is_valid']==2){  //已经过期了
	$isValid = 2;
}else{//检查有没有过期
	$stime = $resOrder['stime'];
	$etime = time();
	$times = $configs['config_val']*3600;
	if(($etime-$stime)>$times){ //过期了 ,可以使用了
		$isValid = 2;
		//修改过期
		$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
		$pdo->update($sqlu);
	}
}

//总钱数
$totalMoney = $resOrder['friend_price'] + $resOrder['red_price'];
$distanceMoney = $resOrder['origin_price']-$totalMoney;
$isLowPrice = 1;  //没有到底价
if($distanceMoney<=$resOrder['low_price']){  //比底价还低，不能砍价了
	$isLowPrice = 2;  //已经是最低价了
	$isValid = 2;
	//修改过期
	$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
	$pdo->update($sqlu);
}
//如果没有过期，求出当前的倒计时的时间
if($isValid==1){
	$limitTime = time()-$resOrder['stime']; //剩余的秒数
	$limitTime = $configs['config_val']*3600-$limitTime;
}
if(!$limitTime){
	$limitTime = 0;
}

//已经砍的金额
$widthDis = ($totalMoney/$resOrder['origin_price'])*530/100;
$widthDis = round($widthDis, 2);

//砍价的好友
$sqls = "select * from ak_bargain_details where order_id={$orderid} and is_type=1 and status=1";
$friendGoods = $pdo->query($sqls);
$sqls = "select * from ak_bargain_details where order_id={$orderid} and is_type=2 and status=1";
$friendBads  = $pdo->query($sqls);

//根据gid获取产品的信息
$sqls = "select * from ak_bargain_goods where id={$gid}";
$resGoods = $pdo->query($sqls, "row");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no,width=device-width,initial-scale=1.0"/>
    <title><?php echo $resOrder['goods_name'];?></title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" href="statics/css/reset.css" />
    <link rel="stylesheet" href="statics/css/common.css" />
    <link rel="stylesheet" href="statics/css/swiper-3.4.2.min.css" />
    <link rel="stylesheet" href="statics/css/children.css" />
    <script>
		window.localStorage.isMy = '<?php echo $openid;?>';
    </script>
</head>
<body>
	<!-- goods-Slider -->
	<div class="goods-sliders">
		<div class="swiper-container swiper">
			<div class="swiper-wrapper">
			    <div class="swiper-slide"><img src="statics/images/<?php echo $imgNameStr;?>/banner-1.jpg"/></div>
			    <div class="swiper-slide"><img src="statics/images/<?php echo $imgNameStr;?>/banner-2.jpg"/></div>
			    <div class="swiper-slide"><img src="statics/images/<?php echo $imgNameStr;?>/banner-3.jpg"/></div>
			</div>
			<div class="swiper-pagination"></div>
		</div>
	</div> 
	
	<!-- limit-time -->
	<div class="limit-times">
		<?php if($isLowPrice==2){?>
		<p onclick="location.href='myself.php?openid=<?php echo $openid;?>';">已经砍到了底价，可直接购买</p>
		<?php }else{?>
		<p onclick="location.href='myself.php?openid=<?php echo $openid;?>';">距活动结束还有 <span class="limitTime"><!-- 07小时32分15秒 --></span></p>
		<?php }?>
	</div>
	
	<!-- goods-name -->
	<section class="goods-name">
		<h3><?php echo $resOrder['goods_name'];?></h3>
		<p>底价: <span><?php echo $resOrder['low_price'];?></span>元      库存<span>3000</span>个名额（已购<?php echo $resGoods['attend_persons'];?>个)</p>
	</section>
	
	<!-- bargain-ctn -->
	<section class="bargain-ctn">
		<div class="bargain-price clearFix">
			<div class="prices left-price fl">
				<p>原价:</p>
				<p>￥<?php echo $resOrder['origin_price'];?></p>
			</div>
			<div class="center-ctn fl">
				<p>
					<span class="bargain-circle" style="width:<?php echo $widthDis; ?>rem;">
						<span class="bargain-after">
							<span class="bargain-position">
								<span>
									已砍 <span class="friend-price" style="font-weight:bold;"><?php echo $resOrder['friend_price'];?> 元</span>
								</span>
								<span>
									红包 <span class="friend-price" style="font-weight:bold;"><?php echo $resOrder['red_price'];?> 元</span>
								</span>
								<span>当前价<span class="red-price" style="font-weight:bold;"><?php echo $distanceMoney;?></span>元</span>
							</span>
						</span>
					</span>
				</p>
			</div>
			<div class="prices right-price fl">
				<p>底价:</p>
				<p>￥<?php echo $resOrder['low_price'];?></p>
			</div>
		</div>
		<div class="bargain-btns clearFix">
			<a href="javascript:;" class="my-self-bargain fl <?php if($isValid==2 || ($resOrder['is_my']==1))echo "active";?>"><span>自己先砍</span></a>
			<a href="javascript:;" class="my-friend-bargain fr <?php if($isValid==2)echo "active";?>"><span>好友帮砍</span></a>
		</div>
	</section>
	
	<!-- bargain-friends -->
	<section class="bargain-friends clearFix">
		<ul class="friends-good fl">
		<?php if($friendGoods){
			foreach ($friendGoods as $key=>$val){
		?>
			<li><?php echo $val['friend_wechat_name'];?>已砍价￥<?php echo $val['money'];?></li>
		<?php }}else{?>
			<li style="color:#ccc;text-align: center;">暂无数据</li>
		<?php }?>
		</ul>
		<ul class="friends-bad fr">
		<?php if($friendBads){
			foreach ($friendBads as $key=>$val){
		?>
			<li><?php echo $val['friend_wechat_name'];?>已加价￥<?php echo $val['money'];?></li>
		<?php }}else{?>
			<li style="color:#ccc;text-align:center;">暂无数据</li>
		<?php }?>
		</ul>
	</section>
	
	<!-- bargain-step -->
	<div class="bargain-step">
		<img src="statics/images/common/bargain-step.jpg" />
	</div>
	
	<!-- goods-details -->
	<section class="goods-details">
		<img src="statics/images/<?php echo $imgNameStr;?>/detail-1.jpg" />
		<img src="statics/images/<?php echo $imgNameStr;?>/detail-2.jpg" />
		<img src="statics/images/<?php echo $imgNameStr;?>/detail-3.jpg" />
		<img src="statics/images/<?php echo $imgNameStr;?>/detail-4.jpg" />
		<img src="statics/images/<?php echo $imgNameStr;?>/detail-5.jpg" />
	</section>
	
	<!-- footers -->
	<section class="footers clearFix">
		<a href="goods.php?openid=<?php echo $openid;?>" class="fl"><span>全部</span></a>
		<a href="myself.php?openid=<?php echo $openid;?>" class="fr"><span>我的</span></a>
	</section>
	
	<!-- mask -->
	<div class="mask"></div>
	
	<!-- share-all -->
	<div class="share-all"></div>
	
	<!-- price-my-success -->
	<section class="price-my-success">
		<span class="price-my-close"></span>
		<p>你为自己砍下<span class="price-my-money"></span>元</p>
	</section>
	
	<!-- red-paper -->
	<?php if(!$resOrder['red_price']){?>
	<div class="red-paper" onclick="location.href = 'goods.php?openid=<?php echo $openid;?>'"></div>
	<?php }?>
	
<script src="statics/js/common.js"></script>
<script src="statics/js/zepto.min.js"></script>
<script src="statics/js/swiper-3.4.2.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
//window.onload = function(){
	//轮播图
	var mySwiper1 = new Swiper ('.swiper', {
	    direction: 'horizontal',
	    loop: true,
	    speed: 600,
	    autoplayDisableOnInteraction : false,
	    autoplay: 2000,
	 	// 如果需要分页器
	    pagination: '.swiper-pagination',
	}); 

	var isLowPrice = <?php echo $isLowPrice;?>;
	var limitTime = <?php echo $limitTime;?>;
	if(isLowPrice!=2){
		$(".limitTime").html(sec_to_time(limitTime));
	
		//启动定时器
		var timer = window.setInterval(function(){
			limitTime--;
			if(limitTime<=0){
				$(".limit-times>p").html("时间已经结束或者砍到了底价");
				clearInterval(timer);  //清除定时器
			}else{
				$(".limitTime").html(sec_to_time(limitTime));
			}
		},1000);
	}

	//将秒数转化为对应的时分秒时间格式
	function sec_to_time(s) {
        if(s > -1){
            var hour = Math.floor(s/3600);
            var min = Math.floor(s/60) % 60;
            var sec = s % 60;
            hour = (hour<10)? "0"+hour: hour;
			min  = (min<10) ? "0"+min : min;
			sec  = (sec<10) ? "0"+sec : sec;
        }
        return hour+"小时"+min+"分"+sec+"秒";
    }
	
//};

//自己先砍
$(".my-self-bargain").on("tap", function(){
	if(!$(".my-self-bargain").hasClass("active")){
		//ajax发送数据到服务器端
		$.ajax({
			url: "apis/common_addSelfPrice.php",
			data: {uid:<?php echo $uid;?>, orderid:<?php echo $orderid;?>},
			type: "post",
			dataType: "json",
			success: function(re){
				console.log(re);
				if(re.status==300){
					alert(re.message);
				}else{
					//自己砍价成功
					var randPrice = re.result.randPrice;
					$(".mask").show();
					$(".price-my-money").html(randPrice);
					$(".price-my-success").show();
				}
			}
		});
	}
});

//自己人品大爆发的关闭的按钮
$(".price-my-close").on("tap", function(){
	location.reload();
});

//好友帮忙砍价
$(".my-friend-bargain").on("tap", function(){
	if(!$(".my-friend-bargain").hasClass("active")){
		$(".mask").show();
		$(".share-all").show();
	}
});
//遮罩层点击触发事件
$(".mask").on("tap", function(){
	$(".mask").hide();
	$(".share-all").hide();
});


//微信JSSDK
wx.config({
    debug: false,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
      // 所有要调用的 API 都要加到这个列表中
      'onMenuShareTimeline',
      'onMenuShareAppMessage'
    ]
});

  wx.ready(function () {
    // 在这里调用 API
  	wx.onMenuShareTimeline({
	    title: '2018旺出好彩头   邀好友砍价“惠”更旺', // 分享标题
	    link: 'http://2wx.ankangdna.com/bargain/share_gap.php?orderid=<?php echo $orderid;?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
	    imgUrl: 'http://2wx.ankangdna.com/bargain/statics/images/common/wx-show.png', // 分享图标
	    success: function () {
	    // 用户确认分享后执行的回调函数
	    },
	    cancel: function(){
		}
	});

	//分享给好友
  	wx.onMenuShareAppMessage({
  		title: '2018旺出好彩头   邀好友砍价“惠”更旺', // 分享标题
  		desc: '<?php echo $descStr;?>', // 分享描述
  		link: 'http://2wx.ankangdna.com/bargain/share_gap.php?orderid=<?php echo $orderid;?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
  		imgUrl: 'http://2wx.ankangdna.com/bargain/statics/images/common/wx-show.png', // 分享图标
  		type: '', // 分享类型,music、video或link，不填默认为link
  		dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
  		success: function () {
  		// 用户确认分享后执行的回调函数
  		},
  		cancel: function () {
  		// 用户取消分享后执行的回调函数
  		}
  	});
  });

</script>
</body>
</html>










































