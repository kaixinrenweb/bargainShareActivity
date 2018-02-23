<?php
/**
 * Bargain Process Button Interface
 * @ctime 2018-02-09 14:28
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//接收参数信息
$gid    = $_POST['gid'];
$openid = $_POST['openid'];

//查询此openid有没有goods_id的记录信息
$sql = "select * from ak_bargain_orders where goods_id={$gid} and openid='{$openid}' and is_valid=1";
$resOrder = $pdo->query($sql, "row");

if(!$resOrder){
	//没有的话，那直接就可以领取红包了
	$did = addNewOrder($pdo, $gid, $openid);
	returnDatas(200, "add success", $did);
}else{
	$orderid = $resOrder['id'];
	//检查是不是过时了
	$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
	$configs = $pdo->query($sqlc, "row");
	$stime = $resOrder['stime'];
	$etime = time();
	$times = $configs['config_val']*3600;
	if(($etime-$stime)>$times){ //过期了 
		//修改过期
		$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
		$pdo->update($sqlu);
		$did = addNewOrder($pdo, $gid, $openid);
		returnDatas(200, "add success", $did);
	}else{
		//判断有没有过最低价
		$disPrice = $resOrder['origin_price']-$resOrder['friend_price']-$resOrder['red_price'];
		if($disPrice<=$resOrder['low_price']){//过了最底价
			//修改
			$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
			$pdo->update($sqlu);
			$did = addNewOrder($pdo, $gid, $openid);
			returnDatas(200, "add success", $did);
		}else{
			//没有到最底价也没有时间过期
			//直接的返回相关的数据信息
			returnDatas(200, "query Success", ['uid'=>$resOrder['uid'],'gid'=>$resOrder['goods_id'],'openid'=>$resOrder['openid'],'orderid'=>$resOrder['id']]);
		}
	}
}


//添加一个新的order的记录信息
function addNewOrder($pdo, $gid, $openid){
	//根据openid获取uid的信息
	$sqls = "select * from ak_bargain_users where openid='{$openid}' and status=1";
	$users = $pdo->query($sqls, "row");
	//根据gid获取goods的信息
	$sql = "select * from ak_bargain_goods where id={$gid}";
	$goods = $pdo->query($sql, "row");
	$keys = "uid,openid,goods_id,goods_name,origin_price,low_price,order_num,stime";
	$stime = time();
	$orderNum = HaveOrders();
	$vals = "{$users['id']},'{$openid}',{$gid},'{$goods['goods_name']}',{$goods['origin_price']},{$goods['low_price']},'{$orderNum}','{$stime}'";
	$sqli = "insert into ak_bargain_orders({$keys}) values({$vals})";
	$res = $pdo->insert($sqli);
	return ['openid'=>$openid,'uid'=>$users['id'],'gid'=>$gid,'orderid'=>$res];
}















































































