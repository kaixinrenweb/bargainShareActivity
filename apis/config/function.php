<?php
    //json返回
    function exports($datas){
    	// 指定允许其他域名访问
    	header('Access-Control-Allow-Origin:*');
    	// 响应类型
    	header('Access-Control-Allow-Methods:POST');
    	// 响应头设置
    	header('Access-Control-Allow-Headers:x-requested-with,content-type');
    	echo json_encode($datas,JSON_UNESCAPED_UNICODE);exit;
    }
    
    //curl远程获取信息
    function curl_get($url){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    	$result=curl_exec($ch);
    	curl_close($ch);
    	return $result;
    }
    
    //聚合数据的demo
    function juhecurl($url,$params=false,$ispost=0){
    	$httpInfo = array();
    	$ch = curl_init();
    
    	curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    	curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
    	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
    	curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
    	curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	if( $ispost )
    	{
    		curl_setopt( $ch , CURLOPT_POST , true );
    		curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
    		curl_setopt( $ch , CURLOPT_URL , $url );
    	}
    	else
    	{
    		if($params){
    			curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
    		}else{
    			curl_setopt( $ch , CURLOPT_URL , $url);
    		}
    	}
    	$response = curl_exec( $ch );
    	if ($response === FALSE) {
    		//echo "cURL Error: " . curl_error($ch);
    		return false;
    	}
    	$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    	$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    	curl_close( $ch );
    	return $response;
    }
    
    
    //生成订单号
    function HaveOrders(){
    	$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    	$orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    	return $orderSn;
    }
    
    //密码加密
    function encry($pass){
    	return md5(sha1($pass)."YR");
    }

	//生成token
	function tokens($strs){
		return md5(sha1($strs)."TK".get_total_millisecond());
	}
	
	//生成的毫秒数
	function get_total_millisecond(){
		$time = explode (" ", microtime () );
		$time = $time [1] . ($time [0] * 1000);
		$time2 = explode ( ".", $time );
		$time = $time2 [0];
		return $time;
	}
	
	
/********************************************************************************************************/
	//参数的格式化
	function paramsString($param){
		if($param){
			return trim($param);
		}else{
			return "";
		}
	}
	function paramsInt($param){
		if($param){
			return intval($param);
		}else{
			return "";
		}
	}
	
	//参数的不能为空的验证
	function paramsVerify($param){
		if(!$param){
			exports(['status'=>205,'message'=>"参数不可为空"]);
		}
		return $param;
	}
	
	//返回数据信息（json）
	function returnDatas($code,$msg,$data=""){
		exports(['status'=>$code,'message'=>$msg,'result'=>$data]);
	}
	

/*********************************************************************************************/
	//二维数组根据指定的字段排序
	function arraySequence($array, $field, $sort = 'SORT_DESC'){
		$arrSort = array();
		foreach ($array as $uniqid => $row) {
			foreach ($row as $key => $value) {
				$arrSort[$key][$uniqid] = $value;
			}
		}
		array_multisort($arrSort[$field], constant($sort), $array);
		return $array;
	}
    
	//显示的条数和页数
	function getPages($page,$pageSize){
		return ($page-1)*$pageSize;
	}    
    

/*********************************************************************************************/
	//远程图片保存到本地
	function getImage($url,$save_dir='',$filename='',$type=0){
		if(trim($url)==''){
			return array('file_name'=>'','save_path'=>'','error'=>1);
		}
		if(trim($save_dir)==''){
			$save_dir='./';
		}
		if(trim($filename)==''){//保存文件名
			$ext=strrchr($url,'.');
			if($ext!='.gif'&&$ext!='.jpg'){
				return array('file_name'=>'','save_path'=>'','error'=>3);
			}
			$filename=time().$ext;
		}
		if(0!==strrpos($save_dir,'/')){
			$save_dir.='/';
		}
		//创建保存目录
		if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
			return array('file_name'=>'','save_path'=>'','error'=>5);
		}
		//获取远程文件所采用的方法
		if($type){
			$ch=curl_init();
			$timeout=5;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$img=curl_exec($ch);
			curl_close($ch);
		}else{
			ob_start();
			readfile($url);
			$img=ob_get_contents();
			ob_end_clean();
		}
		//$size=strlen($img);
		//文件大小
		$fp2=@fopen($save_dir.$filename,'a');
		fwrite($fp2,$img);
		fclose($fp2);
		unset($img,$url);
		return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
	}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    