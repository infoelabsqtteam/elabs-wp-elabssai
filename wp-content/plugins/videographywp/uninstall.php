<?php
if( !defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN') ){
	die();
}

$options = get_option( 'cvwp_plugin_options' );
if( isset( $options['settings']['complete_uninstall'] ) && $options['settings']['complete_uninstall'] ){
	// 1. delete plugin option
	delete_option('cvwp_plugin_options');
	
	// 2. Remove Video Options from posts
	global $wpdb;
	$query = "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_cvwp_video_settings'";
	$wpdb->query( $query );
}