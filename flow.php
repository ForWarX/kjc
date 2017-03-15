<?php

/**
 * PBCC 购物流程
 * ============================================================================
 * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: flow.php $
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
    $_REQUEST['step'] = "cart";
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

assign_template();
assign_dynamic('flow');
$position = assign_ur_here(0, $_LANG['shopping_flow']);
$smarty->assign('page_title',       $position['title']);    // 页面标题
$smarty->assign('ur_here',          $position['ur_here']);  // 当前位置

$smarty->assign('categories',       get_categories_tree()); // 分类树
$smarty->assign('helps',            get_shop_help());       // 网店帮助
$smarty->assign('lang',             $_LANG);
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('data_dir',    DATA_DIR);       // 数据目录
/*------------------------------------------------------ */
//-- 添加商品到购物车
/*------------------------------------------------------ */
if ($_REQUEST['step'] == 'add_to_cart')
{
	if ($_GET["goodsString"]){
		include_once('includes/cls_json.php');
		
		class Good {
		var $quick = 0;
		var $spec = array();
		var $parent = 0;
		var $goods_id;
		var $number;
		}
	
		$data = $_GET["goodsString"];
		$st_data = substr($data, 2, -2);
		
		
		$preg_data = preg_split('/},{/', $st_data);
		//print_r($preg_data);
		
		foreach($preg_data as $pair){
			$json  = new JSON;
			$sp_data = explode(",", $pair);
			$ind = 0;
			$goods  = new Good();
			foreach($sp_data as $info){
				$sp_info = explode(":", $info);
				if (substr($sp_info[0],2,-2) == "goods_id"){
					$goods_sn = substr($sp_info[1],2,-2);;
					$sql = "SELECT g.goods_id ".
					'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
					"WHERE g.goods_sn = '" . $goods_sn ."'";
					$ans = $GLOBALS['db']->getAll($sql);
					foreach($ans as $k => $v){
						$gid = $v["goods_id"];
					}
					$goods->goods_id = intval($gid);
				}
				else if(substr($sp_info[0],2,-2) == "number"){
					$goods->number = substr($sp_info[1],2,-2);
				}
			}
			$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'num' => '0', 'price' => '0');
			
			if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent))
			{
				if ($_CFG['cart_confirm'] > 2)
				{
					$result['message'] = '';
				}
				else
				{
					$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
				}

				$result['content'] = insert_cart_info();
				$result['one_step_buy'] = $_CFG['one_step_buy'];
			}
			else
			{
				$result['message']  = $err->last_message();
				$result['error']    = $err->error_no;
				$result['goods_id'] = stripslashes($goods->goods_id);
				if (is_array($goods->spec))
				{
					$result['product_spec'] = implode(',', $goods->spec);
				}
				else
				{
					$result['product_spec'] = $goods->spec;
				}
			}
				$sql = 'SELECT * ' .
				   ' FROM ' . $GLOBALS['ecs']->table('cart') .
				   " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
				$row2 = $GLOBALS['db']->getAll($sql);
		 
		 
				if($row2){
				  
					foreach($row2 as $k => $v){
				   
					$result['num']+= $v['goods_number'];
					$result['price']+= $v['goods_number'] * $v['goods_price'];

				   
				   }

				}

				$result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
		}	
		
		header("Location: ./flow.php?step=cart");
	}
	else{
    include_once('includes/cls_json.php');
    $_POST['goods']=strip_tags(urldecode($_POST['goods']));
    $_POST['goods'] = json_str_iconv($_POST['goods']);

	if (!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
    {
        if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
        {
            ecs_header("Location:./\n");
        }
        $goods_id = intval($_REQUEST['goods_id']);
        exit;
    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'num' => '0', 'price' => '0');
    $json  = new JSON;

    if (empty($_POST['goods']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $goods = $json->decode($_POST['goods']);

    /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
    if (empty($goods->spec) AND empty($goods->quick))
    {
        $sql = "SELECT a.attr_id, a.attr_name, a.attr_type, ".
            "g.goods_attr_id, g.attr_value, g.attr_price " .
        'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
        "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' and g.attr_price !=''" .
        'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';

        $res = $GLOBALS['db']->getAll($sql);

        if (!empty($res))
        {
            $spe_arr = array();
            foreach ($res AS $row)
            {
                $spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
                $spe_arr[$row['attr_id']]['name']     = $row['attr_name'];
                $spe_arr[$row['attr_id']]['attr_id']     = $row['attr_id'];
                $spe_arr[$row['attr_id']]['values'][] = array(
                                                            'label'        => $row['attr_value'],
                                                            'price'        => $row['attr_price'],
                                                            'format_price' => price_format($row['attr_price'], false),
                                                            'id'           => $row['goods_attr_id']);
            }
            $i = 0;
            $spe_array = array();
            foreach ($spe_arr AS $row)
            {
                $spe_array[]=$row;
            }
            $result['error']   = ERR_NEED_SELECT_ATTR;
            $result['goods_id'] = $goods->goods_id;
            $result['parent'] = $goods->parent;
            $result['message'] = $spe_array;

            die($json->encode($result));
        }
    }

    /* 更新：如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1')
    {
        clear_cart();
    }

    /* 检查：商品数量是否合法 */
    if (!is_numeric($goods->number) || intval($goods->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['invalid_number'];
    }
    /* 更新：购物车 */
    else
    {
        // 更新：添加到购物车
        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
            }

            $result['content'] = insert_cart_info();
			$result['content1'] = insert_cart_info1();
            $result['one_step_buy'] = $_CFG['one_step_buy'];
        }
        else
        {
            $result['message']  = $err->last_message();
            $result['error']    = $err->error_no;
            $result['goods_id'] = stripslashes($goods->goods_id);
            if (is_array($goods->spec))
            {
                $result['product_spec'] = implode(',', $goods->spec);
            }
            else
            {
                $result['product_spec'] = $goods->spec;
            }
        }
    }

$sql = 'SELECT * ' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $row2 = $GLOBALS['db']->getAll($sql);
 
 
 if($row2){
  
  foreach($row2 as $k => $v){
   
   $result['num']+= $v['goods_number'];
   $result['price']+= $v['goods_number'] * $v['goods_price'];

   
   }

 }

    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    die($json->encode($result));
	}
}

elseif ($_REQUEST['step'] == 'drop_goods_cart1')
{ 
    
     $rec_id = trim($_GET['rec_id']);
  flow_drop_cart_goods($rec_id);
  echo 'true';
  exit;
   
}elseif ($_REQUEST['step'] == 'ajax_update_cart')
{
 include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '', 'fanliy_number' => '0', 'rec_id' => '');
 $json = new JSON();

 
 $rec_id   = $_REQUEST['rec_id']; //购物车ID
 $goods_number   = $_REQUEST['goods_number'];//
 
 
 $num = $db -> getOne("select g.goods_number from ".$ecs->table('goods')." g ,".$ecs->table('cart')." c where c.rec_id = '$rec_id' and g.goods_id = c.goods_id ");
 if($goods_number > $num){
  $goods_number = $num;
  $result['error']  = 1;
  $result['fanliy_number']= $num;
  $result['rec_id']       = $rec_id;
  $result['content']  = '该商品库存不足'.$goods_number." 件,只有".$num."件";
  die($json->encode($result));

 }


 
 
 
 $sql = "update ".$ecs->table('cart')." set goods_number = '".$goods_number."' where rec_id = '".$rec_id."'";
 $db -> query($sql);
 
 
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);
 $cart_goods = get_cart_goods();


 
  $str = $s."购物金额小计{$cart_goods['total']['goods_price']}，比市场价 {$cart_goods['total']['market_price']} 节省了 {$cart_goods['total']['saving']} ({$cart_goods['total']['save_rate']})";

    $result['message'] = $str;
 $result['error'] = 0;
  die($json->encode($result));

 exit;
}

