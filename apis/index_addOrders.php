<?php
/**
 * addOrders Interface
 * @ctime 2018-02-06 17:45
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//获取参数的信息
$openid = $_POST['openid'];
$gid    = $_POST['gid'];

//根据openid获取用户的uid信息
$sql = "select * from ak_bargain_users where openid='{$openid}' and status=1";
$resUser = $pdo->query($sql, "row");
$uid = $resUser['id'];

//根据gid获取产品的信息
$sqls = "select * from ak_bargain_goods where id={$gid} and status=1";
$resGoods = $pdo->query($sqls, "row");
$redMoney = $resGoods['red_money'];

//将goods表中的对应的产品的参与人数+1
$sql = "update ak_bargain_goods set attend_persons=attend_persons+1 where id={$gid} and status=1";
$resUpdate = $pdo->update($sql);

//根据用户的UID和openid信息将数据插入到数据表中去
$time     = time();            //当前的时间戳
$orderNum = HaveOrders();      //生成的订单号
$keys = "uid,openid,goods_id,red_price,origin_price,low_price,order_num,stime,goods_name";
$vals = "'{$uid}','{$openid}','{$gid}','{$redMoney}',{$resGoods['origin_price']},{$resGoods['low_price']},'{$orderNum}','{$time}','{$resGoods['goods_name']}'";
$sql = "insert into ak_bargain_orders({$keys}) values({$vals})";
$res = $pdo->insert($sql);

if($res){
	returnDatas(200, "success", ['uid'=>$uid ,'gid'=>$gid, 'openid'=>$openid, 'orderid'=>$res]);
}else{
	returnDatas(300, "添加砍价订单失败", $sql);
}



















































