<?php 
header( 'Content-Type:text/html;charset=utf-8');
require_once("apis/config/pdo.class.php");
require_once("apis/config/config.init.php");
require_once("apis/config/function.php");

$openid = $_GET['openid'];

if(!$openid){
	echo "<script>alert('页面参数错误~~');</script>";exit;
}

//根据openid获取用户的基本信息和头像
$sql = "select * from ak_bargain_users where openid='{$openid}' and status=1";
$resUser = $pdo->query($sql, "row");

//获取超时的时间间隔
$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
$configs = $pdo->query($sqlc, "row");

//查出该用户下的已经发起的砍价的活动的信息
$sqls = "select * from ak_bargain_orders where openid='{$openid}' and status=1 order by goods_id";
$resOrders = $pdo->query($sqls);
if($resOrders){
	foreach ($resOrders as $key=>$val){
		if($val['is_valid']==1){//检测下是不是真的没有过期
			//先检查时间
			$stime = $val['stime'];
			$etime = time();
			$times = $configs['config_val']*3600;
			if(($etime-$stime)>=$times){ //过期了
				//修改过期
				$sqlu = "update ak_bargain_orders set is_valid=2 where id={$val['id']}";
				$pdo->update($sqlu);
				$resOrders[$key]['is_valid'] = 2;
			}
			//检查是不是要底价了
			$disPrice = $val['origin_price']-$val['friend_price']-$val['red_price'];
			if($disPrice<=$val['low_price']){
				//修改
				$sqlu = "update ak_bargain_orders set is_valid=2 where id={$val['id']}";
				$pdo->update($sqlu);
				$resOrders[$key]['is_valid'] = 2;
			}
		}
	}
}