elseif ($_REQUEST['step'] == 'link_buy')
{
    $goods_id = intval($_GET['goods_id']);

    if (!cart_goods_exists($goods_id,array()))
    {
        addto_cart($goods_id);
    }
    ecs_header("Location:./flow.php\n");
    exit;
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
                recalculate_price(); // 重新计算购物车中的商品价格

                /* 检查购物车中是否有商品 没有商品则跳转到首页 */
                $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . " WHERE session_id = '" . SESS_ID . "' ";
                if ($db->getOne($sql) > 0)
                {
                    ecs_header("Location: flow.php?step=checkout\n");
                }
                else
                {
                    ecs_header("Location:index.php\n");
                }

                exit;
            }
            else
            {
                $_SESSION['login_fail']++;
                show_message($_LANG['signin_failed'], '', 'flow.php?step=login');
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
                ecs_header("Location: flow.php?step=consignee\n");
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
elseif ($_REQUEST['step'] == 'consignee')
{
    /*------------------------------------------------------ */
    //-- 收货人信息
    /*------------------------------------------------------ */
    include_once('includes/lib_transaction.php');

    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /*
         * 收货人信息填写界面
         */

        if (isset($_REQUEST['direct_shopping']))
        {
            $_SESSION['direct_shopping'] = 1;
        }

        /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
        $smarty->assign('country_list',       get_regions());
        $smarty->assign('shop_country',       $_CFG['shop_country']);
        $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

        /* 获得用户所有的收货人信息 */
        if ($_SESSION['user_id'] > 0)
        {
            $consignee_list = get_consignee_list($_SESSION['user_id']);

            if (count($consignee_list) < 5)
            {
                /* 如果用户收货人信息的总数小于 5 则增加一个新的收货人信息 */
                $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
            }
        }
        else
        {
            if (isset($_SESSION['flow_consignee'])){
                $consignee_list = array($_SESSION['flow_consignee']);
            }
            else
            {
                $consignee_list[] = array('country' => $_CFG['shop_country']);
            }
        }
        $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
        $smarty->assign('consignee_list', $consignee_list);

        /* 取得每个收货地址的省市区列表 */
        $province_list = array();
        $city_list = array();
        $district_list = array();
        foreach ($consignee_list as $region_id => $consignee)
        {
            $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
            $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
            $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

            $province_list[$region_id] = get_regions(1, $consignee['country']);
            $city_list[$region_id]     = get_regions(2, $consignee['province']);
            $district_list[$region_id] = get_regions(3, $consignee['city']);
        }
        $smarty->assign('province_list', $province_list);
        $smarty->assign('city_list',     $city_list);
        $smarty->assign('district_list', $district_list);

        /* 返回收货人页面代码 */
        $smarty->assign('real_goods_count', exist_real_goods(0, $flow_type) ? 1 : 0);
    }
    else
    {
        /*
         * 保存收货人信息
         */
        $consignee = array(
            'address_id'    => empty($_POST['address_id']) ? 0  :   intval($_POST['address_id']),
            'consignee'     => empty($_POST['consignee'])  ? '' :   compile_str(trim($_POST['consignee'])),
            'country'       => empty($_POST['country'])    ? '' :   intval($_POST['country']),
            'province'      => empty($_POST['province'])   ? '' :   intval($_POST['province']),
            'city'          => empty($_POST['city'])       ? '' :   intval($_POST['city']),
            'district'      => empty($_POST['district'])   ? '' :   intval($_POST['district']),
            'email'         => empty($_POST['email'])      ? '' :   compile_str($_POST['email']),
            'address'       => empty($_POST['address'])    ? '' :   compile_str($_POST['address']),
            'zipcode'       => empty($_POST['zipcode'])    ? '' :   compile_str(make_semiangle(trim($_POST['zipcode']))),
            'tel'           => empty($_POST['tel'])        ? '' :   compile_str(make_semiangle(trim($_POST['tel']))),
            'mobile'        => empty($_POST['mobile'])     ? '' :   compile_str(make_semiangle(trim($_POST['mobile']))),
            'sign_building' => empty($_POST['sign_building']) ? '' :compile_str($_POST['sign_building']),
            'best_time'     => empty($_POST['best_time'])  ? '' :   compile_str($_POST['best_time']),
        );

        if ($_SESSION['user_id'] > 0)
        {
            include_once(ROOT_PATH . 'includes/lib_transaction.php');

            /* 如果用户已经登录，则保存收货人信息 */
            $consignee['user_id'] = $_SESSION['user_id'];

            save_consignee($consignee, true);
        }

        /* 保存到session */
        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);

        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }
}
elseif ($_REQUEST['step'] == 'drop_consignee')
{
    /*------------------------------------------------------ */
    //-- 删除收货人信息
    /*------------------------------------------------------ */
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))
    {
        ecs_header("Location: flow.php?step=consignee\n");
        exit;
    }
    else
    {
        show_message($_LANG['not_fount_consignee']);
    }
}
elseif ($_REQUEST['step'] == 'checkout')
{
    /*------------------------------------------------------ */
    //-- 订单确认
    /*------------------------------------------------------ */

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 团购标志 */
    if ($flow_type == CART_GROUP_BUY_GOODS)
    {
        $smarty->assign('is_group_buy', 1);
    }
    /* 积分兑换商品 */
    elseif ($flow_type == CART_EXCHANGE_GOODS)
    {
        $smarty->assign('is_exchange_goods', 1);
    }
    else
    {
        //正常购物流程  清空其他购物流程情况
        $_SESSION['flow_order']['extension_code'] = '';
    }
    
    /*跨境城购物车改造 获取选中的商品 更新购物车*/
    if($_POST['update_selected']=='yes'){
        $goodsSelect=$_POST['goodsSelect'];
        goods_select_update_cart($goodsSelect);
    }
    /*end*/

    /* 检查购物车中是否有商品 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
    $sql.=" AND is_selected=1 ";//跨境城购物车改造
    
    if ($db->getOne($sql) == 0)
    {
        show_message($_LANG['no_goods_in_cart'], '', '', 'warning');
    }
    
   

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
        ecs_header("Location: flow.php?step=login\n");
        exit;
    }

    $consignee = get_consignee($_SESSION['user_id']);

    /* 检查收货人信息是否完整 */
    if (!check_consignee_info($consignee, $flow_type))
    {
        /* 如果不完整则转向到收货人信息填写界面 */
        ecs_header("Location: flow.php?step=consignee\n");
        exit;
    }

    $_SESSION['flow_consignee'] = $consignee;
    $smarty->assign('consignee', $consignee);
    
    /* 实名认证信息 */
    $real_info = get_account_real_info($_SESSION['user_id']);
    if ($real_info != null) {
        $smarty->assign("real_info", $real_info);
    }
    
    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    
    /*跨境购  购物车改造*/
    $cart_goods = cart_goods_selected($flow_type);
    $kj_counter=0;
    foreach($cart_goods as $goods){
        $cat_id = $GLOBALS['db']->getOne("SELECT `cat_id` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`='{$goods['goods_id']}'");
        if(is_kj_product($cat_id)==1){
            //$check_num=check_is_kj_good($goods['goods_id']);//判断是否备案
            //if($check_num==1){
                $kj_counter++;
            //}
           // if($check_num==2){//请求超时
               // break;
           // }
        }
    }
    if($kj_counter <> 0 && count($cart_goods) > $kj_counter){
        show_message('<br/>购物车中同时含有跨境购商品和其他商品，跨境购商品需要单独下单。', '', '', 'warning');
    }
    /*end*/

    //跨境购改造 接口改造
    //$is_binding = kj_check($cart_goods,$_SESSION['user_name']);
    $is_binding=array(2, ""); //号百接口改造
    if(count($cart_goods) == $kj_counter){//只有跨境订单才进行该检查
        $is_binding = kj_check($cart_goods,$_SESSION['user_name']);
        if($is_binding[0]==0){//接口异常
            if($is_binding[1]==''){
                show_message('<br/>服务器连接异常，请稍后再试。', '', '', 'warning');
            } else {
                show_message('<br/>'.$is_binding[1], '', '', 'warning');
            }
        }
    }

	$smarty->assign('is_binding', $is_binding[0]);
	$smarty->assign('beian_link', $is_binding[1]);
    $smarty->assign('goods_list', $cart_goods);

	if ($is_binding[0] == 1){
		$goods_str = "<Goods>" . $is_binding[1] . "</Goods>";
		$smarty->assign('goods_str', $goods_str);
		$kj_goods_amount = $is_binding[2];
		$smarty->assign('kj_goods_amount', $kj_goods_amount);
		$kj_goods_weight = $is_binding[3];
		$smarty->assign('kj_goods_weight', $kj_goods_weight);
		$kj_goods_number = $is_binding[4];
		$smarty->assign('kj_goods_number', $kj_goods_number);
		$kj_goods_tax = $is_binding[5];
		$smarty->assign('kj_goods_tax', $kj_goods_tax);
                
        $smarty->assign('disable_surplus', true);//接口改造 增加 跨境订单不允许使用余额支付

        if($kj_goods_amount > 1000 && $kj_goods_number > 1){
            show_message('<br/>根据海关规定，单笔订单总价不可以超过1000元，如果单笔订单为一件且不可分割的商品除外。<br/><br/><span style="color:#000;text-decoration:underline;cursor:pointer;font-weight:100;" title="提示：根据海关总署规定，消费者订单均以个人生活消费品为标准，以个人自用、合理数量“为原则，参照《海关总署公告2010年第43号（关于调整进出境邮递物品管理措施有关事宜）》要求，每个订单限值1000元人民币，但如果一个订单内的商品为不可分割的单个商品，价格在1000元以上也是允许的，每人每年有限制交易的额度为2万元人民币，超过将不能够继续购买。">消费者购物金额和数量有限制吗？（请将光标移至此处）</span>', '', '', 'warning');
        }
	}
	
	$kj_goods_counter = $is_binding[6];
	
    /* 对是否允许修改购物车赋值 */
    if ($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1')
    {
        $smarty->assign('allow_edit_cart', 0);
    }
    else
    {
        $smarty->assign('allow_edit_cart', 1);
    }

    /*
     * 取得购物流程设置
     */
    $smarty->assign('config', $_CFG);
    /*
     * 取得订单信息
     */
    $order = flow_order_info();
    $smarty->assign('order', $order);

    /* 计算折扣 */
    if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS)
    {
        $discount = compute_discount();
        $smarty->assign('discount', $discount['discount']);
        $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
        $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
    }

    /*
     * 计算订单的费用
     */
    $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);

    $smarty->assign('total', $total);
    $smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

    /* 取得配送列表 */
    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
    $shipping_list     = available_shipping_list($region);
    $cart_weight_price = cart_weight_price($flow_type);
	
	$smarty->assign("cart_weight_price", $cart_weight_price);
	
    $insure_disabled   = true;
    $cod_disabled      = true;

    // 查看购物车中是否全为免运费商品，若是则把运费赋为零
    $sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
    $sql.=" AND is_selected=1 ";//跨境城购物车改造
    $shipping_count = $db->getOne($sql);

    foreach ($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
        $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?
            price_format($val['insure'], false) : $val['insure'];

        /* 当前的配送方式是否支持保价 */
        if ($val['shipping_id'] == $order['shipping_id'])
        {
            $insure_disabled = ($val['insure'] == 0);
            $cod_disabled    = ($val['support_cod'] == 0);
        }
    }

    $smarty->assign('shipping_list',   $shipping_list);
    $smarty->assign('insure_disabled', $insure_disabled);
    $smarty->assign('cod_disabled',    $cod_disabled);

    /* 取得支付列表 */
    if ($order['shipping_id'] == 0)
    {
        $cod        = true;
        $cod_fee    = 0;
    }
    else
    {
        $shipping = shipping_info($order['shipping_id']);
        $cod = $shipping['support_cod'];

        if ($cod)
        {
            /* 如果是团购，且保证金大于0，不能使用货到付款 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $group_buy_id = $_SESSION['extension_id'];
                if ($group_buy_id <= 0)
                {
                    show_message('error group_buy_id');
                }
                $group_buy = group_buy_info($group_buy_id);
                if (empty($group_buy))
                {
                    show_message('group buy not exists: ' . $group_buy_id);
                }

                if ($group_buy['deposit'] > 0)
                {
                    $cod = false;
                    $cod_fee = 0;

                    /* 赋值保证金 */
                    $smarty->assign('gb_deposit', $group_buy['deposit']);
                }
            }

            if ($cod)
            {
                $shipping_area_info = shipping_area_info($order['shipping_id'], $region);
                $cod_fee            = $shipping_area_info['pay_fee'];
            }
        }
        else
        {
            $cod_fee = 0;
        }
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

    /* 取得包装与贺卡 */
    if ($total['real_goods_count'] > 0)
    {
        /* 只有有实体商品,才要判断包装和贺卡 */
        if (!isset($_CFG['use_package']) || $_CFG['use_package'] == '1')
        {
            /* 如果使用包装，取得包装列表及用户选择的包装 */
            $smarty->assign('pack_list', pack_list());
        }

        /* 如果使用贺卡，取得贺卡列表及用户选择的贺卡 */
        if (!isset($_CFG['use_card']) || $_CFG['use_card'] == '1')
        {
            $smarty->assign('card_list', card_list());
        }
    }

    $user_info = user_info($_SESSION['user_id']);
    /*如果是跨境订单，判断用户是否已经实名注册 接口改造 实名认证*/
    if(count($cart_goods) == $kj_counter && check_account($user_info['user_name'])==1) {//只有跨境订单才进行该检查
        $smarty->assign('real_authenty', 1); // 该变量在页面中作为判断是否需要实名认证，1即为未认证，需要实名认证
    }

    /* 如果使用余额，取得用户余额 */
    if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1')
        && $_SESSION['user_id'] > 0)
    {
        // 能使用余额
        $smarty->assign('allow_use_surplus', 1);
        $smarty->assign('your_surplus', $user_info['user_money']);
		$smarty->assign('your_surplus_cny', $user_info['user_money_cny']);
    }

    /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
    /*if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['pay_points'] > 0
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))*/
	if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 能使用积分
        $smarty->assign('allow_use_integral', 1);
		include_once(ROOT_PATH . 'languages/zh_cn/common.php');
		$amount = round($total['amount'] * floatval($xe) * 10);
		
		$smarty->assign('order_max_integral', $amount);
        /*$smarty->assign('order_max_integral', flow_available_points());*/  // 可用积分
        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分
    }

    /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */
    if ((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1')
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 取得用户可用红包
        $user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);
        if (!empty($user_bonus))
        {
            foreach ($user_bonus AS $key => $val)
            {
                $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
            }
            $smarty->assign('bonus_list', $user_bonus);
        }

        // 能使用红包
        $smarty->assign('allow_use_bonus', 1);
    }

    /* 如果使用缺货处理，取得缺货处理列表 */
    if (!isset($_CFG['use_how_oos']) || $_CFG['use_how_oos'] == '1')
    {
        if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos']))
        {
            $smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
        }
    }

    /* 如果能开发票，取得发票内容列表 */
    if ((!isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1')
        && isset($_CFG['invoice_content'])
        && trim($_CFG['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS)
    {
        $inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']));
        $smarty->assign('inv_content_list', $inv_content_list);

        $inv_type_list = array();
        foreach ($_CFG['invoice_type']['type'] as $key => $type)
        {
            if (!empty($type))
            {
                $inv_type_list[$type] = $type . ' [' . floatval($_CFG['invoice_type']['rate'][$key]) . '%]';
            }
        }
        $smarty->assign('inv_type_list', $inv_type_list);
    }

    /* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'select_shipping')
{
    /*------------------------------------------------------ */
    //-- 改变配送方式
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    
    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造
    
    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['shipping_id'] = intval($_REQUEST['shipping']);
        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
        $shipping_info = shipping_area_info($order['shipping_id'], $regions);
        
        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee, $_GET['kjAmount'], $_GET['kjTax']);
        $smarty->assign('total', $total);
        
        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['cod_fee']     = $shipping_info['pay_fee'];
        if (strpos($result['cod_fee'], '%') === false)
        {
            $result['cod_fee'] = price_format($result['cod_fee'], false);
        }
        $result['need_insure'] = ($shipping_info['insure'] > 0 && !empty($order['need_insure'])) ? 1 : 0;
        $result['content']     = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_insure')
{
    /*------------------------------------------------------ */
    //-- 选定/取消配送的保价
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['need_insure'] = intval($_REQUEST['insure']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
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
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_payment')
{
    /*------------------------------------------------------ */
    //-- 改变支付方式
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
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
        $total = order_fee($order, $cart_goods, $consignee, $_GET['kjAmount'], $_GET['kjTax']);
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
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_pack')
{
    /*------------------------------------------------------ */
    //-- 改变商品包装
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造
    
    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['pack_id'] = intval($_REQUEST['pack']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
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
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_card')
{
    /*------------------------------------------------------ */
    //-- 改变贺卡
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造
    
    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['card_id'] = intval($_REQUEST['card']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'change_surplus')
{
    /*------------------------------------------------------ */
    //-- 改变余额
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
	
	if ($_GET['surplus_cny']){
		include_once(ROOT_PATH . 'languages/zh_cn/common.php');
		$surplus = round(floatval($_GET['surplus_cny'])/floatval($xe),2);
	}
	else{
    	$surplus   = floatval($_GET['surplus']);
	}
    $user_info = user_info($_SESSION['user_id']);

    if ($user_info['user_money'] + $user_info['credit_line'] < $surplus)
    {
        $result['error'] = $_LANG['surplus_not_enough'];
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
        $cart_goods = cart_goods_selected($flow_type); //购物车改造

        if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
        {
            $result['error'] = $_LANG['no_goods_in_cart'];
        }
        else
        {
            /* 取得订单信息 */
            $order = flow_order_info();
            $order['surplus'] = $surplus;

            /* 计算订单的费用 */
            //$total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
            $total = order_fee($order, $cart_goods, $consignee,$_GET['kjAmount'],$_GET['kjTax']);// $_GET['kjAmount'], $_GET['kjTax'] 错误变更
            $smarty->assign('total', $total);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }

            $result['content'] = $smarty->fetch('library/order_total.lbi');
        }
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_integral')
{
    /*------------------------------------------------------ */
    //-- 改变积分
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $points    = intval($_GET['points']);
    $user_info = user_info($_SESSION['user_id']);

    /* 取得订单信息 */
    $order = flow_order_info();

    $flow_points = flow_available_points();  // 该订单允许使用的积分
    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)
    {
        $result['error'] = $_LANG['integral_not_enough'];
    }
    elseif ($points > $flow_points)
    {
        $result['error'] = sprintf($_LANG['integral_too_much'], $flow_points);
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        $order['integral'] = $points;

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
        $cart_goods = cart_goods_selected($flow_type); //购物车改造

        if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
        {
            $result['error'] = $_LANG['no_goods_in_cart'];
        }
        else
        {
            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
            $smarty->assign('total',  $total);
            $smarty->assign('config', $_CFG);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }

            $result['content'] = $smarty->fetch('library/order_total.lbi');
            $result['error'] = '';
        }
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_bonus')
{
    /*------------------------------------------------------ */
    //-- 改变红包
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)
        {
            $order['bonus_id'] = intval($_GET['bonus']);
        }
        else
        {
            $order['bonus_id'] = 0;
            $result['error'] = $_LANG['invalid_bonus'];
        }

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_needinv')
{
    /*------------------------------------------------------ */
    //-- 改变发票的设置
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');
    $json = new JSON();
    $_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
    $_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
    $_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
        die($json->encode($result));
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1)
        {
            $order['need_inv']    = 1;
            $order['inv_type']    = trim(stripslashes($_GET['inv_type']));
            $order['inv_payee']   = trim(stripslashes($_GET['inv_payee']));
            $order['inv_content'] = trim(stripslashes($_GET['inv_content']));
        }
        else
        {
            $order['need_inv']    = 0;
            $order['inv_type']    = '';
            $order['inv_payee']   = '';
            $order['inv_content'] = '';
        }

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        die($smarty->fetch('library/order_total.lbi'));
    }
}
elseif ($_REQUEST['step'] == 'change_oos')
{
    /*------------------------------------------------------ */
    //-- 改变缺货处理时的方式
    /*------------------------------------------------------ */

    /* 取得订单信息 */
    $order = flow_order_info();

    $order['how_oos'] = intval($_GET['oos']);

    /* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'check_surplus')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的余额
    /*------------------------------------------------------ */
    $surplus   = floatval($_GET['surplus']);
    $user_info = user_info($_SESSION['user_id']);

    if (($user_info['user_money'] + $user_info['credit_line'] < $surplus))
    {
        die($_LANG['surplus_not_enough']);
    }

    exit;
}
elseif ($_REQUEST['step'] == 'check_integral')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的余额
    /*------------------------------------------------------ */
    $points      = floatval($_GET['integral']);
    $user_info   = user_info($_SESSION['user_id']);
    $flow_points = flow_available_points();  // 该订单允许使用的积分
    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)
    {
        die($_LANG['integral_not_enough']);
    }

    if ($points > $flow_points)
    {
        die(sprintf($_LANG['integral_too_much'], $flow_points));
    }

    exit;
}
/*------------------------------------------------------ */
//-- 完成所有订单操作，提交到数据库
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'done')
{
    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');
	include_once(ROOT_PATH . 'languages/zh_cn/common.php');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 检查购物车中是否有商品 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
    $sql.=" AND is_selected=1 ";//跨境城购物车改造
    if ($db->getOne($sql) == 0)
    {
        show_message($_LANG['no_goods_in_cart'], '', '', 'warning');
    }

    /* 检查商品库存 */
    /* 如果使用库存，且下订单时减库存，则减少库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        $cart_goods_stock = get_cart_goods();
        $_cart_goods_stock = array();
        foreach ($cart_goods_stock['goods_list'] as $value)
        {
            //$_cart_goods_stock[$value['rec_id']] = $value['goods_number'];
            if($value['is_selected']==1) $_cart_goods_stock[$value['rec_id']] = $value['goods_number'];//购物车改造
        }
        flow_cart_stock($_cart_goods_stock);
        unset($cart_goods_stock, $_cart_goods_stock);
    }

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
        ecs_header("Location: flow.php?step=login\n");
        exit;
    }

    $consignee = get_consignee($_SESSION['user_id']);

    /* 检查收货人信息是否完整 */
    if (!check_consignee_info($consignee, $flow_type))
    {
        /* 如果不完整则转向到收货人信息填写界面 */
        ecs_header("Location: flow.php?step=consignee\n");
        exit;
    }

    $_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
    $_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
    $_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
    $_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
    $_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
    $_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';
	
	$goods_str = $_POST['goods_str'];
	$kj_goods_amount = $_POST['kj_goods_amount'];
	$kj_goods_weight = $_POST['kj_goods_weight'];
	$kj_goods_number = $_POST['kj_goods_number'];
	$kj_goods_tax = $_POST['kj_goods_tax'];
	
	
	
    $order = array(
        'shipping_id'     => intval($_POST['shipping']),
        'pay_id'          => intval($_POST['payment']),
        'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
        'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,
        'card_message'    => trim($_POST['card_message']),
        'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,
		'surplus_cny'     => isset($_POST['surplus_cny']) ? floatval($_POST['surplus_cny']) : 0.0,
        'integral'        => isset($_POST['integral']) ? floatval($_POST['integral']) : 0,
        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,
        'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
        'inv_type'        => $_POST['inv_type'],
        'inv_payee'       => trim($_POST['inv_payee']),
        'inv_content'     => $_POST['inv_content'],
        'postscript'      => trim($_POST['postscript']),
        'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',
        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
        'user_id'         => $_SESSION['user_id'],
        'add_time'        => gmtime(),
        'order_status'    => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status'      => PS_UNPAYED,
        'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']))
        );

    /* 扩展信息 */
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }
    else
    {
        $order['extension_code'] = '';
        $order['extension_id'] = 0;
    }

    /* 检查积分余额是否合法 */
    $user_id = $_SESSION['user_id'];
    if ($user_id > 0)
    {
        $user_info = user_info($user_id);

        $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
        if ($order['surplus'] < 0)
        {
            $order['surplus'] = 0;
        }
		
		$order['surplus_cny'] = min($order['surplus_cny'], $user_info['user_money_cny'] + $user_info['credit_line']);
        if ($order['surplus_cny'] < 0)
        {
            $order['surplus_cny'] = 0;
        }

        // 查询用户有多少积分
        $flow_points = flow_available_points();  // 该订单允许使用的积分
        $user_points = $user_info['pay_points']; // 用户的积分总数

        $order['integral'] = min($order['integral'], $user_points, $flow_points);
        if ($order['integral'] < 0)
        {
            $order['integral'] = 0;
        }
    }
    else
    {
        $order['surplus']  = 0;
        $order['integral'] = 0;
    }

    /* 检查红包是否存在 */
    if ($order['bonus_id'] > 0)
    {
        $bonus = bonus_info($order['bonus_id']);

        if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))
        {
            $order['bonus_id'] = 0;
        }
    }
    elseif (isset($_POST['bonus_sn']))
    {
        $bonus_sn = trim($_POST['bonus_sn']);
        $bonus = bonus_info(0, $bonus_sn);
        $now = gmtime();
        if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_date'])
        {
        }
        else
        {
            if ($user_id > 0)
            {
                $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                $db->query($sql);
            }
            $order['bonus_id'] = $bonus['bonus_id'];
            $order['bonus_sn'] = $bonus_sn;
        }
    }

    /* 订单中的商品 */
    //$cart_goods = cart_goods($flow_type);
    $cart_goods = cart_goods_selected($flow_type);//购物车改造，获取选中的商品
    
    if (empty($cart_goods))
    {
        show_message($_LANG['no_goods_in_cart'], $_LANG['back_home'], './', 'warning');
    }

    /* 检查商品总额是否达到最低限购金额 */
    if ($flow_type == CART_GENERAL_GOODS && cart_amount(true, CART_GENERAL_GOODS) < $_CFG['min_goods_amount'])
    {
        show_message(sprintf($_LANG['goods_amount_not_enough'], price_format($_CFG['min_goods_amount'], false)));
    }

    /* 收货人信息 */
    foreach ($consignee as $key => $value)
    {
        $order[$key] = addslashes($value);
    }

   /* 判断是不是实体商品 */
    foreach ($cart_goods AS $val)
    {
        /* 统计实体商品的个数 */
        if ($val['is_real'])
        {
            $is_real_good=1;
        }
    }
    if(isset($is_real_good))
    {
        $sql="SELECT shipping_id FROM " . $ecs->table('shipping') . " WHERE shipping_id=".$order['shipping_id'] ." AND enabled =1"; 
        if(!$db->getOne($sql))
        {
           show_message($_LANG['flow_no_shipping']);
        }
    }
    /* 订单中的总额 */
    $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax,$kj_goods_weight,$kj_goods_number);
    $order['bonus']        = $total['bonus'];
    $order['goods_amount'] = $total['goods_price'];
    $order['discount']     = $total['discount'];
    $order['surplus']      = $total['surplus'];
	$order['surplus_cny']  = $total['surplus_cny'];
	$order['tax']          = $total['tax'];
	
    // 购物车中的商品能享受红包支付的总额
    $discount_amout = compute_discount_amount();
    // 红包和积分最多能支付的金额为商品总额
    $temp_amout = $order['goods_amount'] - $discount_amout;
    if ($temp_amout <= 0)
    {
        $order['bonus_id'] = 0;
    }

    /* 配送方式 */
    if ($order['shipping_id'] > 0)
    {
        $shipping = shipping_info($order['shipping_id']);
        $order['shipping_name'] = addslashes($shipping['shipping_name']);
    }
    $order['shipping_fee'] = $total['shipping_fee'];
    $order['insure_fee']   = $total['shipping_insure'];

    /* 支付方式 */
    if ($order['pay_id'] > 0)
    {
        $payment = payment_info($order['pay_id']);
        $order['pay_name'] = addslashes($payment['pay_name']);
    }
    $order['pay_fee'] = $total['pay_fee'];
    $order['cod_fee'] = $total['cod_fee'];

    /* 商品包装 */
    if ($order['pack_id'] > 0)
    {
        $pack               = pack_info($order['pack_id']);
        $order['pack_name'] = addslashes($pack['pack_name']);
    }
    $order['pack_fee'] = $total['pack_fee'];

    /* 祝福贺卡 */
    if ($order['card_id'] > 0)
    {
        $card               = card_info($order['card_id']);
        $order['card_name'] = addslashes($card['card_name']);
    }
    $order['card_fee']      = $total['card_fee'];

    $order['order_amount']  = number_format($total['amount'], 2, '.', '');

    /* 如果全部使用余额支付，检查余额是否足够 */
    if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)
    {
        if($order['surplus'] >0) //余额支付里如果输入了一个金额
        {
            $order['order_amount'] = $order['order_amount'] + $order['surplus'];
            $order['surplus'] = 0;
        }
		if($order['surplus_cny'] >0) //余额支付里如果输入了一个金额
        {
            $order['order_amount'] = $order['order_amount'] + round($order['surplus_cny']/floatval($xe),2);
            $order['surplus_cny'] = 0;
        }
        if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
        {
            show_message($_LANG['balance_not_enough']);
        }
        else
        {
			$order['surplus_cny'] = round($order['order_amount']*floatval($xe),1);
            $order['surplus'] = $order['order_amount'];
            $order['order_amount'] = 0;
        }
    }

    /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
    if ($order['order_amount'] <= 0)
    {
        $order['order_status'] = OS_CONFIRMED;
        $order['confirm_time'] = gmtime();
        $order['pay_status']   = PS_PAYED;
        $order['pay_time']     = gmtime();
        $order['order_amount'] = 0;
    }

    $order['integral_money']   = $total['integral_money'];
    $order['integral']         = $total['integral'];

    if ($order['extension_code'] == 'exchange_goods')
    {
        $order['integral_money']   = 0;
        $order['integral']         = $total['exchange_integral'];
    }

    $order['from_ad']          = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
    $order['referer']          = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

    /* 记录扩展信息 */
    if ($flow_type != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    $affiliate = unserialize($_CFG['affiliate']);
    if(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1)
    {
        //推荐订单分成
        $parent_id = get_affiliate();
        if($user_id == $parent_id)
        {
            $parent_id = 0;
        }
    }
    elseif(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0)
    {
        //推荐注册分成
        $parent_id = 0;
    }
    else
    {
        //分成功能关闭
        $parent_id = 0;
    }
    $order['parent_id'] = $parent_id;
    
    //订单支付改造
    $kj_counter=0;
    foreach($cart_goods as $goods){
        $cat_id = $GLOBALS['db']->getOne("SELECT `cat_id` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`='{$goods['goods_id']}'");
        if(is_kj_product($cat_id)==1){ 
            $kj_counter++;
        }
    }
    /*if($kj_counter <> 0 && count($cart_goods) > $kj_counter){
        show_message('<br/>购物车中同时含有跨境购商品和其他商品，跨境购商品需要单独下单。', '','flow.php?step=checkout', 'warning');
        exit;
    }*/
    $order['order_type'] = 0;
    if($kj_counter==count($cart_goods)){
        $order['order_type'] = 1;
    }
    //实名认证
    $user_info = user_info($_SESSION['user_id']);
    $order['id_num']='';
    $order['real_name']='';
    $order['phone']='';
    $order['identy_email']='';
    
    if($order['order_type'] == 1 && check_account($user_info['user_name'])==1){
        $id_num=$_POST['id_num'];
        $real_name=$_POST['real_name'];
        $phone=$_POST['phone'];
        $identy_email=$_POST['identy_email'];
        if(empty($id_num) || empty($real_name) || empty($phone)){
            show_message('<br/>跨境订单，实名认证信息不能为空', '','', 'warning');
            exit;
        }
        //验证身份证号是否合法
        if(!validation_filter_id_card($id_num)){
            show_message('<br/>实名认证需要输入正确的身份证号', '','', 'warning');
            exit;
        }
        /*$order['id_num']=$id_num;
        $order['real_name']=$real_name;
        $order['phone']=$phone;
        if(!empty($identy_email))
            $order['identy_email']=$identy_email;
        */
        //绑定实名帐号
        $bind_res=bind_account($user_info['user_id'],$id_num,$real_name,$phone,$identy_email);
        if($bind_res[0]==0){
            show_message('<br/>绑定实名帐号出错，'.$bind_res[1], '','', 'warning');
            exit;
        }
    }
    //end
    
    /* 插入订单表 */
    $error_no = 0;
    do
    {
        $order['order_sn'] = get_order_sn(); //获取新订单号
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();

        if ($error_no > 0 && $error_no != 1062)
        {
            die($GLOBALS['db']->errorMsg());
        }
    }
    while ($error_no == 1062); //如果是订单号重复则重新提交数据

    $new_order_id = $db->insert_id();
    $order['order_id'] = $new_order_id;

    /* 插入订单商品 */
    $sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".
            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id".
            " FROM " .$ecs->table('cart') .
            " WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
    $sql.=" AND is_selected=1 ";//跨境城购物车改造
    $db->query($sql);
    /* 修改拍卖活动状态 */
    if ($order['extension_code']=='auction')
    {
        $sql = "UPDATE ". $ecs->table('goods_activity') ." SET is_finished='2' WHERE act_id=".$order['extension_id'];
        $db->query($sql);
    }

    /* 处理余额、积分、红包 */
    if ($order['user_id'] > 0 && $order['surplus'] > 0)
    {
        log_account_change($order['user_id'], 0, $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
    }
	if ($order['user_id'] > 0 && $order['surplus_cny'] > 0)
    {
        log_account_change($order['user_id'], 1, $order['surplus_cny'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
    }
    if ($order['user_id'] > 0 && $order['integral'] > 0)
    {
        log_account_change($order['user_id'], 0, 0, 0, 0, $order['integral'] * (-1), sprintf($_LANG['pay_order'], $order['order_sn']));
    }


    if ($order['bonus_id'] > 0 && $temp_amout > 0)
    {
        use_bonus($order['bonus_id'], $new_order_id);
    }

    /* 如果使用库存，且下订单时减库存，则减少库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        change_order_goods_storage($order['order_id'], true, SDT_PLACE);
    }

	/*用P币或余额支付，返利*/
	if ($order['order_amount'] <= 0){
		$integral = integral_to_give($order);
		log_account_change($order['user_id'], 0, 0, 0, intval($integral['rank_points']), floatval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
	}
	
    /* 给商家发邮件 */
    /* 增加是否给客服发送邮件选项 */
    if ($_CFG['send_service_email'] && $_CFG['service_email'] != '')
    {
        $tpl = get_mail_template('remind_of_new_order');
        $smarty->assign('order', $order);
        $smarty->assign('goods_list', $cart_goods);
        $smarty->assign('shop_name', $_CFG['shop_name']);
        $smarty->assign('send_date', date($_CFG['time_format']));
        $content = $smarty->fetch('str:' . $tpl['template_content']);
        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
    }

    /* 如果需要，发短信 */
    if ($_CFG['sms_order_placed'] == '1' && $_CFG['sms_shop_mobile'] != '')
    {
        include_once('includes/cls_sms.php');
        $sms = new sms();
        $msg = $order['pay_status'] == PS_UNPAYED ?
            $_LANG['order_placed_sms'] : $_LANG['order_placed_sms'] . '[' . $_LANG['sms_paid'] . ']';
        $sms->send($_CFG['sms_shop_mobile'], sprintf($msg, $order['consignee'], $order['tel']),'', 13,1);
    }

    /* 如果订单金额为0 处理虚拟卡 */
    if ($order['order_amount'] <= 0)
    {
        $sql = "SELECT goods_id, goods_name, goods_number AS num FROM ".
               $GLOBALS['ecs']->table('cart') .
                " WHERE is_real = 0 AND extension_code = 'virtual_card'".
                " AND session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
        $sql.=" AND is_selected=1 ";//跨境城购物车改造
        $res = $GLOBALS['db']->getAll($sql);

        $virtual_goods = array();
        foreach ($res AS $row)
        {
            $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
        }

        if ($virtual_goods AND $flow_type != CART_GROUP_BUY_GOODS)
        {
            /* 虚拟卡发货 */
            if (virtual_goods_ship($virtual_goods,$msg, $order['order_sn'], true))
            {
                /* 如果没有实体商品，修改发货状态，送积分和红包 */
                $sql = "SELECT COUNT(*)" .
                        " FROM " . $ecs->table('order_goods') .
                        " WHERE order_id = '$order[order_id]' " .
                        " AND is_real = 1";
                if ($db->getOne($sql) <= 0)
                {
                    /* 修改订单状态 */
                    update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                    /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                    if ($order['user_id'] > 0)
                    {
                        /* 取得用户信息 */
                        $user = user_info($order['user_id']);

                        /* 计算并发放积分 */
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                        /* 发放红包 */
                        send_order_bonus($order['order_id']);
                    }
                }
            }
        }

    }
    
    if($order['order_type'] == 1){//接口改造增加
        /* 需要拿到支付单号才能提交给申报系统，因此不在这里提交
	   $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') ." WHERE order_id = ".$order['order_id'];
        $orderDb=$GLOBALS['db']->getRow($sql);
        $goods_list=order_goods($orderDb['order_id']);
        $orderData=get_report_order($orderDb,$goods_list);
        $submit_suc=array(0,'');
        if($orderData!=null){
            $submit_suc=kj_order_submit($orderData,$orderDb);
        }
        */
        /* 旧代码， 在上面的注释之前就已注释
        if($submit_suc[0]==1){
            update_order($order['order_id'], array('kj_order_amount' => $orderData['orderAmount']));
        }else{
            show_message('<br/>请求超时，订单提交失败，请稍后再试。', '', 'flow.php?step=checkout', 'warning'); 
        }*/
    }

    /* 清空购物车 */
    //clear_cart($flow_type);
    clear_select_cart($flow_type);//跨境城购物车改造
    /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
    clear_all_files();

    /* 插入支付日志 */
    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

    /* 取得支付信息，生成支付代码 */
    if ($order['order_amount'] > 0)
    {
        $payment = payment_info($order['pay_id']);
        //支付改造
        if($order['order_type'] == 1){//跨境订单使用
            // 号百的支付方式
            //$pay_online= '<div style="text-align:center"><input type="button" onclick="window.open(\'flow.php?step=pay_order&order_id='.$order['order_id'].'&logId='.$order['log_id'].'\')"  value="立即使用支付宝支付" /></div>';

            // 现改回原来的支付方式，调用支付宝的普通接口
            include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
            $pay_obj    = new $payment['pay_code'];
            $pay_config = unserialize_config($payment['pay_config']);
            $pay_config['is_kj'] = true; // 跨境订单标识
            $pay_online = $pay_obj->get_code($order, $pay_config);
        }else{
            include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
            $pay_obj    = new $payment['pay_code'];
            $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
        }
        $order['pay_desc'] = $payment['pay_desc'];
        $smarty->assign('pay_online', $pay_online);
    }
    if(!empty($order['shipping_name']))
    {
        $order['shipping_name']=trim(stripcslashes($order['shipping_name']));
    }
    
    /* 订单信息 */
    $smarty->assign('order',      $order);
    $smarty->assign('total',      $total);
    $smarty->assign('goods_list', $cart_goods);
    $smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center'])); // 返回提示

    user_uc_call('add_feed', array($order['order_id'], BUY_GOODS)); //推送feed到uc
    unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
    unset($_SESSION['flow_order']);
    unset($_SESSION['direct_shopping']);
}

/*------------------------------------------------------ */
//-- 更新购物车
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'update_cart')
{
    if (isset($_POST['goods_number']) && is_array($_POST['goods_number']))
    {
        flow_update_cart($_POST['goods_number']);
         /*跨境城购物车改造 获取选中的商品 更新购物车*/
        $goodsSelect=$_POST['goodsSelect'];
        goods_select_update_cart($goodsSelect);
    }

    show_message($_LANG['update_cart_notice'], $_LANG['back_to_cart'], 'flow.php');
    exit;
}

/*------------------------------------------------------ */
//-- 删除购物车中的商品
/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'drop_goods')
{
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods($rec_id);

    ecs_header("Location: flow.php\n");
    exit;
}

/* 把优惠活动加入购物车 */
elseif ($_REQUEST['step'] == 'add_favourable')
{
    /* 取得优惠活动信息 */
    $act_id = intval($_POST['act_id']);
    $favourable = favourable_info($act_id);
    if (empty($favourable))
    {
        show_message($_LANG['favourable_not_exist']);
    }

    /* 判断用户能否享受该优惠 */
    if (!favourable_available($favourable))
    {
        show_message($_LANG['favourable_not_available']);
    }

    /* 检查购物车中是否已有该优惠 */
    $cart_favourable = cart_favourable();
    if (favourable_used($favourable, $cart_favourable))
    {
        show_message($_LANG['favourable_used']);
    }

    /* 赠品（特惠品）优惠 */
    if ($favourable['act_type'] == FAT_GOODS)
    {
        /* 检查是否选择了赠品 */
        if (empty($_POST['gift']))
        {
            show_message($_LANG['pls_select_gift']);
        }

        /* 检查是否已在购物车 */
        $sql = "SELECT goods_name" .
                " FROM " . $ecs->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
                " AND is_gift = '$act_id'" .
                " AND goods_id " . db_create_in($_POST['gift']);
        $gift_name = $db->getCol($sql);
        if (!empty($gift_name))
        {
            show_message(sprintf($_LANG['gift_in_cart'], join(',', $gift_name)));
        }

        /* 检查数量是否超过上限 */
        $count = isset($cart_favourable[$act_id]) ? $cart_favourable[$act_id] : 0;
        if ($favourable['act_type_ext'] > 0 && $count + count($_POST['gift']) > $favourable['act_type_ext'])
        {
            show_message($_LANG['gift_count_exceed']);
        }

        /* 添加赠品到购物车 */
        foreach ($favourable['gift'] as $gift)
        {
            if (in_array($gift['id'], $_POST['gift']))
            {
                add_gift_to_cart($act_id, $gift['id'], $gift['price']);
            }
        }
    }
    elseif ($favourable['act_type'] == FAT_DISCOUNT)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);
    }
    elseif ($favourable['act_type'] == FAT_PRICE)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], $favourable['act_type_ext']);
    }

    /* 刷新购物车 */
    ecs_header("Location: flow.php\n");
    exit;
}
elseif ($_REQUEST['step'] == 'clear')
{
    $sql = "DELETE FROM " . $ecs->table('cart') . " WHERE session_id='" . SESS_ID . "'";
    $db->query($sql);

    ecs_header("Location:./\n");
}
elseif ($_REQUEST['step'] == 'drop_to_collect')
{
    if ($_SESSION['user_id'] > 0)
    {
        $rec_id = intval($_GET['id']);
        $goods_id = $db->getOne("SELECT  goods_id FROM " .$ecs->table('cart'). " WHERE rec_id = '$rec_id' AND session_id = '" . SESS_ID . "' ");
        $count = $db->getOne("SELECT goods_id FROM " . $ecs->table('collect_goods') . " WHERE user_id = '$_SESSION[user_id]' AND goods_id = '$goods_id'");
        if (empty($count))
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            $db->query($sql);
        }
        flow_drop_cart_goods($rec_id);
    }
    ecs_header("Location: flow.php\n");
    exit;
}

