<?php
	header("Content-Type:text/html;charset=UTF-8");
	date_default_timezone_set("Asia/Shanghai");
	
	//include_once ROOT_PATH. 'languages/zh_cn/common.php';
	include_once ROOT_PATH. 'httputils/httpRequest.php';
	
	/**
	 * 该方法测试用
	 */
	function orderReportTest()
	{
		$userId = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		$customCode = $GLOBALS['_LANG']['kj_customs_code'];
		$orgName= $GLOBALS['_LANG']['kj_org_name'];
		$msgtype = 'cnec_jh_order';
		$customs = '3105';
		
		
		$currentTime = date("Y-m-d H:i:s");
		
		$sign = md5($userId . $pwd . $currentTime);
		
		$orderNo = '8973777301';
		$operation = '0';
		$mftNo = '';
		$orderForm = '0000';
		$goodsAmount = 375.8;
		$postFee = 10;
		$amount = 375.8;
		$buyerAccount = 'polober';
		$taxAmount = 0;
		$disAmount = 0;
		
		$proAmount = 0;
		$proRemark = '无优惠';
		
		$productId =  '310520156140000003' ;
		$goodsName =  'Huggies好奇 女宝成长训练裤4-5岁17-23公斤 72片' ;
		$qty =  10 ;
		$unit =  '件' ;
		$price =  36.58 ;
		$amount1 = 365.8;
		
		$goods_str = "<Goods><Detail><ProductId>" . $productId . "</ProductId><GoodsName>" . $goodsName . 
		"</GoodsName><Qty>" . $qty . "</Qty><Unit>" . $unit . "</Unit><Price>" . $price . "</Price><Amount>" . $amount1 . 
		"</Amount></Detail></Goods>";
		
		$order_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . 
		$customCode . "</CustomsCode><OrgName>" . $orgName . "</OrgName><CreateTime>" . $currentTime .
		 "</CreateTime></Header><Body><Order><Operation>" . $operation . "</Operation><MftNo>" . $mftNo . 
		 "</MftNo><OrderFrom>" . $orderForm . "</OrderFrom><OrderNo>" . 
		$orderNo . "</OrderNo><PostFee>" . $postFee . 
		"</PostFee><Amount>" . $amount . "</Amount><BuyerAccount>" . $buyerAccount . "</BuyerAccount><TaxAmount>" .
		 $taxAmount . "</TaxAmount><DisAmount>". $disAmount ."</DisAmount><Promotions/>" . $goods_str . "</Order></Body></Message>";	

		print_r($order_xmlstr . "\n");
		
		$postField = array("userid" => $userId,"timestamp" => $currentTime,"sign" => $sign,"xmlstr" => $order_xmlstr,"msgtype" => $msgtype ,"customs" => $customs);
		
		print_r($postField);
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		
		$result = curl_exec($httpRequest);
		
		curl_close($httpRequest);
		
		print_r($result);
	}
	
	/**
	 *  方法描述：
	 *  	申报订单
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $mftNo
	 */
	function orderReport($merchantCode,$merchantName,$createTime,$orderData,$payData,$logisData)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "orderReport";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$mftNo = "15051401000018 ";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,"merchantName" => $merchantName,
		"createTime" => $createTime,"orderData" => $orderData,"payData" => $payData,"logisData" => $logisData);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("订单关闭结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("订单关闭异常：" .$e);
		}
		return $server_output;

	}
	
	/**
	 *  方法描述：
	 *  	关闭订单
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $mftNo
	 */
	function closeOrder($merchantCode,$merchantName,$mftNo)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "closeOrder";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$mftNo = "15051401000018 ";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"mftNo" => $mftNo);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			
			//print_r("订单关闭结果：" .$server_output);
                        if($obj==null){
                            logResult("订单关闭  请求超时.");
                        }else if($obj['code']!='200'){
                            logResult("订单关闭  返回错误代码：".$obj['code']."错误描述：".$obj['desc']." 申报单号：".$mftNo);
                        }
                        
			return $obj;
		} catch (Exception $e) 
		{
			print_r("订单关闭异常：" .$e);
		}
		return $server_output;

	}
	/**
	 *  方法描述：
	 *  	申报单号查询
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $orderNo
	 * @param unknown_type $orderFrom
	 */
	function queryOrderNoList($merchantCode,$merchantName,$orderNo,$orderFrom)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryOrderNoList";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$orderNo = "8973777130";
		$orderFrom = "0000";*/
                //$orderNo = "8973777130";
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"orderNo" => $orderNo,"orderFrom" => $orderFrom);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			
			//print_r("申报单号查询结果：" .$server_output);
                        //申报单号查询结果：{"code":"200","desc":"{0}-操作成功！","result":{"mftList":[{"mftNo":"15031101000056","status":"24"}]}} 
                        if($obj==null){
                            logResult("申报单号查询  请求超时.");
                        }else if($obj['code']!='200'){
                            logResult("申报单号查询  返回错误代码：".$obj['code']."错误描述：".$obj['desc']." 订单号：".$orderNo);
                        }
                        
			return $obj;
		} catch (Exception $e) 
		{
			print_r("申报单号查询异常：" .$e);
		}
		return $server_output;

	}
	
	/**
	 * 方法描述：
	 *     商品备案查询
	 * Enter description here ...
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $orderNo
	 */
	function queryCommodityRecord($merchantCode,$merchantName,$productId)
	{
		
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "getGoods";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		$orderSource=$GLOBALS['_LANG']['orderSource'];
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$productId = "310520156140000004";*/

		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"goodsSku" => $productId, 'orderSource' => $orderSource);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			logResult("商品查询结果：".$server_output);
            //print_r("商品备案查询结果：" .$server_output);
            if($obj==null){
                logResult("商品备案查询  请求超时.");
            }else if($obj['code']!='200'){
                logResult("商品备案查询 kj_sn：".$productId."  返回错误代码：".$obj['code']."错误描述：".$obj['desc']);
            }
            return $obj;
		} catch (Exception $e) 
		{
			print_r("商品备案查询异常：" .$e);
		}
		return $server_output;
		
	}
	
	/**2016.04.20
	 * 方法描述：调用接口查询订单的税费
	 */
	function apiComputeTariff($shipping_fee,$shipping_insure,$goods){

	    //系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "getTaxPrice";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
	
	    //业务参数
	    $postField = array(
	        "appId" => $appId,
	        "method" => $method,
	        "version" => $version,
	        "nonce" => $nonce,
	        "timestamp" => $timestamp,
	    );
	    $goodsData=array();
	    $goodsData['orderSource']=$GLOBALS['_LANG']['orderSource'];
	    $goodsData['postFee']=$shipping_fee;
	    $goodsData['insuranceFee']=$shipping_insure;
	    $goodsData['goodDetails']=array();
	    foreach($goods as $val){
	        $goodsData['goodDetails'][]=array(
	            'goodsSku'=>$val['goods_sn'],
	            'price'=>$val['goods_price'],
	            'quantity'=>$val['goods_number']
	        );
	    }
	    $postField['goodsData']=json_encode($goodsData);
	
	    $sign = getSign($postField, $appSecret);
	    $postField['sign'] = $sign;
	
	    logResult('订单税费计算接口调用，参数:'.json_encode($postField));
	    
	    $httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
	    
	    try{
	        $server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			logResult('订单税费计算接口调用，返回结果:'.$server_output);
			
			$obj = json_decode($server_output,true);
	        
	        if ($obj == null) {
	            logResult("查询订单的税费 请求超时.");
	        } else{
	            if ($obj['code'] != '200') {
	                logResult("查询订单的税费   返回错误代码：" . $obj['code'] . "，错误描述：" . $obj['desc']);
	            }
	        }
	        return $obj;
	    } catch (Exception $e)
	    {
	        logResult("查询订单的税费异常：" .$e);
	    }
	    return $server_output;
	}
	
	
	
	/**
	 * 方法描述：
	 *     消费者姓名身份证查询
	 * Enter description here ...
	 * @param unknown_type $orderSource
	 * @param unknown_type $account
	 * @param unknown_type $type 1查询，2解绑
	 */
	function queryConsumerInfo($orderSource,$account,$type)
	{
		
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryConsumerInfo";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$orderFrom = "0000";
		$account = "polober";*/
		
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"orderSource" => $orderSource,
		    "account" => $account,
		    "type" =>$type);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			$obj = json_decode($server_output,true);
			
			//print_r("消费者姓名身份证查询结果：" .$server_output);
			return $obj;
		} catch (Exception $e) 
		{
			print_r("消费者姓名身份证查询查询异常：" .$e);
		}
		return $obj;
		
	}
	
	/**
	 * 方法描述：
	 *     绑定消费者帐号
	 * @param unknown_type $orderSource
	 * @param unknown_type $account
	 * @param unknown_type $type 1查询，2解绑
	 */
	function bindConsumerInfo($orderSource,$bind_info)
	{
	
	    //系统参数
	    $appId = $GLOBALS['_LANG']['appId'];
	    $appSecret = $GLOBALS['_LANG']['appSecret'];
	    $method = "userRegister";
	    $version = $GLOBALS['_LANG']['version'];
	    $nonce = rand(1, 10000);
	    $timestamp = date('YmdHis');
	
	    //业务参数
	    /*		$orderFrom = "0000";
	     $account = "polober";*/
	    $userData=json_encode(
	        array(
    	        "orderSource" => $orderSource,
    	        "account" => $bind_info['account'],
    	        "idNum"=>$bind_info['id_num'],
    	        "name" =>$bind_info['real_name'],
    	        "phone"=>  $bind_info['phone'],
                "email"=>  $bind_info['email']
	        )
	     );
	    //print_r("\n用户数据：".$userData."\n");
	
	    $postField = array("appId" => $appId,"method" => $method,"version" => $version,
	        "nonce" => $nonce,"timestamp" => $timestamp ,"userData" => $userData
	    );
	
	    $sign = getSign($postField, $appSecret);
	    //print_r("\n签名值：".$sign."\n");
	    
	    $postField['sign'] = $sign;
	
	    //var_dump($postField);
	
	    $httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
	    
	    try
	    {
	        $server_output = curl_exec($httpRequest);
	        curl_close($httpRequest);
	        $obj = json_decode($server_output,true);
	        
	        //print_r("消费者姓名身份证绑定结果：" .$server_output);
	        
	        return $obj;
	    } catch (Exception $e)
	    {
	        print_r("消费者姓名身份证绑定异常：" .$e);
	    }
	    return $server_output;
	
	}
	
	/**
	 *  方法描述：
	 *  	申报状态查询：根据申报单号
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $orderNo
	 */
	function queryStatusByReportNo($merchantCode,$merchantName,$mftNo)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryStatusByReportNo";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$orderNo = "8973777130";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"mftNo" => $mftNo);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("申报状态查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("申报状态查询异常：" .$e);
		}
		return $server_output;

	}
	
	/**
	 *  方法描述：
	 *  	申报状态查询：根据时间段
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $startTime
	 * @param unknown_type $endTime
	 * @param unknown_type $page
	 */
	function queryStatusByReportByTime($merchantCode,$merchantName,$startTime,$endTime,$page)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryStatusByReportByTime";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$startTime = "2015-03-09";
		$endTime = "2015-03-014 23:59:59";
		$page = "1";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"startTime" => $startTime,"endTime" => $endTime,"page" => $page);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("申报状态查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("申报状态查询异常：" .$e);
		}
		return $server_output;

	}
	/**
	 *  方法描述：
	 *  	退货申请
	 * @param unknown_type $merchantCode
	 * @param unknown_type $orderData
	 */
	function returnsOrderRequest($merchantCode,$orderData)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "returnsOrderRequest";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$orderData = "jsondata";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"orderData" => $orderData);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			
			//print_r("退货申请结果：" .$server_output);
                        if($obj==null){
                            logResult("退货申请  请求超时.");
                        }else if($obj['code']!='200'){
                            logResult("退货申请  返回错误代码：".$obj['code']."错误描述：".$obj['desc']." 退货数据:".$orderData);
                        }
			return $obj;
		} catch (Exception $e) 
		{
			print_r("退货申请异常：" .$e);
		}
		return $server_output;

	}
	
	
	/**
	 *  方法描述：
	 *  	退货状态查询
	 * @param unknown_type $mftNo
	 * @param unknown_type $waybillNo
	 */
	function queryReturnsOrderStatus($mftNo,$waybillNo)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryReturnsOrderStatus";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$orderData = "jsondata";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"mftNo" => $mftNo,
		"waybillNo" => $waybillNo);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			
			//print_r("退货状态查询结果：" .$server_output);
                        if($obj==null){
                            logResult("退货状态查询  请求超时.");
                        }else if($obj['code']!='200'){
                            logResult("退货状态查询  返回错误代码：".$obj['code']."错误描述：".$obj['desc']." 申报单号:".$mftNo." 运单号:".$waybillNo);
                        }
			return $obj;
		} catch (Exception $e) 
		{
			print_r("退货状态查询异常：" .$e);
		}
		return $server_output;

	}
	
	
	/**
	 *  方法描述：
	 *  	物流动态查询
	 * @param unknown_type $mftNo
	 * @param unknown_type $waybillNo
	 */
	function queryLogisticsInfo($logisticsName,$waybillNo)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryLogisticsInfo";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$orderData = "jsondata";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"logisticsName" => $logisticsName,
		"waybillNo" => $waybillNo);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("物流动态查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("物流动态查询异常：" .$e);
		}
		return $server_output;

	}
	
	
	/**
	 *  方法描述：
	 *  	申报单详情查询
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $startTime
	 * @param unknown_type $endTime
	 * @param unknown_type $page
	 */
	function queryOrderDetail($merchantCode,$merchantName,$startTime,$endTime,$orderFrom,$page)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryOrderDetail";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$startTime = "2015-03-09";
		$endTime = "2015-03-014 23:59:59";
		$page = "1";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"merchantCode" => $merchantCode,
		"merchantName" => $merchantName,"startTime" => $startTime,"endTime" => $endTime,
		"orderFrom" => $orderFrom,"page" => $page);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("申报单详情查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("申报单详情查询异常：" .$e);
		}
		return $server_output;

	}
	
	
		/**
	 *  方法描述：
	 *  	税单查询
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $orderNo
	 */
	function queryTaxList($mftNo)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryTaxList";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$orderNo = "8973777130";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"mftNo" => $mftNo);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("税单查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("税单查询异常：" .$e);
		}
		
		return $server_output;

	}
	
	
		/**
	 *  方法描述：
	 *  	同步订单
	 * @param $orderData：JSON格式，参数参考接口文档
	 */
	function createGlobalOrder($orderData)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "createGlobalOrder";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$mftNo = "15051401000018 ";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"orderData" => $orderData);
		
		$sign = getSign($postField, $appSecret);
		//print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		//var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output,true);
			
			//var_dump($obj);
			
			//print_r("同步订单结果：" .$server_output);
                        if($obj==null){
                            logResult("同步订单  请求超时.");
                        }else if($obj['code']!='200'){
                            logResult("同步订单  返回错误代码：".$obj['code']."错误描述：".$obj['desc']);
                        }
                        
			return $obj;
		} catch (Exception $e) 
		{
			print_r("同步订单异常：" .$e);
		}
		
		return $server_output;
	}
	
	
	/**
	 *  方法描述：
	 *  	订单查询
	 * @param $orderData：JSON格式，参数参考接口文档
	 */
	function queryGlobalOrder($orderData)
	{
		//系统参数
		$appId = $GLOBALS['_LANG']['appId'];
		$appSecret = $GLOBALS['_LANG']['appSecret'];
		$method = "queryGlobalOrder";
		$version = $GLOBALS['_LANG']['version'];
		$nonce = rand(1, 10000);
		$timestamp = date('YmdHis');
		
		//业务参数
/*		$merchantCode = "3302461400";
		$merchantName = "宁波宝乐贝尔国际贸易有限公司";
		$mftNo = "15051401000018 ";*/
		
		$postField = array("appId" => $appId,"method" => $method,"version" => $version,
		"nonce" => $nonce,"timestamp" => $timestamp ,"orderData" => $orderData);
		
		$sign = getSign($postField, $appSecret);
		print_r("\n签名值：".$sign."\n");
		
		$postField['sign'] = $sign;
		
		var_dump($postField);
		
		$httpRequest = getHttpRequest($GLOBALS['_LANG']['req_url'], $postField, $GLOBALS['_LANG']['proxyEnabled']);
		
		try
		{
			$server_output = curl_exec($httpRequest);
			curl_close($httpRequest);
			
			$obj = json_decode($server_output);
			
			var_dump($obj);
			
			print_r("订单查询结果：" .$server_output);
			
		} catch (Exception $e) 
		{
			print_r("订单查询异常：" .$e);
		}
		
		return $server_output;
	}
	
	/**
	 *  方法描述：
	 *  	申请跨境订单退款
	 * @param unknown_type $merchantCode
	 * @param unknown_type $merchantName
	 * @param unknown_type $order 订单信息
	 */
	function kjRefundOrder($merchantCode,$merchantName,$order)
	{
	    //系统参数
	    $appId = $GLOBALS['_LANG']['appId'];
	    $appSecret = $GLOBALS['_LANG']['appSecret'];
	    $method = "refund";
	    $version = $GLOBALS['_LANG']['version'];
	    $nonce = rand(1, 10000);
	    $timestamp = date('YmdHis');
	    $orderSource=$GLOBALS['_LANG']['orderSource'];
	    
	    //业务参数
	    //$merchantCode='114kjg';//测试数据
	    //$orderSource='114mall';//测试数据
	    $refundData = array(
	        'orderNum'         =>  $order['order_sn'],
	        'orderSource'        =>  $orderSource,
	        'sourceMerchantCode' =>  $merchantCode,
	        'goodsAmount'      =>  $order['goods_amount'],
	        'payAmount'        =>  $order['kj_order_amount'],
	        'currCode'         =>  'RMB'
	    );
	    
	    $postField = array(
	        "appId" => $appId,
	        "method" => $method,
	        "version" => $version,
	        "nonce" => $nonce,
	        "timestamp" => $timestamp,
	        "refundData" => json_encode($refundData)
	    );
	     
	    $sign = getSign($postField, $appSecret);
	    //print_r("\n签名值：".$sign."\n");
	    $postField['sign'] = $sign;	
	    //var_dump($postField);
	    $httpRequest = getHttpRequest($GLOBALS['_LANG']['kj_payurl'], $postField,0);
	    
	    try
	    {
	        $server_output = curl_exec($httpRequest);
	        curl_close($httpRequest);
	        $obj = json_decode($server_output,true);
	        //var_dump($obj);
	        //print_r("跨境订单退款结果：" .$server_output);
	        if($obj==null){
	            logResult("跨境订单退款  请求超时.");
	        }else if($obj['code']!='200'){
	            logResult("跨境订单退款  返回错误代码：".$obj['code']."错误描述：".$obj['desc']." 订单号：".$order['order_sn']);
	        }
	        return $obj;
	    } catch (Exception $e)
	    {
	        print_r("跨境订单退款异常：" .$e);
	    }
	    return null;
	
	}
	
	function getSign($arr,$appSecret)
	{
		$sign = "";
		if(!is_array($arr))
		{
			print_r("That's not an array!\n");
			
			return $sign;
		}
		
		array_multisort(array_keys($arr),$arr);
		
		$signParam = "";
		foreach ($arr as $k=>$v)
		{
			$signParam .= $k . "=" . $v . "&";		
		}
		
		//print_r("签名串：".$signParam.$appSecret); 
		return md5($signParam.$appSecret);
				
	}
        
        function logResult($word){
		$fp = fopen(ROOT_PATH."rizhi/kj_request_log.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"[".date("Y-m-d H:i:s")."]".$word."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
//	orderReport();
//	queryOrderNoList("3302461400","宁波宝乐贝尔国际贸易有限公司","8973777130","0000");
//	closeOrder("3302461400","宁波宝乐贝尔国际贸易有限公司","15051401000018");
//	queryCommodityRecord("3302461400","宁波宝乐贝尔国际贸易有限公司","310520156140000004");
//	queryConsumerInfo("0000","polober");
//	queryStatusByReportNo("3302461400","宁波宝乐贝尔国际贸易有限公司","15051401000018");
//	queryStatusByReportByTime("3302461400","宁波宝乐贝尔国际贸易有限公司","2015-03-09 00:00:00","2015-03-14 23:59:59",1);
	/*
	$rejectedInfo = array
	(
		"mftNo" => "15031201000036",
		"waybillNo" => "TEST100411261",
		"flag" => "00"
	);
	$detailList = array();
	
	for($i=0;$i<2;$i++)
	{
		$cargo = array();
		if($i == 0)
		{
			$cargo['productId'] = "310520156140000004";
		}
		else
		{
			$cargo['productId'] = "310520156140000003";
		}
		$cargo['rejectedQty'] = 1;
		$detailList[$i] = $cargo;
	};
	
	$rejectedGoods['detailList'] = $detailList;
	
	$rejectedInfo['rejectedGoods'] = $rejectedGoods;
//	print_r(json_encode($rejectedInfo));
//	returnsOrderRequest("3302461400", json_encode($rejectedInfo));
//	queryReturnsOrderStatus("15031101000056","TEST100411259");
//	queryLogisticsInfo('邮政速递','TEST100411259');
//	queryOrderDetail("3302461400","宁波宝乐贝尔国际贸易有限公司","2015-03-09 00:00:00","2015-03-13 23:59:59","0000",1);
//	queryTaxList("15031101000056");
	
	$orderInfo = array(
		"operation" => "0",
		"mftNo" => "",
		"orderForm" => "0000",
		"packageFlag" => "0",
		"orderShop" => "",
		"orderNo" => "8913121100",
		"postFee" => "0",
		"amount" => "100",
		"buyerAccount" => "polober",
		"email" => "polober@163.com",
		"phone" => "13913559801",
		"taxAmount" => "",
		"disAmount" => ""
		);
//	orderReport("3302461400","宁波宝乐贝尔国际贸易有限公司","2015-05-18 12:10:18",'','');
	*/
?>