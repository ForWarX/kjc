<?php

/**
 * PBCC beanstream支付
 * ============================================================================
 * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * 网站地址: http://www.pbcc.ca
 *
 */

if (!defined('IN_ECS'))
{
   die('Hacking attempt');
}
$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/'.basename(__FILE__);
if (file_exists($payment_lang))
{
   global $_LANG;
   include_once($payment_lang);
}

/* 安装模块的基本信息后台管理中用到的 */
if (isset($set_modules) && $set_modules == TRUE)
{
   $i = isset($modules) ? count($modules) : 0;
   /* 代码 */
   $modules[$i]['code']    = basename(__FILE__, '.php');
   /* 描述对应的语言项 */
   $modules[$i]['desc']    = 'beanstream_desc';//对应语言文件中的说明内容下标
   /* 是否货到付款 */
   $modules[$i]['is_cod']  = '0';
   /* 是否支持在线支付 */
   $modules[$i]['is_online']  = '1';
   /* 作者 */
   $modules[$i]['author']  = 'PBCC网销部';
   /* 网址 */
   $modules[$i]['website'] = 'http://www.pbcc.ca';
   /* 版本号 */
   $modules[$i]['version'] = '1.0.0';
   /* 配置信息 表单元素*/
   $modules[$i]['config']  = array(
       array('name' => 'beanstream_merchant_id',/*对应语言文件中的下标*/       'type' => 'text',   'value' => ''),//商户号
   );
   return;
}