/* 验证红包序列号 */
elseif ($_REQUEST['step'] == 'validate_bonus')
{
    $bonus_sn = trim($_REQUEST['bonus_sn']);
    if (is_numeric($bonus_sn))
    {
        $bonus = bonus_info(0, $bonus_sn);
    }
    else
    {
        $bonus = array();
    }

//    if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0)
//    {
//        die($_LANG['bonus_sn_error']);
//    }
//    if ($bonus['min_goods_amount'] > cart_amount())
//    {
//        die(sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false)));
//    }
//    die(sprintf($_LANG['bonus_is_ok'], price_format($bonus['type_money'], false)));
    $bonus_kill = price_format($bonus['type_money'], false);

    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    //$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $cart_goods = cart_goods_selected($flow_type); //购物车改造

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();


        if (((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
        {
            //$order['bonus_kill'] = $bonus['type_money'];
            $now = gmtime();
            if ($now > $bonus['use_end_date'])
            {
                $order['bonus_id'] = '';
                $result['error']=$_LANG['bonus_use_expire'];
            }
            else
            {
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;
            }
        }
        else
        {
            //$order['bonus_kill'] = 0;
            $order['bonus_id'] = '';
            $result['error'] = $_LANG['invalid_bonus'];
        }

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);

        if($total['goods_price']<$bonus['min_goods_amount'])
        {
         $order['bonus_id'] = '';
         /* 重新计算订单 */
         $total = order_fee($order, $cart_goods, $consignee,$kj_goods_amount,$kj_goods_tax);
         $result['error'] = sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false));
        }

        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }
    $json = new JSON();
    die($json->encode($result));
}
/*------------------------------------------------------ */
//-- 添加礼包到购物车
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'add_package_to_cart')
{
    include_once('includes/cls_json.php');
    $_POST['package_info'] = json_str_iconv($_POST['package_info']);

    $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');
    $json  = new JSON;

    if (empty($_POST['package_info']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $package = $json->decode($_POST['package_info']);

    /* 如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1')
    {
        clear_cart();
    }

    /* 商品数量是否合法 */
    if (!is_numeric($package->number) || intval($package->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['invalid_number'];
    }
    else
    {
        /* 添加到购物车 */
        if (add_package_to_cart($package->package_id, $package->number))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
            }

            $result['content'] = insert_cart_info();
            $result['one_step_buy'] = $_CFG['one_step_buy'];
        }
        else
        {
            $result['message']    = $err->last_message();
            $result['error']      = $err->error_no;
            $result['package_id'] = stripslashes($package->package_id);
        }
    }
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    die($json->encode($result));
}
/**
 * 支付订单
 */
else if($_REQUEST['step'] == 'pay_order'){//支付改造 增加
    $order_id=$_REQUEST['order_id'];
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') ." WHERE order_id = '$order_id'";
    $order=$GLOBALS['db']->getRow($sql);
    require_once(ROOT_PATH . 'includes/lib_order.php');
    $payment = payment_info($order['pay_id']);
    $log_id=$_REQUEST['logId'];
    if (empty($log_id)){
        require_once(ROOT_PATH . 'includes/lib_clips.php');
        $log_id= insert_pay_log($order['order_id'], $order['order_amount'], PAY_ORDER);
    }
    $order['log_id']=$log_id;
    
    include_once('includes/lib_payment.php');
    if($order['order_type']==1){ // 跨境订单
        //判断订单是否已经同步
        /* 原本号百同步需要，现在直接对接海关应该在支付成功获取支付单号之后再同步订单
        if($order['report_status']==0){
            $goods_list=order_goods($order['order_id']);
            //发送订单到接口,再次同步订单
            $orderData=get_report_order($order,$goods_list);
            if($orderData==null){
                show_message('<br/>服务器连接异常，订单支付失败，请稍后再试。', '', '', 'warning');
            }
            if($orderData!=null){
                $submit_suc=kj_order_submit($orderData,$order);
            }
            if($submit_suc[0]==0){
                show_message('<br/>订单支付失败，'.$submit_suc[1].'请稍后再试。', '', '', 'warning');
            }
        }
        */
        /* 号百的支付方式，已停用
        include_once('includes/lib_time.php');
        include_once('includes/cls_json.php');
        include_once(ROOT_PATH . 'languages/zh_cn/common.php');
        include_once('includes/modules/payment/kjgpay.php');
        $pay_obj = new kjgpay();
        //$url=$pay_obj->get_code($order);//get方式提交
        //header("Location:".$url);
        //post 请求提交
        $postData=$pay_obj->getPostData($order);
        echo "<form style='display:none;' id='form1' name='form1' method='post' action='{$GLOBALS['_LANG']['kj_payurl']}'>
                <input name='payData' type='text' value='{$postData["payData"]}' />
                <input name='method' type='text' value='{$postData["method"]}'/>
                <input name='version' type='text' value='{$postData["version"]}'/>
                <input name='appId' type='text' value='{$postData["appId"]}'/>
                <input name='timestamp' type='text' value='{$postData["timestamp"]}'/>
                <input name='nonce' type='text' value='{$postData["nonce"]}'/>
                <input name='sign' type='text' value='{$postData["sign"]}'/>
               </form>
               <script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
        */

        include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
        $pay_obj  = new $payment['pay_code'];
        $pay_config = unserialize_config($payment['pay_config']);
        $pay_config['is_kj'] = true; // 跨境订单标识
        $url = $pay_obj->get_code_url($order, $pay_config);
        ecs_header("Location:".$url);
    }else{
        include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
        $pay_obj  = new $payment['pay_code'];
        $url = $pay_obj->get_code_url($order, unserialize_config($payment['pay_config']));
        ecs_header("Location:".$url);
    }
    exit;
}
else
{
    /* 标记购物流程为普通商品 */
    $_SESSION['flow_type'] = CART_GENERAL_GOODS;

    /* 如果是一步购物，跳到结算中心 */
    if ($_CFG['one_step_buy'] == '1')
    {
        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }

    /* 取得商品列表，计算合计 */
    $cart_goods = get_cart_goods();
    
    //购物车改造  购物车中商品分离
    require_once(ROOT_PATH . 'includes/lib_goods.php');
    $goods_list=$cart_goods['goods_list'];
    $kj_goods_list=array();
    $com_goods_list=array();
    $all_selected=1;
    foreach($goods_list as $goods){
        $cat_id = $GLOBALS['db']->getOne("SELECT `cat_id` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`='{$goods['goods_id']}'");
        if(is_kj_product($cat_id)==1){
            $kj_goods_list[]=$goods;
        }else{
            $com_goods_list[]=$goods;
        }
        if($goods['is_selected']==0){
            $all_selected=0;
        }
    }
    //$smarty->assign('goods_list', $cart_goods['goods_list']);
    $smarty->assign('goods_list', $com_goods_list);
    $smarty->assign('kj_goods_list', $kj_goods_list);
    $smarty->assign('is_all_checked', $all_selected);
    $smarty->assign('currencyFormat',sprintf($GLOBALS['_CFG']['currency_format'],''));
    $smarty->assign('shopping_money_lang',sprintf($_LANG['shopping_money'],''));
    //end
    
    $smarty->assign('total', $cart_goods['total']);

    //购物车的描述的格式化
    $smarty->assign('shopping_money',         sprintf($_LANG['shopping_money'], $cart_goods['total']['goods_price']));
    $smarty->assign('market_price_desc',      sprintf($_LANG['than_market_price'],
        $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));

    // 显示收藏夹内的商品
    if ($_SESSION['user_id'] > 0)
    {
        require_once(ROOT_PATH . 'includes/lib_clips.php');
        $collection_goods = get_collection_goods($_SESSION['user_id']);
        $smarty->assign('collection_goods', $collection_goods);
    }

    /* 取得优惠活动 */
    $favourable_list = favourable_list($_SESSION['user_rank']);
    usort($favourable_list, 'cmp_favourable');

    $smarty->assign('favourable_list', $favourable_list);

    /* 计算折扣 */
    $discount = compute_discount();
    $smarty->assign('discount', $discount['discount']);
    $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
    $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));

    /* 增加是否在购物车里显示商品图 */
    $smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);

    /* 增加是否在购物车里显示商品属性 */
    $smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);

    /* 购物车中商品配件列表 */
    //取得购物车中基本件ID
    $sql = "SELECT goods_id " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "' " .
            "AND is_gift = 0 " .
            "AND extension_code <> 'package_buy' " .
            "AND parent_id = 0 ";
    $parent_list = $GLOBALS['db']->getCol($sql);

    $fittings_list = get_goods_fittings($parent_list);

    $smarty->assign('fittings_list', $fittings_list);
}

