<?php

/**
 * 跨境购 支付改造
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: kjgpay.php $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/*
$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/alipay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}
*/
/**
 * 类
 */
class kjgpay
{

    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    

    function __construct()
    {
        $this->kjgpay();
    }
    
    function kjgpay()
    {
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     */
    function get_code($order)
    {
        //时间戳
        $current_time = date("YmdHis");//Y-m-d H:i:s
        $merchantCode = $GLOBALS['_LANG']['merchantCode'];
        $merchantName = $GLOBALS['_LANG']['merchantName'];
        
        $return_url=  return_url('kjgpay');
        $notify_url=$GLOBALS['ecs']->url() . 'respond_notify.php';
        $data = array(
                'orderNum'         =>  $order['order_sn'],
                'orderSource'        =>  $GLOBALS['_LANG']['orderSource'],
                //'merchantCode'     =>  $merchantCode,
                'sourceMerchantCode' =>  $merchantCode,
                'transChannel'       =>  '1', //交易渠道,1:互联网 3:wap;
                'bankId'             =>  'alipay', //alipay,wxpay
                'returnUrl'       =>   $return_url,
                'notifyUrl'       =>   $notify_url,
                'goodsAmount'      =>  $order['goods_amount'],
                'payAmount'        =>  $order['order_amount'],
                'currCode'         =>  'RMB',
                'extraCommonParam' =>  $order['log_id']
            );
        $json = new JSON();
        $payData=$json->encode($data);
        $parameter = array(
                'payData'          =>  $payData,
                'method'           =>  'payOrder',
                'version'          =>  '1.0',
                'appId'            =>  $GLOBALS['_LANG']['kj_appId'],
                'timestamp'        => $current_time,
                'nonce'            => rand(1,10000)
        );
        $appSecret=$GLOBALS['_LANG']['kj_appSecret'];
        ksort($parameter);
        reset($parameter);
        $param = '';
        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .$val. "&";
        }

        $param = substr($param, 0, -1);
        //加密签名
        $sign = md5($param.'&'.$appSecret);
        $url=$GLOBALS['_LANG']['kj_payurl'];
        $url.='?'.$param. '&sign='.$sign;
        //$button = '<div style="text-align:center"><input type="button" onclick="window.open(\''.$url.'\')"  value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
        return $url;
    }
    /**
     * 生成支付POST请求参数信息
     * @param   array   $order      订单信息
     */
    function getPostData($order){
        //时间戳
        $current_time = date("YmdHis");//Y-m-d H:i:s
        $merchantCode = $GLOBALS['_LANG']['merchantCode'];
        $merchantName = $GLOBALS['_LANG']['merchantName'];
        
        $return_url=  return_url('kjgpay');
        $notify_url=$GLOBALS['ecs']->url() . 'respond_notify.php';
        //$notify_url='http://172.16.20.21/kjc/'. 'respond_notify.php';
        $data = array(
                'orderNum'         =>  $order['order_sn'],
                'orderSource'        =>  $GLOBALS['_LANG']['orderSource'],
                //'merchantCode'     =>  $merchantCode,
                'sourceMerchantCode' =>  $merchantCode,
                'transChannel'       =>  '1', //交易渠道,1:互联网 3:wap;
                'bankId'             =>  'alipay', //alipay,wxpay
                'returnUrl'       =>   $return_url,
                'notifyUrl'       =>   $notify_url,
                'goodsAmount'      =>  $order['goods_amount'],
                'payAmount'        =>  $order['order_amount'],
                'currCode'         =>  'RMB',
                'extraCommonParam' =>  $order['log_id']
            );
        $json = new JSON();
        $payData=$json->encode($data);
        $parameter = array(
                'payData'          =>  $payData,
                'method'           =>  'payOrder',
                'version'          =>  $GLOBALS['_LANG']['version'],
                'appId'            =>  $GLOBALS['_LANG']['appId'],
                'timestamp'        =>  $current_time,
                'nonce'            =>  rand(1,10000)
        );
        $appSecret=$GLOBALS['_LANG']['appSecret'];
        ksort($parameter);
        reset($parameter);
        //生成签名
        $param = '';
        foreach ($parameter AS $key => $val){ $param .= "$key=" .$val. "&";}
        $param = substr($param, 0, -1);
        $sign = strtoupper(md5($param.'&'.$appSecret));
        $parameter['sign']=$sign;
        $this->debug("支付数据：".$json->encode($parameter));
        return $parameter;
    }
    