/**
* 类
*/
class beanstream{
   function beanstream()
   {
   }
   function __construct()
   {
       $this->beanstream();
   }
   //获取指定长度的字符串  不足以0填充
   function str($str,$len,$fill='0')
   {
       if(strlen($str)<$len)
       {
           return str_repeat($fill, $len-strlen($str)).$str;
       }
       return substr($str, 0,$len);
   }
   //金额处理
   function order_amount($amount)
   {
       if(strpos($amount, '.'))
           return $this->str(str_replace('.', '', $amount), 12);
       else return $this->str($amount.'00', 12);
   }
   /**
    * 生成支付代码
    * @param   array   $order      订单信息
    * @param   array   $payment    支付方式信息
    * 这里必须根据要求生成银联商务可用的连接代码
    */
   function get_code($order, $payment)
   {
		if (!defined('EC_CHARSET'))
        {
            $charset = 'UTF-8';
        }
        else
        {
            $charset = strtoupper(EC_CHARSET);
        }
		$front_pay_url         = 'https://www.beanstream.com/scripts/process_transaction.asp';
		$merchant_id = $payment['beanstream_merchant_id'];
		
       $order_no=$this->str($order['order_sn'], 16);//处理定单
       $TransAmt=$this->order_amount($order['order_amount']);//订单交易金额
       $Priv1='chinaPay';
       $CuryId='156';//订单交易币种
       $TransDate=date('Ymd');//订单交易日期
       $TransType='0001';//交易类型
       $msg=$payment['chinaPay_MerId'].$order_no.$TransAmt.$CuryId.$TransDate.$TransType.$Priv1;
       $button='<form action="http://payment-test.chinapay.com/pay/TransGet" METHOD="POST" target="_blank">'.// （这里action的内容为提交交易数据的URL地址）
       '<input type="hidden" name="MerId" value="'.$payment['chinaPay_MerId'].'"/>'.// （MerId为ChinaPay统一分配给商户的商户号，15位长度，必填）
       '<input type="hidden" name="OrdId" value="'.$order_no.'"/>'. //（商户提交给ChinaPay的交易订单号，16位长度，必填）
       '<input type="hidden" name="TransAmt" value="'.$TransAmt.'"/>'. //（订单交易金额，12位长度，左补0， 必填,单位为分）
       '<input type="hidden" name="CuryId" value="'.$CuryId.'"/>'.// （订单交易币种，3位长度，固定为人民币156， 必填）
       '<input type="hidden" name="TransDate" value="'.$TransDate.'"/>'.//（订单交易日期，8位长度，必填）
       '<input type="hidden" name="TransType" value="0001"/>'.// （交易类型，4位长度，必填）
       '<input type="hidden" name="Version" value="20070129"/>'.// （支付接入版本号，必填）
       '<input type="hidden" name="BgRetUrl" value="'.$GLOBALS['ecs']->url() . 'respond.php'.'"/>'.// （后台交易接收URL，长度不要超过80个字节，必填）
       '<input type="hidden" name="PageRetUrl" value="'.$GLOBALS['ecs']->url() . 'respond.php'.'"/>'.// （页面交易接收URL，长度不要超过80个字节，必填）
       '<input type="hidden" name="GateId" value=""/>'.//（支付网关号，可选）
       '<input type="hidden" name="Priv1" value="'.$Priv1.'"/>'.//（商户私有域，长度不要超过60个字节） 传参数用的
       '<input type="hidden" name="ChkValue" value="'.sign($msg).'"/>'.//（256字节长的ASCII码,为此次交易提交关键数 据的数字签名，必填）
       '<input type="submit" value="立即使用银联支付"/>
       </form>';
       return $button;
   }
   /**
    * 响应操作
这里主要处理银联回传的数据  并且要处理付款的形式  是否为付款成功  或付款中  这里的状态很重要
回传的数据  验证最重要的
    */
   function respond()
   {
       /*
       当消费支付交易完成时，ChinaPay会将交易应答信息发送给商户，
       对于页面易接收地址和后台交易接收地址都会收到交易接收数据，
       应答的数据域段包括如下内容：(以页面Form数据为例,注意大小写，
       后台应答数据的发送的域段名和下面的一致)
       <form name="SendToMer" method="post" action=""> （这里action的内容为提交交易数据的URL地址）
       <input type="hidden" name="merid" value="808000000000001"/>（MerId为ChinaPay统一分配给商户的商户号，15位长度，必填）
       <input type="hidden" name="orderno" value="0000000010096806"/> （商户提交给ChinaPay的交易订单号，16位长度，必填）
       <input type="hidden" name="transdate" value="20070801"/> （订单交易日期，8位长度，必填）
       <input type="hidden" name="amount" value="000000001234"/> （订单交易金额，12位长度，左补0， 必填,单位为分）
       <input type="hidden" name="currencycode" value="156"/>（订单交易币种，3位长度，固定为人民币156， 必填）
       <input type="hidden" name="transtype" value="0001"/> （交易类型，4位长度，必填）
       <input type="hidden" name="status" value="1001"/> （交易状态，4位长度，必填）
       <input type="hidden" name="checkvalue" value=" X…X "/> （256字节长的ASCII码,为此次交易提交关键数 据的数字签名，必填）
       <input type="hidden" name="GateId" value=" 0001"/> （支付网关号，可选）
       <input type="hidden" name="Priv1" value=" Memo"/> （商户私有域，长度不要超过60个字节） 传参数用的
       </form>
       status 表示交易转态，只有"1001"的时候才为交易成功，其他均为失败，因此在验证签名数据为ChinaPay发出的以后，还需要判定交易状态代码为"1001"。
        */
       $sql = "SELECT pay_config FROM " . $GLOBALS['ecs'] ->table('payment') . " WHERE pay_code = 'chinaPay' AND enabled = '1'";
       $pay = $GLOBALS['db']->getRow($sql);//取出内容
       if(empty($pay['pay_config']))return false;//支付配置数据不存在
       $payment = unserialize($pay['pay_config']);
       foreach ($payment as $value)
       {
           if($value['name']=='chinaPay_PgPubk')
           {
               $PgPubk=$value['value'];
               break;
           }

       }
       //创建验证钥
       if(!isset($PgPubk)||!buildKey($PgPubk))return false;
       if (empty($_POST)||!is_array($_POST))return false;//交易失败
       $respond=array('merid','orderno','transdate','amount','currencycode','transtype','status','checkvalue','GateId');
       foreach ($respond as $val)
       {
           if(array_key_exists($val, $_POST));else return false;//交易失败返回数据不合法或缺少
       }
       /* 检查支付的金额是否相符 */
       $order_no=substr($_POST['orderno'], -13);//取出定单号这块可以不去处理SQL注入，后面调用的函数都会判断这个变量的值是否为全数字
       $log_id=get_order_id_by_sn($order_no);//取出支付编号
       //判断是为为充值定单
       if(empty($log_id))$log_id=get_order_id_by_sn($order_no,true);//取出支付编号
       if (!check_money($log_id, (float)substr_replace($_POST['amount'], '.', -2,0)))
       {
           return false;
       }
       //判断交易状态
       if($_POST['status']!='1001')return false;//交易失败
       /* 检查数字签名是否正确 */
       //验证交易应答
       if(!verifyTransResponse($_POST['merid'], $_POST['orderno'], $_POST['amount'], $_POST['currencycode'], $_POST['transdate'], $_POST['transtype'], $_POST['status'], $_POST['checkvalue']))
       return false;//交易失败
           /* 改变订单状态 */
       order_paid($log_id);
      return true;//支付成功
   }
}
?>