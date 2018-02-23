<?php 
header( 'Content-Type:text/html;charset=utf-8');
require_once("apis/config/pdo.class.php");
require_once("apis/config/config.init.php");
require_once("apis/config/function.php");

$orderid = $_GET['orderid'];
$openid  = $_GET['openid'];

//获取砍价发起后的时间差
$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
$configs = $pdo->query($sqlc, "row");
//根据orderid获取一些基本情况
$sqls = "select * from ak_bargain_orders where id={$orderid}";
$resOrder = $pdo->query($sqls, "row");
$gid = $resOrder['goods_id'];
$imgNameStr = "";
if($gid==1){
	$imgNameStr = "children";
}else if($gid==2){
	$imgNameStr = "woman";
}else if($gid==3){
	$imgNameStr = "man";
}

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

//判断此好友有没有砍过价
$sqls = "select * from ak_bargain_details where friend_openid='{$openid}' and order_id={$orderid} and status=1";
$resDetail = $pdo->query($sqls, "row");
$isShared = 1;       //还没有分享
if($resDetail){
	$isShared = 2;   //分享过了
}

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
		<p>已经砍到了底价，可直接购买</p>
		<?php }else{?>
		<p>距活动结束还有 <span class="limitTime"><!-- 07小时32分15秒 --></span></p>
		<?php }?>
	</div>
	
	<!-- goods-name -->
	<section class="goods-name">
		<h3><?php echo $resOrder['goods_name'];?></h3>
		<p>底价: <span><?php echo $resOrder['low_price'];?></span>元      库存<span>3000</span>个名额（已购1263个)</p>
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
			<a href="javascript:;" class="my-self-bargain fl <?php if($isValid==2 || ($isShared==2))echo "active";?>"><span>帮TA砍价</span></a>
			<a href="javascript:;" class="my-friend-bargain fr"><span>我也参与</span></a>
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
		<p>你帮助好友砍下<span class="price-my-money"></span>元</p>
	</section>
	
<script src="statics/js/common.js"></script>
<script src="statics/js/zepto.min.js"></script>
<script src="statics/js/swiper-3.4.2.min.js"></script>
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


//我也参与
$(".my-friend-bargain").on("tap", function(){
	//ajax传送数据到数据库
	$.ajax({
		url: "apis/share_myselfAttend.php",
		data: {gid: <?php echo $resOrder['goods_id'];?>, openid:'<?php echo $openid;?>'},
		type: "post",
		dataType: "json",
		success: function(re){
			if(parseInt(re['status'])==200){
				var res     = re.result;
				var gid     = res.gid;
				var openid  = res.openid;
				var uid     = res.uid;
				var orderid = res.orderid;
				var queryStr = "uid="+uid+"&gid="+gid+"&orderid="+orderid+"&openid="+openid;
				window.location.href = "goods_details.php?"+queryStr;
			}else{
				alert(re.message);
			}
		}
	});
});

</script>
</body>
</html>















































































