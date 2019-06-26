<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://maticpogladic.com
 * @since      1.0.0
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/includes
 * @author     Matic Pogladič <https://maticpogladic.com>
 */
class Personalized_Shortcode_Pro {

	/**
	 * @var object The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Personalized_Shortcode_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $errors
	 */
	public static $errors = array();

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ), 8 );
	}

	public function init() {

		if ( defined( 'PSP_VERSION' ) ) {
			$this->version = PSP_VERSION;
		} else {
			$this->version = '1.0';
		}

		$this->plugin_name = 'personalized-shortcode-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Check if we can activate plugin
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function check() {

		$passed = true;

		/* translators: 1: Plugin name */
		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'personalized-shortcode-pro' ), PSP_NAME ) . '</strong>';

		if ( version_compare( phpversion(), PSP_MIN_PHP_VER, '<=' ) ) {
			/* translators: 1: inactive text, 2: plugin name */
			self::$errors[] = sprintf( __( '%1$s The plugin requires PHP version %2$s or newer.', 'personalized-shortcode-pro' ), $inactive_text, PSP_MIN_PHP_VER );
			$passed         = false;
		} elseif ( ! self::is_wp_version_ok() ) {
			/* translators: 1: inactive text, 2: plugin name */
			self::$errors[] = sprintf( __( '%1$s The plugin requires WordPress version %2$s or newer.', 'personalized-shortcode-pro' ), $inactive_text, PSP_MIN_WP_VER );
			$passed         = false;
		}

		return $passed;
	}

	/**
	 * Check WP version
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected static function is_wp_version_ok() {
		global $wp_version;
		if ( ! PSP_MIN_WP_VER ) {
			return true;
		}
		return version_compare( $wp_version, PSP_MIN_WP_VER, '>=' );
	}

	/**
	 * Admin notices
	 *
	 * @since 1.0.0
	 */
	public static function admin_notices() {
		if ( empty( self::$errors ) ) {
			return;
		};
		echo '<div class="notice notice-error"><p>';
		echo implode( '<br>', self::$errors ); // WPCS XSS ok.
		echo '</p></div>';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Personalized_Shortcode_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Personalized_Shortcode_Pro_i18n. Defines internationalization functionality.
	 * - Personalized_Shortcode_Pro_Admin. Defines all hooks for the admin area.
	 * - Personalized_Shortcode_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-personalized-shortcode-pro-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-personalized-shortcode-pro-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-personalized-shortcode-pro-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-personalized-shortcode-pro-public.php';

		$this->loader = new Personalized_Shortcode_Pro_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Personalized_Shortcode_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Personalized_Shortcode_Pro_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Personalized_Shortcode_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugin_action_links_' . basename( dirname( __DIR__ ) ) . '/personalized-shortcode-pro.php', $plugin_admin, 'plugin_action_links' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_submenu_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_register' );

		if ( '1' === get_option( PSP_PREFIX . 'enable_titles' ) ) {
			$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
			$this->loader->add_action( 'save_post', $plugin_admin, 'save_custom_title_data' );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Personalized_Shortcode_Pro_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'start_session' );
		$this->loader->add_action( 'wp_ajax_psp_get_user_data', $plugin_public, 'psp_get_user_data_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_psp_get_user_data', $plugin_public, 'psp_get_user_data_ajax' );
		$this->loader->add_action( 'wp_ajax_psp_conditional_content', $plugin_public, 'psp_conditional_content_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_psp_conditional_content', $plugin_public, 'psp_conditional_content_ajax' );
		$this->loader->add_action( 'query_vars', $plugin_public, 'add_query_vars' );

		add_shortcode( 'psp', array( $plugin_public, 'psp_shortcode' ) );
		add_shortcode( 'psp-if', array( $plugin_public, 'psp_shortcode_conditional' ) );

		if ( '1' === get_option( PSP_PREFIX . 'enable_titles' ) ) {
			$this->loader->add_filter( 'the_title', $plugin_public, 'add_shortcodes_to_title' );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Personalized_Shortcode_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Class Instance
	 *
	 * @static
	 * @return object instance
	 *
	 * @since  1.0.0
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

}

/**
 * Instance of plugin
 *
 * @return object
 * @since  1.0.0
 */
if ( ! function_exists( 'personalized_shortcode_pro' ) ) {

	function personalized_shortcode_pro() {
		return Personalized_Shortcode_Pro::instance();
	}
}
