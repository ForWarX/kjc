<?php

/**
 * 跨境订单回调 生成发货单，填写运单号
 * ============================================================================
 * * 
 * ============================================================================
 * $Id: kj_order_return.php $
 */
define('IN_ECS', true);
error_reporting(1);//设置报错级别
header('Content-type: text/html; charset=UTF-8');
date_default_timezone_set("Asia/Shanghai");

openDebugFile();
debug("收到异步订单回调");

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
//require_once(ROOT_PATH . 'includes/lib_goods.php');


     

        if (!empty($_POST))
        {
               foreach($_POST as $key => $data)
               {
                   $_GET[$key] = $data;
               }
        }   
        
        include_once('includes/cls_json.php');
        $json = new JSON();
        $result=array();
        if($_GET['method']=='do_send'){
            $result=orderReturn();
        }else{
             $result=array(
                    "code" => "202",
                    "desc" => "请求方式非法"
              );
        }
        echo $json->encode($result);
        closeDebugFile();
        
/**
 * 调用订单回调接口的方法
 * @return type
 */
function orderReturn(){
         /* 检查数字签名是否正确*/
        if(!checkSign()){ 
            return array("code" => "-2", "desc" => "签名校验错误");
        } 
        /*订单号，物流单号*/
        $orderDataStr=$_GET['orderData'];
        $json = new JSON();
        $orderData=$json->decode($orderDataStr,true);
        $order_sn = trim($orderData['orderNum']);
        $logistics_no = trim($orderData['logisticsNo']);
        
        //$order_sn=$_GET['orderNum'];
        //$logistics_no=$_GET['logisticsNo'];
        
        /* 取得订单 */
        $sql = "SELECT *  FROM " . $GLOBALS['ecs']->table('order_info') ." WHERE order_sn = '$order_sn'";
        $order = $GLOBALS['db']->getRow($sql);
        if($order==null){
            debug('操作失败，orderNum:'.$order_sn.'的订单不存在。');
            return array(
                    "code" => "201",
                    "desc" => "操作失败，订单不存在"
              );
        }
         /* 订单状态是已发货时，直接返回*/
        if($order['shipping_status']==SS_SHIPPED){
            debug('订单状态已经是已发货状态，直接返回。');
            return array("code" => "200","desc" => "操作成功");
        }
        $order_id=$order['order_id'];
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳
        //查询发货单列表中是否已存在发货单
        $sql = "SELECT delivery_id  FROM " . $GLOBALS['ecs']->table('delivery_order') ." WHERE order_sn = '$order_sn' order by update_time desc limit 0,1";
        $delivery_id = $GLOBALS['db']->getOne($sql);
        if($delivery_id==null){
            debug('发货单不存在，生成发货单。');
             /*生成发货单*/
            $res=create_delivery_order($order,$logistics_no);
            if($res['code']!='200'){
                return $res;
            }
            $delivery_id=$res['desc'];
            debug('生成发货单号：'.$delivery_id);
        }else{
             debug('发货单已不存在，直接修改发货单信息。');
        }
            /* 修改发货单信息 */
            update_delivery_order($delivery_id, $logistics_no);
            /* 如果使用库存，且发货时减库存，则修改库存 */
            storage_update($delivery_id);
        
            /* 标记订单为已确认 “已发货” */
            /* 更新发货时间 */
            $order_finish = get_order_finish($order_id);
            $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
            if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART)
            {
                $arr['order_status']    = OS_CONFIRMED;
                $arr['confirm_time']    = GMTIME_UTC;
            }
            $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
            $arr['shipping_status']     = $shipping_status;
            $arr['shipping_time']       = GMTIME_UTC; // 发货时间
            $arr['invoice_no']          = $logistics_no;
            update_order($order_id, $arr);

            /* 发货单发货记录log */
            order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], '宁波订单接口回调','ningbo',1);

            /* 清除缓存 */
            clear_cache_files();
            return array("code" => "200","desc" => "操作成功");
}

/**
 * 修改发货单信息 
 * @param type $delivery_id
 * @param type $logistics_no
 * @return type
 */
function update_delivery_order($delivery_id,$logistics_no){
    $invoice_no = $logistics_no;
    $_delivery['invoice_no'] = $invoice_no;
    $_delivery['status'] = 0; // 0，为已发货
    $query = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('delivery_order'), $_delivery, 'UPDATE', "delivery_id = $delivery_id", 'SILENT');
    if (!$query)
    {
        return array("code" => "303","desc" => "操作失败，修改数据异常");
    }
    
    return array("code" => "200", "desc" => "操作成功");
}
/**
 * 生成发货单信息
 * @param type $order
 * @return type
 */