$flag = false;
$friendSum = 0;
$redSum    = 0;
$resultSum = 0;
foreach ($resOrders as $key=>$val){
	if($val['is_valid']==2){
		$flag = true;
		$friendSum += $val['friend_price'];
		$redSum    += $val['red_price'];
		$resultSum += $val['origin_price']-$val['friend_price']-$val['red_price'];
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no,width=device-width,initial-scale=1.0"/>
    <title>我的砍价</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" href="statics/css/reset.css" />
    <link rel="stylesheet" href="statics/css/common.css" />
    <link rel="stylesheet" href="statics/css/myself.css" />
</head>
<body>

	<!-- headers -->
	<section class="headers clearFix">
		<div class="person-img fl"><img src="<?php echo $resUser['headimgurl'];?>"/></div>
		<p class="fl"><?php echo $resUser['wechat_name'];?></p>
	</section>
	
	<!-- bargain-list -->
	<section class="bargain-list">
		<h3>正在砍价</h3>
		<?php if(!$resOrders){?>
		<div class="no-bargain">
			<img src="statics/images/myself/dao.jpg"/>
			<p>空空如也，快去砍价吧</p>
			<a href="goods.php?openid=<?php echo $openid;?>">去砍价</a>
		</div>
		<?php }else{?>
		<ul class="list-infos">
		<?php foreach ($resOrders as $key=>$val){?>
			<li class="<?php if($val['is_valid']==2){echo "finished";}?>" onclick="location.href='<?php echo "goods_details.php?uid={$val['uid']}&gid={$val['goods_id']}&orderid={$val['id']}&openid={$openid}";?>';">
				<h3><?php echo $val['goods_name'];?></h3>
				<div class="list-desc clearFix">
					<div class="about-price fl">
						<p>原价：</p>
						<p><?php echo $val['origin_price'];?>元</p>
					</div>
					<div class="price-process fl">
						<p>已砍价<?php echo $val['friend_price'];?>元，红包已抵<?php echo $val['red_price'];?>元，当前价<span><?php echo $val['origin_price']-$val['friend_price']-$val['red_price'];?></span>元</p>
						<div class="process-detail">
							<span style="width:<?php echo round(($val['friend_price']+$val['red_price'])/$val['origin_price']*5, 2); ?>rem;"></span>
						</div>
					</div>
					<div class="about-price fr">
						<p>底价：</p>
						<p><?php echo $val['low_price'];?>元</p>
					</div>
				</div>
			</li>
			<?php }?>
		</ul>
		<?php }?>
	</section>
	
	<!-- activity-regular -->
	<section class="activity-regular">
		<h3>活动规则</h3>
		<p>1.用户针对每款产品，可发起一次申请</p>
		<p>2.每位好友收到邀请后，可为发起者进行砍/加价1次</p>
		<p>切记：<span>邀请好友越多，砍价越低</span></p>
		<p>3.本活动有效期：即日起——2018年3月2日</p>
		<p>4.活动最终解释权归安康集团所有</p>
		<p class="my-tel">24小时服务热线：<a href="tel:400-066-2126">400-066-2126</a></p>
	</section>
	
	<!-- footers -->
	<section class="footers clearFix">
		<a href="goods.php?openid=<?php echo $openid;?>" class="fl"><span>全部</span></a>
		<a href="javascript:;" class="fr"><span>我的</span></a>
	</section>
	
	<!-- buy -->
	<section class="buy">
		<a href="javascript:;">结算</a>
		<?php if($flag){?>
		<div class="total-num fr">
			<p>合计：<span>￥<?php echo $resultSum;?></span></p>
			<p>已砍价￥<?php echo $friendSum;?> 红包已抵￥<?php echo $redSum;?></p>
		</div>
		<?php }?>
	</section>
	
	<!-- mask -->
	<div class="mask"></div>
	
	<!-- person-infos -->
	<section class="person-infos">
		<h3>* 接收采样工具必填</h3>
		<div class="input-sth">
			<div class="per-line clearFix">
				<p class="fl">收货人姓名：</p>
				<div class="input-wrap fl"><input type="text" class="person-name"/></div>
			</div>
			<div class="per-line clearFix">
				<p class="fl">手机号码：</p>
				<div class="input-wrap fl"><input type="text" class="person-phone"/></div>
			</div>
			<div class="per-line clearFix" id="text-area">
				<p class="fl">详细地址：</p>
				<div class="input-wrap fl">
					<textarea class="person-address"></textarea>
				</div>
			</div>
		</div>
		<a href="javascript:;">确认</a>
	</section>
	
	<!-- customer-service -->
	<section class="customer-service">
		<img src="statics/images/myself/wx.png"/>
		<p>春节期间，请加客服微信，VIP一对一专属服务</p>
		<p>长按客服妹子二维码，即可加好友啦</p>
	</section>
	
<script src="statics/js/common.js"></script>
<script src="statics/js/zepto.min.js"></script>
<script>

//结算按钮的触发事件
$(".buy>a").on("tap", function(){
	//到数据库判断下该用户是否需要填写相关的地址号码和姓名的信息
	$.ajax({
		url: "apis/myself_verifyAddress.php",
		data: {openid:'<?php echo $openid;?>'},
		dataType: "json",
		type: "post",
		success: function(re){
			if(parseInt(re['status'])==300){
				$(".mask").on("click", function(){
					//...
				});
				//还不存在
				$(".mask").show();
				$(".person-infos").show();
			}else{
				//已经存在了
				$(".mask").show();
				$(".mask").on("click", function(){
					$(".mask").hide();
					$(".customer-service").hide();
				});
				$(".customer-service").show();
			}
		}
	});
});

//地址信息的按钮的触发事件
$(".person-infos>a").on("tap", function(){
	//获取基本的数据的信息
	var uname    = $(".person-name").val();
	var uphone   = $(".person-phone").val();
	var uaddress = $(".person-address").val();
	if(uname==""){
		alert("请填写姓名");
		return false;
	}
	if(uphone==""){
		alert("请填写手机号码");
		return false;
	}else if(!/^1\d{10}$/.test(uphone)){
		alert("请填写正确格式的手机号码");
		return false;
	}
	if(uaddress==""){
		alert("请填写地址信息");
		return false;
	}
	//将数据信息发送到数据库
	$.ajax({
		url: "apis/myself_addDatas.php",
		data: {openid:'<?php echo $openid;?>', uname: uname, uphone:uphone, uaddress:uaddress},
		dataType: "json",
		type: "post",
		success: function(re){
			console.log(re);
			if(parseInt(re['status'])==200){
				//添加成功
				$(".person-infos").hide();
				$(".mask").on("click", function(){
					$(".mask").hide();
					$(".customer-service").hide();
				});
				$(".customer-service").show();
			}else{
				//添加失败
				alert(re.message);
			}
		}
	});
});

</script>
</body>
</html>










































































