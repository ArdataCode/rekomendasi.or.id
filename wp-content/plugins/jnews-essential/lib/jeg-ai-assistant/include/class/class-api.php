<?php
/**
 * @author Jegtheme
 */

namespace JEG\AI_ASSISTANT;

use WP_Query;

/**
 * Class JEG AI Assistant API
 */
class Api {
	/**
	 * Endpoint Path
	 *
	 * @var string
	 */
	const ENDPOINT = 'jeg-ai-assistant-client/v1';

	/**
	 * Blocks constructor.
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Register APIs
	 */
	private function register_routes() {
		register_rest_route(
			self::ENDPOINT,
			'import/images',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'import_images' ),
				'permission_callback' => '__return_true',
			)
		);
	}



	/**
	 * Import Images
	 *
	 * @param object $request images.
	 */
	public function import_images( $request ) {
		$images = $request->get_param( 'images' );
		$array  = array();

		/**
		 * Temporarily increase time limit for import.
		 * Default 30s is not enough for importing long content.
		 */
		set_time_limit( 300 );

		foreach ( $images as $image ) {
			$data = $this->check_image_exist( $image );
			if ( ! $data ) {
				$data = $this->handle_file( $image );
			}
			$array[] = $data;
		}

		return array(
			'images' => $array,
		);
	}

	/**
	 * Return image
	 *
	 * @param string $url Image attachment url.
	 *
	 * @return array|null
	 */
	public function check_image_exist( $url ) {
		$attachments = new WP_Query(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'meta_query'  => array(
					array(
						'key'     => '_import_source',
						'value'   => $url,
						'compare' => 'LIKE',
					),
				),
			)
		);

		foreach ( $attachments->posts as $post ) {
			$attachment_url = wp_get_attachment_url( $post->ID );
			return array(
				'id'  => $post->ID,
				'url' => $attachment_url,
			);
		}

		return $attachments->posts;
	}

	/**
	 * Handle Import file, and return File ID when process complete
	 *
	 * @param string $url URL of file.
	 *
	 * @return int|null
	 */
	public function handle_file( $url ) {
		$file_name = basename( $url );
		$file_type = wp_check_filetype( $file_name );
		if ( ! $file_type['type'] ) {
			$file_name = $this->generate_random_string() . '.png';
		}
		$upload = wp_upload_bits( $file_name, null, '' );
		$this->fetch_file( $url, $upload['file'] );

		if ( $upload['file'] ) {
			$file_loc  = $upload['file'];
			$file_name = basename( $upload['file'] );
			$file_type = wp_check_filetype( $file_name );

			$attachment = array(
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			include_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_id = wp_insert_attachment( $attachment, $file_loc );
			update_post_meta( $attach_id, '_import_source', $url );

			try {
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			} catch ( \Exception $e ) {
				$this->handle_exception( $e );
			} catch ( \Throwable $t ) {
				$this->handle_exception( $e );
			}

			return array(
				'id'  => $attach_id,
				'url' => $upload['url'],
			);
		} else {
			return null;
		}
	}

	/**
	 * Handle Exception.
	 *
	 * @param \Exception $e Exception.
	 */
	public function handle_exception( $e ) {
		// Empty Exception.
	}

	/**
	 * Download file and save to file system
	 *
	 * @param string $url File URL.
	 * @param string $file_path file path.
	 *
	 * @return array|bool
	 */
	public function fetch_file( $url, $file_path ) {
		$http     = new \WP_Http();
		$response = $http->get(
			add_query_arg(
				array(
					'plugin_version' => JEG_AI_ASSISTANT_VERSION,
				),
				$url
			),
			array(
				'timeout' => 300,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$headers             = wp_remote_retrieve_headers( $response );
		$headers['response'] = wp_remote_retrieve_response_code( $response );

		if ( false === $file_path ) {
			return $headers;
		}

		// GET request - write it to the supplied filename.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents( $file_path, wp_remote_retrieve_body( $response ), FS_CHMOD_FILE );

		return $headers;
	}

	/**
	 * Generate random string
	 *
	 * @param int $length Length of string behind date
	 *
	 * @return string
	 */
	private function generate_random_string( $length = 10 ) {
		$date   = gmdate( 'Ymd' );
		$string = $date;
		if ( function_exists( 'jeg_generate_random_string' ) ) {
			$string .= '-' . jeg_generate_random_string( $length );
		}
		return $string;
	}
}
