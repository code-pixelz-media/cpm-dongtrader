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



/* Enqueuing the scripts and styles for the plugin on admin settings */
function dongtrader_scripts()
{
	/* css for plugin settings */
	wp_enqueue_style('dongtrader-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), false, 'all');
	wp_enqueue_style('dongtrader-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), false, 'all');
	wp_enqueue_style('dongtrader-jquery-ui-custom-styles', plugin_dir_url(__FILE__) . 'assets/css/jquery-ui.custom.css', array(), false, 'all');
	wp_enqueue_style('dongtrader-select2.min-styles', plugin_dir_url(__FILE__) . 'assets/css/select2.min.css', array(), false, 'all');

	/* js for plugin settings */
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('dongtrader-scripts', plugin_dir_url(__FILE__) . 'assets/js/plugin-scripts.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('dongtrader-admin-scripts', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('dongtrader-admin-select-scripts', plugin_dir_url(__FILE__) . 'assets/js/select2.min.js', array('jquery'), '1.0.0', true);
	wp_add_inline_script('dongtrader-scripts', 'const dongScript = ' . json_encode(array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
	)), 'before');
}
add_action('admin_enqueue_scripts', 'dongtrader_scripts');

function dongtrader_styles()
{
	wp_enqueue_style('dongtrader-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), false, 'all');
}
//add_action('wp_enqueue_scripts', 'dongtrader_styles');



add_action('admin_menu', 'register_my_custom_menu_page');
function register_my_custom_menu_page()
{
	// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page(__('DongTraders Generate QR', 'dongtraders'), 'DongTraders Generate QR', 'manage_options', 'dongtrader_api_settings', 'dongtraders_add_setting_page', 'dashicons-welcome-widgets-menus', 90);
}

function dongtraders_add_setting_page()
{
	require plugin_dir_path(__FILE__) . 'inc/cpm-dongtrader-settings.php';
}


/* register setting to store value to options table fro setting page */
function dongtraders_api_register_settings()
{
	register_setting('dongtraders_api_setting_page', 'dongtraders_api_settings_fields', 'sd_callback_function');
}

add_action('admin_init', 'dongtraders_api_register_settings');
