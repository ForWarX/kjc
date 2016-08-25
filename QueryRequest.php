<?php

	if (!defined('IN_ECS'))
	{
		die('Hacking attempt');
	}
	
	function SearchByOrderNo($orderno){
		//申报单状态查询(订单号)
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
				
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		print_r($current_time);
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/mftsearchmsg!doSeachbyOrderNo.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><OrderNo>".$orderno."</OrderNo></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function SearchByTime($starttime,$endtime,$type=''){
		//申报单状态查询(下单时间)
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
				
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		if ($type == 'order'){
			$time_url = "http://i.trainer.kjb2c.com/msg/mftsearchmsg!doSeachbyOrderTime.do";
		}
		else if ($type == 'update'){
			$time_url = "http://i.trainer.kjb2c.com/msg/mftsearchmsg!doSeachbyUpdate.do";
		}
		else{
			$time_url = '';
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$time_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		//$starttime=urlencode("2014-5-1 12:12:12");
		//$starttime="2014-05-01 12:12:12";
		//$endtime=urlencode("2014-7-16 12:12:12");
		//$endtime="2014-07-16 12:12:12";
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><StartTime>".$starttime."</StartTime><EndTime>".$endtime."</EndTime></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function SearchByOrderLgs($orderno){
		//查询申报单海关卡口放行状态	
		
		//电商平台帐号		
		$userid= $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/mftsearchmsg!doSeachbyOrderLgs.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><OrderNo>".$orderno."</OrderNo></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function LogisticsFirm(){
		//物流快递企业查询
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/logisticsfirm.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		$OrgOrder_xmlstr="";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function GetConsumer($consumeraccount){
		//消费者姓名身份证查询	
		
		//电商平台帐号		
		$userid= $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$order_from ="0000";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/getconsumer.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><OrderFrom>" . $order_from . "</OrderFrom><Account>" . $consumeraccount . "</Account></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function GetRejected($orderno,$waybillno,$flag){
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
		
		//退换货状态查询		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/getrejected.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><OrderNo>" . $orderno . "</orderno><WaybillNo>" . $waybillno . "</WaybillNo><Flag>" . $flag . "</Flag></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function GetGoods($productid){
		//备案商品查询（根据货号查询）
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/getgoods.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><ProductId>".$productid."</ProductId></Header></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	function OrderCancel($order_no){
		//进口订单关闭
		
		//电商平台帐号		
		$userid = $GLOBALS['_LANG']['kj_userid'];
		$pwd = $GLOBALS['_LANG']['kj_pwd'];
		//电商企业海关代码		
		$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
		//电商企业名称		
		$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
		
		//时间戳
		$current_time = date("Y-m-d H:i:s");
		$timestamp= urlencode($current_time);
		//加密签名
		$sign = md5($userid . $pwd . $current_time);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/ordercancel.do");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$OrgOrder_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><CreateTime>".$current_time."</CreateTime></Header><Body><Order><OrderNo>". $order_no ."</OrderNo></Order></Body></Message>";
		$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $OrgOrder_xmlstr);
		
		$fp = fopen("order_cancel.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$OrgOrder_xmlstr."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch); 	
		logResult($server_output);
		return simplexml_load_string($server_output);
	}
	
	function logResult($word){
		$fp = fopen("queryrequestlog.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
?>