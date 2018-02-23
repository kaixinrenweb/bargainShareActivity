<?php
header("content-type:text/html;charset=utf-8");

$orderid = $_GET['orderid'];

header("location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=&redirect_uri=http%3A%2F%2F2wx.ankangdna.com%2Fbargain%2Fshare.php&response_type=code&scope=snsapi_userinfo&state={$orderid}#wechat_redirect");
