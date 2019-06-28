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
 * @author     Matic Pogladič <https://maticpogladic.com>
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

		add_options_page(
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

		add_settings_section( PSP_PREFIX . 'section', __( 'General Settings', 'personalized-shortcode-pro' ), null, 'psp-settings' );

		add_settings_field( PSP_PREFIX . 'ipstack_api_key', __( 'Ipstack API key', 'personalized-shortcode-pro' ), array( $this, 'shortcode_ipstack_api_key_field_callback' ), 'psp-settings', PSP_PREFIX . 'section' );
		add_settings_field( PSP_PREFIX . 'enable_titles', __( 'Enable Shortcodes in Titles', 'personalized-shortcode-pro' ), array( $this, 'shortcode_title_field_callback' ), 'psp-settings', PSP_PREFIX . 'section' );
		add_settings_field( PSP_PREFIX . 'only_ajax', __( 'Use only AJAX', 'personalized-shortcode-pro' ), array( $this, 'shortcode_only_ajax_field_callback' ), 'psp-settings', PSP_PREFIX . 'section' );
		add_settings_field( PSP_PREFIX . 'shapchat_preload', __( 'Snapchat preload mode', 'personalized-shortcode-pro' ), array( $this, 'shortcode_shapchat_preload_field_callback' ), 'psp-settings', PSP_PREFIX . 'section' );

		register_setting( PSP_PREFIX . 'section', PSP_PREFIX . 'ipstack_api_key' );
		register_setting( PSP_PREFIX . 'section', PSP_PREFIX . 'enable_titles' );
		register_setting( PSP_PREFIX . 'section', PSP_PREFIX . 'only_ajax' );
		register_setting( PSP_PREFIX . 'section', PSP_PREFIX . 'shapchat_preload' );
	}

	/**
	 * API text field callback
	 *
	 * @since 1.0.0
	 */
	public function shortcode_ipstack_api_key_field_callback() {
		?>
			<input value='<?php echo get_option( PSP_PREFIX . 'ipstack_api_key' ); // WPCS XSS ok ?>' id="<?php echo PSP_PREFIX . 'ipstack_api_key'; // WPCS XSS ok ?>" name="<?php echo PSP_PREFIX . 'ipstack_api_key'; // WPCS XSS ok ?>" type="text" />
			<p>Get your API key <a href="https://ipstack.com" target="_blank">here</a></p>
		<?php
	}

	/**
	 * API text field callback
	 *
	 * @since 1.0.0
	 */
	public function shortcode_title_field_callback() {
		?>
			<input value='1' id="<?php echo PSP_PREFIX . 'enable_titles'; // WPCS XSS ok ?>" name="<?php echo PSP_PREFIX . 'enable_titles'; // WPCS XSS ok ?>" type="checkbox" <?php checked( 1, get_option( PSP_PREFIX . 'enable_titles' ), true ); ?> />
		<?php
	}

	/**
	 * Only AJAX callback
	 *
	 * @since 1.0.0
	 */
	public function shortcode_only_ajax_field_callback() {
		?>
			<input value='1' id="<?php echo PSP_PREFIX . 'only_ajax'; // WPCS XSS ok ?>" name="<?php echo PSP_PREFIX . 'only_ajax'; // WPCS XSS ok ?>" type="checkbox" <?php checked( 1, get_option( PSP_PREFIX . 'only_ajax' ), true ); ?> />
		<?php
	}

	/**
	 * Only AJAX callback
	 *
	 * @since 1.0.0
	 */
	public function shortcode_shapchat_preload_field_callback() {
		?>
			<input value='1' id="<?php echo PSP_PREFIX . 'shapchat_preload'; // WPCS XSS ok ?>" name="<?php echo PSP_PREFIX . 'shapchat_preload'; // WPCS XSS ok ?>" type="checkbox" <?php checked( 1, get_option( PSP_PREFIX . 'shapchat_preload' ), true ); ?> />
		<?php
	}


	/**
	 * Add meta box
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		$screens = apply_filters( 'psp_post_types_titles', array( 'post', 'page', 'product' ) );
		foreach ( $screens as $screen ) {
			add_meta_box(
				PSP_PREFIX . 'shortcodes_title',
				'Title with shortcodes',
				array( $this, 'custom_title_meta_box_callback' ),
				$screen,
				'advanced',
				'high'
			);
		}
	}

	public function custom_title_meta_box_callback( $post ) {
		$title_id = PSP_PREFIX . 'custom_title';
		$title    = get_post_meta( $post->ID, '_' . $title_id, true );
		?>
			<label for="<?php echo $title_id; ?>">Custom title with shortcodes</label>
			<textarea name="<?php echo $title_id; ?>" id="<?php echo $title_id; ?>" style="width: 100%;"><?php echo $title; ?></textarea>
		<?php
	}

	public function save_custom_title_data( $post_id ) {

		$title_id = PSP_PREFIX . 'custom_title';

		if ( ! isset( $_POST[ $title_id ] ) ) {
			return;
		}

		update_post_meta(
			$post_id,
			'_' . $title_id,
			sanitize_text_field( $_POST[ $title_id ] )
		);
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
			<form method='post' action='options.php'>
				<?php
				//wp_nonce_field( PSP_PREFIX . 'settings_action', PSP_PREFIX . 'settings_nonce_field' );
				/* 'option_group' must match 'option_group' from register_setting call */
				settings_fields( PSP_PREFIX . 'section' );
				do_settings_sections( 'psp-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function get_sample_permalink( $permalink, $post_id, $title, $name, $post ) {

		if ( sanitize_title( $post->post_title ) === $permalink ) {
			return sanitize_title( strip_shortcodes( $post->post_title ) );
		}

		return $permalink;
	}
}