function create_delivery_order($order){
        $order_sn=$order['order_sn'];
        $order_id=$order['order_id'];
        /* 生成发货单 */
        $delivery['order_sn']=$order['order_sn'];
        $delivery['order_id']=$order['order_id'];
        /* 获取发货单号和流水号 */
        $delivery['delivery_sn'] = get_delivery_sn();//lib_order.php
        /* 获取当前操作员 */
        $delivery['action_user'] = 'ningbo';
        /* 获取发货单生成时间 */
        $delivery['update_time'] = gmtime();
         /* 获取发货单所属供应商 */
        $delivery['suppliers_id'] = '0';
        /* 设置默认值 */
        $delivery['status'] = 2; // 正常
        //$delivery['invoice_no']=$logistics_no;
        /* 过滤字段项 */
        $filter_fileds = array(
                                'add_time', 'user_id', 'how_oos',  'shipping_id', 'shipping_fee',
                               'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building',
                               'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
                               'agency_id','shipping_name'
                               );
        foreach ($filter_fileds as $value)
        {
            $delivery[$value] = $order[$value];
        }
        $db=$GLOBALS['db'];
        $ecs=$GLOBALS['ecs'];
        /* 发货单入库 */
        $query = $db->autoExecute($ecs->table('delivery_order'), $delivery, 'INSERT', '', 'SILENT');
        $delivery_id = $db->insert_id();
        
        /* 取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $order_sn));
        $goods_list = $_goods['goods_list'];
        $send_number=  getSendNumber($goods_list);
        
        if ($delivery_id)
        {
            debug('生成发货单成功，orderNum:'.$order_sn.'的订单的发货单id:'.$delivery_id);
            $delivery_goods = array();
            //发货单商品入库
            if (!empty($goods_list))
            {
                foreach ($goods_list as $value)
                {
                    // 商品（实货）（虚货）
                    if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card')
                    {
                        $delivery_goods = array('delivery_id' => $delivery_id,
                                                'goods_id' => $value['goods_id'],
                                                'product_id' => $value['product_id'],
                                                'product_sn' => $value['product_sn'],
                                                'goods_id' => $value['goods_id'],
                                                'goods_name' => addslashes($value['goods_name']),
                                                'brand_name' => addslashes($value['brand_name']),
                                                'goods_sn' => $value['goods_sn'],
                                                'send_number' => $send_number[$value['rec_id']],
                                                'parent_id' => 0,
                                                'is_real' => $value['is_real'],
                                                'goods_attr' => addslashes($value['goods_attr'])
                                                );

                        /* 如果是货品 */
                        if (!empty($value['product_id']))
                        {
                            $delivery_goods['product_id'] = $value['product_id'];
                        }

                        $query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
                    }
                    // 商品（超值礼包）
                    elseif ($value['extension_code'] == 'package_buy')
                    {
                        foreach ($value['package_goods_list'] as $pg_key => $pg_value)
                        {
                            $delivery_pg_goods = array('delivery_id' => $delivery_id,
                                                    'goods_id' => $pg_value['goods_id'],
                                                    'product_id' => $pg_value['product_id'],
                                                    'product_sn' => $pg_value['product_sn'],
                                                    'goods_name' => $pg_value['goods_name'],
                                                    'brand_name' => '',
                                                    'goods_sn' => $pg_value['goods_sn'],
                                                    'send_number' => $send_number[$value['rec_id']][$pg_value['g_p']],
                                                    'parent_id' => $value['goods_id'], // 礼包ID
                                                    'extension_code' => $value['extension_code'], // 礼包
                                                    'is_real' => $pg_value['is_real']
                                                    );
                            $query = $db->autoExecute($ecs->table('delivery_goods'), $delivery_pg_goods, 'INSERT', '', 'SILENT');
                        }
                    }
                }
            }
        }
        else
        {
            /* 操作失败 */
           debug('操作失败，orderNum:'.$order_sn.'的订单生成发货单数据insert报错。');
           return array(
                    "code" => "302",
                    "desc" => "操作失败，插入发货单数据异常"
            );
        }
         
            
            $_sended = & $send_number;
            foreach ($_goods['goods_list'] as $key => $value)
            {
                if ($value['extension_code'] != 'package_buy')
                {
                    unset($_goods['goods_list'][$key]);
                }
            }
            foreach ($goods_list as $key => $value)
            {
                if ($value['extension_code'] == 'package_buy')
                {
                    unset($goods_list[$key]);
                }
            }
            $_goods['goods_list'] = $goods_list + $_goods['goods_list'];
            unset($goods_list);

            /* 更新订单的虚拟卡 商品（虚货） 跨境订单没有虚拟卡商品
            $_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
            update_order_virtual_goods($order_id, $_sended, $_virtual_goods);
            */
            
            /* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
            update_order_goods($order_id, $_sended, $_goods['goods_list']);
            
        return array("code" => "200", "desc" => $delivery_id);
}

function checkSign(){
        $appSecret=$GLOBALS['_LANG']['appSecret'];
        ksort($_GET);
        reset($_GET);
        $param = '';
        $singArr=array();
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign')
            {
                $param .= "$key=".stripslashes($val)."&";
            }
        }
        $param=substr($param, 0, -1);
        
        $sign=$param.'&'.$appSecret;
        
        debug("签名:".$param);
        $sign = strtoupper(md5($sign));//加密签名
        debug("加密后签名:".$sign."    request获得的签名：".$_GET['sign']);
        
        if ($sign != $_GET['sign'])
        {
            return false;
        }
        return true;
 }
 
 /**
 * 取得订单商品
 * @param   array     $order  订单数组
 * @return array
 */
