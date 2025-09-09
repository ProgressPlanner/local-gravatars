<?php
/**
 * Plugin Name: Local Gravatars
 * Plugin URI: https://github.com/aristath/local-gravatars
 * Description: Locally host gravatars - for the privacy concious
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Version: 1.1.2
 * Author: Ari Stathopoulos
 * Text Domain: local-gravatars
 *
 * @package aristath/local-gravatars
 * @license https://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/includes/class-local-gravatars.php';

use Aristath\LocalGravatars\LocalGravatars;

add_filter(
	'get_avatar',
	/**
	 * Filters the HTML for a user's avatar.
	 *
	 * @param string $avatar HTML for the user's avatar.
	 * @return string
	 */
	function( $avatar ) {
		preg_match_all( '/srcset=["\']?((?:.(?!["\']?\s+(?:\S+)=|\s*\/?[>"\']))+.)["\']?/', $avatar, $srcset );
		if ( isset( $srcset[1] ) && isset( $srcset[1][0] ) ) {
			$url             = explode( ' ', $srcset[1][0] )[0];
			$local_gravatars = new LocalGravatars( $url );
			$avatar          = str_replace( $url, $local_gravatars->get_gravatar(), $avatar );
		}

		preg_match_all( '/src=["\']?((?:.(?!["\']?\s+(?:\S+)=|\s*\/?[>"\']))+.)["\']?/', $avatar, $src );
		if ( isset( $src[1] ) && isset( $src[1][0] ) ) {
			$url             = explode( ' ', $src[1][0] )[0];
			$local_gravatars = new LocalGravatars( $url );
			$avatar          = str_replace( $url, $local_gravatars->get_gravatar(), $avatar );
		}
		return $avatar;
	},
	\PHP_INT_MAX
);
