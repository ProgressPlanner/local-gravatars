<?php
/**
 * Plugin Name: Local Gravatars
 * Plugin URI: https://github.com/aristath/local-gravatars
 * Description: Locally host gravatars - for the privacy conscious
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Version: 1.1.3
 * Author: Ari Stathopoulos
 * Text Domain: local-gravatars
 *
 * @package aristath/local-gravatars
 * @license https://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/includes/class-local-gravatars.php';

use Aristath\LocalGravatars\LocalGravatars;

// Initialize the plugin hooks and schedules.
LocalGravatars::init();

/**
 * Process a URL and replace it with the local gravatar URL.
 *
 * @param string $url The URL to process.
 * @return string The local gravatar URL or the original URL if processing failed.
 */
function local_gravatars_process_url( $url ) {
	$local_gravatars = new LocalGravatars( $url );
	return $local_gravatars->get_gravatar();
}

add_filter(
	'get_avatar',
	/**
	 * Filters the HTML for a user's avatar.
	 *
	 * @param string $avatar HTML for the user's avatar.
	 * @return string
	 */
	function( $avatar ) {
		// Process srcset attribute.
		preg_match_all( '/srcset=["\']?((?:.(?!["\']?\s+(?:\S+)=|\s*\/?[>"\']))+.)["\']?/', $avatar, $srcset );
		if ( isset( $srcset[1] ) && isset( $srcset[1][0] ) ) {
			$url    = explode( ' ', $srcset[1][0] )[0];
			$avatar = str_replace( $url, local_gravatars_process_url( $url ), $avatar );
		}

		// Process src attribute.
		preg_match_all( '/src=["\']?((?:.(?!["\']?\s+(?:\S+)=|\s*\/?[>"\']))+.)["\']?/', $avatar, $src );
		if ( isset( $src[1] ) && isset( $src[1][0] ) ) {
			$url    = explode( ' ', $src[1][0] )[0];
			$avatar = str_replace( $url, local_gravatars_process_url( $url ), $avatar );
		}
		return $avatar;
	},
	\PHP_INT_MAX
);
