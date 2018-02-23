<?php
/**
 * myself Attend Interface
 * @ctime 2018-02-09 16:55
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//获取参数的信息
$openid  = $_POST['openid'];
$gid     = $_POST['gid'];

//根据openid获取用户的uid信息
$sql = "select * from ak_bargain_users where openid='{$openid}' and status=1";
$resUser = $pdo->query($sql, "row");
$uid = $resUser['id'];

//根据gid获取产品的信息
$sqls = "select * from ak_bargain_goods where id={$gid} and status=1";
$resGoods = $pdo->query($sqls, "row");

//将goods表中的对应的产品的参与人数+1
$sql = "update ak_bargain_goods set attend_persons=attend_persons+1 where id={$gid} and status=1";
$resUpdate = $pdo->update($sql);

//先判断orders表中有没有此用户的该产品已经添加的信息
$sqls = "select * from ak_bargain_orders where openid='{$openid}' and goods_id={$gid} and is_valid=1 and status=1";
$existsOrders = $pdo->query($sqls, "row");
if(!$existsOrders){ //不存在就添加
	addNewOrders($pdo, $uid, $gid, $resGoods, $openid);
}else{//存在就判断是不是过期和到了最底价
	//判断时间有没有过期
	//获取砍价发起后的时间差
	$sqlc = "select * from ak_bargain_configs where config_name='limit_time' and status=1";
	$configs = $pdo->query($sqlc, "row");
	$stime = $existsOrders['stime'];
	$etime = time();
	$times = $configs['config_val']*3600;
	if(($etime-$stime)>$times){ //过期了
		//修改过期
		$sqlu = "update ak_bargain_orders set is_valid=2 where id={$existsOrders['id']}";
		$pdo->update($sqlu);
		addNewOrders($pdo, $uid, $gid, $resGoods, $openid);
	}else{
		//判断有没有过最低价
		$disPrice = $existsOrders['origin_price']-$existsOrders['friend_price']-$existsOrders['red_price'];
		if($disPrice<=$existsOrders['low_price']){
			//修改
			$sqlu = "update ak_bargain_orders set is_valid=2 where id={$existsOrders['id']}";
			$pdo->update($sqlu);
			addNewOrders($pdo, $uid, $gid, $resGoods, $openid);
		}else{
			//直接返回已经存在的orders
			returnDatas(200, "success", ['uid'=>$uid ,'gid'=>$gid, 'openid'=>$openid, 'orderid'=>$existsOrders['id']]);
		}
	}
}


//添加新的记录信息
function addNewOrders($pdo, $uid, $gid, $resGoods, $openid){
	//根据用户的UID和openid信息将数据插入到数据表中去
	$time     = time();            //当前的时间戳
	$orderNum = HaveOrders();      //生成的订单号
	$keys = "uid,openid,goods_id,origin_price,low_price,order_num,stime,goods_name";
	$vals = "'{$uid}','{$openid}','{$gid}',{$resGoods['origin_price']},{$resGoods['low_price']},'{$orderNum}','{$time}','{$resGoods['goods_name']}'";
	$sql = "insert into ak_bargain_orders({$keys}) values({$vals})";
	$res = $pdo->insert($sql);
	if($res){
		returnDatas(200, "success", ['uid'=>$uid ,'gid'=>$gid, 'openid'=>$openid, 'orderid'=>$res]);
	}else{
		returnDatas(300, "添加砍价订单失败", $sql);
	}	
}





















































