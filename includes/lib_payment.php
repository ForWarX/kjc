<?php

/**
 * PBCC 支付接口函数库
 * ============================================================================
 * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: lib_payment.php $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url($code)
{
    return $GLOBALS['ecs']->url() . 'respond.php?code=' . $code;
}
/**
 *  取得某支付方式信息
 *  @param  string  $code   支付方式代码
 */
function get_payment($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 *  通过订单sn取得订单ID
 *  @param  string  $order_sn   订单sn
 *  @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn($order_sn, $voucher = 'false')
{
    if ($voucher == 'true')
    {
        if(is_numeric($order_sn))
        {
              return $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id=" . $order_sn . ' AND order_type=1');
        }
        else
        {
            return "";
        }
    }
    else
    {
        if(is_numeric($order_sn))
        {
            $sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info'). " WHERE order_sn = '$order_sn'";
            $order_id = $GLOBALS['db']->getOne($sql);
        }
        if (!empty($order_id))
        {
            $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
            return $pay_log_id;
        }
        else
        {
            return "";
        }
    }
}

/**
 *  通过订单ID取得订单商品名称
 *  @param  string  $order_id   订单ID
 */
function get_goods_name_by_id($order_id)
{
    $sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('order_goods'). " WHERE order_id = '$order_id'";
    $goods_name = $GLOBALS['db']->getCol($sql);
    return implode(',', $goods_name);
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if(is_numeric($log_id))
    {
        $sql = 'SELECT order_amount FROM ' . $GLOBALS['ecs']->table('pay_log') .
              " WHERE log_id = '$log_id'";
        $amount = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        return false;
    }
    if ($money == $amount)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid($log_id, $pay_status = PS_PAYED, $note = '')
{
    /* 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0)
    {
        /* 取得要修改的支付记录信息 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') .
                " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0)
        {
            /* 修改此次支付操作的状态为已付款 */
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') .
                    " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);

            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == PAY_ORDER)
            {
                /* 取得订单信息 */
                $sql = 'SELECT order_id, user_id, order_sn, consignee, address, tel, shipping_id, extension_code, extension_id, goods_amount ' .
                        'FROM ' . $GLOBALS['ecs']->table('order_info') .
                       " WHERE order_id = '$pay_log[order_id]'";
                $order    = $GLOBALS['db']->getRow($sql);
                $order_id = $order['order_id'];
                $order_sn = $order['order_sn'];

                /* 修改订单状态为已付款 */
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                            " SET order_status = '" . OS_CONFIRMED . "', " .
                                " confirm_time = '" . gmtime() . "', " .
                                " pay_status = '$pay_status', " .
                                " pay_time = '".gmtime()."', " .
                                " money_paid = order_amount," .
                                " order_amount = 0 ".
                       "WHERE order_id = '$order_id'";
                $GLOBALS['db']->query($sql);

                /* 记录订单操作记录 */
                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
				
				$integral = integral_to_give($order);
                log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), floatval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));

                /* 如果需要，发短信 */
                if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $GLOBALS['_CFG']['sms_shop_mobile'] != '')
                {
                    include_once(ROOT_PATH.'includes/cls_sms.php');
                    $sms = new sms();
                    $sms->send($GLOBALS['_CFG']['sms_shop_mobile'],
                        sprintf($GLOBALS['_LANG']['order_payed_sms'], $order_sn, $order['consignee'], $order['tel']),'', 13,1);
                }

                /* 对虚拟商品的支持 */
                $virtual_goods = get_virtual_goods($order_id);
                if (!empty($virtual_goods))
                {
                    $msg = '';
                    if (!virtual_goods_ship($virtual_goods, $msg, $order_sn, true))
                    {
                        $GLOBALS['_LANG']['pay_success'] .= '<div style="color:red;">'.$msg.'</div>'.$GLOBALS['_LANG']['virtual_goods_ship_fail'];
                    }

                    /* 如果订单没有配送方式，自动完成发货操作 */
                    if ($order['shipping_id'] == -1)
                    {
                        /* 将订单标识为已发货状态，并记录发货记录 */
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                               " SET shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .
                               " WHERE order_id = '$order_id'";
                        $GLOBALS['db']->query($sql);

                         /* 记录订单操作记录 */
                        order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
                    }
                }

            }
            elseif ($pay_log['order_type'] == PAY_SURPLUS)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, currency_type, amount FROM " . $GLOBALS['ecs']->table('user_account') .
                            " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    $_LANG = array();
                    include_once(ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/user.php');
					
					log_account_change($arr['user_id'], $arr['currency_type'], $arr['amount'], 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);
                }
            }
			 elseif ($pay_log['order_type'] == 2)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, amount,pay_points FROM " . $GLOBALS['ecs']->table('user_account') .
                            " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    log_account_change($arr['user_id'], 0, 0, 0, 0, $arr['pay_points'], '积分充值', ACT_SAVING);//积分充值
                }
            }
			elseif ($pay_log['order_type'] == 3)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
					/* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);
                    
					//升级会员成为高级会员
					$sql = "SELECT user_id, vip_duration FROM " . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' LIMIT 1";
					$new_vip_list = $GLOBALS['db']->getRow($sql);

					$new_vip = $new_vip_list['user_id'];
					
					$sql = "SELECT vip_end FROM ". $GLOBALS['ecs']->table('users') . " WHERE user_id = '$new_vip' LIMIT 1";
					$old_end_time = $GLOBALS['db']->getOne($sql);
					$new_start_time = time();
					
					//如果目前还没到期
					if($old_end_time > $new_start_time){
						$new_end_time = get_x_months_to_the_future($old_end_time, $new_vip_list['vip_duration']);
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') .
							   " SET user_rank = 3, vip_end = " . $new_end_time .
							   " WHERE user_id = '$new_vip' LIMIT 1";
						$GLOBALS['db']->query($sql);
					
					}
					else{
						$new_end_time = get_x_months_to_the_future($new_start_time, $new_vip_list['vip_duration']);
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') .
							   " SET user_rank = 3, vip_start = " . $new_start_time .", vip_end = " . $new_end_time .
							   " WHERE user_id = '$new_vip' LIMIT 1";
						$GLOBALS['db']->query($sql);
					}
                }
            }
        }
        else
        {
            /* 取得已发货的虚拟商品信息 */
            $post_virtual_goods = get_virtual_goods($pay_log['order_id'], true);

            /* 有已发货的虚拟商品 */
            if (!empty($post_virtual_goods))
            {
                $msg = '';
                /* 检查两次刷新时间有无超过12小时 */
                $sql = 'SELECT pay_time, order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$pay_log[order_id]'";
                $row = $GLOBALS['db']->getRow($sql);
                $intval_time = gmtime() - $row['pay_time'];
                if ($intval_time >= 0 && $intval_time < 3600 * 12)
                {
                    $virtual_card = array();
                    foreach ($post_virtual_goods as $code => $goods_list)
                    {
                        /* 只处理虚拟卡 */
                        if ($code == 'virtual_card')
                        {
                            foreach ($goods_list as $goods)
                            {
                                if ($info = virtual_card_result($row['order_sn'], $goods))
                                {
                                    $virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
                                }
                            }

                            $GLOBALS['smarty']->assign('virtual_card',      $virtual_card);
                        }
                    }
                }
                else
                {
                    $msg = '<div>' .  $GLOBALS['_LANG']['please_view_order_detail'] . '</div>';
                }

                $GLOBALS['_LANG']['pay_success'] .= $msg;
            }

           /* 取得未发货虚拟商品 */
           $virtual_goods = get_virtual_goods($pay_log['order_id'], false);
           if (!empty($virtual_goods))
           {
               $GLOBALS['_LANG']['pay_success'] .= '<br />' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
           }
        }
    }
}

