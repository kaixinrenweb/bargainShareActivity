<?php 
/**
 * friend add price Interface
 * @ctime 2018-02-09 16:30
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//接收参数的信息
$orderid     = $_POST['orderid'];
$openid      = $_POST['openid'];
$wechat_name = $_POST['wechat_name'];
$headimgurl  = $_POST['headimgurl'];

//根据orderid获取订单的信息
$sqls = "select * from ak_bargain_orders where id={$orderid}";
$resOrder = $pdo->query($sqls, "row");
if(!$resOrder){
	returnDatas(300, "砍价错误", '');
}

$sqls = "select * from ak_bargain_details where friend_openid='{$openid}' and order_id={$orderid} and status=1";
$resDetail = $pdo->query($sqls, "row");
if($resDetail){
	returnDatas(300, "您已经砍过了", '');
}

//判断时间有没有过期
//获取砍价发起后的时间差
$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
$configs = $pdo->query($sqlc, "row");
$stime = $resOrder['stime'];
$etime = time();
$times = $configs['config_val']*3600;
if(($etime-$stime)>$times){ //过期了 
	//修改过期
	$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
	$pdo->update($sqlu);
	returnDatas(300, "活动已经过期", '');
}

//判断有没有过最低价
$disPrice = $resOrder['origin_price']-$resOrder['friend_price']-$resOrder['red_price'];
if($disPrice<=$resOrder['low_price']){
	//修改
	$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
	$pdo->update($sqlu);
	returnDatas(300, "已经低于低价", '');
}

//判断目前有多少人已经帮TA砍价了
$sql = "select count(*) as nums from ak_bargain_details where order_id={$orderid} and status=1";
$resDetails = $pdo->query($sql, "row");
$totalNums = $resDetails['nums'];
if(($totalNums!=0) && ($totalNums%3==0)){
	//减价
	$randPrice = mt_rand(1, 10);
	//添加数据信息到details的表中
	$keys = "uid,order_id,money,friend_wechat_name,friend_openid,friend_headimgurl,is_type";
	$vals = "{$resOrder['uid']},{$orderid},{$randPrice},'{$wechat_name}','{$openid}','{$headimgurl}',2";
	$sql = "insert into ak_bargain_details({$keys}) values({$vals})";
	$resDetailsId = $pdo->insert($sql);
	
	//更新orders表中的数据信息
	$sqls = "update ak_bargain_orders set friend_price=friend_price-{$randPrice} where id={$orderid}";
	$res = $pdo->update($sqls);
	if($res){
		returnDatas(200, "success", ['types'=>'subs','randPrice'=>$randPrice]);
	}else{
		returnDatas(300, "添加数据错误", $sqls);
	}
}else{
	//加价
	//产生一个随机的砍价的价格
	$disPrice = $disPrice-$resOrder['low_price'];
	$randPrice = 0;
	if($disPrice>200){
		$randPrice = mt_rand(1, 200);
	}else{
		$randPrice = mt_rand(1, $disPrice);
	}
	//添加数据信息到details的表中
	$keys = "uid,order_id,money,friend_wechat_name,friend_openid,friend_headimgurl,is_type";
	$vals = "{$resOrder['uid']},{$orderid},{$randPrice},'{$wechat_name}','{$openid}','{$headimgurl}',1";
	$sql = "insert into ak_bargain_details({$keys}) values({$vals})";
	$resDetailsId = $pdo->insert($sql);
	
	//更新orders表中的数据信息
	$sqls = "update ak_bargain_orders set friend_price=friend_price+{$randPrice} where id={$orderid}";
	$res = $pdo->update($sqls);
	if($res){
		returnDatas(200, "success", ['types'=>"adds",'randPrice'=>$randPrice]);
	}else{
		returnDatas(300, "添加数据错误", $sqls);
	}
}



























































