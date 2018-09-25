<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/admin
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Personalized_Shortcode_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since   1.0.0
	 * @param   mixed $links Plugin Action links.
	 * @return  array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=psp-settings' ) . '" aria-label="' . esc_attr__( 'View Advanced Reviews Pro Settings', 'advanced-reviews-pro' ) . '">' . esc_html__( 'Settings', 'advanced-reviews-pro' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 *
	 * Add a submenu under settings menu
	 *
	 * @since   1.0.0
	 */
	public function add_submenu_page() {

		add_submenu_page(
			'options-general.php',
			'Personalized Shortcode',
			'Personalized Shortcode',
			'manage_options',
			'psp-settings',
			array( $this, 'add_submenu_page_callback' )
		);
	}

	/**
	 * Register our settings. Add the settings section, and settings fields
	 *
	 * @since   1.0.0
	 */
	public function settings_register() {

		register_setting( PSP_PREFIX . 'settings', PSP_PREFIX . 'option' );
		add_settings_section( PSP_PREFIX . 'section', 'General Settings', '', 'options-general.php?page=psp-settings' );
		add_settings_field( PSP_PREFIX . 'api_key', 'API Key', array( $this, 'api_text_field_callback' ), 'options-general.php?page=psp-settings', PSP_PREFIX . 'section' );
	}

	/**
	 * API text field callback
	 *
	 * @since 1.0.0
	 */
	public function api_text_field_callback() {
		?>
			<input name="<?php echo PSP_PREFIX . 'api_key'; // WPCS XSS ok ?>" type="text" style="min-width: 300px;" value="<?php echo get_option( PSP_PREFIX . 'api_key' ); // WPCS XSS ok. ?>" />
		<?php
	}

	/**
	 * Settings page
	 *
	 * @since 1.0.0
	 */
	public function add_submenu_page_callback() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Personalized Shortcode Pro', 'personalized-shortcode-pro' ); ?></h2>
			<form method='post'>
				<?php
				wp_nonce_field( PSP_PREFIX . 'settings_action', PSP_PREFIX . 'settings_nonce_field' );
				/* 'option_group' must match 'option_group' from register_setting call */
				settings_fields( PSP_PREFIX . '_settings' );
				do_settings_sections( 'options-general.php?page=psp-settings' );
				?>
				<p class='submit'>
					<input name='submit' type='submit' id='submit' class='button-primary' value='<?php esc_html_e( 'Save Changes' ); ?>' />
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Save fields
	 *
	 * @since 1.0.0
	 */
	public function save_fields() {

		global $pagenow;

		if ( isset( $_POST[ PSP_PREFIX . 'settings_nonce_field' ] ) && wp_verify_nonce( $_POST[ PSP_PREFIX . 'settings_nonce_field' ], PSP_PREFIX . 'settings_action' ) ) {

			if ( 'options-general.php' === $pagenow ) {

				if ( isset( $_POST[ PSP_PREFIX . 'api_key' ] ) ) {
					update_option( PSP_PREFIX . 'api_key', $_POST[ PSP_PREFIX . 'api_key' ] );
				}
			}
		}
	}
}
