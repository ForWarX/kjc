<?php

/**
 * PBCC PM文件
 * ============================================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ============================================================================
 * $Id: pm.php $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if (empty($_SESSION['user_id'])||$_CFG['integrate_code']=='ecshop')
{
    ecs_header('Location:./');
}

uc_call("uc_pm_location", array($_SESSION['user_id']));
//$ucnewpm = uc_pm_checknew($_SESSION['user_id']);
//setcookie('checkpm', '');

?>