function get_order_goods($order)
{
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, g.suppliers_id AS suppliers_id,IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name, p.product_sn " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS o ".
            "LEFT JOIN " . $GLOBALS['ecs']->table('products') . " AS p ON o.product_id = p.product_id " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON o.goods_id = g.goods_id " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('brand') . " AS b ON g.brand_id = b.brand_id " .
            "WHERE o.order_id = '$order[order_id]' ";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        // 虚拟商品支持
        if ($row['is_real'] == 0)
        {
            /* 取得语言项 */
            $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php';
            if (file_exists($filename))
            {
                include_once($filename);
                if (!empty($GLOBALS['_LANG'][$row['extension_code'].'_link']))
                {
                    $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);
                }
            }
        }

        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);

        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组

        if ($row['extension_code'] == 'package_buy')
        {
            $row['storage'] = '';
            $row['brand_name'] = '';
            $row['package_goods_list'] = get_package_goods_list($row['goods_id']);
        }

        //处理货品id
        $row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];

        $goods_list[] = $row;
    }

    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);//以 : 号将属性拆开
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }

    return array('goods_list' => $goods_list, 'attr' => $attr);
}
 /**
 * 取得订单明细中的商品发货数，默认等于商品订单数
 * @param   array     $goods_list  订单商品明细
 * @return array
 */
function getSendNumber($goods_list){
     $send_number=array();
     foreach ($goods_list as $goods)
     {
          // 商品（实货）（虚货）
          if (empty($goods['extension_code']) || $goods['extension_code'] == 'virtual_card')
          {
              $send_number[$goods['rec_id']]=$goods['goods_number'];
          }// 商品（超值礼包）
          elseif ($goods['extension_code'] == 'package_buy')
          {
                foreach ($goods['package_goods_list'] as $pg_key => $pg_value)
                {
                        $send_number[$goods['rec_id']][$pg_value['g_p']]=$goods['goods_number'];
                }
           }               
     }
     return $send_number;
}

/**
 * 更新订单商品信息
 * @param   int     $order_id       订单 id
 * @param   array   $_sended        Array(‘商品id’ => ‘此单发货数量’)
 * @param   array   $goods_list
 * @return  Bool
 */
function update_order_goods($order_id, $_sended, $goods_list = array())
{
    if (!is_array($_sended) || empty($order_id))
    {
        return false;
    }

    foreach ($_sended as $key => $value)
    {
        // 超值礼包
        if (is_array($value))
        {
            if (!is_array($goods_list))
            {
                $goods_list = array();
            }

            foreach ($goods_list as $goods)
            {
                if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list'])))
                {
                    continue;
                }

                $goods['package_goods_list'] = package_goods($goods['package_goods_list'], $goods['goods_number'], $goods['order_id'], $goods['extension_code'], $goods['goods_id']);
                $pg_is_end = true;

                foreach ($goods['package_goods_list'] as $pg_key => $pg_value)
                {
                    if ($pg_value['order_send_number'] != $pg_value['sended'])
                    {
                        $pg_is_end = false; // 此超值礼包，此商品未全部发货

                        break;
                    }
                }

                // 超值礼包商品全部发货后更新订单商品库存
                if ($pg_is_end)
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('order_goods') . "
                            SET send_number = goods_number
                            WHERE order_id = '$order_id'
                            AND goods_id = '" . $goods['goods_id'] . "' ";

                    $GLOBALS['db']->query($sql, 'SILENT');
                }
            }
        }
        // 商品（实货）（货品）
        elseif (!is_array($value))
        {
            /* 检查是否为商品（实货）（货品） */
            foreach ($goods_list as $goods)
            {
                if ($goods['rec_id'] == $key && $goods['is_real'] == 1)
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('order_goods') . "
                            SET send_number = send_number + $value
                            WHERE order_id = '$order_id'
                            AND rec_id = '$key' ";
                    $GLOBALS['db']->query($sql, 'SILENT');
                    break;
                }
            }
        }
    }

    return true;
}

