<?php
/**
 * Plugin Name:       SM Easy Duplicator
 * Plugin URI:        https://wordpress.org/plugins/sm-easy-duplicator/
 * Description:      This Plugin is use for Duplicate Posts / Pages & Custom Posts easily in Just single click.
 * Version:           1.0.1
 * Author:            Shail Mehta
 * Author URI:        https://profiles.wordpress.org/shailu25/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sm-easy-duplicator
 * Domain Path:       /languages
 * Requires at least: 5.0 or higher
 * Requires PHP: 5.6
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SM_EASY_DUPLICATOR_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sm-easy-duplicator-activator.php
 */
function sm_easy_duplicator_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sm-easy-duplicator-activator.php';
	Sm_Easy_Duplicator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sm-easy-duplicator-deactivator.php
 */
function sm_easy_duplicator_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sm-easy-duplicator-deactivator.php';
	Sm_Easy_Duplicator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'sm_easy_duplicator_activate' );
register_deactivation_hook( __FILE__, 'sm_easy_duplicator_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sm-easy-duplicator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function sm_easy_duplicator_run() {

	$plugin = new Sm_Easy_Duplicator();
	$plugin->run();

}
sm_easy_duplicator_run();
