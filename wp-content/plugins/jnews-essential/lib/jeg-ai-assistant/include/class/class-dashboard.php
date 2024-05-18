<?php
/**
 * Dashboard Class
 */

namespace JEG\AI_ASSISTANT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Dashboard
 */
class Dashboard {

	/**
	 * Class instance
	 *
	 * @var Dashboard
	 */
	private static $instance;

	/**
	 * Option name for setting
	 *
	 * @var string
	 */
	private $option_name = 'jeg-ai-setting';

	/**
	 * Setting page slug
	 *
	 * @var string
	 */
	private $setting_page_slug = 'jeg-ai-setting';

	/**
	 * Hold current page
	 *
	 * @var string
	 */
	private $current_page;

	/**
	 * Hold dashboard page
	 *
	 * @var string
	 */
	private $dashboard_page;

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$page                 = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$this->current_page   = admin_url( 'admin.php?page=' . $page );
		$this->dashboard_page = admin_url( 'admin.php?page=' . $this->setting_page_slug );

		add_action( 'admin_menu', array( $this, 'setup_parent_page' ) );
		add_action( 'admin_menu', array( $this, 'setup_child_page' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'submit_key' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_script' ), 99 );
	}

	public function backend_script() {
		if ( $this->check_current_page() ) {
			wp_register_style(
				'fontawesome-jeg-ai-assistant',
				JEG_AI_ASSISTANT_URL . '/assets/fontawesome/css/all.css',
				null,
				JEG_AI_ASSISTANT_VERSION
			);
			wp_enqueue_style(
				'jeg-ai-assistant-editor',
				JEG_AI_ASSISTANT_URL . '/assets/css/admin/editor.css',
				array( 'fontawesome-jeg-ai-assistant' ),
				JEG_AI_ASSISTANT_VERSION
			);

			wp_enqueue_script( 'jeg-ai-assistant-dashboard', JEG_AI_ASSISTANT_URL . '/assets/js/admin/dashboard.js', array(), JEG_AI_ASSISTANT_VERSION, false );
		}
	}

	/**
	 * Check current page
	 *
	 * @return boolean
	 */
	private function check_current_page() {
		return $this->current_page === $this->dashboard_page;
	}

	/**
	 * Plugin key submit handler
	 */
	public function submit_key() {
		if ( $this->check_current_page() ) {
			if ( isset( $_GET['action'] ) && 'submit-key' === sanitize_key( $_GET['action'] ) ) {
				if ( ! isset( $_GET['api_key'] ) ) {
					return;
				}

				$this->update_key( $_GET['api_key'] );
			}
		}
	}

	/**
	 * Update api key
	 *
	 * @param string $api_key API Key.
	 */
	public function update_key( $api_key ) {
		$options        = get_option( $this->option_name, array() );
		$options['key'] = $api_key;
		add_settings_error( 'general', 'settings_updated', __( 'API key successfully connected.', 'jeg-ai-assistant' ), 'success' );
		update_option( $this->option_name, $options );
	}

	/**
	 * Setup parent page menu
	 */
	public function setup_parent_page() {
		$icon = file_get_contents( JEG_AI_ASSISTANT_DIR . '/assets/img/icon-wp-dashboard-robot-circle.svg' );
		add_menu_page( esc_html__( 'AI Assistant', 'jeg-ai-assistant' ), esc_html__( 'AI Assistant', 'jeg-ai-assistant' ), 'edit_theme_options', $this->setting_page_slug, null, 'data:image/svg+xml;base64,' . base64_encode( $icon ), '3.001' );
	}

	/**
	 * Setup child page menu
	 */
	public function setup_child_page() {
		$pages = array(
			array(
				'title'      => esc_html__( 'General Setting', 'jeg-ai-assistant' ),
				'menu'       => esc_html__( 'General Setting', 'jeg-ai-assistant' ),
				'slug'       => $this->setting_page_slug,
				'permission' => 'edit_theme_options',
				'callback'   => array( $this, 'setting_page' ),
			),
		);

		foreach ( $pages as $page ) {
			add_submenu_page( $this->setting_page_slug, $page['title'], $page['menu'], $page['permission'], $page['slug'], $page['callback'] );
		}
	}

	/**
	 * Register JEG AI setting
	 */
	public function register_setting() {
		$home_url             = home_url();
		$jeg_ai_dashboard_url = $this->dashboard_page;
		$callback             = str_replace( $home_url, '', $jeg_ai_dashboard_url );
		$url                  = add_query_arg(
			array(
				'siteurl'  => home_url(),
				'callback' => $callback,
			),
			JEG_AI_ASSISTANT_SERVER . '/get-key/'
		);
		register_setting(
			$this->setting_page_slug,
			$this->option_name
		);

		add_settings_section( $this->setting_page_slug, esc_html__( 'API Setting', 'jeg-ai-assistant' ), '__return_false', $this->setting_page_slug );

		add_settings_field(
			'status',
			esc_html__( 'Status', 'jeg-ai-assistant' ),
			array( $this, 'settings_field' ),
			$this->setting_page_slug,
			$this->setting_page_slug,
			array(
				'id'            => 'status',
				'success'       => esc_attr__( 'Activated', 'jeg-ai-assistant' ),
				'failed'        => esc_attr__( 'inactive', 'jeg-ai-assistant' ),
				'status_option' => 'key',
				'type'          => 'status',
				'desc'          => sprintf(
					__( "To connect your API key, click '<strong>Connect</strong>' to automatically authenticate your account. Alternatively, you can manually copy your API key from your <a href='%s' target='_blank'>account dashboard</a> and paste it into the input field above.", 'jeg-ai-assistant' ),
					JEG_AI_ASSISTANT_SERVER . '/account/login/'
				),
				'api_url'       => $url,
				'button_text'   => esc_attr__( 'Connect & Activate', 'jeg-ai-assistant' ),
			)
		);

		add_settings_field(
			'key',
			esc_html__( 'API Key', 'jeg-ai-assistant' ),
			array( $this, 'settings_field' ),
			$this->setting_page_slug,
			$this->setting_page_slug,
			array(
				'id'   => 'key',
				'type' => 'password',
			)
		);

		add_settings_field(
			'model',
			esc_html__( 'OpenAI Model', 'jeg-ai-assistant' ),
			array( $this, 'settings_field' ),
			$this->setting_page_slug,
			$this->setting_page_slug,
			array(
				'id'      => 'model',
				'default' => 'text-davinci-003',
				'choices' => array(
					'gpt-3.5-turbo'      => esc_attr__( 'gpt-3.5-turbo' ),
					'gpt-3.5-turbo-0301' => esc_attr__( 'gpt-3.5-turbo-0301' ),
					'text-davinci-003'   => esc_attr__( 'text-davinci-003' ),
					'text-davinci-002'   => esc_attr__( 'text-davinci-002' ),
					'text-curie-001'     => esc_attr__( 'text-curie-001' ),
					'text-babbage-001'   => esc_attr__( 'text-babbage-001' ),
					'text-ada-001'       => esc_attr__( 'text-ada-001' ),
				),
				'type'    => 'select',
				'desc'    => sprintf(
					__( 'Select the OpenAI model that best suits your content generation needs. Learn more about <a href="%s">OpenAI Models</a>.', 'jeg-ai-assistant' ),
					'https://platform.openai.com/docs/models/gpt-3'
				),
			)
		);
	}

	public function settings_field( $args ) {
		$default = array(
			'id'      => '',
			'desc'    => '',
			'default' => '',
			'type'    => 'text',
		);
		$args    = wp_parse_args( $args, $default );

		$options = get_option( $this->option_name );
		$value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : '';
		$value   = empty( $value ) ? $args['default'] : $value;

		switch ( $args['type'] ) {
			case 'status':
				$status = 'failed';
				$text   = $args[ $status ];
				if ( isset( $options[ $args['status_option'] ] ) && ! empty( $options[ $args['status_option'] ] ) ) {
					$status = 'success';
					$text   = $args[ $status ];
				}
				echo "<div class='api_connect'><div class='status-option-wrapper'><div class='status-option status-{$args['status_option']} status-option-{$status}'><span class='status-dot'></span><span class='status-text'>{$text}</span></div></div><a href='" . $args['api_url'] . "' class='button'>" . $args['button_text'] . '</a>' . '</div>'; //phpcs:ignore WordPress.Security
				break;
			case 'select':
				$option_select = '';
				if ( isset( $args['choices'] ) && is_array( $args['choices'] ) ) {
					foreach ( $args['choices'] as $option_value => $label ) {
						$selected       = $option_value === $value ? "selected='selected'" : '';
						$option_select .= "<option {$selected} value='{$option_value}'>{$label}</option>";
					}
				}
				echo "<select id='" . $args['id'] . "' name='" . $this->option_name . '[' . $args['id'] . "]'>{$option_select}</select>"; //phpcs:ignore WordPress.Security
				break;
			case 'number':
				echo "<input class='small-text' id='" . $args['id'] . "' name='" . $this->option_name . '[' . $args['id'] . "]' " . ( isset( $args['step'] ) ? "step='{$args['step']}'" : '' ) . ' ' . ( isset( $args['min'] ) ? "min='{$args['min']}'" : '' ) . ' ' . ( isset( $args['max'] ) ? "max='{$args['max']}'" : '' ) . " type='{$args['type']}' value='" . $value . "' />"; //phpcs:ignore WordPress.Security
				break;
			case 'password':
			case 'text':
			default:
				echo "<input id='" . $args['id'] . "' name='" . $this->option_name . '[' . $args['id'] . "]' size='40' type='{$args['type']}' value='" . $value . "' />"; //phpcs:ignore WordPress.Security
				break;
		}

		if ( isset( $args['desc'] ) ) {
			echo "<p class='description'>" . $args['desc'] . '</p>'; //phpcs:ignore WordPress.Security
		}
	}

	/**
	 * Load setting page
	 */
	public function setting_page() {
		load_template( JEG_AI_ASSISTANT_DIR . 'template-parts/dashboard/setting.php', true, $this->setting_page_slug );
	}
}
