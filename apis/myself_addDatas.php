<?php
/**
 * add some Infos Interface
 * @ctime 2018-02-10 13:30
 * @author tales
 *
 */
header("content-type:text/html;charset=utf-8");

//预加载所需的配置文件信息
require_once("config/pdo.class.php");
require_once("config/config.init.php");
require_once("config/function.php");

//接收参数信息
$openid   = $_POST['openid'];
$uname    = $_POST['uname'];
$uphone   = $_POST['uphone'];
$uaddress = $_POST['uaddress'];

$sqls = "select * from ak_bargain_users where phone='{$uphone}' and status=1";
$resUser = $pdo->query($sqls, "row");
if($resUser){
	returnDatas(300, "此手机号码已经存在了", "");
}

//插入数据信息到相关的数据表中去。ak_bargain_users
$sql = "update ak_bargain_users set true_name='{$uname}',phone='{$uphone}',address='{$uaddress}' where openid='{$openid}' and status=1";
$res = $pdo->update($sql);

if($res){
	returnDatas(200, "success", "");
}else{
	returnDatas(300, "添加数据失败", $sql);
}













































