$smarty->assign('currency_format', $_CFG['currency_format']);
$smarty->assign('integral_scale',  $_CFG['integral_scale']);
$smarty->assign('step',            $_REQUEST['step']);
assign_dynamic('shopping_flow');

$smarty->display('flow.dwt');

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

    $val = floatval($GLOBALS['db']->getOne($sql));
	$new_val = round($val/100,2);
    return integral_of_value($new_val);
}

/**
 * 更新购物车选中的商品的字段为1
 * 跨境城购物车改造
 */
function goods_select_update_cart($arr){
    //更新购物车中所有商品为非选中
    $sql = "UPDATE " .$GLOBALS['ecs']->table('cart')." SET is_selected=0  WHERE session_id='" . SESS_ID . "'";
    $GLOBALS['db']->query($sql);
     /* 处理选中的商品更新成1 */
    foreach ($arr AS $key => $val){
        $sql = "UPDATE " .$GLOBALS['ecs']->table('cart')." SET is_selected =1 WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $GLOBALS['db']->query($sql);
    }
}

/**
 * 更新购物车中的商品数量
 *
 * @access  public
 * @param   array   $arr
 * @return  void
 */
function flow_update_cart($arr)
{
    /* 处理 */
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }

        //查询：
        $sql = "SELECT `goods_id`, `goods_attr_id`, `product_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $GLOBALS['db']->getRow($sql);

        //查询：系统启用了库存，检查输入的商品数量是否有效
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }
            /* 是货品 */
            $goods['product_id'] = trim($goods['product_id']);
            if (!empty($goods['product_id']))
            {
                $sql = "SELECT product_number FROM " .$GLOBALS['ecs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $goods['product_id'] . "'";

                $product_number = $GLOBALS['db']->getOne($sql);
                if ($product_number < $val)
                {
                    show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                    $product_number['product_number'], $product_number['product_number']));
                    exit;
                }
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }

        /* 查询：检查该项是否为基本件 以及是否存在配件 */
        /* 此处配件是指添加商品时附加的并且是设置了优惠价格的配件 此类配件都有parent_id goods_number为1 */
        $sql = "SELECT b.goods_number, b.rec_id
                FROM " .$GLOBALS['ecs']->table('cart') . " a, " .$GLOBALS['ecs']->table('cart') . " b
                WHERE a.rec_id = '$key'
                AND a.session_id = '" . SESS_ID . "'
                AND a.extension_code <> 'package_buy'
                AND b.parent_id = a.goods_id
                AND b.session_id = '" . SESS_ID . "'";

        $offers_accessories_res = $GLOBALS['db']->query($sql);

        //订货数量大于0
        if ($val > 0)
        {
            /* 判断是否为超出数量的优惠价格的配件 删除*/
            $row_num = 1;
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                if ($row_num > $val)
                {
                    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                            " WHERE session_id = '" . SESS_ID . "' " .
                            "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                    $GLOBALS['db']->query($sql);
                }

                $row_num ++;
            }

            /* 处理超值礼包 */
            if ($goods['extension_code'] == 'package_buy')
            {
                //更新购物车中的商品数量
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
            /* 处理普通商品或非优惠的配件 */
            else
            {
                $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
                $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);

                //更新购物车中的商品数量
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
        }
        //订货数量等于0
        else
        {
            /* 如果是基本件并且有优惠价格的配件则删除优惠价格的配件 */
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                        " WHERE session_id = '" . SESS_ID . "' " .
                        "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";
        }

        $GLOBALS['db']->query($sql);
    }

    /* 删除所有赠品 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);
}

/**
 * 检查订单中商品库存
 *
 * @access  public
 * @param   array   $arr
 *
 * @return  void
 */