function kjpay($create_time, $payment_no, $order_no, $order_seq_no, $pay_id, $source){
	//提交支付单
	
	//电商平台帐号
	$userid = $GLOBALS['_LANG']['kj_userid'];
	//时间戳
	$current_time = date("Y-m-d H:i:s");
	$timestamp = urlencode($current_time);
	
	$pwd = $GLOBALS['_LANG']['kj_pwd'];
	//加密签名
	$sign = md5($userid . $pwd . $current_time);
	//电商企业海关代码
	$customs_code = $GLOBALS['_LANG']['kj_customs_code'];
	//电商企业名称
	$org_name = urlencode($GLOBALS['_LANG']['kj_org_name']);
	
	//买家帐号
	$sql = "SELECT `order_id` FROM " . $GLOBALS['ecs']->table('pay_log') .
                " WHERE log_id = '$pay_id'";
    $order_id = $GLOBALS['db']->getOne($sql);
	
	$sql = "SELECT `user_id` , `kj_order_amount` FROM " . $GLOBALS['ecs']->table('order_info') .
                " WHERE order_id = '$order_id'";
    $info_arr = $GLOBALS['db']->getRow($sql);
	$user_id = $info_arr['user_id'];
	$amount = $info_arr['kj_order_amount'];
	
	$sql = "SELECT `user_name` FROM " . $GLOBALS['ecs']->table('users') .
                " WHERE user_id = '$user_id'";
    $account = $GLOBALS['db']->getOne($sql);
	
	//提交进口支付单
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/paymsg.do");
	curl_setopt($ch, CURLOPT_POST, 1);
	
	$pay_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><CreateTime>" . date("Y-m-d h:i:s", $create_time) . "</CreateTime></Header><Body><Pay><PaymentNo>" . $payment_no . "</PaymentNo><OrderNo>" . $order_no . "</OrderNo><OrderSeqNo>". $order_seq_no ."</OrderSeqNo><Amount>" . $amount . "</Amount><CurrCode>RMB</CurrCode><BuyerAccount>" . $account . "</BuyerAccount><Source>" . $source . "</Source></Pay></Body></Message>";
	
	$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $pay_xmlstr);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close($ch); 	
	$xml = simplexml_load_string($server_output);
	
	/*$fp = fopen("queryrequest.txt","a");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"结果：".$xml->Header->ResultMsg."\n");
	flock($fp, LOCK_UN);
	fclose($fp);*/
	
}

?>