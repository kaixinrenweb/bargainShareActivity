<?php 
/**
 * add Self Orders Interface
 * @ctime 2018-02-07 17:40
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//接收参数的信息
$uid     = $_POST['uid'];
$orderid = $_POST['orderid'];

//根据uid的信息获取用户的信息
$sql     = "select * from ak_bargain_users where id={$uid}";
$resUser = $pdo->query($sql, "row");

//根据orderid获取订单的信息
$sqls = "select * from ak_bargain_orders where id={$orderid} and is_my=0";
$resOrder = $pdo->query($sqls, "row");
if(!$resOrder){
	returnDatas(300, "砍价错误", '');
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
$vals = "{$uid},{$orderid},{$randPrice},'{$resUser['wechat_name']}','{$resUser['openid']}','{$resUser['headimgurl']}',1";
$sql = "insert into ak_bargain_details({$keys}) values({$vals})";
$resDetailsId = $pdo->insert($sql);

//更新orders表中的数据信息
$sqls = "update ak_bargain_orders set is_my=1,friend_price=friend_price+{$randPrice} where id={$orderid}";
$res = $pdo->update($sqls);

if($res){
	returnDatas(200, "success", ['randPrice'=>$randPrice]);
}else{
	returnDatas(300, "添加数据错误", $sqls);
}


























































