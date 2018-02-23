<?php
/**
 * verify Address Interface
 * @ctime 2018-02-10 13:10
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//接收参数信息
$openid = $_POST['openid'];

//查询该用户的相关的地址号码信息
$sql = "select * from ak_bargain_users where openid='{$openid}' and status=1";
$resUser = $pdo->query($sql, "row");

if($resUser['phone']){
	returnDatas(200, "已经存在了", '');
}else{
	returnDatas(300, "还不存在呢", "");
}














































































