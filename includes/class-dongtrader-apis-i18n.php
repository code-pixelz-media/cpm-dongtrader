<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://codepixelz.tech/dongtraders
 * @since      1.0.0
 *
 * @package    Dongtrader_Apis
 * @subpackage Dongtrader_Apis/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Dongtrader_Apis
 * @subpackage Dongtrader_Apis/includes
 * @author     Codepixelzmedia <anil@codepixelzmedia.com.np>
 */
class Dongtrader_Apis_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'dongtrader-apis',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