function flow_cart_stock($arr)
{
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }

        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number, c.product_id ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $GLOBALS['db']->getRow($sql);

        //系统启用了库存，检查输入的商品数量是否有效
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }

            /* 是货品 */
            $row['product_id'] = trim($row['product_id']);
            if (!empty($row['product_id']))
            {
                $sql = "SELECT product_number FROM " .$GLOBALS['ecs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";
                $product_number = $GLOBALS['db']->getOne($sql);
                if ($product_number < $val)
                {
                    show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                    $row['goods_number'], $row['goods_number']));
                    exit;
                }
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }
    }

}

/**
 * 删除购物车中的商品
 *
 * @access  public
 * @param   integer $id
 * @return  void
 */
function flow_drop_cart_goods($id)
{
    /* 取得商品id */
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('cart'). " WHERE rec_id = '$id'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row)
    {
        //如果是超值礼包
        if ($row['extension_code'] == 'package_buy')
        {
            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND rec_id = '$id' LIMIT 1";
        }

        //如果是普通商品，同时删除所有赠品及其配件
        elseif ($row['parent_id'] == 0 && $row['is_gift'] == 0)
        {
            /* 检查购物车中该普通商品的不可单独销售的配件并删除 */
            $sql = "SELECT c.rec_id
                    FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('group_goods') . " AS gg, " . $GLOBALS['ecs']->table('goods'). " AS g
                    WHERE gg.parent_id = '" . $row['goods_id'] . "'
                    AND c.goods_id = gg.goods_id
                    AND c.parent_id = '" . $row['goods_id'] . "'
                    AND c.extension_code <> 'package_buy'
                    AND gg.goods_id = g.goods_id
                    AND g.is_alone_sale = 0";
            $res = $GLOBALS['db']->query($sql);
            $_del_str = $id . ',';
            while ($id_alone_sale_goods = $GLOBALS['db']->fetchRow($res))
            {
                $_del_str .= $id_alone_sale_goods['rec_id'] . ',';
            }
            $_del_str = trim($_del_str, ',');

            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND (rec_id IN ($_del_str) OR parent_id = '$row[goods_id]' OR is_gift <> 0)";
        }

        //如果不是普通商品，只删除该商品即可
        else
        {
            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND rec_id = '$id' LIMIT 1";
        }

        $GLOBALS['db']->query($sql);
    }

    flow_clear_cart_alone();
}

