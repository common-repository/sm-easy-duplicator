<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://profiles.wordpress.org/shailu25/
 * @since      1.0.0
 *
 * @package    Sm_Easy_Duplicator
 * @subpackage Sm_Easy_Duplicator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sm_Easy_Duplicator
 * @subpackage Sm_Easy_Duplicator/includes
 * @author     Shail Mehta <shailmehta25@gmail.com>
 */
class Sm_Easy_Duplicator_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sm-easy-duplicator',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
