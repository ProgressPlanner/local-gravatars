<?php
/**
 * Uninstall the plugin.
 *
 * Deletes the custom WP cron job.
 *
 * @package aristath/local-gravatars
 */

use Aristath\LocalGravatars\LocalGravatars;

require_once __DIR__ . '/includes/class-local-gravatars.php';

// If uninstall not called from WordPress, then exit.
if ( ! \defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the gravatars folder.
$local_gravatars = new LocalGravatars( '' );
$local_gravatars->delete_gravatars_folder();

// Clear the cron job.
wp_unschedule_event( wp_next_scheduled( 'delete_gravatars_folder' ), 'delete_gravatars_folder' );