/**
 * 删除购物车中不能单独销售的商品
 *
 * @access  public
 * @return  void
 */
function flow_clear_cart_alone()
{
    /* 查询：购物车中所有不可以单独销售的配件 */
    $sql = "SELECT c.rec_id, gg.parent_id
            FROM " . $GLOBALS['ecs']->table('cart') . " AS c
                LEFT JOIN " . $GLOBALS['ecs']->table('group_goods') . " AS gg ON c.goods_id = gg.goods_id
                LEFT JOIN" . $GLOBALS['ecs']->table('goods') . " AS g ON c.goods_id = g.goods_id
            WHERE c.session_id = '" . SESS_ID . "'
            AND c.extension_code <> 'package_buy'
            AND gg.parent_id > 0
            AND g.is_alone_sale = 0";
    $res = $GLOBALS['db']->query($sql);
    $rec_id = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $rec_id[$row['rec_id']][] = $row['parent_id'];
    }

    if (empty($rec_id))
    {
        return;
    }

    /* 查询：购物车中所有商品 */
    $sql = "SELECT DISTINCT goods_id
            FROM " . $GLOBALS['ecs']->table('cart') . "
            WHERE session_id = '" . SESS_ID . "'
            AND extension_code <> 'package_buy'";
    $res = $GLOBALS['db']->query($sql);
    $cart_good = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $cart_good[] = $row['goods_id'];
    }

    if (empty($cart_good))
    {
        return;
    }

    /* 如果购物车中不可以单独销售配件的基本件不存在则删除该配件 */
    $del_rec_id = '';
    foreach ($rec_id as $key => $value)
    {
        foreach ($value as $v)
        {
            if (in_array($v, $cart_good))
            {
                continue 2;
            }
        }

        $del_rec_id = $key . ',';
    }
    $del_rec_id = trim($del_rec_id, ',');

    if ($del_rec_id == '')
    {
        return;
    }

    /* 删除 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') ."
            WHERE session_id = '" . SESS_ID . "'
            AND rec_id IN ($del_rec_id)";
    $GLOBALS['db']->query($sql);
}

/**
 * 比较优惠活动的函数，用于排序（把可用的排在前面）
 * @param   array   $a      优惠活动a
 * @param   array   $b      优惠活动b
 * @return  int     相等返回0，小于返回-1，大于返回1
 */
