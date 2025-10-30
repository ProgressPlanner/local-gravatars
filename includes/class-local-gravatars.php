<?php
/**
 * Gravatars class.
 *
 * @package aristath/local-gravatars
 */

namespace Aristath\LocalGravatars;

/**
 * Download gravatars locally.
 */
class LocalGravatars {

	/**
	 * The remote URL.
	 *
	 * @access protected
	 * @since 1.1.0
	 * @var string
	 */
	protected $remote_url;

	/**
	 * Base path.
	 *
	 * @access protected
	 * @since 1.1.0
	 * @var string
	 */
	protected $base_path;

	/**
	 * Base URL.
	 *
	 * @access protected
	 * @since 1.1.0
	 * @var string
	 */
	protected $base_url;

	/**
	 * Cleanup routine frequency.
	 */
	const CLEANUP_FREQUENCY = 'weekly';

	/**
	 * Maximum process seconds.
	 *
	 * @since 1.0
	 */
	const MAX_PROCESS_TIME = 5;

	/**
	 * Start time of all processes.
	 *
	 * @static
	 *
	 * @access private
	 *
	 * @since 1.0.1
	 *
	 * @var int
	 */
	private static $start_time;

	/**
	 * Set to true if we want to stop processing.
	 *
	 * @static
	 *
	 * @access private
	 *
	 * @since 1.0.1
	 *
	 * @var bool
	 */
	private static $has_stopped = false;

	/**
	 * Constructor.
	 *
	 * Get a new instance of the object for a new URL.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $url The remote URL.
	 */
	public function __construct( $url = '' ) {
		$this->remote_url = $url;
	}

	/**
	 * Initialize hooks and schedules.
	 *
	 * Should be called once during plugin initialization.
	 *
	 * @access public
	 * @since 1.1.3
	 * @return void
	 */
	public static function init() {
		static $initialized = false;

		if ( $initialized ) {
			return;
		}

		$initialized = true;

		// Add a cleanup routine.
		$instance = new self();
		$instance->schedule_cleanup();
		add_action( 'delete_gravatars_folder', array( $instance, 'delete_gravatars_folder' ) );
	}

	/**
	 * Get the local file's URL.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_gravatar() {

		// Early exit if we don't want to process.
		if ( ! $this->should_process() ) {
			return $this->get_fallback_url();
		}

		// Validate that this is a Gravatar URL.
		if ( ! $this->is_valid_gravatar_url( $this->remote_url ) ) {
			return $this->get_fallback_url();
		}

		// If the gravatars folder doesn't exist, create it.
		if ( ! file_exists( $this->get_base_path() ) ) {
			$this->get_filesystem()->mkdir( $this->get_base_path(), FS_CHMOD_DIR );
		}

		// Get the base filename without extension.
		$base_filename = basename( wp_parse_url( $this->remote_url, PHP_URL_PATH ) );

		// Remove any existing extension to ensure we detect the correct one.
		$base_filename = pathinfo( $base_filename, PATHINFO_FILENAME );

		// Sanitize the filename to prevent directory traversal.
		$base_filename = sanitize_file_name( $base_filename );

		// Early exit if sanitization removed everything.
		if ( empty( $base_filename ) ) {
			return $this->get_fallback_url();
		}

		// Check if the file already exists with any common extension.
		$existing_file = $this->find_existing_avatar_file( $base_filename );
		if ( $existing_file ) {
			return $this->get_base_url() . '/' . $existing_file;
		}

		// require file.php if the download_url function doesn't exist.
		if ( ! function_exists( 'download_url' ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
		}

		// Download file to temporary location.
		$tmp_path = download_url( $this->remote_url );

		// Make sure there were no errors.
		if ( ! is_wp_error( $tmp_path ) ) {
			// Detect file extension from the downloaded file.
			$file_extension = $this->detect_file_extension( $tmp_path );
			$filename = $base_filename . '.' . $file_extension;
			$path = $this->get_base_path() . '/' . $filename;

			// Move temp file to final destination.
			$success = $this->get_filesystem()->move( $tmp_path, $path, true );
			if ( ! $success ) {
				return $this->get_fallback_url();
			}

			// Return the URL to the local file.
			return $this->get_base_url() . '/' . $filename;
		}

		// If we got here, download failed.
		return $this->get_fallback_url();
	}

	/**
	 * Get the base path.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return string
	 */
	public function get_base_path() {
		if ( ! $this->base_path ) {
			$this->base_path = apply_filters(
				'get_local_gravatars_base_path',
				$this->get_filesystem()->wp_content_dir() . '/gravatars'
			);
		}
		return $this->base_path;
	}

	/**
	 * Get the base URL.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return string
	 */
	public function get_base_url() {
		if ( ! $this->base_url ) {
			$this->base_url = apply_filters(
				'get_local_gravatars_base_url',
				content_url() . '/gravatars'
			);
		}
		return $this->base_url;
	}

