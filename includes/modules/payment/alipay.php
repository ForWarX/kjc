<?php

/**
 * PBCC 支付宝插件
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: alipay.php $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/alipay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'alipay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'PBCC网销部';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.alipay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.2';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'alipay_account',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_key',               'type' => 'text',   'value' => ''),
        array('name' => 'alipay_partner',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_pay_method',        'type' => 'select', 'value' => '')
    );

    return;
}

/**
 * 类
 */
class alipay
{
    /**
     * 参数
     */
    //ca证书路径地址，用于curl中ssl校验
    //请保证cacert.pem文件在当前文件夹目录中
    var $alipay_cacert = "";
    // 支付宝API接口地址
    var $alipay_url = 'https://mapi.alipay.com/gateway.do?';

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
        $this->alipay();
    }
    
    function alipay()
    {
        $this->alipay_cacert = ROOT_PATH . "cacert.pem";
    }

    /**
     * 生成支付代码按钮
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        $params=$this->get_params($order, $payment);
        $button = '<div style="text-align:center"><input type="button" onclick="window.open(\'' . $this->alipay_url .$params . '\')" value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
        return $button;
    }

    /**
     * 生成支付代码链接
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code_url($order, $payment){
        $params=$this->get_params($order, $payment);
        $url = $this->alipay_url . $params;
        return $url;
    }
    
    function get_params($order, $payment){
        if (!defined('EC_CHARSET'))
        {
            $charset = 'utf-8';
        }
        else
        {
            $charset = EC_CHARSET;
        }

        $real_method = $payment['alipay_pay_method'];

        switch ($real_method){
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
        }

        $extend_param = 'isv^sh22';

        $url = return_url(basename(__FILE__, '.php'));
        if ($payment['is_kj']) $url .= '_kj'; // 跨境订单标识

        $parameter = array(
            'extend_param'      => $extend_param,
            'service'           => $service,
            'partner'           => $payment['alipay_partner'],
            //'partner'           => ALIPAY_ID,
            '_input_charset'    => $charset,
            'notify_url'        => $url,
            'return_url'        => $url,
            /* 业务参数 */
            'subject'           => $order['order_sn'],
            'out_trade_no'      => $order['order_sn'] . $order['log_id'],
            'price'             => $order['order_amount'],
            'quantity'          => 1,
            'payment_type'      => 1,
            /* 物流参数 */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* 买卖双方信息 */
            'seller_email'      => $payment['alipay_account']
        );

        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $payment['alipay_key'];
        //$sign  = substr($sign, 0, -1). ALIPAY_AUTH;
        return $param. '&sign='.md5($sign).'&sign_type=MD5';
    }

    /**
     * 响应操作
     */
    function respond($is_kj=false, $merchant_customs_code='', $merchant_customs_name='')
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }

        // 以下变量名有些混乱，故在此说明
        // order_sn：支付的id，非订单本身的id或编号，是pay_log这张表中的log_id
        // order_no：订单的编号，非数据库中的id，是order_info这张表中的order_sn
        $code = $is_kj ? str_replace("_kj", "", $_GET['code']) : $_GET['code'];
        $payment  = get_payment($code);
        //$seller_email = rawurldecode($_GET['seller_email']);
        $order_sn = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $order_sn = trim($order_sn);

		//$create_time = strtotime($_GET['gmt_payment']);
		$payment_no = $_GET['trade_no'];
		//$order_seq_no = $_GET['out_trade_no'];
		$amount = $_GET['total_fee'];
        /*别用，不知道写这个的程序员怎么想的
		//$pay_id = $order_sn;
		//$tmp = str_replace($pay_id, '', $_GET['out_trade_no']);
		//$order_no = trim($tmp);
        */
        $order_no = trim($_GET['subject']); // 别用上面的写法，out_trade_no是order_sn+log_id，而subject本身就是order_sn，不管是看上面的写法还是看get_params()里的业务参数都能看出这一点
		
        /* 检查数字签名是否正确 */
        ksort($_GET);
        reset($_GET);

        $sign = '';
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
            {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
        //$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
        if (md5($sign) != $_GET['sign'])
        {
            return false;
        }

        /* 检查支付的金额是否相符 */
        if (!check_money($order_sn, $_GET['total_fee']))
        {
            return false;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
        {
            /* 改变订单状态 */
            order_paid($order_sn, 2); // 2代表支付成功

            return true;
        }
        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')//超过签约合同指定的可退款时间段时，支付宝会主动发送TRADE_FINISHED（不能对该交易再做任何操作）交易状态。
        {
            /* 改变订单状态 */
            order_paid($order_sn); // 默认为支付成功
            if ($is_kj) { // 跨境订单支付宝报关接口
                $res = $this->acquire_customs($payment_no, $payment, $merchant_customs_code, $merchant_customs_name, $order_no, $amount);
                return array(true, $res); // 可能需要加失败判断，万一跟支付宝的对接出了问题不能返回true
            }
            return true;
        }
        elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')//TRADE_SUCCESS（可对交易做其他操作，如退款、分润等）
        {
            /* 改变订单状态 */
            order_paid($order_sn, 2); // 2代表支付成功
            if ($is_kj) {  // 跨境订单支付宝报关接口
                $res = $this->acquire_customs($payment_no, $payment, $merchant_customs_code, $merchant_customs_name, $order_no, $amount);
                return array(true, $res); // 可能需要修改，万一跟支付宝的对接出了问题不能返回true
            }
			/*if($create_time != '')支付改造 一般进口商品不需要发送到跨境海关
			{
				kjpay($create_time, $payment_no, $order_no, $order_seq_no, $pay_id, '02');
			}*/
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 支付宝报关接口
     * 调用时机：支付宝支付成功 -> 此接口 -> 提交进口订单到申报系统
     * @param int $trade_no                         支付宝交易号
     * @param array $payment                        包含支付宝信息的数组
     * @param string $merchant_customs_code         商户海关备案编号
     * @param string $merchant_customs_name         商户海关备案名称
     * @param string $order_sn                      跨境城的订单编号，用作报关流水号
     * @param string $amount                        申报金额
     * @return bool|mixed
     */
    function acquire_customs($trade_no=0, $payment=null, $merchant_customs_code='', $merchant_customs_name='', $order_sn='', $amount='') {
        if ($trade_no == 0) return false;

        if (!defined('EC_CHARSET')) {
            $charset = 'UTF-8';
        } else {
            $charset = EC_CHARSET;
        }

        $parameter = array(
            'trade_no'              => $trade_no,                   // 支付宝交易号
            'merchant_customs_code' => $merchant_customs_code,      // 商户海关备案编号
            'merchant_customs_name' => $merchant_customs_name,      // 商户海关备案名称
            'service'               => 'alipay.acquire.customs',    // 支付宝接口名称
            'partner'               => $payment['alipay_partner'],  // 合作者身份ID，签约的支付宝账号对应的支付宝唯一用户号
            '_input_charset'        => $charset,                    // 商户网站使用的编码格式
            'amount'                => $amount,                     // 报关金额，单位为人民币“元”，精确到小数点后2位
            'customs_place'         => 'NINGBO',                    // 海关编号
            'out_request_no'        => $order_sn,                   // 报关流水号，商户生成的用于唯一标识一次报关操作的业务编号
        );

        ksort($parameter);
        reset($parameter);

        $sign  = '';
        foreach ($parameter AS $key => $val)
        {
            $sign  .= "$key=$val&";
        }

        $sign  = substr($sign, 0, -1). $payment['alipay_key'];
        $parameter['sign'] = md5($sign); // 签名
        $parameter['sign_type'] = 'MD5'; // 签名类型

        $curl = curl_init($this->alipay_url);
        // 证书验证不了，因此不使用SSL认证
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);         // SSL证书认证
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);            // 严格认证
        //curl_setopt($curl, CURLOPT_CAINFO, $this->alipay_cacert); // 证书地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);        // 不使用SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);            // 不认证
        curl_setopt($curl, CURLOPT_HEADER, 0 );                   // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);            // 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true);                   // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);       // post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) ); // 如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        // 返回的xml会带有发过去的请求参数，由于这段参数在返回的xml中使用了非闭合格式，会导致simplexml函数无法解析，因此删除
        /**
         * 支付宝返回值示例：
         * <alipay>
         *     <is_success>T</is_success>
         *     <request>
         *         <param name="amount">0.01
         *         <param name="sign_type">MD5
         *         及其它发过去的请求，全部为非闭合格式
         *     </request>
         *     <response>
         *         <alipay>
         *             <alipay_declare_no>2016120311082072979979107</alipay_declare_no>
         *             及其它返回值
         *         </alipay>
         *     </response>
         * </alipay>
         */
        $responseText = preg_replace("/<request>([\s\S]*?)<\/request>/i", '', $responseText);

        return simplexml_load_string($responseText);
    }
}

?>