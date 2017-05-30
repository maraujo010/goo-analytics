<?php
/**
* Plugin Name: goo-analytics
* Plugin URI: http://challenge.dev/
* Description: Manage google analytics tracking code
* Version: 1.0
* Author: Marco Araújo
* Author URI: https://github.com/maraujo010
* License: GPL2
*/

if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('GOO_ANALYTICS_PLUGIN_FILE', __FILE__);
define('GOO_ANALYTICS_PLUGIN_PATH', plugin_dir_path(__FILE__));

require GOO_ANALYTICS_PLUGIN_PATH.'class.goo-analytics.php';

new goo_analytics();
