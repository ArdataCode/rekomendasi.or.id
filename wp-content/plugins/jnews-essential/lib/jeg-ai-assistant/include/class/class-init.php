<?php
/**
 * @author Jegtheme
 */

namespace JEG\AI_ASSISTANT;

use JEG\AI_ASSISTANT\Editor\Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JEG AI Assistant Init
 */
class Init {
	/**
	 * Instance of Init
	 *
	 * @var Init
	 */
	private static $instance;

	/**
	 * Hold instance of Api
	 *
	 * @var Api
	 */
	public $api;

	/**
	 * Hold instance of Editor
	 *
	 * @var Editor
	 */
	public $editor;

	/**
	 * Hold instance of Dashboard
	 *
	 * @var Dashboard
	 */
	public $dashboard;

	/**
	 * View counter options
	 *
	 * @var array
	 */
	public $defaults = array(
		'key'               => '',
		'advanced'          => false,
		'temperature'       => '0.7',
		'max_tokens'        => '4000',
		'top_p'             => '1',
		'best_of'           => '1',
		'frequency_penalty' => '0.01',
		'presence_penalty'  => '0.01',
		'image_size'        => '1024x1024',
	);

	/**F
	 * Disable object cloning.
	 */
	public function __clone() {}

	/**
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {}

	/**
	 * Instance of Init JEG AI Assistant
	 *
	 * @return Init
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Init ) ) {
			self::$instance = new Init();
		}
		return self::$instance;
	}

	/**
	 * Construct of JEG AI Assistant
	 */
	private function __construct() {
		$this->load_plugin_textdomain();
		$this->init_instance();
		$this->init_hook();
	}

	/**
	 * Initialize Instance.
	 */
	public function init_instance() {
		$this->editor = new Dashboard();
		$this->editor = new Editor();
	}

	/**
	 * Initialize API
	 */
	public function init_api() {
		$this->api = new Api();
	}

	/**
	 * Init Hook
	 */
	public function init_hook() {
		add_action( 'rest_api_init', array( $this, 'init_api' ) );
		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_button' ) );
	}

	/**
	 * Tinymce Plugin.
	 * 
	 * @param array $plugin_array Plugin Array.
	 * 
	 * @return array
	 */
	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array['jegai'] = JEG_AI_ASSISTANT_URL . '/assets/js/admin/classic.js';
		return $plugin_array;
	}

	/**
	 * Register Button.
	 * 
	 * @param array $buttons Plugin Button.
	 * 
	 * @return array
	 */
	public function register_button( $buttons ) {
		array_push( $buttons, 'jegai_rephrase');
		array_push( $buttons, 'jegai_expand');
		array_push( $buttons, 'jegai_command');
		return $buttons;
	}

	/**
	 * Load textdomain
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( JEG_AI_ASSISTANT, false, JEG_AI_ASSISTANT_LANG_DIR );
	}
}