function cmp_favourable($a, $b)
{
    if ($a['available'] == $b['available'])
    {
        if ($a['sort_order'] == $b['sort_order'])
        {
            return 0;
        }
        else
        {
            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
        }
    }
    else
    {
        return $a['available'] ? -1 : 1;
    }
}

/**
 * 取得某用户等级当前时间可以享受的优惠活动
 * @param   int     $user_rank      用户等级id，0表示非会员
 * @return  array
 */
function favourable_list($user_rank)
{
    /* 购物车中已有的优惠活动及数量 */
    $used_list = cart_favourable();

    /* 当前用户可享受的优惠活动 */
    $favourable_list = array();
    $user_rank = ',' . $user_rank . ',';
    $now = gmtime();
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND start_time <= '$now' AND end_time >= '$now'" .
            " AND act_type = '" . FAT_GOODS . "'" .
            " ORDER BY sort_order";
    $res = $GLOBALS['db']->query($sql);
    while ($favourable = $GLOBALS['db']->fetchRow($res))
    {
        $favourable['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['start_time']);
        $favourable['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $favourable['end_time']);
        $favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);
        $favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);
        $favourable['gift']       = unserialize($favourable['gift']);

        foreach ($favourable['gift'] as $key => $value)
        {
            $favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods') . " WHERE is_on_sale = 1 AND goods_id = ".$value['id'];
            $is_sale = $GLOBALS['db']->getOne($sql);
            if(!$is_sale)
            {
                unset($favourable['gift'][$key]);
            }
        }

        $favourable['act_range_desc'] = act_range_desc($favourable);
        $favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);

        /* 是否能享受 */
        $favourable['available'] = favourable_available($favourable);
        if ($favourable['available'])
        {
            /* 是否尚未享受 */
            $favourable['available'] = !favourable_used($favourable, $used_list);
        }

        $favourable_list[] = $favourable;
    }

    return $favourable_list;
}

/**
 * 根据购物车判断是否可以享受某优惠活动
 * @param   array   $favourable     优惠活动信息
 * @return  bool
 */
function favourable_available($favourable)
{
    /* 会员等级是否符合 */
    $user_rank = $_SESSION['user_rank'];
    if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false)
    {
        return false;
    }

    /* 优惠范围内的商品总额 */
    $amount = cart_favourable_amount($favourable);

    /* 金额上限为0表示没有上限 */
    return $amount >= $favourable['min_amount'] &&
        ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
}

/**
 * 取得优惠范围描述
 * @param   array   $favourable     优惠活动
 * @return  string
 */
function act_range_desc($favourable)
{
    if ($favourable['act_range'] == FAR_BRAND)
    {
        $sql = "SELECT brand_name FROM " . $GLOBALS['ecs']->table('brand') .
                " WHERE brand_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        $sql = "SELECT cat_name FROM " . $GLOBALS['ecs']->table('category') .
                " WHERE cat_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_GOODS)
    {
        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE goods_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    else
    {
        return '';
    }
}

/**
 * 取得购物车中已有的优惠活动及数量
 * @return  array
 */
function cart_favourable()
{
    $list = array();
    $sql = "SELECT is_gift, COUNT(*) AS num " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'" .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
            " AND is_gift > 0" .
            " GROUP BY is_gift";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $list[$row['is_gift']] = $row['num'];
    }

    return $list;
}

/**
 * 购物车中是否已经有某优惠
 * @param   array   $favourable     优惠活动
 * @param   array   $cart_favourable购物车中已有的优惠活动及数量
 */
function favourable_used($favourable, $cart_favourable)
{
    if ($favourable['act_type'] == FAT_GOODS)
    {
        return isset($cart_favourable[$favourable['act_id']]) &&
            $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] &&
            $favourable['act_type_ext'] > 0;
    }
    else
    {
        return isset($cart_favourable[$favourable['act_id']]);
    }
}

/**
 * 添加优惠活动（赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   int     $id         赠品id
 * @param   float   $price      赠品价格
 */
function add_gift_to_cart($act_id, $id, $price)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . " (" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "SELECT '$_SESSION[user_id]', '" . SESS_ID . "', goods_id, goods_sn, goods_name, market_price, ".
                "'$price', 1, is_real, extension_code, 0, '$act_id', '" . CART_GENERAL_GOODS . "' " .
            "FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE goods_id = '$id'";
    $GLOBALS['db']->query($sql);
}

/**
 * 添加优惠活动（非赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   string  $act_name   优惠活动name
 * @param   float   $amount     优惠金额
 */
function add_favourable_to_cart($act_id, $act_name, $amount)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "VALUES('$_SESSION[user_id]', '" . SESS_ID . "', 0, '', '$act_name', 0, ".
                "'" . (-1) * $amount . "', 1, 0, '', 0, '$act_id', '" . CART_GENERAL_GOODS . "')";
    $GLOBALS['db']->query($sql);
}

/**
 * 取得购物车中某优惠活动范围内的总金额
 * @param   array   $favourable     优惠活动
 * @return  float
 */
function cart_favourable_amount($favourable)
{
    /* 查询优惠范围内商品总额的sql */
    $sql = "SELECT SUM(c.goods_price * c.goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id > 0 ";

    /* 根据优惠范围修正sql */
    if ($favourable['act_range'] == FAR_ALL)
    {
        // sql do not change
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        /* 取得优惠范围分类的所有下级分类 */
        $id_list = array();
        $cat_list = explode(',', $favourable['act_range_ext']);
        foreach ($cat_list as $id)
        {
            $id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0, false)));
        }

        $sql .= "AND g.cat_id " . db_create_in($id_list);
    }
    elseif ($favourable['act_range'] == FAR_BRAND)
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.brand_id " . db_create_in($id_list);
    }
    else
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.goods_id " . db_create_in($id_list);
    }

    /* 优惠范围内的商品总额 */
    return $GLOBALS['db']->getOne($sql);
}

/* 旧版申报系统
function kj_check($cart_goods,$account){
	$url = $GLOBALS['_LANG']['kj_url'];
	//电商平台帐号
	$userid = $GLOBALS['_LANG']['kj_userid'];
	//时间戳
	$current_time = date("Y-m-d H:i:s");
	$timestamp = urlencode($current_time);
	
	$pwd = $GLOBALS['_LANG']['kj_pwd'];
	//加密签名
	$sign = md5($userid . $pwd . $current_time);
	
	//获取消费者是否已备案并绑定帐号
	include_once('QueryRequest.php');
	$cosumer_is_filed = GetConsumer($account)->Header->Result;
	
	$goods_str = "";
	$kj_goods_amount = 0;
	$kj_goods_weight = 0.0;
	$kj_goods_number = 0;
	$kj_goods_tax = 0.0;
	$kj_goods_counter = 0;
	
	foreach ($cart_goods as $good){
		$product_id = $good["kj_sn"];
		//获取商品备案信息
		$product_xml = GetGoods($product_id);
		$product_info =array('goods_name' => $product_xml->Body->GoodsName, 'unit'=> $product_xml->Body->Unit, 'tax' => $product_xml->Body->Tax, 'status' => $product_xml->Body->Status);
		if ($product_info['status'] == "2"){
			if ($cosumer_is_filed == "F"){
				//消费者备案
				$beian =  "http://trainer.kjb2c.com/reg/reg!eplatformEdit.do?userid=" . $userid . "&amp;". "timestamp=" . $timestamp . "&sign=" . $sign . "&rurl=" . $url . "&purl=" . $url . "&account=" . $account;
				return array(0, $beian);
			}
			else{
				$good_amount = (float)$good["goods_number"] * (float)$good["goods_price"];
				$goods_str .= "<Detail><ProductId>" . $product_id . "</ProductId><GoodsName>" . $product_info['goods_name'] . "</GoodsName><Qty>" . $good["goods_number"] . "</Qty><Unit>" . $product_info['unit'] . "</Unit><Price>" . $good["goods_price"] . "</Price><Amount>" . $good_amount . "</Amount></Detail>";
				
				$sql = "SELECT goods_weight FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
				$good_weight = $GLOBALS['db']->getOne($sql) * $good['goods_number'];
				
				$kj_goods_amount += $good_amount;
				$kj_goods_weight += $good_weight;
				$kj_goods_number += (int)$good["goods_number"];
				$kj_goods_tax += (float)$product_info['tax'] * $good_amount;
				$kj_goods_counter += 1;
			
			$fp = fopen("ordersubmit.txt","a");
			flock($fp, LOCK_EX) ;
			fwrite($fp,$product_info['goods_name'] ."的重量：".$good_weight."\n");
			flock($fp, LOCK_UN);
			fclose($fp);
			
			}
		}
	}
	
	if (strlen($goods_str) > 0){
		return array(1, $goods_str, $kj_goods_amount, $kj_goods_weight, $kj_goods_number, $kj_goods_tax, $kj_goods_counter);
	}
	else{
		return array(2, "");
	}
}

function kj_order_submit($order, $goods_str,$kj_goods_weight, $kj_goods_amount, $kj_shipping_fee,$kj_goods_tax){
	//提交进口订单
	
	include_once('includes/lib_time.php');
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
	$order_from = "0000";
	//消费者
	$account = $_SESSION['user_name'];
	
	$kj_order_amount = $kj_goods_amount + $kj_shipping_fee + $kj_goods_tax;
	
	//提交进口订单
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://i.trainer.kjb2c.com/msg/ordermsg.do");
	curl_setopt($ch, CURLOPT_POST, 1);
	
	$order_xmlstr="<?xml version='1.0' encoding='UTF-8'?><Message><Header><CustomsCode>" . $customs_code . "</CustomsCode><OrgName>" . $org_name . "</OrgName><CreateTime>" . local_date($GLOBALS['_CFG']['time_format'], $order['add_time']) . "</CreateTime></Header><Body><Order><OrderFrom>" . $order_from . "</OrderFrom><OrderNo>" . $order["order_sn"] . "</OrderNo><GoodsAmount>" . $kj_goods_amount . "</GoodsAmount><PostFee>" . $kj_shipping_fee . "</PostFee><Amount>" . $kj_order_amount . "</Amount><BuyerAccount>" . $account . "</BuyerAccount><TaxAmount>" . $kj_goods_tax . "</TaxAmount>" . $goods_str . "</Order></Body></Message>";
	
	$fp = fopen("ordersubmit.txt","a");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"字串：".$order_xmlstr."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
	
	$post_fields = array("userid" => $userid, "timestamp" => $current_time, "sign" => $sign, "xmlstr" => $order_xmlstr);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close($ch); 	
	$xml = simplexml_load_string($server_output);
	
	
	$fp = fopen("ordersubmit.txt","a");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"结果：".$server_output."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
		
	
	return $kj_order_amount;
}
旧版申报系统末尾 */

