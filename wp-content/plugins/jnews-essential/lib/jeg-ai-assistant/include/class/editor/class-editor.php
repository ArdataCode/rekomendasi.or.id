<?php
/**
 * JEG AI Class
 *
 * @author Jegtheme
 * @since 0.0.1
 * @package jeg-ai-assistant
 */

namespace JEG\AI_ASSISTANT\Editor;

/**
 * Class Editor
 *
 * @package JEG\AI_ASSISTANT\Editor
 */
class Editor {
	/**
	 * Editor constructor.
	 */
	public function __construct() {
		add_action( 'admin_footer', array( $this, 'register_root' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend' ) );
	}

	/**
	 * Register Javascript Script
	 */
	public function register_script() {
		// Register & Enqueue Style.
	}

	/**
	 * Add root div
	 */
	public function register_root() {
		?>
		<div id='jeg-ai-assistant-editor-root'></div><div id='jeg-ai-assistant-editor-error'></div>
		<div id='jeg-ai-assistant-classic-root'></div>
		<?php
	}

	/**
	 * Enqueue Backend Font
	 */
	public function enqueue_backend( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook || 'site-editor.php' === $hook ) {
			$include = include_once JEG_AI_ASSISTANT_DIR . '/lib/dependencies/editor.asset.php';

			wp_register_style(
				'fontawesome-jeg-ai-assistant',
				JEG_AI_ASSISTANT_URL . '/assets/fontawesome/css/all.css',
				null,
				JEG_AI_ASSISTANT_VERSION
			);

			wp_enqueue_script(
				'jeg-ai-assistant-editor',
				JEG_AI_ASSISTANT_URL . '/assets/js/admin/editor.js',
				$include['dependencies'],
				JEG_AI_ASSISTANT_VERSION,
				true
			);

			wp_set_script_translations( 'jeg-ai-assistant-editor', 'jeg-ai-assistant', JEG_AI_ASSISTANT_LANG_DIR );

			wp_enqueue_style(
				'jeg-ai-assistant-editor',
				JEG_AI_ASSISTANT_URL . '/assets/css/admin/editor.css',
				array( 'fontawesome-jeg-ai-assistant' ),
				JEG_AI_ASSISTANT_VERSION
			);

			wp_enqueue_style(
				'jeg-ai-assistant-classic-editor',
				JEG_AI_ASSISTANT_URL . '/assets/css/classic/editor.css',
				array( 'fontawesome-jeg-ai-assistant' ),
				JEG_AI_ASSISTANT_VERSION
			);

			wp_enqueue_style(
				'jeg-ai-assistant-editor-fonts',
				'https://fonts.googleapis.com/css2?family=Inter:wght@500&display=swap',
				array(),
				JEG_AI_ASSISTANT_VERSION
			);

			$this->inline_scripts(
				array(
					'JEGAIConfig' => $this->config(),
				),
				'jeg-ai-assistant-editor'
			);
		}
	}

	/**
	 * AI Writer config.
	 *
	 * @return array
	 */
	private function config() {
		$config = array();

		$config['settings']         = $this->get_ai_writer_options();
		$config['pluginVersion']    = JEG_AI_ASSISTANT_VERSION;
		$config['server']           = JEG_AI_ASSISTANT_SERVER;
		$config['settingsPage']     = admin_url( 'admin.php?page=jeg-ai-setting' );

		return $config;
	}

	/**
	 * Generate and Register inline scripts
	 *
	 * @param array  $objects List Object.
	 * @param string $handle Name of script to add the inline script data.
	 */
	private function inline_scripts( $objects, $handle ) {
		if ( is_array( $objects ) ) {
			$script = '';
			foreach ( $objects as $object_name => $data ) {
				$script .= "var $object_name = " . wp_json_encode( $data ) . ';';
			}
			wp_add_inline_script( $handle, $script, 'before' );
		}
	}

	/**
	 * Get all AI Writer option from Dashboard
	 *
	 * @param array $default Default options.
	 *
	 * @return array
	 */
	private function get_ai_writer_options( $default = array() ) {
		$value = get_option( 'jeg-ai-setting', $default );
		$value = array_merge( JEG_AI_Assistant()->defaults, $value );
		return apply_filters( 'jeg_ai_option_ai_assistant', $value );
	}
}
