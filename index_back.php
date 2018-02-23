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

//获取砍价发起后的时间差
$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
$configs = $pdo->query($sqlc, "row");

//查询此人是否在我们的数据库里面
$sqls = "select * from ak_bargain_users where openid='{$openid}' and status=1";
$user = $pdo->query($sqls, "row");
if($user){
	foreach ($goods as $key=>$val){
		//根据uid和goods_id查询能不能砍价
		$sqlp = "select * from ak_bargain_orders where uid='{$user['id']}' and goods_id='{$val['id']}' and is_valid=1 and status=1";
		$orders = $pdo->query($sqlp, "row");
		if($orders){ //查到了有该商品的砍价信息
			//判断该砍价的商品有没有过期
			$stime = $orders['stime'];
			$etime = time();
			$times = $configs['config_val']*3600;
			if(($etime-$stime)>$times){ //过期了 ,可以使用了
				$goods[$key]['isValid'] = 1;
				//修改过期
				$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orders['id']}";
				$pdo->update($sqlu);
			}else{//没有过期
				$goods[$key]['isValid'] = 2;
			}
		}else{
			$goods[$key]['isValid'] = 1;
		}
	}
}else{
	foreach ($goods as $key=>$val){
		$goods[$key]['isValid'] = 1;
	}
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no,width=device-width,initial-scale=1.0"/>
    <title>拆礼盒</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" href="statics/css/reset.css" />
    <link rel="stylesheet" href="statics/css/common.css" />
    <link rel="stylesheet" href="statics/css/index.css" />
    <script>
    window.addEventListener('pageshow', function(e) {
        // 通过persisted属性判断是否存在 BF Cache
        if (e.persisted) {
            location.reload();
        }
   });
    </script>
</head>
<body>
<div class="pages">

	<!-- three gifts -->
	<div class="gifts clearFix">
		<?php 
			foreach ($goods as $key=>$val){
				if($val['isValid']==1){
		?>
		<div class="first-gift fl valid-gift" data-goodsid="<?php echo $val['id'];?>"><img src="statics/images/index/gift.png"/></div>
		<?php }else{?>
		<div class="second-gift fl"><img src="statics/images/index/gift-cancel.png"/></div>
		<?php 
			}}
		?>
	</div>
	
	<!-- mask -->
	<div class="mask"></div>
	
	<!-- wang -->
	<div class="wang">
		<img src="statics/images/index/baby-wang.png" />
		<span class="wang-close"></span>
		<a href="javascript:;"></a>
	</div>
	
	<!-- myself -->
	<div class="myself" onclick="location.href='myself.php?openid=<?php echo $openid;?>';"><img src="statics/images/index/my.png"/></div>
	
	<!-- loading -->
	<div class="loading">
		<img src="statics/images/loads.gif"/>
	</div>
	
	<!-- copyright -->
	<div class="copyright">© 上海安康生物市场企划部提供服务</div>
	
</div>

<script src="statics/js/common.js"></script>
<script src="statics/js/zepto.min.js"></script>
<script>
	document.body.addEventListener('touchmove', function(evt) {
	  if(!evt._isScroller) {
	    evt.preventDefault();
	  }
	});

	//图片的名称
	var imgNameArrs = ['baby-wang.png', 'woman-wang.png', 'man-wang.png'];
	var $giftList = $(".valid-gift");
	var $wang     = $(".wang");
	var $mask     = $(".mask");
	var gid       = 1;   //默认的商品的ID
	$.each($giftList, function(index,item){
		$(item).on("tap", function(){
			var goodsId = this.dataset.goodsid;
			gid = goodsId;
			var $wangImg = document.querySelector(".wang>img");
			$wangImg.src = "statics/images/index/"+imgNameArrs[goodsId-1];
			showWang();
		});
	});

	//领礼物关闭
	$(".wang-close").on("tap", function(){
		hideWang();
	});

	//收入囊中
	$(".wang>a").on("tap", function(){
		$mask.css({"zIndex":102});
		$(".loading").show();
		//ajax将数据发送到服务器
		$.ajax({
			url: "apis/index_addOrders.php",
			type: "post",
			data: {gid: gid, openid: '<?php echo $openid;?>'},
			dataType: "json",
			success: function(re){
				console.log(re);
				if(re.status==200){//成功
					var res     = re.result;
					var gid     = res.gid;
					var openid  = res.openid;
					var uid     = res.uid;
					var orderid = res.orderid;
					//alert(uid+"--"+gid+"--"+openid+"--"+orderid);
					var queryStr = "uid="+uid+"&gid="+gid+"&orderid="+orderid+"&openid="+openid;
					window.location.href = "goods_details.php?"+queryStr;
				}else{
					alert(re.message);
					return false;
				}
			}
		});
	});

	//show
	function showWang(){
		$mask.show();
		$wang.show();
	}
	//hide
	function hideWang(){
		$mask.hide();
		$wang.hide();
	}
	
	
</script>

</body>
</html>























































