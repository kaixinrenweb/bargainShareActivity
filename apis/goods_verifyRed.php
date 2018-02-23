<?php
/**
 * verifyRedMoney Interface
 * @ctime 2018-02-09 11:20
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
	returnDatas(200, "可以领取红包", $did);
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
		returnDatas(200, "活动过期可以重新开始一个新的order", $did);
	}else{
		//判断有没有过最低价
		$disPrice = $resOrder['origin_price']-$resOrder['friend_price']-$resOrder['red_price'];
		if($disPrice<=$resOrder['low_price']){//过了最底价
			//修改
			$sqlu = "update ak_bargain_orders set is_valid=2 where id={$orderid}";
			$pdo->update($sqlu);
			$did = addNewOrder($pdo, $gid, $openid);
			returnDatas(200, "活动已经过于最底价了可以重新开始一个order", $did);
		}else{
			//判断此order有没有领取红包
			if($resOrder['red_price']){
				returnDatas(300, "不可以重复领取红包哦", ['orderid'=>$resOrder['id']]);
			}else{
				//根据gid获取goods的red_money
				$sql = "select * from ak_bargain_goods where id={$gid}";
				$resGoods = $pdo->query($sql, "row");
				//判断当前的order中的红包领取后是不是会超出底价
				$mayPrice = $resOrder['origin_price']-$resOrder['friend_price'];
				if($mayPrice<=$resOrder['low_price']){
					$maybeRedPrice = 0;
				}else{
					$gapPrice = $mayPrice-$resGoods['red_money'];   //还剩余要砍的钱 198-200
					if($gapPrice<=$resOrder['low_price']){  //如果剩余要砍的钱比底价
						$maybeRedPrice = $mayPrice - $resOrder['low_price'];
					}else{
						$maybeRedPrice = $resGoods['red_money'];
					}
				}
				$sql = "update ak_bargain_orders set red_price = {$maybeRedPrice} where id={$resOrder['id']}";
				$resRecord = $pdo->update($sql);
				returnDatas(200, "更新红包成功", ['openid'=>$resOrder['openid'],'uid'=>$resOrder['uid'],'gid'=>$resOrder['goods_id'],'orderid'=>$resOrder['id'], 'red_price'=>$maybeRedPrice]);
			}
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
	$keys = "uid,openid,goods_id,red_price,goods_name,origin_price,low_price,order_num,stime";
	$stime = time();
	$orderNum = HaveOrders();
	$vals = "{$users['id']},'{$openid}',{$gid},{$goods['red_money']},'{$goods['goods_name']}',{$goods['origin_price']},{$goods['low_price']},'{$orderNum}','{$stime}'";
	$sqli = "insert into ak_bargain_orders({$keys}) values({$vals})";
	$res = $pdo->insert($sqli);
	return ['openid'=>$openid,'uid'=>$users['id'],'gid'=>$gid,'orderid'=>$res, 'red_price'=>$goods['red_money']];
}















































































