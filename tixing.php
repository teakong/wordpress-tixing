<?php
/*
  Plugin Name: Wordpress动态提醒
  Plugin URI: http://www.phprm.com/wordpress-tixing/
  Description: 聚合推送，将博客动态发送提醒通知到手机上（微信，钉钉、飞书等方式）。
  Version: 1.0.0
  Author: teakong
 */

define('TIXING_VERSION', '1.0.0');
define('TIXING_URL', plugins_url('', __FILE__));
define('TIXING_PATH', dirname( __FILE__ ));

/**
 * 加载函数
 */
require_once(TIXING_PATH . '/functions.php');
tixing_load();

function tixing_settings_link($action_links,$plugin_file){
	if($plugin_file==plugin_basename(__FILE__)){
		$wcu_settings_link = '<a href="options-general.php?page=' . dirname(plugin_basename(__FILE__)) . '/tixing_admin.php">' . __('Settings') . '</a>';
		array_unshift($action_links,$wcu_settings_link);
	}
	return $action_links;
}
add_filter('plugin_action_links','tixing_settings_link',10,2);

if(is_admin()){
	require_once('tixing_admin.php');
}