/* 号百接口，已停止合作
function kj_check($cart_goods,$account){
	$merchantCode = $GLOBALS['_LANG']['merchantCode'];
        $merchantName = $GLOBALS['_LANG']['merchantName'];
        include_once('report/orderReportNew.php');
	
	$kj_goods_amount = 0;
	$kj_goods_weight = 0.0;
	$kj_goods_number = 0;
	$kj_goods_tax = 0.0;
	$kj_goods_counter = 0;
        $goods_str="";
	foreach ($cart_goods as $good){
		$product_id = $good["kj_sn"];
		//获取商品备案信息
		//$product_rt=queryCommodityRecord($merchantCode,$merchantName,$product_id);
		$product_rt=queryCommodityRecord($merchantCode,$merchantName,$good['goods_sn']);
                if($product_rt==null){//请求异常
                   return array(0,"");
                }
                if ($product_rt['code'] == "200"){
                        $product_info=$product_rt['result'];
			$good_amount = (float)$good["goods_number"] * (float)$good["goods_price"];
			$sql = "SELECT goods_weight FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
			$good_weight = $GLOBALS['db']->getOne($sql) * $good['goods_number'];
			$kj_goods_amount += $good_amount;
			$kj_goods_weight += $good_weight;
			$kj_goods_number += (int)$good["goods_number"];
			$kj_goods_tax += (float)$product_info['consolidatedTaxAmount'] * $good_amount;
			$kj_goods_counter += 1;
		}else{
                    return array(0,$cart_goods['goods_name'].','.$product_rt['desc']);
                }
	} 
	if ($kj_goods_counter > 0){
		return array(1, $goods_str, $kj_goods_amount, $kj_goods_weight, $kj_goods_number, $kj_goods_tax, $kj_goods_counter);
	}
	else{
		return array(2, "");
	}
}

function kj_order_submit($orderData,$order){
	//提交进口订单
        include_once('report/orderReportNew.php');
        include_once('includes/cls_json.php');
	$json=new Json();
        $orderDataJson=$json->encode($orderData);
        
	logResult("submit order data:".$orderDataJson);//记录发送数据日志
	
        $result=createGlobalOrder($orderDataJson);//发送订单到海关接口

        //发送成功修改订单同步状态
        if($result['code']=='200' || $result['code']=='209'){//209表示订单同步重复
             update_order($order['order_id'], array('report_status' => 1,'kj_order_amount' => $orderData['orderAmount']));
             return array(1,"");
        }
        return array(0,$result['desc']);
}
号百接口末尾 */

// 申报系统新代码
function kj_check($cart_goods,$account){
    include_once('report/orderReportHaiGuan.php');

    $result = hg_GetAccount($account);
    if ($result->Header->Result == 'F') {
        $beian = '消费者需要在<a href="http://www.kjb2c.com/reg/reg!regedit.html" target="_blank" style="padding-left:0;">宁波跨境贸易电子商务服务平台——跨境购</a>进行实名注册，填写真实身份信息并关联本网站用户名，才能购买本网站跨境购商品。（如已在跨境购平台注册过，只需在跨境购平台上关联本网站用户名即可。）';
        return array(0, $beian);
    } else if ($result->Body->IsAuth != 1) {
        return array(0, "您在<a href='http://www.kjb2c.com/reg/reg!regedit.html' target='_blank' style='padding-left:0;'>宁波跨境贸易电子商务服务平台——跨境购</a>所注册的账号还未通过认证，请先通过认证。");
    }

    $kj_goods_amount = 0;
    $kj_goods_weight = 0.0;
    $kj_goods_number = 0;
    $kj_goods_tax = 0.0;
    $kj_goods_counter = 0;
    $goods_str="";
    foreach ($cart_goods as $good){
        //获取商品备案信息
        $product_rt=hg_GetGoods($good['kj_sn']);
        if($product_rt==null){//请求异常
            return array(0,"");
        }
        if ($product_rt->Header->Result == "T"){
            $good_amount = (float)$good["goods_number"] * (float)$good["goods_price"];
            $sql = "SELECT goods_weight FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
            $good_weight = $GLOBALS['db']->getOne($sql) * $good['goods_number'];
            $kj_goods_amount += $good_amount;
            $kj_goods_weight += $good_weight;
            $kj_goods_number += (int)$good["goods_number"];
            $kj_goods_counter += 1;

            /**
             * 税的计算参考good.php，搜索综合税
             */
            $tax_A = (float)($product_rt->Body->AddedValueTax); // 增值税率
            $tax_C = (float)($product_rt->Body->ConsumptionDuty); // 消费税率
            $total_tax = ($tax_C * (1+$tax_A) / (1-$tax_C) + $tax_A) * 0.7; // 综合税率
            $total_tax = round($total_tax, 2);
            $kj_goods_tax += $total_tax * $good_amount; // 综合税
        }else{
            return array(0,$good['goods_name'].', '.$product_rt->Header->ResultMsg);
        }
    }
    if ($kj_goods_counter > 0){
        return array(1, $goods_str, $kj_goods_amount, $kj_goods_weight, $kj_goods_number, $kj_goods_tax, $kj_goods_counter);
    }
    else{
        return array(2, "");
    }
}

// 订单提交不在这里做，改为在respond.php中等待支付信息成功返回后再进行
// 因此下面不需要该函数，且该函数也未改完
/*
function kj_order_submit($orderData,$order){
    //提交进口订单
    include_once('report/orderReportHaiGuan.php');
    include_once('includes/cls_json.php');
    $json=new Json();
    $orderDataJson=$json->encode($orderData);

    logResult("submit order data:".$orderDataJson);//记录发送数据日志

    $result=createGlobalOrder($orderDataJson);//发送订单到海关接口

    //发送成功修改订单同步状态
    if($result['code']=='200' || $result['code']=='209'){//209表示订单同步重复
        update_order($order['order_id'], array('report_status' => 1,'kj_order_amount' => $orderData['orderAmount']));
        return array(1,"");
    }
    return array(0,$result['desc']);
}*/

// 订单提交不在这里做，改为在respond.php中等待支付信息成功返回后再进行
// 因此下面不需要该函数，且该函数也未改完
/**
 *  生成申报订单数据
 * @param type $order 订单头信息
 * @param type $goods_list 订单明细信息
 */
/*
function get_report_order($order,$goods_list){
        include_once('report/orderReportHaiGuan.php');
        $sql = "SELECT user_name, real_name, real_id, real_phone, real_email FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = " . $order['user_id'];
	    $account = $GLOBALS['db']->getRow($sql);
        //print_r("帐号：" .$account);
        $sql = "SELECT shipping_code FROM " . $GLOBALS['ecs']->table('shipping') . " WHERE shipping_id = " . $order['shipping_id'];
	    $shipping_code = $GLOBALS['db']->getOne($sql);
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
                //$sql = "SELECT kj_sn FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $good['goods_id'];
		        //$product_id = $GLOBALS['db']->getOne($sql);
        		//获取商品备案信息
        		$product_rt=hg_GetGoods($good['goods_sn']);
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
                        "goodsSku" => $good['goods_sn'],
                        "goodsName" => $good['goods_name'],
                        "quantity" => $good["goods_number"],
                        "unit"     => $product_info->Unit,
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
        $kj_shipping_fee=$order['shipping_fee'];
        
        if($shipping_code=='sf_express') $shipping_code='shunfeng';
        $orderData=array(
            "Operation" => 0, // 0=新建，1=更新
            "orderNum" => $order['order_sn'],
            "orderSource" => $GLOBALS['_LANG']['orderSource'],
            //"cityCode" => 'ningbo',
            "checkStore" => '0',// 库存校验
            "buyerAccount" => $account['user_name'],
            //物流信息
            "logisticsName" =>  $shipping_code,
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
        //var_dump($orderData);
        return $orderData;
}
*/

function get_kj_shipping_fee($order,$kj_goods_weight,$kj_goods_amount,$kj_goods_number){
        $region['country']  = $order['country'];
        $region['province'] = $order['province'];
        $region['city']     = $order['city'];
        $region['district'] = $order['district'];
        $shipping_info = shipping_area_info($order['shipping_id'], $region);
        $kj_shipping_fee = shipping_fee($shipping_info['shipping_code'], $shipping_info['configure'], $kj_goods_weight, $kj_goods_amount, $kj_goods_number);
        return $kj_shipping_fee;
}
// 该函数未使用
function check_is_kj_good($goods_id){
    include_once('report/orderReportNew.php');
    $merchantCode = $GLOBALS['_LANG']['merchantCode'];
    $merchantName = $GLOBALS['_LANG']['merchantName'];
    $sql = "SELECT kj_sn FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $goods_id;
    $product_id = $GLOBALS['db']->getOne($sql);
    //获取商品备案信息
    $product_rt=queryCommodityRecord($merchantCode,$merchantName,$product_id);
    if($product_rt==null){
        return 2;
    }
    if($product_rt['code']=='200'){
        return 1;
    }
    return 0;
}
/**
 * 查询账户是否已经实名认证
 * @param  $account 查询账户
 */
function check_account($account){
    include_once('report/orderReportHaiGuan.php');
    $res = hg_GetAccount($account);
    $user_info = user_info($_SESSION['user_id']);

    if ($res->Header->Result=='T' && $res->Body->IsAuth == 1 && !empty($user_info['real_name']) && !empty($user_info['real_id']) && !empty($user_info['real_phone']) && !empty($user_info['real_email'])) {
        // 检查身份证是否与申报系统里的一致，只检查后四位
        $idnum = $res->Body->Idnum;
        if (!empty($idnum) && substr($idnum, -4, 4) != substr($user_info['real_id'], -4, 4)) {
            show_message('<br/>实名认证信息与宁波跨境贸易电子商务服务平台——跨境购上的信息不一致，请重新填写', '','', 'warning');
            exit;
        }
        return 1;
    }
    return 0;
}
/**
 * 绑定用户帐号，记录实名信息
 */
 function bind_account($uid,$id_num,$real_name,$phone,$email){
     include_once('report/orderReportHaiGuan.php');
     $bind_info=array(
         "user_id"=>  $uid,             // 用户账号ID
         "id_num"=>  $id_num,           // 身份证
         "real_name"=>  $real_name,     // 真实姓名
         "phone"=>  $phone,             // 电话
         "email"=>  $email              // 邮箱
     );
     hg_RecordRealName($bind_info);
     return array(1,'');
 }
/**
 * 获取实名信息
 */
function get_account_real_info($userid=null, $username=null) {
    if ($userid == null && $username == null) return null;

    $sql = "SELECT user_id, user_name, real_name, real_id, real_phone, real_email FROM " . $GLOBALS['ecs']->table('users') . "WHERE ";
    $sql .= $userid != null ? "user_id='" . $userid ."'" : "user_name='" . $username ."'";
    $res = $GLOBALS['db']->getRow($sql);
    return $res;
}
/**
 *身份证号校验
 **/
function validation_filter_id_card($id_card)
{
    if(strlen($id_card) == 18)
    {
        return idcard_checksum18($id_card);
    }
    elseif((strlen($id_card) == 15))
    {
        $id_card = idcard_15to18($id_card);
        return idcard_checksum18($id_card);
    }
    else
    {
        return false;
    }
}
// 计算身份证校验码，根据国家标准GB 11643-1999
function idcard_verify_number($idcard_base)
{
    if(strlen($idcard_base) != 17)
    {
        return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++)
    {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}
// 将15位身份证升级到18位
function idcard_15to18($idcard){
    if (strlen($idcard) != 15){
        return false;
    }else{
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
            $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
        }else{
            $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
        }
    }
    $idcard = $idcard . idcard_verify_number($idcard);
    return $idcard;
}
// 18位身份证校验码有效性检查
function idcard_checksum18($idcard){
    if (strlen($idcard) != 18){ 
        return false; 
    }
    //校验出生日期是否合法
    $current_date = date("Y-m-d");
    $vBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday || date('Y-m-d', strtotime($vBirthday))>$current_date ){
        //print "出生日期错误";
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
        return false;
    }else{
        return true;
    }
}

?>