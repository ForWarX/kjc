<?php

/**
 * PBCC 异步请求响应界面 支付改造
 * ============================================================================
 * * 
 * ============================================================================
 * $Id: respond_notify.php $
 */

define('IN_ECS', true);
error_reporting(1);//设置报错级别
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');

     /*$fp = fopen("payReturn.txt","a");
     flock($fp, LOCK_EX) ;
     fwrite($fp,date("Y-m-d H:i:s")."收到异步支付返回\n");
     flock($fp, LOCK_UN);
     fclose($fp);*/
    


     if (!empty($_POST))
     {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
     }
      //返回接受支付信息是否成功的结果
        header('Content-type: text/html; charset=' . EC_CHARSET);
        include_once('includes/cls_json.php');
        $json = new JSON();
        $result=array();
        if($_GET['method']=='payOrder'){
            $result=payOrder($payment);
        }else{
             $result=array(
                    "code" => "202",
                    "desc" => "请求方式非法"
              );
        }
        
        echo $json->encode($result);


function payOrder($payment){
        include_once('includes/modules/payment/kjgpay.php');
        $payment = new kjgpay();
        $payment->openDebugFile();
        $result=$payment->respond_notify();
        $payment->closeDebugFile();
        return $result;
}
        



?>