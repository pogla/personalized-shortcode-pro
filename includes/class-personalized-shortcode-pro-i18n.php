<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Personalized_Shortcode_Pro_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'personalized-shortcode-pro',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
