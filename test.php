<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_payment.php');

/**********************************
 *  测试订单支付流程
 */

$time = '2017-03-15 04:24:15';
$No = "20171031521001004070209513070";

// 提交订单
$data = array(
    'Operation' => '0',
    'orderNum' => '2017031516052',
    'orderSource' => '',
    'checkStore' => '0',
    'buyerAccount' => 'warket',
    'logisticsName' => '邮政速递',
    'postFee' => '0.00',
    'consignee' => 'warket',
    'consigneeTel' => '12345678',
    'regionCode' => '/',
    'consigneeCountry' => 'CHN',
    'consigneeProvince' => '浙江省',
    'consigneeCity' => '宁波市',
    'consigneeDistrict' => '江东区',
    'consigneeAddr' => 'warket',
    'mailNo' => '123456',
    'senderAddr' => '/',
    'senderCity' => '/',
    'senderCompanyName' => '/',
    'senderCountry' => '/',
    'senderName' => '/',
    'senderProvince' => '/',
    'senderTel' => '/',
    'senderZip' => '/',
    'goodsDesc' => 'Cheerios通用磨坊蜜果麦圈圈（超大号）;',
    'goodsAmount' => '0.01',
    'orderAmount' => '0.00',
    'payAmount' => '0.00',
    'disAmount' => '0.00',
    'quantity' => '1',
    'weight' => '1.45',
    'tariffAmount' => '0.00',
    'tariffFee' => '0.00',
    'currCode' => 'RMB',
    'orderType' => '1',
    'orderDesc' => 'Cheerios通用磨坊蜜果麦圈圈（超大号）;',
    'status' => '0',
    'payStatus' => '0',
    'orderTime' => $time,
    'orderCreateTime' => $time,
    'payTime' => $time,
    'deliveryStatus' => '0',
    'deliveryTime' => $time,
    'payType' => '91',
    'idNum' => '330205199203250318',
    'realName' => '我是谁',
    'phone' => '13901236621',
    'email' => 'it@pbcc.ca',
    'tariffTaxAmount' => '0',
    'consumptionTaxAmount' => '0',
    'addedvalueTaxAmount' => '0',
    'insuranceFee' => '0',
    'orderDetails' => array(
        '0' => array(
            'goodsSku' => '310516614000000003',
            'goodsName' => 'Cheerios通用磨坊蜜果麦圈圈（超大号）',
            'quantity' => '1',
            'unit' => '件',
            'price' => '0.01',
            'weight' => '1.45',
            'tariffRate' => '0',
            'tariffPrice' => '0',
            'tariffFee' => '0',
        )
    ),
    'OrderSeqNo' => $No,
    'PaymentNo' => $No,
    'Source' => '02',
);

include_once('report/orderReportHaiGuan.php');
//$result = hg_SendOrder($data);
//var_dump($result);

// 获取订单申报号之后再提交支付信息
//if ($result->Header->Result == 'T') {
    $data = array(
        "CreateTime" => $time,
        "MftNo" => '31052017I010040613',
        "PaymentNo" => $No,
        "OrderSeqNo" => $No,
        "Amount" => "0.01",
        "Source" => "02",
        "Idnum" => "330205199203250318",
        "Name" => "我是谁",
        "Phone" => "13901236621",
        "Email" => "it@pbcc.ca",
    );

    //$result = hg_SendPayment($data);
    //echo "<br><br>";
    //var_dump($result);
//}

// --------------------------------------------

// 查看税
$data = array(
    "PostFee"=>5.3,
    "InsuranceFee"=>0,
    "goods"=>array(
        0=>array(
            "kj_sn"=>"310515614000000482",
            "goods_name"=>"Aveeno 润肤露(2+1套装) 2x600ml+71ml", //艾维诺润肤露
            "goods_number"=>1,
            "goods_price"=>99
        )
    )
);
$result = hg_GetTax($data);
var_dump($result);

if ($result->Header->Result == 'T') {
    $tax_fee = $result->Body;

    $total['tariff_amount'] = (float)($tax_fee->TariffAmount); // 关税
    $total['consumption_duty_amount'] = (float)($tax_fee->ConsumptionDutyAmount); // 消费税
    $total['added_value_tax_amount'] = (float)($tax_fee->AddedValueTaxAmount); // 增值税
    $total['tax'] = (float)($tax_fee->TaxAmount); // 总税额
    $total['tariff_amount_formated'] = price_format($total['tariff_amount'], false);
    $total['consumption_duty_amount_formated'] = price_format($total['consumption_duty_amount'], false);
    $total['added_value_tax_amount_formated'] = price_format($total['added_value_tax_amount'], false);
    $total['tax_formated'] = price_format($total['tax'], false);
    echo "<br><br>";
    var_dump($total);
}

?>