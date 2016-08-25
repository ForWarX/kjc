<?php

/**
 * PBCC 会员升级
 * ============================================================================
 * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: upgrade.php $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

if (!isset($_REQUEST['step']))
{
    $_REQUEST['step'] = "checkout";
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

assign_template();
assign_dynamic('flow');
$position = assign_ur_here(0, "高级会员开通/续费");
$smarty->assign('page_title',       $position['title']);    // 页面标题
$smarty->assign('ur_here',          $position['ur_here']);  // 当前位置

$smarty->assign('categories',       get_categories_tree()); // 分类树
$smarty->assign('helps',            get_shop_help());       // 网店帮助
$smarty->assign('lang',             $_LANG);
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('data_dir',    DATA_DIR);       // 数据目录

if ($_REQUEST['step'] == 'checkout'){

	if ($_SESSION['user_id'] == 0)
    {
        /* 用户没有登录，转向到登录页面 */
        ecs_header("Location: upgrade.php?step=login\n");
        exit;
    }
	if ($_SESSION['user_rank'] == 3){
		$smarty->assign('already_is', 1);	
	}
	else{
		$smarty->assign('already_is', 0);
	}
	// 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
    $payment_list = available_payment_list(1, $cod_fee);
    if(isset($payment_list))
    {
        foreach ($payment_list as $key => $payment)
        {
            if ($payment['is_cod'] == '1')
            {
                $payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
            }
            /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
            if ($payment['pay_code'] == 'yeepayszx' && $total['amount'] > 300)
            {
                unset($payment_list[$key]);
            }
            /* 如果有余额支付 */
            if ($payment['pay_code'] == 'balance')
            {
                /* 如果未登录，不显示 */
                if ($_SESSION['user_id'] == 0)
                {
                    unset($payment_list[$key]);
                }
                else
                {
                    if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id'])
                    {
                        $smarty->assign('disable_surplus', 1);
                    }
                }
            }
        }
    }
	$smarty->assign('payment_list', $payment_list);
	$user_info = user_info($_SESSION['user_id']);

    /* 如果使用余额，取得用户余额 */
    if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['user_money'] > 0)
    {
        // 能使用余额
        $smarty->assign('allow_use_surplus', 1);
        $smarty->assign('your_surplus', $user_info['user_money']);
    }

    /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['pay_points'] > 0
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 能使用积分
        $smarty->assign('allow_use_integral', 1);
        $smarty->assign('order_max_integral', flow_available_points());  // 可用积分
        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分
    }
	
	include_once(ROOT_PATH . 'includes/lib_clips.php');

    $candidate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($candidate_id);

    //$smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('candidate_order',   $account);

	/* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'login')
{
    include_once('languages/'. $_CFG['lang']. '/user.php');

    /*
     * 用户登录注册
     */
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        $smarty->assign('anonymous_buy', $_CFG['anonymous_buy']);

        /* 检查是否有赠品，如果有提示登录后重新选择赠品 */
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
                " WHERE session_id = '" . SESS_ID . "' AND is_gift > 0";
        if ($db->getOne($sql) > 0)
        {
            $smarty->assign('need_rechoose_gift', 1);
        }

        /* 检查是否需要注册码 */
        $captcha = intval($_CFG['captcha']);
        if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
        {
            $smarty->assign('enabled_login_captcha', 1);
            $smarty->assign('rand', mt_rand());
        }
        if ($captcha & CAPTCHA_REGISTER)
        {
            $smarty->assign('enabled_register_captcha', 1);
            $smarty->assign('rand', mt_rand());
        }
    }
    else
    {
        include_once('includes/lib_passport.php');
        if (!empty($_POST['act']) && $_POST['act'] == 'signin')
        {
            $captcha = intval($_CFG['captcha']);
            if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
            {
                if (empty($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }

                /* 检查验证码 */
                include_once('includes/cls_captcha.php');

                $validator = new captcha();
                $validator->session_word = 'captcha_login';
                if (!$validator->check_word($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }
            }

            if ($user->login($_POST['username'], $_POST['password'],isset($_POST['remember'])))
            {
                update_user_info();  //更新用户信息
                

                /* 检查购物车中是否有商品 没有商品则跳转到首页 */
                /* $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . " WHERE session_id = '" . SESS_ID . "' "; */
                ecs_header("Location: upgrade.php?step=checkout\n");

                exit;
            }
            else
            {
                $_SESSION['login_fail']++;
                show_message($_LANG['signin_failed'], '', 'upgrade.php?step=login');
            }
        }
        elseif (!empty($_POST['act']) && $_POST['act'] == 'signup')
        {
            if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
            {
                if (empty($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }

                /* 检查验证码 */
                include_once('includes/cls_captcha.php');

                $validator = new captcha();
                if (!$validator->check_word($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }
            }

            if (register(trim($_POST['username']), trim($_POST['password']), trim($_POST['email'])))
            {
                /* 用户注册成功 */
                ecs_header("Location: upgrade.php?step=checkout\n");
                exit;
            }
            else
            {
                $err->show();
            }
        }
        else
        {
            // TODO: 非法访问的处理
        }
    }
}
elseif ($_REQUEST['step'] == 'select_payment')
{
    /*------------------------------------------------------ */
    //-- 改变支付方式
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);


        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['pay_id'] = intval($_REQUEST['payment']);
        $payment_info = payment_info($order['pay_id']);
        $result['pay_code'] = $payment_info['pay_code'];

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');

    echo $json->encode($result);
    exit;
}

elseif ($_REQUEST['step'] == 'done')
{
	include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
	
	include_once(ROOT_PATH . 'languages/zh_cn/common.php');
	
	
	$duration = isset($_POST['month'])   ? intval($_POST['month'])   : 0;
	
	/*$amount = 0.01;*/
	//会费in人民币
	$amount = membership_fee_payable($duration);
	//in加币
	$real_amount = round($amount/floatval($xe),2);
	
	$candidate = array(
			'user_id' => $_SESSION['user_id'],
			'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
			'process_type' => 0,
			'payment_id' => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
			'amount'       => $real_amount,
			'user_note'	   => '开通/续费高级会员',
			'vip_duration' => $duration
	);
	if ($candidate['payment_id'] <= 0)
    {
        show_message($_LANG['select_payment_pls']);
    }
	include_once(ROOT_PATH .'includes/lib_payment.php');
	
	//获取支付方式名称
      $payment_info = array();
      $payment_info = payment_info($candidate['payment_id']);
      $candidate['payment'] = $payment_info['pay_name'];
	   
	  if ($candidate['rec_id'] > 0)
      {
          //更新会员账目明细
          $candidate['rec_id'] = update_user_account($candidate);
      }
      else
      {
          //插入会员账目明细
          $candidate['rec_id'] = insert_user_account($candidate, $real_amount);
      }
	  
	  //取得支付信息，生成支付代码
      $payment = unserialize_config($payment_info['pay_config']);
	  
	  //生成伪订单号, 不足的时候补0
      $order = array();
	  if ($payment_info['pay_name'] == "paypal"){
      	$order['order_sn']       = "北美商城高级会员会费";
	  }
	  else{
      	$order['order_sn']       = $candidate['rec_id'];
	  }
      $order['user_name']      = $_SESSION['user_name'];
      $order['membership_fee'] = $real_amount;
	  
	  //计算此次预付款需要支付的总金额
	  $order['order_amount']   = $real_amount;
	  
	   //记录支付log
      $order['log_id'] = insert_pay_log($candidate['rec_id'], $order['order_amount'], $type=3, 0);
	  
	  /* 调用相应的支付方式文件 */
	  include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');
	  /* 取得在线支付方式的支付按钮 */
      $pay_obj = new $payment_info['pay_code'];
      $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
	  
	  /* 模板赋值 */
	  $smarty->assign('payment', $payment_info);
	  $smarty->assign('rmb_amount', $amount);
	  $smarty->assign('amount',  price_format($real_amount, false));
	  $smarty->assign('order',   $order);
}
$smarty->assign('currency_format', $_CFG['currency_format']);
$smarty->assign('integral_scale',  $_CFG['integral_scale']);
$smarty->assign('step',            $_REQUEST['step']);
assign_dynamic('shopping_flow');

$smarty->display('upgrade.dwt');
/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得用户的可用积分
 *
 * @access  private
 * @return  integral
 */
function flow_available_points()
{
    $sql = "SELECT SUM(g.integral * c.goods_number) ".
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";

    $val = intval($GLOBALS['db']->getOne($sql));

    return integral_of_value($val);
}

/**
 * 获得用户的选择交付的会费
 *
 * @access  private
 * @param	integer	  $month
 * @return  integer
 */
function membership_fee_payable($month)
{
	return 55 * (int)($month/3) + 20 * ($month % 3);
}
?>