    /**
     * 响应操作
     */
    function respond()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        
        //20151102支付同步接口返回参数调整修改
        include_once(ROOT_PATH.  'includes/cls_json.php');
        if(isset($_GET['orderNum'])){
            $order_sn = trim($_GET['orderNum']);
            $log_id = trim($_GET['extraCommonParam']);
            $payAmount=$_GET['payAmount'];
            $payStatus=$_GET['payStatus'];
        }else if(isset($_GET['payData'])){
            $payDataStr=$_GET['payData'];
            $json = new JSON();
            $payData=$json->decode($payDataStr,true);
            $order_sn = trim($payData['orderNum']);
            $log_id = trim($payData['extraCommonParam']);
            $payAmount=$payData['payAmount'];
            $payStatus=$payData['payStatus'];
        }

        $this->debug("收到跨境购return_url支付返回：orderNum:".$order_sn."   logId:".$log_id."   payAmount:".$payAmount."   payStatus:".$payStatus);
        /* 检查数字签名是否正确 */
        if(!$this->checkSign()){ 
            $this->debug("数字签名校验不正确");
            return false;
        }
        
        /* 检查支付的金额是否相符 */
        if (!check_money($log_id, $payAmount)){
            $this->debug("支付金额校验不正确");
            return false;
        }
        
        /*判断日志和记录是否已经更新完成*/
        if(!empty($log_id)){
            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') ." WHERE log_id = '$log_id'";
            $pay_log = $GLOBALS['db']->getRow($sql);
            if($pay_log['is_paid']==1){
                return true;
            }
        }
        return $this->editOrderPay($log_id,$payStatus);
        
    }
    
    /**
     * 响应操作
     */
    function respond_notify()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        include_once(ROOT_PATH.  'includes/cls_json.php');
        $payDataStr=$_GET['payData'];
        
        $json = new JSON();
        $payData=$json->decode($payDataStr,true);
        //var_dump($payData);
        $order_sn = trim($payData['orderNum']);
        $log_id = trim($payData['extraCommonParam']);
        
        $this->debug("收到跨境购notify_url支付返回：orderNum:".$order_sn."   logId:".$log_id);
        /* 检查数字签名是否正确 */
        if(!$this->checkSign()){ 
            $this->debug("数字签名校验不正确");
            return array("code" => "-2", "desc" => "签名校验错误");
        }
        
        /* 检查支付的金额是否相符 */
        if (!check_money($log_id, $payData['payAmount'])){
            $this->debug("支付金额校验不正确");
            return array("code" => "201", "desc" => "支付金额校验不正确");
        }
        
        /*判断日志和记录是否已经更新完成*/
        if(!empty($log_id)){
            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') ." WHERE log_id = '$log_id'";
            $pay_log = $GLOBALS['db']->getRow($sql);
            if($pay_log['is_paid']==1){
                $this->debug("订单状态已经被变更过，异步通知成功");
                return array("code" => "200", "desc" => "操作成功");
            }
        }
        return $this->editOrderPay($log_id,$payData['payStatus']);
        
    }
    
    function checkSign(){
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        
        $appSecret=$GLOBALS['_LANG']['appSecret'];
        ksort($_GET);
        reset($_GET);
        $param = '';
        $singArr=array();
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'code')
            {
                $param .= "$key=".stripslashes($val)."&";
            }
        }
        $param=substr($param, 0, -1);
        
        $sign=$param.'&'.$appSecret;
        
        $this->debug("签名:".$param);
        $sign = strtoupper(md5($sign));//加密签名
        //$sign=$this->getSign($singArr, $appSecret);
        $this->debug("加密后签名:".$sign."    request获得的签名：".$_GET['sign']);
        
        if ($sign != $_GET['sign'])
        {
            return false;
        }
        return true;
    }
    
    function editOrderPay($log_id,$payStatus){
        /*if ($payStatus == 'WAIT_SELLER_SEND_GOODS')
        {
            // 改变订单状态 
            order_paid($log_id, 2);

            return true;
        }
        elseif ($payStatus == 'TRADE_FINISHED')
        {
            
            order_paid($log_id);
            return true;
        }
        elseif ($payStatus == 'TRADE_SUCCESS')
        {
            order_paid($log_id, 2);
            return true;
        }*/
        if ($payStatus == '1')
        {
            $this->debug("服务器返回支付成功，进行订单状态修改");
            order_paid($log_id, 2);// 改变订单状态
            return array("code" => "200", "desc" => "操作成功");
        }
        else
        {
            $this->debug("服务器返回支付失败");
            return array("code" => "200", "desc" => "操作成功");
        }
    }
    
    private $fp;
    
    function openDebugFile(){
        $this->fp = fopen("rizhi/kj_pay_return_log.txt","a");
	flock($this->fp, LOCK_EX);
    }
    
    function closeDebugFile(){
        fwrite($this->fp,"\n");
        flock($this->fp, LOCK_UN);
	fclose($this->fp);
    }
    
    function debug($message){
        if($this->fp!=null){
            fwrite($this->fp,"[".date("Y-m-d H:i:s")."]".$message."\n");
        }
    }

}

?>