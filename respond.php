<?php

/**
 * PBCC 支付响应页面
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: respond.php $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';

//获取首信支付方式
if (empty($pay_code) && !empty($_REQUEST['v_pmode']) && !empty($_REQUEST['v_pstring']))
{
    $pay_code = 'cappay';
}

//获取快钱神州行支付方式
if (empty($pay_code) && ($_REQUEST['ext1'] == 'shenzhou') && ($_REQUEST['ext2'] == 'ecshop'))
{
    $pay_code = 'shenzhou';
}

/* 参数是否为空 */
if (empty($pay_code))
{
    $msg = $_LANG['pay_not_exist'];
}
else
{
    /* 检查code里面有没有问号 */
    if (strpos($pay_code, '?') !== false)
    {
        $arr1 = explode('?', $pay_code);
        $arr2 = explode('=', $arr1[1]);

        $_REQUEST['code']   = $arr1[0];
        $_REQUEST[$arr2[0]] = $arr2[1];
        $_GET['code']       = $arr1[0];
        $_GET[$arr2[0]]     = $arr2[1];
        $pay_code           = $arr1[0];
    }
    
    
    if($pay_code=='kjgpay'){//支付改造
        include_once('includes/modules/payment/kjgpay.php');
        $payment = new kjgpay();
        $payment->openDebugFile();
        $msg     = (@$payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
        $payment->closeDebugFile();
    } else if ($pay_code == 'alipay_kj') { // 支付宝 - 跨境订单
        $pay_code = 'alipay';
        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

        /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
        if (file_exists($plugin_file))
        {
            // 支付宝会调用两次该文件，一次notify，一次return，避免重复运行该代码
            // 获取订单数据
            $order_sn = $_REQUEST['subject']; // 订单号
            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') ." WHERE order_sn = '$order_sn'";
            $order = $GLOBALS['db']->getRow($sql);

            if ((int)($order['report_status']) == 1) {
                // 已经申报
                $msg = $_LANG['pay_success'];
            } else {
                /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
                include_once($plugin_file);

                $payment = new $pay_code();
                // 跨境订单使用respond函数报关的调用方式来调用支付宝报关接口
                $res = (@$payment->respond(true, $GLOBALS['_LANG']['kj_customs_code'], $GLOBALS['_LANG']['kj_org_name'])); // $res = array(0=>是否成功, 1=>成功后返回的支付宝数据)
                $msg = $res === false ? $_LANG['pay_fail'] : $_LANG['pay_success'];

                if ($res && $res[0]) {
                    // 调用完支付宝的报关接口后，调用申报系统的进口订单接口

                    // 获取要提交给申报系统的数据
                    require_once(ROOT_PATH . 'includes/lib_order.php');
                    $payment = payment_info($order['pay_id']);
                    $log_id = $_REQUEST['logId'];
                    if (empty($log_id)) {
                        require_once(ROOT_PATH . 'includes/lib_clips.php');
                        $log_id = insert_pay_log($order['order_id'], $order['order_amount'], PAY_ORDER);
                    }
                    $order['log_id'] = $log_id;

                    include_once('includes/lib_payment.php');
                    $goods_list = order_goods($order['order_id']);

                    // 整合要提交给申报系统的数据
                    $orderData = get_report_order($order, $goods_list);
                    if ($orderData != null) {
                        $alipay_data = $res[1]; // 支付宝返回的数据
                        $orderData['PaymentNo'] = $orderData['OrderSeqNo'] = $alipay_data->response->alipay->trade_no;
                        $orderData['Source'] = '02'; // 申报系统定义的支付宝代码

                        if ($orderData != null) {
                            // 提交进口订单
                            include_once('report/orderReportHaiGuan.php');
                            $result = hg_SendOrder($orderData); // 申报系统API：进口订单

                            if ($result->Header->Result == 'T') {
                                // 全部都成功处理后修改订单状态为已申报
                                update_order($order['order_id'], array('report_status' => 1, 'report_sn' => $result->Header->MftNo, 'kj_order_amount' => $orderData['orderAmount']));
                            } else {
                                $msg = "申报进口订单失败";
                            }
                        } else {
                            $msg = $_LANG['err_kj_order_info'];
                        }
                    } else {
                        $msg = "查询产品请求异常";
                    }
                }
            }
        }
        else
        {
            $msg = $_LANG['pay_not_exist'];
        }
    } else{ // 普通订单
        /* 判断是否启用 */
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
        if ($db->getOne($sql) == 0)
        {
            $msg = $_LANG['pay_disabled'];
        }
        else
        {
            $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

            /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
            if (file_exists($plugin_file))
            {
                /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
                include_once($plugin_file);

                $payment = new $pay_code();
                $msg     = (@$payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
            }
            else
            {
                $msg = $_LANG['pay_not_exist'];
            }
        }
    }
}

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('helps',      get_shop_help());      // 网店帮助

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());

$smarty->display('respond.dwt');


/*****************************************************************************
 * 辅助函数
 *****************************************************************************/
/**
 *  生成申报订单数据
 * @param array $order 订单头信息
 * @param array $goods_list 订单明细信息
 */
function get_report_order($order,$goods_list){
    include_once('report/orderReportHaiGuan.php');
    $sql = "SELECT user_name, real_name, real_id, real_phone, real_email FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = " . $order['user_id'];
    $account = $GLOBALS['db']->getRow($sql);
    //print_r("帐号：" .$account);
    $sql = "SELECT shipping_name FROM " . $GLOBALS['ecs']->table('shipping') . " WHERE shipping_id = " . $order['shipping_id'];
    $shipping_name = $GLOBALS['db']->getOne($sql);
    $sql = "SELECT region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_id = " . $order['province'];
    $province = $GLOBALS['db']->getOne($sql);
    $sql = "SELECT region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_id = " . $order['city'];
    $city = $GLOBALS['db']->getOne($sql);
    $sql = "SELECT region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_id = " . $order['district'];
    $district = $GLOBALS['db']->getOne($sql);
    //$sql = "SELECT region_code FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_id = " . $order['district'];
    //$regionCode = $GLOBALS['db']->getOne($sql);
    $regionCode="/";
    $current_time = date("Y-m-d H:i:s");
    $goodsDesc="";
    $kj_goods_amount = 0;
    $kj_goods_weight = 0.0;
    $kj_goods_number = 0;
    //$kj_goods_tax = 0.0;
    $goods=array();
    foreach($goods_list as $good){
        // 获取商品备案货号
        $sql = "SELECT kj_sn FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
        $kj_id = $GLOBALS['db']->getOne($sql);
        //获取商品备案信息
        $product_rt=hg_GetGoods($kj_id);
        if($product_rt==null){//请求异常，直接返回
            return null;
        }
        if ($product_rt->Header->Result == "T"){
            $product_info = $product_rt->Body;
            $good_amount = (float)$good["goods_number"] * (float)$good["goods_price"];
            $sql = "SELECT goods_weight FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
            $good_weight = $GLOBALS['db']->getOne($sql) * $good['goods_number'];
            //$tariffFee=(float)$product_info['tax'] * $good_amount;
            // $tariffPrice=(float)$product_info['tax'] * (float)$good["goods_price"];
            $goods[]=array(
                "goodsSku" => $kj_id,    // 货号（跨境平台商品备案时产生的唯一编码）
                "goodsName" => $good['goods_name'],
                "quantity" => $good["goods_number"],
                "unit" => $product_info->Unit,
                "price" => $good["goods_price"],
                "weight" => $good_weight,
                "tariffRate" => '0',
                "tariffPrice" => '0',
                "tariffFee" => '0'
            );
            $kj_goods_amount += $good_amount;
            $kj_goods_weight += $good_weight;
            $kj_goods_number += (int)$good["goods_number"];
            //$kj_goods_tax += $tariffFee;
            $goodsDesc .= $good['goods_name'].";";
        }
    }
    //$kj_shipping_fee=get_kj_shipping_fee($order,$kj_goods_weight,$kj_goods_amount,$kj_goods_number);
    //$kj_order_amount = $kj_goods_amount + $kj_shipping_fee + $kj_goods_tax;
    $consignee = get_consignee($_SESSION['user_id']);
    $total = order_fee($order, $goods_list, $consignee);
    $kj_shipping_fee = $order['shipping_fee'];

    /* Debug
    $fp = fopen("output.txt", "a+");
    fwrite($fp, "total:\n");
    fwrite($fp, print_r($total, true));
    fwrite($fp, "\n");
    fwrite($fp, "order:\n");
    fwrite($fp, print_r($order, true));
    fwrite($fp, "\n");
    fwrite($fp, "goods_list:\n");
    fwrite($fp, print_r($goods_list, true));
    fwrite($fp, "\n");
    fclose($fp);
    */

    //if($shipping_code=='sf_express') $shipping_code='shunfeng';
    $orderData=array(
        "Operation" => 0, // 0=新建，1=更新
        "orderNum" => $order['order_sn'],
        "orderSource" => $GLOBALS['_LANG']['orderSource'],
        //"cityCode" => 'ningbo',
        "checkStore" => '0',// 库存校验
        "buyerAccount" => $account['user_name'],
        //物流信息
        "logisticsName" =>  $shipping_name,
        "postFee" => $kj_shipping_fee,
        "consignee" => $order['consignee'],
        "consigneeTel" => $order['tel'],
        "regionCode" => $regionCode,
        "consigneeCountry" => "CHN",
        "consigneeProvince" => $province,
        "consigneeCity" => $city,
        "consigneeDistrict" => $district,
        "consigneeAddr" => $order['address'],
        "mailNo" => $order['zipcode'],
        "senderAddr" => "/",
        "senderCity" => "/",
        "senderCompanyName" => "/",
        "senderCountry" => "/",
        "senderName" => "/",
        "senderProvince" => "/",
        "senderTel" => "/",
        "senderZip" => "/",
        "goodsDesc" => $goodsDesc,
        //数量金额信息
        "goodsAmount" => $kj_goods_amount,
        "orderAmount" => $order['order_amount'],
        "payAmount"   => $order['order_amount'],
        "disAmount" => $order['discount'],
        "quantity" => $kj_goods_number,
        "weight" => $kj_goods_weight,
        "tariffAmount" => $order['tax'],// 应缴税费
        "tariffFee" => $order['tax'],// 实缴税费
        "currCode" => "RMB",
        //其他信息
        "orderType" => 1,
        "orderDesc" => $goodsDesc,
        "status" => 0,
        "payStatus" => 0,
        "orderTime" => $current_time,
        "orderCreateTime" => $current_time,
        "payTime" => $current_time,
        "deliveryStatus" => 0,
        "deliveryTime" => $current_time,
        "payType" => '91',
        //实名认证信息
        "idNum" => $account['real_id'],
        "realName" => $account['real_name'],
        "phone" => $account['real_phone'],
        "email" => $account['real_email'],
        'tariffTaxAmount'=>$total['tariff_amount'],
        'consumptionTaxAmount'=>$total['consumption_duty_amount'],
        'addedvalueTaxAmount'=>$total['added_value_tax_amount'],
        'insuranceFee'=>'0',
        //订单明细
        "orderDetails" => $goods
    );
    return $orderData;
}

?>