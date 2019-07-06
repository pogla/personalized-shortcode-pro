<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Personalized_Shortcode_Pro
 * @subpackage Personalized_Shortcode_Pro/public
 * @author     Matic Pogladič <https://maticpogladic.com>
 */

use DeviceDetector\DeviceDetector;

class Personalized_Shortcode_Pro_Public {

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
	 * User data.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $user_data    array.
	 */
	private $user_data = array();

	/**
	 * Give each shortcode an id.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $incremental_id    int.
	 */
	private $incremental_id = 1;

	/**
	 * Cache user device info
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $user_info    int.
	 */
	private $user_info = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register query vars
	 *
	 * @param $vars
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'psp_debug_ip';
		return $vars;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$ajax_nonce = wp_create_nonce( 'psp-public-js-nonce' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/personalized-shortcode-pro-public.js', array( 'jquery' ), $this->version, false );

		$scripts_object = array(
			'security'         => $ajax_nonce,
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'snapchat_preload' => '1' === get_option( PSP_PREFIX . 'shapchat_preload' ),
			'testing_ip'       => get_query_var( 'psp_debug_ip' ),
		);

		wp_localize_script( $this->plugin_name, 'wp_vars', $scripts_object );

	}

	/**
	 * AJAX - Get user data. This makes sure it works also with caching plugins
	 *
	 * @since 1.0.0
	 */
	public function psp_get_user_data_ajax() {

		if ( ! check_ajax_referer( 'psp-public-js-nonce', 'security', false ) || ! count( $_POST['values'] ) ) {
			wp_send_json_error();
		}

		$ip = isset( $_POST['testip'] ) ? sanitize_text_field( $_POST['testip'] ) : false;

		$this->set_user_data( $ip );

		$response_array = array();

		foreach ( $_POST['values'] as $type ) {
			$type             = sanitize_text_field( $type );
			$response_array[] = array(
				'type'  => $type,
				'value' => $this->get_user_data( $type ),
			);
		}

		wp_send_json_success( $response_array );
	}

	/**
	 * AJAX - Check conditionals
	 *
	 * @since 1.0.0
	 */
	public function psp_conditional_content_ajax() {

		if ( ! check_ajax_referer( 'psp-public-js-nonce', 'security', false ) || ! count( $_POST['values'] ) ) {
			wp_send_json_error();
		}

		$ip = isset( $_POST['testip'] ) ? sanitize_text_field( $_POST['testip'] ) : false;

		$this->set_user_data( $ip );

		$response_array = array();

		foreach ( $_POST['values'] as $item ) {

			$item    = array_map( 'sanitize_text_field', $item );
			$val     = $this->get_user_data( $item['type'] );
			$content = $item['content'];

			if ( ! self::should_show_content( $item['values'], $val, $item['exclude'] ) ) {
				$content = '';
			}

			$response_array[] = array(
				'id'      => $item['id'],
				'content' => $content,
			);
		}

		wp_send_json_success( $response_array );
	}

	/**
	 * Shortcode to show visitor data
	 *
	 * @since 1.0.0
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function psp_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'type'      => '',
			'default'   => '',
			'modifiers' => '',
			'styles'    => '',
		), $atts );

		if ( ! $atts['type'] ) {
			return '';
		}

		$value = ( '1' !== get_option( PSP_PREFIX . 'only_ajax' ) && '1' !== get_option( PSP_PREFIX . 'shapchat_preload' ) ) || isset( $_COOKIE['psp_user'] ) ? $this->get_user_data( $atts['type'] ) : false;

		if ( ! $value && $atts['default'] ) {
			$value = $atts['default'];
		}

		$styles = $atts['styles'];

		if ( ! empty( $atts['modifiers'] ) ) {

			$modifiers = explode( ',', $atts['modifiers'] );

			foreach ( $modifiers as $modifier ) {

				if ( 'uppercase' === $modifier ) {
					$styles .= 'text-transform: uppercase;';
				}

				if ( 'lowercase' === $modifier ) {
					$styles .= 'text-transform: lowercase;';
				}

				if ( 'bold' === $modifier ) {
					$styles .= 'font-weight: bold;';
				}

				if ( 'italics' === $modifier ) {
					$styles .= 'font-style: italic;';
				}
			}
		}

		return "<span class='psp-type' style='{$styles}' data-psp-type='{$atts['type']}'>{$value}</span>";
	}

	/**
	 * Shortcode for conditional content
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return null|string
	 */
	public function psp_shortcode_conditional( $atts, $content = null ) {

		if ( ! $content ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'type'    => '',
			'values'  => '',
			'exclude' => '',
		), $atts );

