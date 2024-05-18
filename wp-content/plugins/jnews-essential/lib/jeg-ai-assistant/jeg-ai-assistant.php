<?php
/*
	Plugin Name: JegAI Assistant
	Plugin URI: http://jegtheme.com/
	Description: Increase Productivity using Power of AI by generating high quality content for title, content article, subheading, and any other.
	Version: 1.2.1
	Author: Jegtheme
	Author URI: http://jegtheme.com
	Network: false
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'JEG_AI_ASSISTANT' ) || define( 'JEG_AI_ASSISTANT', 'jeg-ai-assistant' );
defined( 'JEG_AI_ASSISTANT_VERSION' ) || define( 'JEG_AI_ASSISTANT_VERSION', '1.2.1' );
defined( 'JEG_AI_ASSISTANT_FILE' ) || define( 'JEG_AI_ASSISTANT_FILE', __FILE__ );
defined( 'JEG_AI_ASSISTANT_URL' ) || define( 'JEG_AI_ASSISTANT_URL', plugins_url( JEG_AI_ASSISTANT ) );
defined( 'JEG_AI_ASSISTANT_DIR' ) || define( 'JEG_AI_ASSISTANT_DIR', plugin_dir_path( JEG_AI_ASSISTANT_FILE ) );
defined( 'JEG_AI_ASSISTANT_CLASSPATH' ) || define( 'JEG_AI_ASSISTANT_CLASSPATH', JEG_AI_ASSISTANT_DIR . 'include/class/' );
defined( 'JEG_AI_ASSISTANT_LANG_DIR' ) || define( 'JEG_AI_ASSISTANT_LANG_DIR', JEG_AI_ASSISTANT_DIR . '/languages/' );
defined( 'JEG_AI_ASSISTANT_SERVER' ) || define( 'JEG_AI_ASSISTANT_SERVER', 'https://support.jegtheme.com' );

require_once JEG_AI_ASSISTANT_DIR . 'include/autoload.php';

if ( ! function_exists( 'JEG_AI_Assistant' ) ) {
	/**
	 * Initialise JEG AI Assistant
	 *
	 * @return JEG\AI_ASSISTANT\Init
	 */
	function JEG_AI_Assistant() {
		static $instance;

		// First call to instance() initializes the plugin.
		if ( null === $instance || ! ( $instance instanceof JEG\AI_ASSISTANT\Init ) ) {
			$instance = JEG\AI_ASSISTANT\Init::instance();
		}

		return $instance;
	}
}
JEG_AI_Assistant();