	/**
	 * Schedule a cleanup.
	 *
	 * Deletes the gravatars files on a regular basis.
	 * This way gravatars will get updated regularly,
	 * and we avoid edge cases where unused files remain in the server.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return void
	 */
	public function schedule_cleanup() {
		if ( ! is_multisite() || ( is_multisite() && is_main_site() ) ) {
			if ( ! wp_next_scheduled( 'delete_gravatars_folder' ) && ! wp_installing() ) {
				wp_schedule_event(
					time(),
					apply_filters( 'get_local_gravatars_cleanup_frequency', self::CLEANUP_FREQUENCY ),
					'delete_gravatars_folder'
				);
			}
		}
	}

	/**
	 * Delete the gravatars folder.
	 *
	 * This runs as part of a cleanup routine.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return bool
	 */
	public function delete_gravatars_folder() {
		return $this->get_filesystem()->delete( $this->get_base_path(), true );
	}

	/**
	 * Get the filesystem.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return WP_Filesystem
	 */
	protected function get_filesystem() {
		global $wp_filesystem;

		// If the filesystem has not been instantiated yet, do it here.
		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}
			\WP_Filesystem();
		}
		return $wp_filesystem;
	}

	/**
	 * Should we process or not?
	 *
	 * @access public
	 *
	 * @since 1.0.1
	 *
	 * @return bool
	 */
	public function should_process() {

		// Early exit if we've already determined we want to stop.
		if ( self::$has_stopped ) {
			return false;
		}

		// Set the start time.
		if ( ! self::$start_time ) {
			self::$start_time = time();
		}

		// Return false if we've got over the max time limit.
		if ( time() > self::$start_time + $this->get_max_process_time() ) {
			self::$has_stopped = true;
			return false;
		}

		// Fallback to true.
		return true;
	}

	/**
	 * Get maximum process time in seconds.
	 *
	 * @access public
	 *
	 * @since 1.0.1
	 *
	 * @return int
	 */
	public function get_max_process_time() {
		return apply_filters( 'get_local_gravatars_max_process_time', self::MAX_PROCESS_TIME );
	}

	/**
	 * Get fallback image
	 *
	 * @access public
	 *
	 * @since 1.0.1
	 *
	 * @return string
	 */
	public function get_fallback_url() {
		return apply_filters( 'get_local_gravatars_fallback_url', '', $this->remote_url );
	}

	/**
	 * Validate if a URL is a valid Gravatar URL.
	 *
	 * @access private
	 *
	 * @since 1.1.3
	 *
	 * @param string $url The URL to validate.
	 * @return bool True if valid Gravatar URL, false otherwise.
	 */
	private function is_valid_gravatar_url( $url ) {
		// Parse the URL.
		$parsed_url = wp_parse_url( $url );

		// Check if we have a host.
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$host = strtolower( $parsed_url['host'] );

		// Check if the host is gravatar.com or any subdomain (*.gravatar.com).
		// This covers: gravatar.com, www.gravatar.com, secure.gravatar.com, 0-9.gravatar.com, etc.
		$is_gravatar = ( 'gravatar.com' === $host || substr( $host, -13 ) === '.gravatar.com' );

		// Allow filtering for edge cases.
		return apply_filters( 'local_gravatars_is_valid_url', $is_gravatar, $url, $host );
	}

	/**
	 * Detect file extension from downloaded file.
	 *
	 * @access private
	 *
	 * @since 1.1.3
	 *
	 * @param string $file_path Path to the downloaded file.
	 * @return string File extension (defaults to 'jpg' if detection fails).
	 */
	private function detect_file_extension( $file_path ) {

		// Early exit if file doesn't exist.
		if ( ! file_exists( $file_path ) ) {
			return 'jpg';
		}

		// Get file info using finfo.
		$finfo = finfo_open( FILEINFO_MIME_TYPE );

		// Check if finfo_open failed.
		if ( false === $finfo ) {
			return 'jpg';
		}

		$mime_type = finfo_file( $finfo, $file_path );
		finfo_close( $finfo );

		// Check if finfo_file failed.
		if ( false === $mime_type ) {
			return 'jpg';
		}

		$mime_to_extension = [
			'image/jpeg' => 'jpg',
			'image/jpg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
			'image/svg+xml' => 'svg',
			'image/bmp' => 'bmp',
			'image/tiff' => 'tiff',
		];

		// Return detected extension or default to jpg.
		return isset( $mime_to_extension[ $mime_type ] ) ? $mime_to_extension[ $mime_type ] : 'jpg';
	}

	/**
	 * Find existing avatar file with any common extension.
	 *
	 * @access private
	 *
	 * @since 1.1.3
	 *
	 * @param string $base_filename Base filename without extension.
	 * @return string|false Existing filename with extension, or false if not found.
	 */
	private function find_existing_avatar_file( $base_filename ) {

		// Common image extensions to check.
		$extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff' ];

		// Check each extension.
		foreach ( $extensions as $ext ) {
			$filename = $base_filename . '.' . $ext;
			$file_path = $this->get_base_path() . '/' . $filename;

			if ( file_exists( $file_path ) ) {
				return $filename;
			}
		}

		return false;
	}
}
