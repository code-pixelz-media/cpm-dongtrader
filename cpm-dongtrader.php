<?php
/*
Plugin Name: CPM Dongtrader
Plugin URI: https://codepixelzmedia.com/
Description: Plugin Intented For the Integration of API in Dongtrader
Version: 1.0.0
Author: Codepixelzmedia
Author URI: https://codepixelzmedia.com/
Text Domain: cpm-dongtrader
*/
if (!defined('ABSPATH')) {
	exit;
}
//Plugin Version
define('CPM_DONGTRADER_VERSION', '1.0.0');


//Loads All the required files
require_once('inc/cpm-dongtrader-loader.php');


function dongtrader_scripts()
{

	// Need check for page sometimes later
	wp_enqueue_script('dongtrader-scripts', plugin_dir_url(__FILE__) . 'assets/js/plugin-scripts.js', array('jquery'), '1.0.0', true);
	wp_add_inline_script('dongtrader-scripts', 'const dongScript = ' . json_encode(array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
	)), 'before');
}
add_action('wp_enqueue_scripts', 'dongtrader_scripts');

function dongtrader_styles()
{
	wp_enqueue_style('dongtrader-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), false, 'all');
}
add_action('wp_enqueue_scripts', 'dongtrader_styles');