/**
 * 订单中的商品是否已经全部发货
 * @param   int     $order_id  订单 id
 * @return  int     1，全部发货；0，未全部发货
 */
function get_order_finish($order_id)
{
    $return_res = 0;

    if (empty($order_id))
    {
        return $return_res;
    }

    $sql = 'SELECT COUNT(rec_id)
            FROM ' . $GLOBALS['ecs']->table('order_goods') . '
            WHERE order_id = \'' . $order_id . '\'
            AND goods_number > send_number';

    $sum = $GLOBALS['db']->getOne($sql);
    if (empty($sum))
    {
        $return_res = 1;
    }

    return $return_res;
}

/**
 * 检查此单发货商品库存缺货情况,发货时修改库存
 * @param type $delivery_id
 * @return type
 */
function storage_update($delivery_id){
    /* 检查此单发货商品库存缺货情况 */
    $virtual_goods = array();
    $delivery_stock_sql = "SELECT DG.goods_id, DG.is_real, DG.product_id, SUM(DG.send_number) AS sums, IF(DG.product_id > 0, P.product_number, G.goods_number) AS storage, G.goods_name, DG.send_number
        FROM " . $GLOBALS['ecs']->table('delivery_goods') . " AS DG, " . $GLOBALS['ecs']->table('goods') . " AS G, " . $GLOBALS['ecs']->table('products') . " AS P
        WHERE DG.goods_id = G.goods_id
        AND DG.delivery_id = '$delivery_id'
        AND DG.product_id = P.product_id
        GROUP BY DG.product_id ";

    $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

    /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
    if(!empty($delivery_stock_result))
    {
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['storage'] || $value['storage'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
                return array( "code" => "201","desc" => "操作失败，库存不足");
            }
        }
    }
    else
    {
        $delivery_stock_sql = "SELECT DG.goods_id, DG.is_real, SUM(DG.send_number) AS sums, G.goods_number, G.goods_name, DG.send_number
        FROM " . $GLOBALS['ecs']->table('delivery_goods') . " AS DG, " . $GLOBALS['ecs']->table('goods') . " AS G
        WHERE DG.goods_id = G.goods_id
        AND DG.delivery_id = '$delivery_id'
        GROUP BY DG.goods_id ";
        $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['goods_number'] || $value['goods_number'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
               return array( "code" => "201","desc" => "操作失败，库存不足");
            }
        }
    }
    /* 如果使用库存，且发货时减库存，则修改库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP)
    {

        foreach ($delivery_stock_result as $value)
        {

            /* 商品（实货）、超级礼包（实货） */
            if ($value['is_real'] != 0)
            {
                //（货品）
                if (!empty($value['product_id']))
                {
                    $minus_stock_sql = "UPDATE " . $GLOBALS['ecs']->table('products') . "
                                        SET product_number = product_number - " . $value['sums'] . "
                                        WHERE product_id = " . $value['product_id'];
                    $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
                }

                $minus_stock_sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . "
                                    SET goods_number = goods_number - " . $value['sums'] . "
                                    WHERE goods_id = " . $value['goods_id'];

                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
            }
        }
    }
    return array( "code" => "200","desc" => "操作成功");
}
    
    
   function openDebugFile(){
        $fp = fopen("rizhi/kj_order_return_log.txt","a");
	flock($fp, LOCK_EX);
        define('debug_fp', $fp);
    }
    
    function closeDebugFile(){
        if(defined('debug_fp')){
            $fp=debug_fp;
            fwrite($fp,"\n");
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        
    }
    
    function debug($message){
        if(defined('debug_fp')){
            $fp=debug_fp;
            fwrite($fp,"[".date("Y-m-d H:i:s")."]".$message."\n");
        }
    }



?>