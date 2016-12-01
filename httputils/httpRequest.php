<?php
	function getHttpRequest($req_url,$postField,$needProxy=0)
	{
		
		$httpRequest = curl_init();
		//print_r("\nREQ_URL：".$req_url."\n");
		curl_setopt($httpRequest, CURLOPT_URL, $req_url);
		curl_setopt($httpRequest, CURLOPT_POST, 1);

		curl_setopt($httpRequest, CURLOPT_POSTFIELDS, $postField);
		curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, true);
		if($needProxy == 1)
		{
			curl_setopt($httpRequest,CURLOPT_PROXY,$GLOBALS['_LANG']['proxyHost']);
			curl_setopt($httpRequest,CURLOPT_PROXYPORT,$GLOBALS['_LANG']['proxyPort']);
		}
		curl_setopt($httpRequest, CURLOPT_TIMEOUT, 20);
		
		return $httpRequest;
	}
?>