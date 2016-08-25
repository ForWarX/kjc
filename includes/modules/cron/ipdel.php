<?php

/**
 * PBCC 定期删除
 * ===========================================================
 * * 版权所有 2013-2014 加拿大极地熊集团，并保留所有权利。
 * ==========================================================
 * $Id: ipdel.php $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/ipdel.php';
if (file_exists($cron_lang))
{
    global $_LANG;

    include_once($cron_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'ipdel_desc';

    /* 作者 */
    $modules[$i]['author']  = 'PBCC 网销部';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.pbcc.ca';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'ipdel_day', 'type' => 'select', 'value' => '30'),
    );

    return;
}

empty($cron['ipdel_day']) && $cron['ipdel_day'] = 7;

$deltime = gmtime() - $cron['ipdel_day'] * 3600 * 24;
$sql = "DELETE FROM " . $ecs->table('stats') .
       "WHERE  access_time < '$deltime'";
$db->query($sql);

?>