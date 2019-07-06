<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://maticpogladic.com
 * @package           Personalized_Shortcode_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       Personalized Shortcode Pro
 * Plugin URI:        https://maticpogladic.com/personalized-shortcode-pro/
 * Description:       Plugin enables you to use data from user in shortcodes.
 * Version:           1.0.1
 * Author:            Matic Pogladič
 * Author URI:        https://maticpogladic.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       personalized-shortcode-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * SemVer - https://semver.org
 */
define( 'PSP_VERSION', '1.0.1' );
define( 'PSP_PREFIX', 'psp_' );
define( 'PSP_NAME', 'Personalized Shortcode Pro' );
define( 'PSP_MIN_PHP_VER', '5.6' );
define( 'PSP_MIN_WP_VER', '4.2' );
define( 'PSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PSP_PLUGIN_PATH', dirname( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-personalized-shortcode-pro.php';

function psp_load_plugin() {

	$plugin = personalized_shortcode_pro();

	if ( $plugin::check() ) {

		require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

		$plugin->init();

		register_activation_hook( __FILE__, 'activate_personalized_shortcode_pro' );
		register_deactivation_hook( __FILE__, 'deactivate_personalized_shortcode_pro' );

		$plugin->run();
	}
}
add_action( 'plugins_loaded', 'psp_load_plugin', 8 );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-personalized-shortcode-pro-activator.php
 */
function activate_personalized_shortcode_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-personalized-shortcode-pro-activator.php';
	Personalized_Shortcode_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-personalized-shortcode-pro-deactivator.php
 */
function deactivate_personalized_shortcode_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-personalized-shortcode-pro-deactivator.php';
	Personalized_Shortcode_Pro_Deactivator::deactivate();
}