		$output = $content;

		if ( isset( $_COOKIE['psp_user'] ) || ( '1' !== get_option( PSP_PREFIX . 'only_ajax' ) && '1' !== get_option( PSP_PREFIX . 'shapchat_preload' ) ) ) {
			$val = $this->get_user_data( $atts['type'] );

			if ( ! self::should_show_content( $atts['values'], $val, $atts['exclude'] ) ) {
				$output = '';
			}
		}

		return '<span class="psp-conditional" data-psp-id="' . $this->incremental_id++ . '" data-psp-content="' . $content . '" data-psp-values="' . $atts['values'] . '" data-psp-type="' . $atts['type'] . '" data-psp-exclude="' . $atts['exclude'] . '" style="display: inline;">' . $output . '</span>';
	}

	/**
	 * Check if content should show in shortcode
	 *
	 * @param $values
	 * @param $type_val
	 * @param $exclude
	 *
	 * @return bool
	 */
	private static function should_show_content( $values, $type_val, $exclude ) {

		$values = explode( ',', $values );

		foreach ( $values as $value ) {
			$value = sanitize_text_field( trim( $value ) );
			if ( strtolower( $value ) == strtolower( $type_val ) ) {

				if ( 'true' != $exclude ) {
					return true;
				} else {
					return false;
				}
			}
		}

		if ( 'true' != $exclude ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get user data by type
	 *
	 * @param $type
	 *
	 * @return bool|string
	 */
	public function get_user_data( $type ) {

		$this->set_user_data();

		if ( ! $this->user_data || ! is_array( $this->user_data ) ) {
			return false;
		}

		if ( in_array( $type, array( 'capital', 'country_flag', 'country_flag_emoji', 'calling_code' ), true ) ) {

			if ( isset( $this->user_data['location'][ $type ] ) ) {
				return $this->user_data['location'][ $type ];
			} else {
				return false;
			}
		}

		if ( strpos( $type, 'language_' ) !== false ) {

			$split = explode( 'language_', $type );

			if ( 'name' === $split[1] ) {
				$split[1] = 'native';
			}

			if ( isset( $this->user_data['location']['languages'][0][ $split[1] ] ) ) {
				return $this->user_data['location']['languages'][0][ $split[1] ];
			} else {
				return false;
			}
		}

		if ( strpos( $type, 'time_zone_' ) !== false ) {

			$split = explode( 'time_zone_', $type );

			if ( 'current_date' === $split[1] ) {

				if ( isset( $this->user_data['time_zone']['current_time'] ) ) {
					$date_time = $this->user_data['time_zone']['current_time'];
					$date      = date( 'Y-m-d', strtotime( $date_time ) );
					return $date;
				} else {
					return false;
				}
			}

			if ( 'current_time' === $split[1] ) {

				if ( isset( $this->user_data['time_zone']['current_time'] ) ) {
					$date_time = $this->user_data['time_zone']['current_time'];
					$time      = date( 'H:i:s', strtotime( $date_time ) );
					return $time;
				} else {
					return false;
				}
			}

			return $this->user_data['time_zone'][ $split[1] ];
		}

		if ( strpos( $type, 'currency_' ) !== false ) {

			$split = explode( 'currency_', $type );

			if ( isset( $this->user_data['currency'][ $split[1] ] ) ) {
				return $this->user_data['currency'][ $split[1] ];
			} else {
				return false;
			}
		}

		if ( 'isp' === $type ) {

			if ( isset( $this->user_data['connection']['isp'] ) ) {
				return $this->user_data['connection']['isp'];
			} else {
				return false;
			}
		}

		if ( in_array( $type, array( 'browser', 'os', 'device_brand', 'device_model', 'device_type', 'browser_family' ), true ) ) {
			return $this->get_device_info( $type );
		}

		return $this->user_data[ $type ];
	}

	/**
	 * Get user agent info
	 *
	 * @param $type
	 *
	 * @return string
	 */
	private function get_device_info( $type ) {

		if ( ! $this->user_info ) {
			$user_agent      = $_SERVER['HTTP_USER_AGENT'];
			$this->user_info = new DeviceDetector( $user_agent );
			$this->user_info->parse();
		}

		if ( 'browser' === $type ) {

			$client_info = $this->user_info->getClient();

			if ( isset( $client_info['name'] ) && $client_info['name'] ) {
				return $client_info['name'];
			}
		} elseif ( 'os' === $type ) {

			$os_info = $this->user_info->getOs();

			if ( isset( $os_info['name'] ) && $os_info['name'] ) {
				return $os_info['name'];
			}
		} elseif ( 'device_brand' === $type ) {

			$brand_info = $this->user_info->getBrandName();

			if ( isset( $brand_info ) && $brand_info ) {
				return $brand_info;
			}
		} elseif ( 'device_model' === $type ) {

			$device_model = $this->user_info->getModel();

			if ( isset( $device_model ) && $device_model ) {
				return $device_model;
			}
		} elseif ( 'device_type' === $type ) {

			$device_info = $this->user_info->getDeviceName();

			if ( isset( $device_info ) && $device_info ) {
				return $device_info;
			}
		} elseif ( 'browser_family' === $type ) {

			$browser_family = \DeviceDetector\Parser\Client\Browser::getBrowserFamily( $this->user_info->getClient( 'short_name' ) );

			if ( false !== $browser_family ) {
				return $browser_family;
			}
		}

		return '';
	}

	/**
	 * Gets user data
	 *
	 * @since 1.0.0
	 *
	 * @param string|false $ip
	 *
	 * @return void
	 */
	public function set_user_data( $ip = false ) {

		if ( ! empty( $this->user_data ) ) {
			return;
		}

		if ( isset( $_COOKIE['psp_user'] ) && ! isset( $_GET['psp_debug_ip'] ) ) {
			$this->user_data = json_decode( base64_decode( $_COOKIE['psp_user'] ), true );
			return;
		}

		if ( ! $ip ) {
			$ip = self::get_user_ip();
		}

		$key = get_option( PSP_PREFIX . 'ipstack_api_key' );

		if ( ! $key ) {
			return;
		}

		$url = "http://api.ipstack.com/$ip?access_key=$key";

		$response = wp_remote_get( $url, array( 'timeout' => 60, 'sslverify' => false ) );

		if ( is_wp_error( $response ) || ! isset( $response['response']['code'] ) || 200 !== $response['response']['code'] || ! $response['body'] ) {
			return;
		}

		$body = $response['body'];
		$data = json_decode( $body, true );

		if ( ! $data ) {
			return;
		}

		// Add user data to session so we don't use unnecessary requests
		setcookie( 'psp_user', base64_encode( json_encode( $data ) ) );
		$this->user_data = $data;
	}

	/**
	 * Get user id
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public static function get_user_ip() {

		$debug_ip = isset( $_GET['psp_debug_ip'] ) ? sanitize_text_field( $_GET['psp_debug_ip'] ) : false;

		if ( $debug_ip ) {
			return $debug_ip;
		}

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * Show shortcode title
	 *
	 * @since 1.0.0
	 *
	 * @param string $title
	 *
	 * @return mixed
	 */
	public function add_shortcodes_to_title( $title ) {

		global $post;

		if ( ! is_admin() && is_singular() && $post && sanitize_title( $post->post_title ) === sanitize_title( $title ) ) {

			if ( $post->ID ) {
				$title_id = PSP_PREFIX . 'custom_title';
				$title    = get_post_meta( $post->ID, '_' . $title_id, true );

				if ( $title ) {
					return do_shortcode( $title );
				}
			}
		}

		return $title;
	}

	public function start_session() {
		$this->set_user_data();
	}
}
