<?php
/**
 * 网店配置模板
 *
 * 版本 $Id: config.sample.php $
 */

// ** 数据库配置 ** //
define('DB_USER', 'qdm0110450');  # 数据库用户名
define('DB_PASSWORD', 'Pbcc2014'); # 数据库密码
define('DB_NAME', 'qdm0110450_db');    # 数据库名

# 数据库服务器 -- 99% 的情况下您不需要修改此参数
define('DB_HOST', 'qdm-011.hichina.com');
//define('DB_PCONNECT',1); #是否启用数据库持续连接？
//define('SHOP_DEVELOPER', true);
define('STORE_KEY', 'b1d94c3d8d820c2f8c8af61e204a9078'); #密钥
define('DB_PREFIX', 'sdb_');
define('LANG', '');

#启用触发器日志: home/logs/trigger.php
//define ('TRIGGER_LOG',true);
//define ('DISABLE_TRIGGER',true); #禁用触发器

define('STAGE','release');
define('ERROR_REPORTING',E_ALL ^ E_NOTICE ^ E_WARNING);
define('SHOPADMIN_PATH', 'shopadmin');
//define('GD_VCODE',true); #使用GD验证码

/* 以下为调优参数 */
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('DEBUG_JS',false);
define('DEBUG_CSS',false);
define('BASE_DIR', realpath(dirname(__FILE__).'/../'));
define('CORE_DIR', BASE_DIR.'/core');
define('CORE_INCLUDE_DIR', CORE_DIR.'/include_v5');

//安全模式启用后将禁用插件 no:modifiers
//define('SAFE_MODE',false);

#您可以更改这个目录的位置来获得更高的安全性
define('HOME_DIR', BASE_DIR.'/home'); 
define('PLUGIN_DIR', BASE_DIR.'/plugins');
define('THEME_DIR', BASE_DIR.'/themes');
define('MEDIA_DIR', BASE_DIR.'/images');
define('PUBLIC_DIR', BASE_DIR.'/public');  #同一主机共享文件
define('CERT_DIR', BASE_DIR.'/cert');
define('DEFAULT_LOCAL','mainland');
define('SECACHE_SIZE','15M'); #缓存大小,最大不能超过1G
//define('TEMPLATE_MODE','database');
define("MAIL_LOG",false);
define('DEFAULT_INDEX','');
define('SERVER_TIMEZONE','8'); #服务器时区
//define('APP_ROOT_PHP','index.php'); #iis 5

@ini_set('memory_limit','32M');
define('WITHOUT_GZIP',false);

define('WITHOUT_CACHE',false);

#前台禁ip
//define('BLACKLIST','10.0.0.0/24 192.168.0.1/24');

#数据库集群.
//define('DB_SLAVE_NAME',DB_NAME);
//define('DB_SLAVE_USER',DB_USER);
//define('DB_SLAVE_PASSWORD',DB_PASSWORD);
//define('DB_SLAVE_HOST',DB_HOST);

#支持泛解的时候才可以用这个, 仅支持fs_storager
/*
 * define('HOST_MIRRORS',
 * 'http://img0.example.com,
 * http://img2.example.com,
 * http://img2.example.com');
 */

#使用ftp存放图片文件
//define('WITH_STORAGER','ftp_storager');

#确定服务器支持htaccess文件时，可以打开下面两个参数获得加速。
//define ('GZIP_CSS',true);
//define ('GZIP_JS',true);

#可以选择缓存方式apc 或者 memcached
define('CACHE_METHOD','secache');
//define('CACHE_METHOD','apc');
//======================================
//define('CACHE_METHOD','memcached');
//======================================
#使用单个文件存放，稳定，但无法控制文件大小
//define('CACHE_METHOD','dir'); 


/* 日志 */
//define('LOG_LEVEL',E_ERROR);

#按日期分目录，每个ip一个日志文件。扩展名是php防止下载。
//define('LOG_FILE',HOME_DIR.'/logs/{date}/{ip}.php');

#log文件头部放上exit()保证无法下载。
//define('LOG_HEAD_TEXT','<'.'?php exit()?'.'>');  
//define('LOG_FORMAT',"{gmt}\t{request}\t{code}");

//======================================
//define('WITH_MEMCACHE',true);
//define('MEMCACHED_SERVER','127.0.0.1:11211,127.0.0.1:11212');
//======================================

#禁止运行安装
//define('DISABLE_SYS_CALL',1);

#使用数据库存放改动过的模板
//define('THEME_STORAGE','db');


#使用变动商品图片名
//define('IMAGE_CHANGE',true);

#加载二次开发目录
//define('CUSTOM_CORE_DIR', BASE_DIR.'/custom_core');
