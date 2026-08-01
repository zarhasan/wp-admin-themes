<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wp_admin_theme' );
delete_option( 'wp_admin_primary_color' );

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'wp_admin_theme' );
		delete_option( 'wp_admin_primary_color' );
		restore_current_blog();
	}
}
