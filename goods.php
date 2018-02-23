<?php 
header( 'Content-Type:text/html;charset=utf-8');
require_once("apis/config/pdo.class.php");
require_once("apis/config/config.init.php");
require_once("apis/config/function.php");

$openid = $_GET['openid'];

if(!$openid){
	echo "<script>alert('当前页面参数接收错误');</script>";exit;
}

//获取三个goods
$sql   = "select * from ak_bargain_goods where status=1";
$goods = $pdo->query($sql);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no,width=device-width,initial-scale=1.0"/>
    <title>砍价惠更旺</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" href="statics/css/reset.css" />
    <link rel="stylesheet" href="statics/css/common.css" />
    <link rel="stylesheet" href="statics/css/goods.css" />
</head>
<body>

	<!-- banner -->
	<div class="banner">
		<img src="statics/images/goods/banner.jpg"/>
	</div>
	
	<!-- goods-list -->
	<section class="goods-all">
		<ul class="goods-list">
		<?php foreach ($goods as $key=>$val){
				$sql = "select * from ak_bargain_orders where goods_id={$val['id']} order by id desc";
				$myOrders = $pdo->query($sql, "row");
				if($myOrders){
					$sqls = "select * from ak_bargain_users where openid='{$myOrders['openid']}' and status=1";
					$myUser = $pdo->query($sqls, "row");
					$imgStr = $myUser['headimgurl'];
				}else{
					$imgStr = "statics/images/goods/attend-img.jpg";
				}
		?>
			<li>
				<div class="list-left fl" data-gid=<?php echo $val['id'];?>><img src="statics/images/goods/goods-<?php echo $val['id'];?>.jpg"/></div>
				<div class="list-center fl" data-gid=<?php echo $val['id'];?>>
					<h3><?php echo $val['goods_name'];?></h3>
					<div class="attend-persons clearFix">
						<img src="<?php echo $imgStr;?>" class="fl"/>
						<p class="fl"><?php echo $val['attend_persons'];?> 人已参与</p>
					</div>
					<p>原价：<span>￥<?php echo $val['origin_price'];?></span>  底价：<span>￥<?php echo $val['low_price'];?></span></p>
				</div>
				<div class="list-right fl">
					<img class="red-btn" data-gid=<?php echo $val['id'];?> src="statics/images/goods/red-btn.png"/>
					<img class="bargain-btn" data-gid=<?php echo $val['id'];?> src="statics/images/goods/bargain-btn.jpg"/>
				</div>
			</li>
		<?php }?>
		</ul>
	</section>
	
	<!-- footers -->
	<section class="footers clearFix">
		<a href="javascript:;" class="fl"><span>全部</span></a>
		<a href="myself.php?openid=<?php echo $openid;?>" class="fr"><span>我的</span></a>
	</section>
	
	<!-- error-tips -->
	<div class="error-tips">红包已领</div>
	
	<!-- mask -->
	<div class="mask"></div>
	
	<!-- get-success -->
	<div class="get-success">
		<p>恭喜您获得 <span></span> 元大红包</p>
		<a href="javascript:;" class="alert-confirm">确定</a>
	</div>
	
	

<script src="statics/js/common.js"></script>
<script src="statics/js/zepto.min.js"></script>
<script>
	var queryStrObj = null;
	//根据点击的红包按钮，做对应的触发事件
	$(".red-btn").on("tap", function(){
		var gid = this.dataset.gid;
		var openid = '<?php echo $openid;?>';
		//ajax发送数据到服务器端去验证是否可以领取红包
		$.ajax({
			url: "apis/goods_verifyRed.php",
			data: {gid: gid, openid: openid},
			type: "POST",
			dataType: "json",
			success: function(re){
				if(parseInt(re['status'])==300){
					$(".error-tips").show();
					setTimeout(function(){
						$(".error-tips").hide();
					}, 2000);
				}else{//领取成功的提示
					queryStrObj = re.result;
					$(".mask").show();
					$(".get-success>p>span").html(re.result.red_price);
					$(".get-success").show();
				}
			}
		});
	});

	//确定按钮的触发事件
	$(".alert-confirm").on("tap", function(){
		if(!queryStrObj){
			alert("出错了");
			return;
		}
		var queryStr = "uid="+queryStrObj['uid']+"&gid="+queryStrObj['gid']+"&orderid="+queryStrObj['orderid']+"&openid="+queryStrObj['openid'];
		location.href = "goods_details.php?"+queryStr;
		$(".mask").hide();
		$(".get-success").hide();
	});


	//正在砍价的按钮的触发事件
	$(".bargain-btn").on("tap", function(){
		var gid = this.dataset.gid;
		var openid = '<?php echo $openid;?>';
		$.ajax({
			url: "apis/bargainProcessBtn.php",
			data: {gid:gid, openid:openid},
			dataType: "json",
			type: "post",
			success: function(re){
				var res = re.result;
				var queryStr = "uid="+res['uid']+"&gid="+res['gid']+"&orderid="+res['orderid']+"&openid="+res['openid'];
				location.href = "goods_details.php?"+queryStr;
			}
		});
	});

	//正在砍价的按钮的触发事件
	$(".list-left").on("tap", function(){
		var gid = this.dataset.gid;
		var openid = '<?php echo $openid;?>';
		$.ajax({
			url: "apis/bargainProcessBtn.php",
			data: {gid:gid, openid:openid},
			dataType: "json",
			type: "post",
			success: function(re){
				var res = re.result;
				var queryStr = "uid="+res['uid']+"&gid="+res['gid']+"&orderid="+res['orderid']+"&openid="+res['openid'];
				location.href = "goods_details.php?"+queryStr;
			}
		});
	});

	//正在砍价的按钮的触发事件
	$(".list-center").on("tap", function(){
		var gid = this.dataset.gid;
		var openid = '<?php echo $openid;?>';
		$.ajax({
			url: "apis/bargainProcessBtn.php",
			data: {gid:gid, openid:openid},
			dataType: "json",
			type: "post",
			success: function(re){
				var res = re.result;
				var queryStr = "uid="+res['uid']+"&gid="+res['gid']+"&orderid="+res['orderid']+"&openid="+res['openid'];
				location.href = "goods_details.php?"+queryStr;
			}
		});
	});
	

</script>
</body>
</html>
































